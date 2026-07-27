<?php

use Detection\MobileDetect as MobileDetectLib;

class MobileDetect
{
    /** @var modX $modx */
    public $modx;
    /** @var array $config */
    public $config;
    /** @var MobileDetectLib|null $detector */
    protected $detector;
    /** @var string|null */
    protected $deviceType;
    /** @var bool */
    public $fenomReady = false;


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;
        $this->config = array_merge(array(
            'session_cookie_path' => $this->modx->getOption('session_cookie_path', null,
                $this->modx->getOption('base_url', null, MODX_BASE_URL), true),
            'session_cookie_lifetime' => $this->modx->getOption('session_cookie_lifetime', null, 604800),
            'session_cookie_secure' => $this->modx->getOption('session_cookie_secure', null, false),
            'session_cookie_httponly' => $this->modx->getOption('session_cookie_httponly', null, true),
            'session_cookie_domain' => $this->modx->getOption('session_cookie_domain', null, ''),

            'disable_plugin' => $this->modx->getOption('md_disable_plugin', null, false),
            'use_cookie' => $this->modx->getOption('md_use_cookie', null, true),
            'tablet_is_standard' => $this->modx->getOption('md_tablet_is_standard', null, false),

            'force_browser_variable' => $this->modx->getOption('md_force_browser_variable', null, 'browser'),
            'force_browser_standard' => $this->modx->getOption('md_force_browser_standard', null, 'standard'),
            'force_browser_tablet' => $this->modx->getOption('md_force_browser_tablet', null, 'tablet'),
            'force_browser_detect' => $this->modx->getOption('md_force_browser_detect', null, 'detect'),
            'force_browser_mobile' => $this->modx->getOption('md_force_browser_mobile', null, 'mobile'),

            'mobile_node' => $this->modx->getOption('md_mobile_node', null, 'mobile'),
            'standard_node' => $this->modx->getOption('md_standard_node', null, 'standard'),
            'tablet_node' => $this->modx->getOption('md_tablet_node', null, 'tablet'),
        ), $config);

        $this->modx->lexicon->load('mobiledetect:default');
    }


    /**
     * Get detector instance
     *
     * @return MobileDetectLib
     */
    public function getDetector()
    {
        if (!class_exists(MobileDetectLib::class)) {
            require_once dirname(__FILE__) . '/vendor/autoload.php';
        }
        if (empty($this->detector)) {
            $this->detector = new MobileDetectLib();
        }

        return $this->detector;
    }


    /**
     * Resolve device type from forced value, cookie or detector
     *
     * @param string $forced
     *
     * @return string standard|tablet|mobile
     */
    public function resolveDevice($forced = '')
    {
        $forced = strtolower(trim((string)$forced));

        if ($forced === strtolower($this->config['force_browser_detect'])) {
            $this->clearSettings();
            $forced = '';
        }

        if (empty($forced)) {
            $forced = strtolower((string)$this->getSettings());
        }

        if (!empty($forced) && $forced !== strtolower($this->config['force_browser_detect'])) {
            $device = $this->applyTabletIsStandard($this->normalizeDeviceType($forced));
            $this->deviceType = $device;
            $this->saveSettings($device);

            return $device;
        }

        if ($this->deviceType !== null) {
            return $this->deviceType;
        }

        $detector = $this->getDetector();
        if ($detector->isMobile()) {
            $device = $detector->isTablet()
                ? $this->config['force_browser_tablet']
                : $this->config['force_browser_mobile'];
        } else {
            $device = $this->config['force_browser_standard'];
        }

        $device = $this->applyTabletIsStandard($device);
        $this->deviceType = $device;
        $this->saveSettings($device);

        return $device;
    }


    /**
     * @return string standard|tablet|mobile
     */
    public function getDeviceType()
    {
        if ($this->deviceType === null) {
            $this->resolveDevice();
        }

        return (string)$this->deviceType;
    }


    /**
     * @return bool
     */
    public function isMobile()
    {
        $device = $this->getDeviceType();

        return $device === $this->config['force_browser_mobile']
            || $device === $this->config['force_browser_tablet'];
    }


    /**
     * @return bool
     */
    public function isTablet()
    {
        return $this->getDeviceType() === $this->config['force_browser_tablet'];
    }


    /**
     * @return bool
     */
    public function isDesktop()
    {
        return $this->getDeviceType() === $this->config['force_browser_standard'];
    }


    /**
     * @param string $device
     *
     * @return string
     */
    protected function normalizeDeviceType($device)
    {
        $device = strtolower($device);
        $map = array(
            strtolower($this->config['force_browser_standard']) => $this->config['force_browser_standard'],
            strtolower($this->config['force_browser_tablet']) => $this->config['force_browser_tablet'],
            strtolower($this->config['force_browser_mobile']) => $this->config['force_browser_mobile'],
        );

        return isset($map[$device]) ? $map[$device] : $device;
    }


    /**
     * When md_tablet_is_standard is on, treat tablet as desktop for API, snippet and Fenom.
     *
     * @param string $device
     *
     * @return string
     */
    protected function applyTabletIsStandard($device)
    {
        if (!empty($this->config['tablet_is_standard'])
            && strtolower((string)$device) === strtolower($this->config['force_browser_tablet'])
        ) {
            return $this->config['force_browser_standard'];
        }

        return $device;
    }


    /**
     * Cookies are HTTP-only; skip in CLI and after output started.
     */
    protected function canPersistCookie(): bool
    {
        return !empty($this->config['use_cookie'])
            && PHP_SAPI !== 'cli'
            && !headers_sent();
    }


    /**
     * Save user settings to cookie
     *
     * @param string $value
     */
    public function saveSettings($value)
    {
        if (!$this->canPersistCookie()) {
            return;
        }

        setcookie(
            $this->config['force_browser_variable'],
            $value,
            time() + $this->config['session_cookie_lifetime'],
            $this->config['session_cookie_path'],
            $this->config['session_cookie_domain'],
            $this->config['session_cookie_secure'],
            $this->config['session_cookie_httponly']
        );
    }


    /**
     * Return user settings from cookie
     *
     * @return string
     */
    public function getSettings()
    {
        if ($this->config['use_cookie'] && !empty($_COOKIE[$this->config['force_browser_variable']])) {
            return (string)$_COOKIE[$this->config['force_browser_variable']];
        }

        return '';
    }


    /**
     * Remove user cookie
     */
    public function clearSettings()
    {
        $this->deviceType = null;

        if (!$this->canPersistCookie()) {
            return;
        }

        setcookie(
            $this->config['force_browser_variable'],
            '',
            time() - 3600,
            $this->config['session_cookie_path'],
            $this->config['session_cookie_domain'],
            $this->config['session_cookie_secure'],
            $this->config['session_cookie_httponly']
        );
    }


    /**
     * Delete not needed tags
     *
     * @param string $content
     * @param string $delete_node
     * @param string $preserve_node
     *
     * @return string
     */
    function deleteNode($content, $delete_node = '', $preserve_node = '')
    {
        if (!empty($this->config[$delete_node])) {
            $delete = preg_quote($this->config[$delete_node]);
            $pattern = "#<{$delete}>(.*)</{$delete}>#Usi";
            $content = preg_replace($pattern, '', $content);
        }
        if (!empty($this->config[$preserve_node])) {
            $preserve = preg_quote($this->config[$preserve_node]);
            $pattern = "#<(?:/|){$preserve}>#Usi";
            $content = preg_replace($pattern, '', $content);
        }

        return $content;
    }


    /**
     * @param string $content
     * @param string $mode
     *
     * @return string
     */
    public function parseDocument($content, $mode = '')
    {
        switch (strtolower($mode)) {
            case $this->config['force_browser_standard']:
                $content = $this->deleteNode($content, 'tablet_node', 'standard_node');
                $content = $this->deleteNode($content, 'mobile_node', 'standard_node');
                $this->saveSettings($mode);
                break;
            case $this->config['force_browser_tablet']:
                if (!empty($this->config['tablet_is_standard'])) {
                    return $this->parseDocument($content, $this->config['force_browser_standard']);
                }
                $content = $this->deleteNode($content, 'standard_node', 'tablet_node');
                $content = $this->deleteNode($content, 'mobile_node', 'tablet_node');
                $this->saveSettings($mode);
                break;
            case $this->config['force_browser_mobile']:
                $content = $this->deleteNode($content, 'standard_node', 'mobile_node');
                $content = $this->deleteNode($content, 'tablet_node', 'mobile_node');
                $this->saveSettings($mode);
                break;
            case $this->config['force_browser_detect']:
                $this->clearSettings();

                return $this->parseDocument($content, 'no_settings');
            default:
                if ($mode != 'no_settings') {
                    $saved = $this->getSettings();
                    if (!empty($saved) && strtolower($saved) !== strtolower((string)$mode)) {
                        return $this->parseDocument($content, $saved);
                    }
                }
                $detector = $this->getDetector();
                if ($detector->isMobile()) {
                    if ($detector->isTablet()) {
                        return $this->parseDocument($content, $this->config['force_browser_tablet']);
                    }

                    return $this->parseDocument($content, $this->config['force_browser_mobile']);
                }

                return $this->parseDocument($content, $this->config['force_browser_standard']);
        }

        return $content;
    }

}
