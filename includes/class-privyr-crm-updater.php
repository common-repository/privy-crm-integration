<?php

/**
 * Our plugin updater class
 *
 * This class defines all code necessary to run after our plugin is being updated.
 * Can be used for migrating database, configuration and so on
 *
 * @since      0.2.0
 * @package    Privyr_Crm
 * @subpackage Privyr_Crm/includes
 * @author     Privyr <support@privyr.com>
 */
class Privyr_Crm_Updater
{
    /**
     * Update from v0.1 to v0.2
     * @since 0.2.0
     */
    private function from_01_to_02()
    {
        global $wpdb;

        // Retrieve any existing user info table created on v0.1
        $table_name = $wpdb->prefix . 'privyr_user_info';
        $result = $wpdb->get_row("SELECT * FROM {$table_name}");
        if (!$result) return null;

        // Move the info into wp_options
        $existing_options = array(
            'cf7' => true,
            'privyr_token' => $result->privyr_token,
            'cf_reference' => $result->cf_reference
        );
        Privyr_Options::set_values($existing_options);

        // Finally, remove the table created on v0.1
        $wpdb->query("DROP TABLE {$table_name}");
    }

    /**
     * Checking any update needed
     * @since 0.2
     */    
    public function apply_any_update() {
        $previous_version =  self::get_plugin_version_from_db();
        
        // On 0.1, we don't save any version info on DB 
        if (!$previous_version) {
            $this->from_01_to_02();
        }
        
        // Note: For next update iteration,
        // Simply create a method (from_02_to_03), compare the version,
        // And call the update function afterward. For example:
        // 👇
        // if (version_compare($previous_version, '0.3.0', '<')) {
        //      $this->from_02_to_03()
        // }

        // Finally save current plugin information in database,
        // for the next update reference
        self::save_plugin_version_to_db();
    }


    public static function save_plugin_version_to_db() {
        update_option(Privyr_Constants::WP_OPTION_VERSION_NAME, PRIVYR_CRM_VERSION);
    }

    public static function get_plugin_version_from_db() {
        return get_option(Privyr_Constants::WP_OPTION_VERSION_NAME);
    }
    
}
