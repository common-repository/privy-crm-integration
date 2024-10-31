<?php

/**
 * This class contain constants and source of all information for privyr plugin
 * @since      0.2.0
 */
class Privyr_Constants
{
    /**
     * Choose the correct url based on how privyr-crm-wp-plugin is running:
     *  Local:
     *      http://127.0.0.1:8000
     *  Docker (based on the os):
     *      http://host.docker.internal:8000 or http://docker.for.mac.localhost:8000
     *  Prod:
     *      https://www.privyr.com
     *  QA:
     *      https://www.privyr-test.com
     *  Dev:
     *      https://www.privyr-dev.com
    */
    public const API_BASE_URL = 'https://www.privyr.com';
    
    public const WEB_BASE_URL = 'https://web.privyr.com';
    public const WEBAPP_INTEGRATION_PAGE_LINK = self::WEB_BASE_URL . '/integration/wordpress';
    public const WP_OPTION_NAME = 'privyr_options';
    public const WP_OPTION_VERSION_NAME = 'privyr_version';
    public const WP_SAVE_HOOK_NAME = 'privyr_save_options';

    /**
     * The full information for the supported integration on our privyr plugin
     * Where each integration, have this format:
     * array (
     *   'key' => unique key for the integration, must match with enum on the backend
     *   'name' => integration name that will be displayed on the UI side
     *   'type' => the type of integration, it can be plugin or theme
     *   'identifiers' => the file path (for plugin) or directory name (for theme)
     *   'iconFile' => file path of image logo file on: privyr-crm-wp-plugin/admin/images/plugins
     *   'webhookUrl' => Privyr backend destination where we send our captured lead data
     * )
     */
    public const SUPPORTED_INTEGRATIONS = array(
        array(
            'key' => 'cf7',
            'name' => 'Contact Form 7',
            'type' => WP_Integration_Type::PLUGIN,
            'identifiers' => array('contact-form-7/wp-contact-form-7.php'),
            'iconFile' =>  'cf7-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/new-pvyr-wp-plugin-lead'
        ),
        array(
            'key' => 'wpforms',
            'name' => 'WPForms',
            'type' => WP_Integration_Type::PLUGIN,
            'identifiers' => array('wpforms/wpforms.php', 'wpforms-lite/wpforms.php'),
            'iconFile' =>  'wpforms-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/wpforms-webhook'
        ),
        array(
            'key' => 'elementor_form',
            'name' => 'Elementor (Pro Form & Pro Elements)',
            'type' => WP_Integration_Type::PLUGIN,
            'identifiers' => array('elementor-pro/elementor-pro.php', 'pro-elements/pro-elements.php'),
            'iconFile' =>  'elementor-form-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/elementor-form-webhook'
        ),
        array(
            'key' => 'gravity_form',
            'name' => 'Gravity Forms',
            'type' => WP_Integration_Type::PLUGIN,
            'identifiers' => array('gravityforms/gravityforms.php'),
            'iconFile' =>  'gravity-form-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/gravity-form-webhook'
        ),
        array(
            'key' => 'houzez',
            'name' => 'Houzez',
            'type' => WP_Integration_Type::THEME,
            'identifiers' => array('houzez'),
            'iconFile' =>  'houzez-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/houzez-webhook'
        ),
        array(
            'key' => 'divi',
            'name' => 'Divi',
            'type' => WP_Integration_Type::THEME,
            'identifiers' => array('Divi'),
            'iconFile' =>  'divi-form-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/divi-webhook'
        ),
        array(
            'key' => 'ninja_form',
            'name' => 'Ninja Forms',
            'type' => WP_Integration_Type::PLUGIN,
            'identifiers' => array('ninja-forms/ninja-forms.php'),
            'iconFile' =>  'ninja-form-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/ninja-form-webhook'
        ),
        array(
            'key' => 'forminator_form',
            'name' => 'Forminator',
            'type' => WP_Integration_Type::PLUGIN,
            'identifiers' => array('forminator/forminator.php'),
            'iconFile' =>  'forminator-form-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/forminator-form-webhook'
        ),
        array(
            'key' => 'fluent_form',
            'name' => 'Fluent Forms',
            'type' => WP_Integration_Type::PLUGIN,
            'identifiers' => array('fluentform/fluentform.php'),
            'iconFile' =>  'fluent-form-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/fluent-form-webhook'
        ),
        array(
            'key' => 'formidable_form',
            'name' => 'Formidable Forms',
            'type' => WP_Integration_Type::PLUGIN,
            'identifiers' => array('formidable/formidable.php'),
            'iconFile' =>  'formidable-form-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/formidable-form-webhook'
        ),
        array(
            'key' => 'everest_form',
            'name' => 'Everest Forms',
            'type' => WP_Integration_Type::PLUGIN,
            'identifiers' => array('everest-forms/everest-forms.php'),
            'iconFile' =>  'everest-form-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/everest-form-webhook'
        ),
        array(
            'key' => 'metform',
            'name' => 'MetForm',
            'type' => WP_Integration_Type::PLUGIN,
            'identifiers' => array('metform/metform.php'),
            'iconFile' =>  'metform-logo.png',
            'webhookUrl' => self::API_BASE_URL . '/integrations/api/v1/metform-webhook'
        ),

    );


    public static function get_webhook_subscription_url()
    {
        return self::API_BASE_URL . '/integrations/api/v1/pvyr-wp-plugin-user-webhook-subscription';
    }

    public static function get_webhook_url($integration_key)
    {
        $supported_keys = array_column(self::SUPPORTED_INTEGRATIONS, 'key');
        $found_index = array_search($integration_key, $supported_keys);
        return self::SUPPORTED_INTEGRATIONS[$found_index]['webhookUrl'];
    }

    public static function get_logo_url()
    {
        return plugins_url('admin/images/onboarding-logo.png', dirname(__FILE__));
    }

    public static function get_plugin_icon_url($file_path)
    {
        return plugins_url('admin/images/plugins/' . $file_path, dirname(__FILE__));
    }
}


class WP_Integration_Type
{
    public const THEME = 'theme';
    public const PLUGIN = 'plugin';
}
