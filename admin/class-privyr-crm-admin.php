<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.privyr.com
 * @since      0.1.0
 *
 * @package    Privyr_Crm
 * @subpackage Privyr_Crm/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Privyr_Crm
 * @subpackage Privyr_Crm/admin
 * @author     Privyr <support@privyr.com>
 */
class Privyr_Crm_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    0.1.0
     * @access   private
     * @var      string    $plugin_id    The ID of this plugin.
     */
    private $plugin_id;

    /**
     * The version of this plugin.
     *
     * @since    0.1.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param      string    $privyr_crm       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     *@since    0.1.0
     */
    public function __construct($plugin_id, $version)
    {

        $this->plugin_id = $plugin_id;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    0.1.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Privyr_Crm_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Privyr_Crm_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if ($this->is_on_plugin_page()) {
            $path = plugin_dir_url(__FILE__) . 'css/libs/tailwind.min.css';
            wp_enqueue_style("{$this->plugin_id}_tailwind", $path, array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    0.1.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Privyr_Crm_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Privyr_Crm_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if ($this->is_on_plugin_page()) {
            $path = plugin_dir_url(__FILE__) . 'js/libs/petite-vue.min.js';
            wp_enqueue_script("{$this->plugin_id}_petite-vue", $path, array(), $this->version, false);
        }
        require_once plugin_dir_path(__FILE__) . 'partials/privyr-crm-admin-display.php';
    }

    /**
     * Return a bool whenever the user is on our privyr plugin page or not,
     * used to guard check before load css/js, to avoid load the assets on the other admin pages
     * @since 	0.2.0
     */
    private function is_on_plugin_page()
    {
        $screen = get_current_screen();
        return 'toplevel_page_privyr-crm-admin-display' === $screen->base;
    }

    public function privyr_plugin_menu()
    {
        add_menu_page("Privyr CRM", "Privyr CRM", 'manage_options', "privyr-crm-admin-display", "render_admin_page", "dashicons-id");
    }
}
