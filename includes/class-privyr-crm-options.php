<?php

/**
 * Privyr Plugin Options
 *
 * This class manage all of the options made by privyr plugin,
 * all the option values are stored in `wp_options` table
 * https://codex.wordpress.org/Database_Description#Table:_wp_options
 *
 * @since      0.2.0
 * @package    Privyr_Crm
 * @subpackage Privyr_Crm/includes
 * @author     Privyr <support@privyr.com>
 */
class Privyr_Options
{
    /**
     * Save fields into on `wp_options`,
     * Be careful to call this, because it will override the existing privyr options
     * If you only plan to save one field, try get_values() first,
     * and then add the one value you want to replace on top of existing option
     * @param array $fields payload to be saved into `wp_options`
     */
    public static function set_values($fields)
    {
        $allowed_keys = array_column(Privyr_Constants::SUPPORTED_INTEGRATIONS, 'key');
        array_push($allowed_keys, 'cf_reference', 'privyr_token');
        $filtered_values = array_intersect_key($fields, array_flip($allowed_keys));
        update_option(Privyr_Constants::WP_OPTION_NAME, $filtered_values);
    }

    /**
     * Get all value from `privyr_options` on `wp_options`
     * @return array
     */
    public static function get_values()
    {
        $options = get_option(Privyr_Constants::WP_OPTION_NAME);
        return $options ? $options : array();
    }

    /**
     * Get single value from `privyr_options` on `wp_options`
     * @param string $key option field identifier, e.g elementor_form
     */
    public static function get_single_value($key)
    {
        $options = self::get_values();
        return isset($options[$key]) ? $options[$key] : null;
    }

    /**
     * Get all plugins that supported by our wordpress plugin
     * with the status of each plugin wether it is installed or not
     */
    public static function get_available_integrations()
    {
        function compare_active_status($left, $right)
        {
            return $right['status'] - $left['status'];
        }

        function add_detail($integration)
        {
            $key = $integration['key'];
            $iconFile = $integration['iconFile'];
            $integration['enabled'] = Privyr_Options::get_single_value($key);
            $integration['status'] = Privyr_Integration_Status::get_status($integration);
            $integration['iconUrl'] = Privyr_Constants::get_plugin_icon_url($iconFile);
            
            // Recheck to make sure the integration is disabled when status is not activated
            $is_connected = $integration['status'] > Privyr_Integration_Status::ACTIVATED;
            $integration['enabled'] = $integration['enabled'] && $is_connected;
            return $integration;
        }

        $result = array_map('add_detail', Privyr_Constants::SUPPORTED_INTEGRATIONS);
        usort($result, 'compare_active_status');
        return $result;
    }

    /**
     * Handler for new/updated options 
     * coming from wp-admin dashboard
     */
    public function save_options_handler()
    {
        $submission = $_POST;
        $existing_token = self::get_single_value('privyr_token');
        if ($existing_token) return self::set_values($submission);
        
        // If existing token does not exist
        // We will enable all of the installed integration by default
        $integrations = self::get_available_integrations();
        $installed = array_filter($integrations, function ($integration) {
            return $integration['status'] >= Privyr_Integration_Status::INSTALLED;
        });

        foreach($installed as $integration) {
            $key = $integration['key'];
            $submission[$key] = true;
        }
        
        self::set_values($submission);
    }
}
