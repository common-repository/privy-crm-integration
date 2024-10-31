<?php

/**
 * Check Wordpress Integration Status 
 */
class Privyr_Integration_Status
{
    public const NOT_EXIST = 0;
    public const INSTALLED = 1;
    public const ACTIVATED = 2;
    public const CONNECTED = 3;

    /**
     * Utility function, so it will be easier
     * to be consumed on javascript realm
     */
    public static function to_array()
    {
        return array(
            "NotExist" => self::NOT_EXIST,
            "Installed" => self::INSTALLED,
            "Activated" => self::ACTIVATED,
            "Connected" => self::CONNECTED,
        );
    }

    /**
     * Utility function to check
     * if a plugin on specific directory path active or not
     * @param array $plugin_slugs file path to the plugin, usually end with `.php`
     */
    private static function is_plugin_active($plugin_slugs)
    {
        foreach($plugin_slugs as $slug) {
            if(is_plugin_active($slug)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Utility function to check
     * if a plugin on specific directory path is installed or not
     * @param array $plugin_slugs file path to the plugin, usually end with `.php`
     */
    private static function is_plugin_installed($plugin_slugs)
    {
        $installed_plugins = get_plugins();
        foreach($plugin_slugs as $slug) {
            if(array_key_exists($slug, $installed_plugins)
                || in_array($slug, $installed_plugins, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if theme is installed
     * @param array $theme_names the theme directory name in ./wp-content/themes/
     */
    private static function is_theme_installed($theme_names)
    {
        foreach($theme_names as $name) {
            $theme = wp_get_theme($name);
            if($theme->exists()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if theme is active
     * @param array $theme_names array of the theme directory name in ./wp-content/themes/
     */
    private static function is_theme_active($theme_names)
    {
        foreach($theme_names as $name) {
            $current_theme = wp_get_theme();
            if($name == $current_theme->get_template()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check for plugin status based on their slug path,
     * optionally check if it's connected if token is provided too
     * @param array $plugin_slugs array of file path to the plugin, usually end with `.php`
     * @param boolean $enabled flag wether the plugin state is enabled/not
     */
    private static function get_plugin_status($plugin_slugs, $enabled = false)
    {
        $is_active = self::is_plugin_active($plugin_slugs);
        $is_installed = self::is_plugin_installed($plugin_slugs);
        if (!$is_installed) return self::NOT_EXIST;
        if (!$is_active) return self::INSTALLED;
        if (!$enabled) return self::ACTIVATED;
        return self::CONNECTED;
    }

    /**
     * Check for theme status based on their directory name
     * @param array $theme_names array of directory name of the theme 
     * @param boolean $enabled flag wether the theme state is enabled/not
     */
    private static function get_theme_status($theme_names, $enabled = false)
    {
        $is_active = self::is_theme_active($theme_names);
        $is_installed = self::is_theme_installed($theme_names);
        if (!$is_installed) return self::NOT_EXIST;
        if (!$is_active) return self::INSTALLED;
        if (!$enabled) return self::ACTIVATED;
        return self::CONNECTED;
    }

    /**
     * Get integration status
     * @param object $integration integration instance
     */
    public static function get_status($integration)
    {
        $enabled = $integration['enabled'];
        $identifiers = $integration['identifiers'];
        $type = $integration['type'];
        if (WP_Integration_Type::THEME == $type) {
            return self::get_theme_status($identifiers, $enabled);
        } elseif (WP_Integration_Type::PLUGIN == $type) {
            return self::get_plugin_status($identifiers, $enabled);
        }
        return self::NOT_EXIST;
    }
}
