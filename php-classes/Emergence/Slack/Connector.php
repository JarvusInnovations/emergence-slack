<?php

namespace Emergence\Slack;

use Site;

use Emergence\Connectors\AbstractConnector;
use Emergence\Connectors\IIdentityConsumer;
use Emergence\Connectors\IdentityConsumerTrait;
use Emergence\SAML2\Connector AS SAML2Connector;
use Emergence\People\IPerson;


class Connector extends AbstractConnector implements IIdentityConsumer
{
    use IdentityConsumerTrait {
        getSAMLNameId as getDefaultSAMLNameId;
        handleRequest as handleIdentityConsumerRequest;
    }


    public static $teamHost;

    public static $title = 'Slack';
    public static $connectorId = 'slack';


    public static function handleRequest($action = null)
    {
        switch ($action ?: $action = static::shiftPath()) {
            default:
                return static::handleIdentityConsumerRequest($action);
        }
    }

    public static function handleLaunchRequest()
    {
        $GLOBALS['Session']->requireAuthentication();
        
        Site::redirect('https://'.static::$teamHost.'/sso/saml/start?redir=%2Fmessages%2F'.urlencode(!empty($_GET['channel']) ? $_GET['channel'] : 'general').'%2F');
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