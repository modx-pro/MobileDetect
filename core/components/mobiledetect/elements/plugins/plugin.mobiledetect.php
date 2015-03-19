<?php

if ($modx->event->name != 'OnWebPagePrerender') {
	return;
};

/** @var MobileDetect $MobileDetect */
if (!$MobileDetect = $modx->getService('mobiledetect', 'MobileDetect', MODX_CORE_PATH . 'components/mobiledetect/')) {
	return 'Could not load MobileDetect class!';
}
if (empty($MobileDetect->config['disable_plugin'])) {
	$key = $MobileDetect->config['force_browser_variable'];
	$mode = array_key_exists($key, $_GET)
		? $modx->stripTags($_GET[$key])
		: '';

	$modx->resource->_output = $MobileDetect->parseDocument($modx->resource->_output, $mode);
}