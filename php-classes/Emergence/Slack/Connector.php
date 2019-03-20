<?php

namespace Emergence\Slack;

use Site;
use JSON;

use Emergence\Connectors\AbstractConnector;
use Emergence\Connectors\IIdentityConsumer;
use Emergence\Connectors\IdentityConsumerTrait;
use Emergence\EventBus;
use Emergence\People\IPerson;
use Emergence\SAML2\Connector AS SAML2Connector;


class Connector extends AbstractConnector implements IIdentityConsumer
{
    use IdentityConsumerTrait {
        getSAMLNameId as getDefaultSAMLNameId;
        handleRequest as handleIdentityConsumerRequest;
    }


    public static $teamHost;
    public static $defaultChannel = 'general';
    public static $inviteAccountLevel = false;
    public static $legacyToken; // obtain from https://api.slack.com/custom-integrations/legacy-tokens

    public static $title = 'Slack';
    public static $connectorId = 'slack';


    public static function handleRequest($action = null)
    {
        switch ($action ?: $action = static::shiftPath()) {
            case 'webhooks':
                return static::handleWebhooksRequest();
            case 'auth':
                return static::handleAuthRequest();
            case 'auth-return':
                return static::handleAuthReturnRequest();
            case 'invite':
                return static::handleInviteRequest();
            default:
                return static::handleIdentityConsumerRequest($action);
        }
    }

    public static function handleLaunchRequest()
    {
        $GLOBALS['Session']->requireAuthentication();

        if (!empty($_GET['channel'])) {
            $channel = $_GET['channel'];
        } elseif (!$channel = static::shiftPath()) {
            $channel = static::$defaultChannel;
        }

        Site::redirect('https://'.static::$teamHost.'/sso/saml/start?redir='.urlencode("/messages/$channel/"));
    }

    public static function handleWebhooksRequest()
    {
        $json = JSON::getRequestData();

#        file_put_contents(Site::$rootPath.'/site-data/slack.log', PHP_EOL.PHP_EOL.print_r([
#            'date' => date('Y-m-d H:i:s'),
#            'method' => $_SERVER['REQUEST_METHOD'],
#            'get' => $_GET,
#            'post' => $_POST,
#            'json' => $json
#        ], true), FILE_APPEND);

        if (empty($json['token']) || $json['token'] != API::$verificationToken) {
            throw new \Exception('verification token does not match');
        }

        if (!empty($json['challenge'])) {
            JSON::respond(['challenge' => $json['challenge']]);
        }

        if (!empty($json['event']) && !empty($json['event']['type'])) {
            EventBus::fireEvent($json['event']['type'], __NAMESPACE__, $json['event']);
        }

        return static::respond('webhookReceived', ['success' => true], 'json');
    }

    public static function handleAuthRequest()
    {
        Site::redirect('https://slack.com/oauth/authorize', [
            'client_id' => API::$clientId,
            'scope' => implode(' ', API::getScopes()),
            'redirect_uri' => static::getBaseUrl(true).'/auth-return'
        ]);
    }

    public static function handleAuthReturnRequest()
    {
        throw new \Exception('not implemented');
    }

    public static function handleInviteRequest()
    {
        global $Session;

        if (!static::$inviteAccountLevel || !$Session) {
            return static::throwUnauthorizedError();
        }

        $Session->requireAccountLevel(static::$inviteAccountLevel);

        if (!static::$legacyToken) {
            return static::throwError('Emergence\\Slack\\Connector::$legacyToken must be configured to send invites');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://'.static::$teamHost.'/api/users.admin.invite');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'token' => static::$legacyToken,
            'email' => $Session->Person->Email,
            'first_name' => $Session->Person->FirstName,
            'last_name' => $Session->Person->LastName,
            'resend' => 'true'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


        // execute request
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        // process response
        if (!$response['ok']) {
            $error = 'Failed to send slack invitation';

            if ($response['error']) {
                $error .= ': '.$response['error'];
            }

            return static::throwError($error);
        }

        return static::respond('message', [
            'message' => 'Invitation sent'
        ]);
    }

    /**
    * IdentityConsumer interface methods
    */
    public static function handleLoginRequest(IPerson $Person)
    {
        static::_fireEvent('beforeLogin', [
            'Person' => $Person
        ]);

        return SAML2Connector::handleLoginRequest($Person, __CLASS__);
    }

    public static function getSAMLNameId(IPerson $Person)
    {
        if ($Person->PrimaryEmail) {
            return [
                'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                'Value' => $Person->Username
            ];
        }

        return static::getDefaultSAMLNameId($Person);
    }
}