<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

class Privyr_Forminator_Form
{
    /**
     * Map element_id to field_label
     * for each field_label there is an unique element_id provided
     * we only need the field_label from there.
     */
    private function map_field_labels($form_field_data)
    {
        $form_field_map = array();
        foreach($form_field_data as $key => $value) {
            $element_id=$value->element_id;
            $field_label=$value->field_label;
            $form_field_map[$element_id] = $field_label;
        }
        return $form_field_map;
    }

    /**
     * Reformat form fields into dict key value pair
     * So it is easier to be consumed by our webhook API
     */
    private function format_fields($fields, $form_field_data)
    {
        $form_field_map = $this->map_field_labels($form_field_data);
        $body = array();
        foreach ($fields as $key => $field) {
            // exclude forminator addon information from payload
            if (strpos($key, 'forminator_addon') === 0) continue;
            $field_title = isset($form_field_map[$key]) ? $form_field_map[$key] : $key;
            $body[$field_title] = $field['value'];
        }
        return $body;
    }


    /** 
     * This is the callback after the lead is submitted.
     * Calls a non blocking http API request to privyr with lead data.
     */
    public function submit_to_privyr($form_id, $response)
    {
        if (!$response) return;
        if (!is_array($response)) return;
        if (!$response['success']) return;

        $entry_response = Forminator_Form_Entry_Model::get_latest_entry_by_form_id($form_id);

        $entry_data = $entry_response->{'meta_data'};
        $form_data = Forminator_API::get_form($form_id);
        $form_field_data = Forminator_API::get_form_fields($form_id);

        $form_settings = $form_data->{'settings'};
        $form_name = $form_settings["formName"];

        $privyr_api = new Privyr_API('forminator_form', $form_name);
        $endpoint = $privyr_api->build_api_endpoint();
        $payload = $this->format_fields($entry_data, $form_field_data);
        $privyr_api->submit_lead_to_privyr($endpoint, $payload, Content_Type::JSON);
    }
}
