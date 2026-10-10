<?php
/**
 * Start MODX 2 or MODX 3 from the command line.
 * MODX_CORE_PATH must point at an installed core directory.
 */

$core = getenv('MODX_CORE_PATH');
if (!$core || !is_file(rtrim($core, '/') . '/config/config.inc.php')) {
    fwrite(STDERR, "MODX_CORE_PATH must point at an installed MODX core.\n");
    exit(1);
}

define('MODX_API_MODE', true);
require rtrim($core, '/') . '/config/config.inc.php';
require MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
if (!$modx->initialize('mgr')) {
    fwrite(STDERR, "Could not initialize MODX.\n");
    exit(1);
}
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('ECHO');

/**
 * Core class key: namespaced on MODX 3, legacy on MODX 2.
 *
 * @param string $name
 *
 * @return string
 */
function modx_class($name)
{
    $modern = array(
        'modPlugin' => 'MODX\\Revolution\\modPlugin',
        'modTransportPackage' => 'MODX\\Revolution\\Transport\\modTransportPackage',
    );
    $legacy = array(
        'modTransportPackage' => 'transport.modTransportPackage',
    );

    if (!isset($modern[$name])) {
        throw new InvalidArgumentException("modx_class() does not know {$name}.");
    }
    if (class_exists('MODX\\Revolution\\modX')) {
        return $modern[$name];
    }

    return isset($legacy[$name]) ? $legacy[$name] : $name;
}

return $modx;
