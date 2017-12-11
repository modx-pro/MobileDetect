<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var MobileDetect $MobileDetect */
switch ($modx->event->name) {
    case 'OnWebPagePrerender':
        $MobileDetect = $modx->getService('mobiledetect', 'MobileDetect', MODX_CORE_PATH . 'components/mobiledetect/');
        if ($MobileDetect && empty($MobileDetect->config['disable_plugin'])) {
            $key = $MobileDetect->config['force_browser_variable'];
            $device = !empty($_GET) && array_key_exists($key, $_GET)
                ? $modx->stripTags($_GET[$key])
                : '';

            $modx->resource->_output = $MobileDetect->parseDocument($modx->resource->_output, $device);
        }
        break;
}