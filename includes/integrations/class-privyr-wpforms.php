<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

class Privyr_Wpforms
{
    private function extract_value_by_type($field)
    {
        // Masking password into static 8 chars asterisk
        if ($field['type'] === 'password') return '********';

        // Remove new line on the middle of value for a nicer format
        if ($field['type'] === 'likert_scale') {
            return preg_replace('/(.+:)\n/', '$1 ', $field['value']);
        }

        return $field['value'];
    }

    /**
     * Extracting WPForms field values into a dictionary
     * containing key value pairs
     */
    private function extract_fields($fields)
    {
        $body = array();
        foreach ($fields as $field) {
            $key = _privyr_get_form_key($field, 'name', array('type', 'id'));
            $body[$key] = $this->extract_value_by_type($field);
        }
        return $body;
    }

    /** 
     * This is a wpforms callback after the lead is submitted.
     * Calls a non blocking http API request to privyr with lead data.
     */
    public function submit_wpforms_to_privyr($fields, $__entry, $form_data, $__entry_id)
    {
        $form_name = $form_data['settings']['form_title'];
        $privyr_api = new Privyr_API('wpforms', $form_name);
        $endpoint = $privyr_api->build_api_endpoint();
        $payload = $this->extract_fields($fields);
        $privyr_api->submit_lead_to_privyr($endpoint, $payload, Content_Type::JSON);
    }
}
