<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

class Privyr_Elementor_Form
{
    private function extract_value_by_type($field)
    {
        // Masking password into static 8 chars asterisk
        if ($field['type'] === 'password') return '********';
        return $field['value'];
    }

    /**
     * Reformat elementor form fields into dict key value pair
     * So it is easier to be consumed by our webhook API
     */
    private function format_fields_payload($fields)
    {
        $body = array();
        foreach ($fields as $field) {
            $key = _privyr_get_form_key($field, 'title', array('type', 'id'));
            $body[$key] = $this->extract_value_by_type($field);
        }
        return $body;
    }

    /** 
     * This is a elementor form callback after the lead is submitted.
     * Calls a non blocking http API request to privyr with lead data.
     */
    public function submit_elementor_form_to_privyr($record, $handler)
    {
        $form_name = $record->get_form_settings('form_name');
        $raw_fields = $record->get('fields');
        
        $privyr_api = new Privyr_API('elementor_form', $form_name);
        $endpoint = $privyr_api->build_api_endpoint();
        $payload = $this->format_fields_payload($raw_fields);
        $payload['form_name'] = $form_name;

        $privyr_api->submit_lead_to_privyr($endpoint, $payload, Content_Type::FORM_URLENCODED);
    }
}
