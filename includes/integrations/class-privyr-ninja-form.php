<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

class Privyr_Ninja_Form
{

    /**
     * Reformat form fields into dict key value pair
     * So it is easier to be consumed by our webhook API
     */
    private function format_fields_payload($fields)
    {
        $body = array();
        foreach ($fields as $field) {
            $body[$field['label']] = $field['value'];
        }
        return $body;
    }

    /** 
     * This is ninja form callback after the lead is submitted.
     * Calls a non blocking http API request to privyr with lead data.
     */
    public function submit_to_privyr($form_data)
    {
        $form_title    = $form_data['settings']['title'];
        $form_fields   =  $form_data['fields'];
        $privyr_api = new Privyr_API('ninja_form', $form_title);
        $endpoint = $privyr_api->build_api_endpoint();
        $payload = $this->format_fields_payload($form_fields);
        $payload['form_name'] = $form_title;
        $privyr_api->submit_lead_to_privyr($endpoint, $payload, Content_Type::JSON);
    }
}
