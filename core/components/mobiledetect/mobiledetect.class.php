<?php

class MobileDetect
{
    /** @var modX $modx */
    public $modx;
    /** @var array $config */
    public $config;
    /** @var Mobile_Detect $detector */
    protected $detector;


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

            'disable_plugin' => $this->modx->getOption('md_disable_plugin', null, true),
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
     * @return Mobile_Detect
     */
    public function getDetector()
    {
        if (!class_exists('Mobile_Detect')) {
            require_once dirname(__FILE__) . '/vendor/autoload.php';
        }
        if (empty($this->detector)) {
            $this->detector = new Mobile_Detect();
        }

        return $this->detector;
    }


    /**
     * Save user settings to cookie
     *
     * @param $value
     */
    public function saveSettings($value)
    {
        if (!empty($this->config['use_cookie'])) {
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
    }


    /**
     * Return user settings from cookie
     *
     * @return mixed
     */
    public function getSettings()
    {
        if ($this->config['use_cookie'] && !empty($_COOKIE[$this->config['force_browser_variable']])) {
            return $_COOKIE[$this->config['force_browser_variable']];
        }

        return '';
    }


    /**
     * Remove user cookie
     */
    public function clearSettings()
    {
        if (!empty($this->config['use_cookie'])) {
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
    }


    /**
     * Delete not needed tags
     *
     * @param $content
     * @param $delete_node
     * @param $preserve_node
     *
     * @return mixed
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
     * @param $content
     * @param string $mode
     *
     * @return mixed
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
                if (empty($this->config['tablet_is_standard'])) {
                    $content = $this->deleteNode($content, 'standard_node', 'tablet_node');
                }
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
                break;
            default:
                if ($mode != 'no_settings') {
                    if ($mode = $this->getSettings()) {
                        return $this->parseDocument($content, $mode);
                    }
                }
                /** @var Mobile_Detect $detector */
                $detector = $this->getDetector();
                if ($detector->isMobile()) {
                    if ($detector->isTablet()) {
                        return $this->parseDocument($content, $this->config['force_browser_tablet']);
                    } else {
                        return $this->parseDocument($content, $this->config['force_browser_mobile']);
                    }
                } else {
                    return $this->parseDocument($content, $this->config['force_browser_standard']);
                }
        }

        return $content;
    }

}