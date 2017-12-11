<?php

$properties = array();

$tmp = array(
    'input' => array(
        'type' => 'list',
        'options' => array(
            array('text' => 'standard', 'value' => 'standard'),
            array('text' => 'tablet', 'value' => 'tablet'),
            array('text' => 'mobile', 'value' => 'mobile'),
        ),
        'value' => 'mobile',
    ),
);

foreach ($tmp as $k => $v) {
    $properties[] = array_merge(
        array(
            'name' => $k,
            'desc' => 'md_prop_' . $k,
            'lexicon' => PKG_NAME_LOWER . ':properties',
        ), $v
    );
}

return $properties;