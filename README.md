# emergence-slack
Enable an emergence site to integrate with a Slack team

## Features

- Single Sign-On via SAML2 (requires Slack Plus)

## Installing

1. Install and configure [emergence-saml2](https://github.com/JarvusInnovations/emergence-saml2)
2. Copy `php-config/Git.config.d/emergence-slack.php` into your instance
3. Visit **/site-admin/sources** and initialize the emergence-slack repository.
4. Press **Sync->Update emergence VFS** for the emergence-slack repository to copy the contents into your instance
5. Edit `php-config/Emergence/Slack/Connector.config.php` and configure `Emergence\Slack\Connector::$teamHost`
6. Visit `https://mySlackOrg.slack.com/admin/auth/saml` to enable SAML SSO for Slack
7. For the **SAML 2.0 Endpoint** setting, enter `https://myEmergenceSite.org/connectors/slack/login`
8. Leave the **Identity Provider Issuer** setting blank
9. For the **Public Certificate** setting, upload the public certificate generating during emergence-saml2 setup
10. Configure **Advanced Options** and **Customize** as you like
