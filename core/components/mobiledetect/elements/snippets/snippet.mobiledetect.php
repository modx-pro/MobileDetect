<?php
/** @var string $input */
/** @var MobileDetect $MobileDetect */
$MobileDetect = $modx->getService('mobiledetect', 'MobileDetect', MODX_CORE_PATH . 'components/mobiledetect/');

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

return (int)(strtolower($input) == $device);