<?php

/** @var modX $modx */
/** @var array $scriptProperties */
/** @var MobileDetect $MobileDetect */

/**
 * @param modX $modx
 * @return MobileDetect|null
 */
if (!function_exists('mobiledetectPluginService')) {
    function mobiledetectPluginService($modx)
    {
    static $service = null;
    static $resolved = false;

    if ($service === null) {
        $service = $modx->getService('mobiledetect', 'MobileDetect', MODX_CORE_PATH . 'components/mobiledetect/');
    }

    if (!$service || !empty($service->config['disable_plugin'])) {
        return null;
    }

    if (!$resolved) {
        $key = $service->config['force_browser_variable'];
        $forced = !empty($_GET) && array_key_exists($key, $_GET)
            ? $modx->stripTags($_GET[$key])
            : '';

        $service->resolveDevice($forced);
        $modx->setPlaceholder('mobiledetect.device', $service->getDeviceType());
        $resolved = true;
    }

    return $service;
    }
}

switch ($modx->event->name) {
    case 'OnWebPagePrerender':
        $MobileDetect = mobiledetectPluginService($modx);
        if ($MobileDetect) {
            $key = $MobileDetect->config['force_browser_variable'];
            $device = !empty($_GET) && array_key_exists($key, $_GET)
                ? $modx->stripTags($_GET[$key])
                : '';

            $MobileDetect->resolveDevice($device);
            $modx->setPlaceholder('mobiledetect.device', $MobileDetect->getDeviceType());
            $modx->resource->_output = $MobileDetect->parseDocument($modx->resource->_output, $device);
        }
        break;

    case 'pdoToolsOnFenomInit':
        $fenom = $modx->event->params['fenom'] ?? null;
        if (!is_object($fenom) || !method_exists($fenom, 'addBlockFunction')) {
            break;
        }

        // pdoTools Fenom autoloads unknown modifiers via snippet; getModifier() is always truthy here.
        if ($fenom->getTag('mobile')) {
            break;
        }

        if (!empty($GLOBALS['mobiledetectFenomRegistering'])) {
            break;
        }
        $GLOBALS['mobiledetectFenomRegistering'] = true;

        $fenom->addModifier('mobiledetect', function ($expected, $current = null) use ($modx) {
            $MobileDetect = mobiledetectPluginService($modx);
            if (!$MobileDetect) {
                return 0;
            }

            if ($current === null) {
                $current = $MobileDetect->getDeviceType();
            }

            return (int)(strtolower((string)$expected) === strtolower((string)$current));
        });

        $fenom->addBlockFunction('mobile', function ($options, $content) use ($modx) {
            $MobileDetect = mobiledetectPluginService($modx);
            if (!$MobileDetect) {
                return '';
            }

            $device = $MobileDetect->getDeviceType();

            if ($device === 'mobile' || $device === 'tablet') {
                return $content;
            }

            return '';
        });
        $fenom->addBlockFunction('phone', function ($options, $content) use ($modx) {
            $MobileDetect = mobiledetectPluginService($modx);
            if (!$MobileDetect || $MobileDetect->getDeviceType() !== 'mobile') {
                return '';
            }

            return $content;
        });
        $fenom->addBlockFunction('tablet', function ($options, $content) use ($modx) {
            $MobileDetect = mobiledetectPluginService($modx);
            if (!$MobileDetect || $MobileDetect->getDeviceType() !== 'tablet') {
                return '';
            }

            return $content;
        });
        $fenom->addBlockFunction('desktop', function ($options, $content) use ($modx) {
            $MobileDetect = mobiledetectPluginService($modx);
            if (!$MobileDetect) {
                return '';
            }

            $device = $MobileDetect->getDeviceType();

            if ($device === '' || $device === 'standard') {
                return $content;
            }

            return '';
        });
        $fenom->addBlockFunction('standard', function ($options, $content) use ($modx) {
            $MobileDetect = mobiledetectPluginService($modx);
            if (!$MobileDetect) {
                return '';
            }

            $device = $MobileDetect->getDeviceType();

            if ($device === '' || $device === 'standard') {
                return $content;
            }

            return '';
        });

        unset($GLOBALS['mobiledetectFenomRegistering']);
        break;
}
