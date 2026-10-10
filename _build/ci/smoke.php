<?php
/**
 * After the package is installed, check the service without writing cookies.
 *
 * Usage: MODX_CORE_PATH=/path/to/core/ php _build/ci/smoke.php
 */

/** @var modX $modx */
$modx = require __DIR__ . '/bootstrap.inc.php';

$plugin = $modx->getObject(modx_class('modPlugin'), array('name' => 'MobileDetect'));
if (!$plugin) {
    fwrite(STDERR, "Plugin MobileDetect was not installed.\n");
    exit(1);
}

/** @var MobileDetect|false $service */
$service = $modx->getService('mobiledetect', 'MobileDetect', MODX_CORE_PATH . 'components/mobiledetect/');
if (!$service) {
    fwrite(STDERR, "getService(mobiledetect) failed.\n");
    exit(1);
}

$service->config['use_cookie'] = false;
$service->config['tablet_is_standard'] = false;
$service->clearSettings();

if ($service->resolveDevice('standard') !== 'standard') {
    fwrite(STDERR, "resolveDevice(standard) failed.\n");
    exit(1);
}

$html = '<standard>S</standard><tablet>T</tablet><mobile>M</mobile>';
$parsed = $service->parseDocument($html, 'mobile');
if (strpos($parsed, 'M') === false || strpos($parsed, 'S') !== false || strpos($parsed, 'T') !== false) {
    fwrite(STDERR, "parseDocument(mobile) returned: {$parsed}\n");
    exit(1);
}

echo "smoke ok\n";
