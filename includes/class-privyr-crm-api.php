<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

/**
 * Class enum to decide the content type on the lead submission
 * @since 0.2
 */
class Content_Type
{
    public const JSON = 'application/json';
    public const FORM_URLENCODED = 'application/x-www-form-urlencoded';
}

/**
 * Privyr API Helper function
 * @since 0.2
 */
class Privyr_API
{
    private $plugin_key;
    private $form_name;

    /**
     * Class constructor function
     * @param string $plugin_key plugin identifier, which obtainable on Privyr_Constants
     * @param string $form_name form identifier
     */
    function __construct($plugin_key, $form_name = null)
    {
        $this->plugin_key = $plugin_key;
        $this->form_name = $form_name;
    }

    /**
     * Create an api endpoint url where we can deliver the submission
     * That's including the integration token & form name
     * Sample result, will be something like this: 
     * http://localhost/integrations/api/v1/wpforms-webhook?privyr_token=1JASH23&cf_reference=website
     * @since 0.2
     */
    public function build_api_endpoint()
    {
        $token = Privyr_Options::get_single_value('privyr_token');
        if (empty($token)) return null;

        $endpoint = Privyr_Constants::get_webhook_url($this->plugin_key);
        $cf_reference = Privyr_Options::get_single_value('cf_reference');

        // Make the required information into a query string
        $queries = array(
            'privyr_token' => $token,
            'cf_reference' => $cf_reference
        );
        $querystring = http_build_query($queries);

        return "{$endpoint}?{$querystring}";
    }

    /**
     * Simply format payload in associative array into each respective content type
     * @param array $payload data to be formatted
     * @param ContentType $type type output for the payload
     */
    private function format_payload($payload, $type)
    {
        if ($type == Content_Type::JSON) return wp_json_encode($payload);
        if ($type == Content_Type::FORM_URLENCODED) return http_build_query($payload);
        return $payload;
    }

    /**
     * To check if the integration is enabled or not
     * by retrieve the integration state from wp_options
     */
    private function is_integration_enabled()
    {
        $is_enabled = Privyr_Options::get_single_value($this->plugin_key);
        return 'true' == $is_enabled;
    }

    /** 
     * Make an API submission to the privyr for the lead
     * @since 0.2
     */
    public function submit_lead_to_privyr($endpoint, $payload, $type)
    {
        $is_enabled = $this->is_integration_enabled();
        if (!$endpoint || !$payload || !$is_enabled) return;
        $payload["form_name"] = $this->form_name; 
        $options = [
            'body' => self::format_payload($payload, $type),
            'headers' => ['Content-Type' => $type],
            'blocking' => false,
            'data_format' => 'body',
        ];
        wp_remote_post($endpoint, $options);
    }
}
