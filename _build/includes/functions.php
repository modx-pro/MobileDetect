<?php

/**
 * Bootstrap transport builder classes for MODX 2.8+ and MODX 3.x build scripts.
 *
 * MODX 3 uses PSR-4 classes under MODX\Revolution\Transport\ without legacy aliases.
 * Build scripts in this repo expect modPackageBuilder, modTransportPackage and xPDOTransport.
 *
 * @param modX $modx
 */
function mobiledetectBootstrapTransport($modx)
{
    static $bootstrapped = false;
    if ($bootstrapped) {
        return;
    }
    $bootstrapped = true;

    if (class_exists('MODX\\Revolution\\Transport\\modPackageBuilder')) {
        if (!class_exists('modPackageBuilder', false)) {
            class_alias('MODX\\Revolution\\Transport\\modPackageBuilder', 'modPackageBuilder');
        }
        if (!class_exists('xPDOTransport', false)) {
            class_alias('xPDO\\Transport\\xPDOTransport', 'xPDOTransport');
        }

        return;
    }

    $modx->loadClass('transport.modPackageBuilder', '', false, true);
    $modx->loadClass('transport.modTransportPackage', '', false, true);
    if (!class_exists('xPDOTransport', false)) {
        $modx->loadClass('xPDOTransport', 'xPDOTransport.', false, true);
    }
}

/**
 * xPDO class name for transport packages (MODX 2 vs 3).
 *
 * @return string
 */
function mobiledetectTransportPackageClass()
{
    return class_exists('MODX\\Revolution\\Transport\\modTransportPackage')
        ? 'MODX\\Revolution\\Transport\\modTransportPackage'
        : 'transport.modTransportPackage';
}

/**
 * @param $filename
 *
 * @return string
 */
function getSnippetContent($filename)
{
    $file = trim(file_get_contents($filename));
    preg_match('#\<\?php(.*)#is', $file, $data);

    return rtrim(rtrim(trim($data[1]), '?>'));
}

/**
 * Recursive directory remove
 *
 * @param $dir
 */
function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);

        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    rrmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}
