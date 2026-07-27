<?php

/** @var modX $modx */
/** @var string $input */
/** @var MobileDetect $MobileDetect */
$MobileDetect = $modx->getService('mobiledetect', 'MobileDetect', MODX_CORE_PATH . 'components/mobiledetect/');
if (!$MobileDetect) {
    return 0;
}

$key = $MobileDetect->config['force_browser_variable'];
$forced = !empty($_GET) && array_key_exists($key, $_GET)
    ? $modx->stripTags($_GET[$key])
    : '';

$device = $MobileDetect->resolveDevice($forced);

return (int)(strtolower((string)$input) === strtolower($device));
