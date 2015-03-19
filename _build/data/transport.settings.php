<?php

$settings = array();

$tmp = array(
	'disable_plugin' => array(
		'xtype' => 'combo-boolean',
		'value' => false,
	),
	'use_cookie' => array(
		'xtype' => 'combo-boolean',
		'value' => true,
	),
	'tablet_is_standard' => array(
		'xtype' => 'combo-boolean',
		'value' => false,
	),

	'force_browser_variable' => array(
		'xtype' => 'textfield',
		'value' => 'browser',
	),
	'force_browser_standard' => array(
		'xtype' => 'textfield',
		'value' => 'standard',
	),
	'force_browser_tablet' => array(
		'xtype' => 'textfield',
		'value' => 'tablet',
	),
	'force_browser_mobile' => array(
		'xtype' => 'textfield',
		'value' => 'mobile',
	),
	'force_browser_detect' => array(
		'xtype' => 'textfield',
		'value' => 'detect',
	),

	'standard_node' => array(
		'xtype' => 'textfield',
		'value' => 'standard',
	),
	'tablet_node' => array(
		'xtype' => 'textfield',
		'value' => 'tablet',
	),
	'mobile_node' => array(
		'xtype' => 'textfield',
		'value' => 'mobile',
	),

);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'md_' . $k,
			'namespace' => PKG_NAME_LOWER,
			'area' => 'md_main',
		), $v
	), '', true, true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;
