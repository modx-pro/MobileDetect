{set $mdPageUrl = $_modx->resource.id | url : ['scheme' => 'abs']}
{set $mdDevice = $_modx->getPlaceholder('mobiledetect.device') ?: ''}
{set $mdGetBrowser = $.get.browser ?: ''}
<nav class="mobiledetect-switch" aria-label="Режим отображения">
    <a href="{$mdPageUrl}?browser=standard"{if $mdDevice == 'standard'} class="is-active"{/if}>Desktop</a>
    <a href="{$mdPageUrl}?browser=tablet"{if $mdDevice == 'tablet'} class="is-active"{/if}>Tablet</a>
    <a href="{$mdPageUrl}?browser=mobile"{if $mdDevice == 'mobile'} class="is-active"{/if}>Mobile</a>
    <a href="{$mdPageUrl}?browser=detect"{if $mdGetBrowser == 'detect'} class="is-active"{/if}>Auto</a>
</nav>
