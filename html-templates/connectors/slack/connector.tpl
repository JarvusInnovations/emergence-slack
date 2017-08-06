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
            <dt><code>Emergence\Slack\API::$teamHost</code></dt>
            <dd>
                {if Emergence\Slack\API::$teamHost}
                    <q>{Emergence\Slack\API::$teamHost|escape}</q>
                {else}
                    <em>Not configured</em>
                {/if}
            </dd>
        </dl>
    </section>
    {/if}

    <section id="team">
        <h3>Slack Team</h3>

        {if Emergence\Slack\API::$teamHost}
            <a href="/connectors/slack/launch" class="button">Launch {Emergence\Slack\API::$teamHost|escape}</a>
        {else}
            <em>No Slack team has been configured yet</em>
        {/if}
    </section>
{/block}