<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.privyr.com
 * @since      0.1.0
 *
 * @package    Privyr_Crm
 * @subpackage Privyr_Crm/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.0
 * @package    Privyr_Crm
 * @subpackage Privyr_Crm/includes
 * @author     Privyr Crm <support@privyr.com>
 */
class Privyr_Crm
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      Privyr_Crm_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string    $plugin_id    The string used to uniquely identify this plugin.
     */
    protected $plugin_id;

    /**
     * The current version of the plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    0.1.0
     */
    public function __construct()
    {
        if (defined('PRIVYR_CRM_VERSION')) {
            $this->version = PRIVYR_CRM_VERSION;
        } else {
            $this->version = '1.0.0';
        }

        $this->plugin_id = PRIVYR_CRM_PLUGIN_ID;

        $this->load_dependencies();
        $this->update_plugin();
        $this->define_admin_hooks();
        $this->define_integration_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
     * - Plugin_Name_i18n. Defines internationalization functionality.
     * - Plugin_Name_Admin. Defines all hooks for the admin area.
     * - Plugin_Name_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-privyr-crm-loader.php';

        /**
         * The class responsible for checking any function need to be run after updating the plugin
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-privyr-crm-updater.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-privyr-crm-admin.php';

        /**
         * The class responsible to managing the configuration and options for the plugin
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-privyr-crm-options.php';

        /**
         * All utilities function that helps manipulate form and flow content
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/function-utils.php';

        /**
         * The class responsible to orchestrate the lead submission to the privyr's api
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-privyr-crm-api.php';

        /**
         * The class responsible for defining all action hooks related
         * to any supported 3rd party integration
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-wpcf7.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-wpforms.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-elementor-form.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-gravity-form.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-houzez.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-divi.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-ninja-form.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-forminator-form.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-fluent-form.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-formidable.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-everest-form.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/integrations/class-privyr-metform.php';

        $this->loader = new Privyr_Crm_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    0.1.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Privyr_Crm_Admin($this->get_plugin_id(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'privyr_plugin_menu', 9);

        $plugin_options = new Privyr_Options();
        $hook_name = 'wp_ajax_' . Privyr_Constants::WP_SAVE_HOOK_NAME;
        $this->loader->add_action($hook_name, $plugin_options, 'save_options_handler');
    }

    /**
     * Register all of the hooks related to specific installed
     * wordpress plugin that is supported by privyr integration
     * More ref: https://developer.wordpress.org/reference/functions/add_action/
     * 
     * TODO: Idea for future refactor ⤵️
     * move the action loader on their own class file,
     * then load it automatically if possible?
     * 
     * @since    0.2.0
     * @access   private
     */
    private function define_integration_hooks()
    {
        /**
         * WP Contact Form 7
         * Reference: https://contactform7.com/2020/07/28/accessing-user-input-data/
         */
        $privyr_wpcf7 = new Privyr_CF7();
        $this->loader->add_action('wpcf7_before_send_mail', $privyr_wpcf7, 'submit_cf7_to_privyr', 10);

        /**
         * WPForms Integration
         * Reference: https://wpforms.com/developers/wpforms_process_complete/
         */
        $privyr_wpforms = new Privyr_Wpforms();
        $this->loader->add_action('wpforms_process_complete', $privyr_wpforms, 'submit_wpforms_to_privyr', 10, 4);

        /**
         * Elementor Form Integration
         * Reference: https://developers.elementor.com/docs/hooks/forms/#form-new-record
         */
        $privyr_elementorform = new Privyr_Elementor_Form();
        $this->loader->add_action('elementor_pro/forms/new_record', $privyr_elementorform, 'submit_elementor_form_to_privyr', 10, 2);

        /**
         * Gravity Form Integration
         * Reference: https://docs.gravityforms.com/category/developers/hooks/actions/submission/
         */
        $privyr_gravity_form = new Privyr_Gravity_Form();
        $this->loader->add_action('gform_after_submission', $privyr_gravity_form, 'submit_to_privyr', 10, 2);

        /**
         * Houzez Integration
         * Reference: ./wp-content/plugins/houzez-crm/includes/class-collect-form-data.php
         */
        $privyr_houzez = new Privyr_Houzez();
        $this->loader->add_action('houzez_after_agent_form_submission', $privyr_houzez, 'submit_to_privyr');
        $this->loader->add_action('houzez_after_contact_form_submission', $privyr_houzez, 'submit_to_privyr');
        $this->loader->add_action('houzez_after_estimation_form_submission', $privyr_houzez, 'submit_to_privyr');

        /**
         * Divi Form Integration
         * Reference: https://www.elegantthemes.com/documentation/developers/divi-module-hooks/
         */
        $privyr_divi_form = new Privyr_Divi();
        $this->loader->add_action('et_pb_contact_form_submit', $privyr_divi_form, 'submit_to_privyr', 100, 3);


        /**
         * Ninja Form Integration
         * Reference: https://developer.ninjaforms.com/codex/submission-processing-hooks/
         */
        $privyr_ninja_form = new Privyr_Ninja_Form();
        $this->loader->add_action('ninja_forms_after_submission', $privyr_ninja_form, 'submit_to_privyr');


        /**
         * Forminator Integration
         * References: 
         *      https://wordpress.org/support/topic/send-form-submission-to-webhook/
         *      https://wordpress.org/support/topic/custom-code-forminator_form_after_save_entry-called-if-nothing-was-saved/
         */
        $privyr_forminator = new Privyr_Forminator_Form();
        $this->loader->add_action('forminator_form_after_save_entry', $privyr_forminator, 'submit_to_privyr', 10, 2);
    
        /**
         * Fluent Forms Integration
         * Reference: https://fluentforms.com/docs/
         */
        $privyr_fluent_form = new Privyr_Fluent_Form();
        $this->loader->add_action('fluentform_submission_inserted', $privyr_fluent_form, 'submit_to_privyr', 20, 3);

        /**
         * Formidable Integration
         * Reference: https://formidableforms.com/knowledgebase/frm_after_create_entry/
         */
        $privyr_formidable = new Privyr_Formidable();
        $this->loader->add_action('frm_after_create_entry', $privyr_formidable, 'submit_to_privyr', 30, 2);

        /**
         * Everest Form Integration
         * Reference: ./wp-content/plugins/everest-forms/includes/class-evf-form-task.php
         */
        $privyr_everest_form = new Privyr_Everest_Form();
        $this->loader->add_action('everest_forms_complete_entry_save', $privyr_everest_form, 'submit_to_privyr', 40, 5);

        /**
         * Metform Integration
         * Reference: ./wp-content/plugins/metform/core/entries/action.php
         */
        $privyr_metform = new Privyr_Metform();
        $this->loader->add_action('metform_after_store_form_data', $privyr_metform, 'submit_to_privyr', 50, 4);
    }

    /**
     * Run any code to update our plugin to the latest structure
     * Wheter migration, configuration update, and etc
     * 
     * @since    0.2.0
     * @access   private
     */
    private function update_plugin()
    {
        $updater = new Privyr_Crm_Updater();
        $updater->apply_any_update();
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    0.1.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     0.1.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_id()
    {
        return $this->plugin_id;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     0.1.0
     * @return    Privyr_Crm_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     0.1.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
