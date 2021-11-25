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

    case 'pdoToolsOnFenomInit':
        $MobileDetect = $modx->getService('mobiledetect', 'MobileDetect', MODX_CORE_PATH . 'components/mobiledetect/');
        if ($MobileDetect && empty($MobileDetect->config['disable_plugin'])) {
            $key = $MobileDetect->config['force_browser_variable'];
            $device = !empty($_GET) && array_key_exists($key, $_GET)
                ? $modx->stripTags($_GET[$key])
                : '';

            if (empty($device)) {
                $device = $MobileDetect->getSettings();
            }

            if (empty($device) || $device == 'detect') {
                /** @var Mobile_Detect $detector */
                $detector = $MobileDetect->getDetector();
                $device = ($detector->isMobile()
                    ? ($detector->isTablet() ? 'tablet' : 'mobile')
                    : 'standard');
                $MobileDetect->saveSettings($device);
            }
        }
        
        if (!empty($device)) {
            $_SESSION['mobiledetect']['device'] = $device;
        }
        
        $fenom->addBlockFunction('mobile', function($options, $content) {
			if ($_SESSION['mobiledetect']['device'] == 'mobile' || $_SESSION['mobiledetect']['device'] == 'tablet'){
				return $content;
			}
		});
        $fenom->addBlockFunction('phone', function($options, $content) {
			if ($_SESSION['mobiledetect']['device'] == 'mobile'){
				return $content;
			}
		});
        $fenom->addBlockFunction('tablet', function($options, $content) {
			if ($_SESSION['mobiledetect']['device'] == 'tablet'){
				return $content;
			}
		});
        $fenom->addBlockFunction('desktop', function($options, $content) {
			if (empty($_SESSION['mobiledetect']['device']) || $_SESSION['mobiledetect']['device'] == 'standard'){
				return $content;
			}
		});
        $fenom->addBlockFunction('standard', function($options, $content) {
			if (empty($_SESSION['mobiledetect']['device']) || $_SESSION['mobiledetect']['device'] == 'standard'){
				return $content;
			}
		});
        break;
}