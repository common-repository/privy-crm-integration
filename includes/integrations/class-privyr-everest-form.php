<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

class Privyr_Everest_Form
{

    private function extract_value_by_type($field)
    {
        $field_type_with_raw_value = array("select", "radio", "checkbox");
        if (!in_array($field["type"], $field_type_with_raw_value)) {
            return $field["value"];
        }
        
        // if the raw value is an array and the length is only one, just return it directly
        // since it's only single choice prompt select
        $raw = $field["value_raw"];
        if ($field["type"] == "select" && is_array($raw) && count($raw) == 1) {
            return $field["value_raw"][0];
        } else {
            return $field["value_raw"];
        }
    }

    private function extract_key($field)
    {            
        // Try retrieve field label name
        if (isset($field["name"])) return $field["name"];
        elseif (isset($field["value"]["name"])) return $field["value"]["name"];
        return $field["id"];
    }

    /**
     * Extracting Everest field values into a dictionary
     * containing key value pairs
     */
    private function extract_fields($fields)
    {
        $body = array();
        foreach ($fields as $index => $field) {
            $key = $this->extract_key($field);
            $body[$key] = $this->extract_value_by_type($field);
        }
        return $body;
    }

    /** 
     * This is a everest form callback after the lead is submitted.
     * Calls a non blocking http API request to privyr with lead data.
     */
    public function submit_to_privyr($entry_id, $fields, $entry, $form_id, $form_data)
    {
        $form_name = $form_data["settings"]["form_title"];
        $privyr_api = new Privyr_API('everest_form', $form_name);
        $endpoint = $privyr_api->build_api_endpoint();
        $payload = $this->extract_fields($fields);

        $privyr_api->submit_lead_to_privyr($endpoint, $payload, Content_Type::JSON);
    }
}
