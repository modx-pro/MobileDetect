<?php

/** @var modX $modx */
/** @var array $sources */

$chunks = array();

$tmp = array(
    'tplMobileDetectSwitch' => array(
        'file' => 'mobileswitch',
        'description' => 'Example links to force desktop, tablet, mobile or auto-detect mode',
    ),
);

foreach ($tmp as $k => $v) {
    /** @var modChunk $chunk */
    $chunk = $modx->newObject('modChunk');
    $chunk->fromArray(array(
        'id' => 0,
        'name' => $k,
        'description' => @$v['description'],
        'snippet' => file_get_contents($sources['source_core'] . '/elements/chunks/chunk.' . $v['file'] . '.tpl'),
        'static' => defined('BUILD_CHUNK_STATIC') ? BUILD_CHUNK_STATIC : false,
        'source' => 1,
        'static_file' => 'core/components/' . PKG_NAME_LOWER . '/elements/chunks/chunk.' . $v['file'] . '.tpl',
    ), '', true, true);

    $chunks[] = $chunk;
}
unset($tmp);

return $chunks;
