<?php

$templates = array();

$tmp = array(
    'MobileDetect Test' => array(
        'file' => 'test',
        'description' => 'Mobile Detect example template',
    ),
);

foreach ($tmp as $k => $v) {
    /** @var modTemplate $template */
    $template = $modx->newObject('modTemplate');
    /** @var array $sources */
    $template->fromArray(array(
        'id' => 0,
        'templatename' => $k,
        'description' => @$v['description'],
        'content' => file_get_contents($sources['source_core'] . '/elements/templates/template.' . $v['file'] . '.tpl'),
        'static' => BUILD_TEMPLATE_STATIC,
        'source' => 1,
        'static_file' => 'core/components/' . PKG_NAME_LOWER . '/elements/templates/template.' . $v['file'] . '.tpl',
    ), '', true, true);

    $templates[] = $template;
}
unset($tmp);

return $templates;