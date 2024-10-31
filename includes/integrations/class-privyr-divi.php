<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

class Privyr_Divi
{
    /**
     * Reformat elementor form fields into dict key value pair
     * So it is easier to be consumed by our webhook API
     */
    private function format_fields_payload($fields)
    {
        $body = array();
        foreach ($fields as $key => $value) {
            $body[$key] = $value['value'];
        }
        return $body;
    }

    /** 
     * This is a elementor form callback after the lead is submitted.
     * Calls a non blocking http API request to privyr with lead data.
     */
    public function submit_to_privyr($processed_fields_values, $et_contact_error, $contact_form_info)
    {
        global $post;

        // Divi does not provide any form name, so we are grabbing the page name with form-id (my page - 3)
        $form_name = $post->post_title;
        $privyr_api = new Privyr_API('divi', $form_name);
        $endpoint = $privyr_api->build_api_endpoint();
        $payload = $this->format_fields_payload($processed_fields_values);
        $payload['form_name'] = $form_name;

        $privyr_api->submit_lead_to_privyr($endpoint,  $payload, Content_Type::FORM_URLENCODED);
    }
}
