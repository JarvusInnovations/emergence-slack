{extends "designs/site.tpl"}

{block "title"}Slack Connection Status &mdash; {$dwoo.parent}{/block}

{block "content"}
    <header class="page-header">
        <h2 class="header-title">Slack Connection Status</h2>
	</header>

    {if $.User->hasAccountLevel('Administrator')}
    <section id="configuration">
        <h3>Configuration</h3>

        <dl>
            <dt><code>Emergence\Slack\Connector::$teamHost</code></dt>
            <dd>
                {if Emergence\Slack\Connector::$teamHost}
                    <q>{Emergence\Slack\Connector::$teamHost|escape}</q>
                {else}
                    <em>Not configured</em>
                {/if}
            </dd>

            <dt><code>Emergence\Slack\API::$clientId</code></dt>
            <dd>
                {if Emergence\Slack\API::$clientId}
                    <q>{Emergence\Slack\API::$clientId|escape}</q>
                {else}
                    <em>Not configured</em>
                {/if}
            </dd>

            <dt><code>Emergence\Slack\API::$clientSecret</code></dt>
            <dd>
                {if Emergence\Slack\API::$clientSecret}
                    <q>{Emergence\Slack\API::$clientSecret|escape}</q>
                {else}
                    <em>Not configured</em>
                {/if}
            </dd>

            <dt><code>Emergence\Slack\API::$verificationToken</code></dt>
            <dd>
                {if Emergence\Slack\API::$verificationToken}
                    <q>{Emergence\Slack\API::$verificationToken|escape}</q>
                {else}
                    <em>Not configured</em>
                {/if}
            </dd>

            <dt><code>Emergence\Slack\API::$accessToken</code></dt>
            <dd>
                {if Emergence\Slack\API::$accessToken}
                    <q>{Emergence\Slack\API::$accessToken|escape}</q>
                {else}
                    <em>Not configured</em>
                {/if}
            </dd>
        </dl>
    </section>
    {/if}

    <section id="team">
        <h3>Slack Team</h3>

        {if Emergence\Slack\Connector::$teamHost}
            <a href="/connectors/slack/launch" class="button">Launch {Emergence\Slack\Connector::$teamHost|escape}</a>
        {else}
            <em>No Slack team has been configured yet</em>
        {/if}
    </section>
{/block}