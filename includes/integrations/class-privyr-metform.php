<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

class Privyr_Metform
{

    private function extract_value_by_type($field, $form_value)
    {
        if ($field["widgetType"] === 'mf-password') return '********';
        if ($field["widgetType"] === 'mf-switch') {
            // the "swtich" is intentional, not sure why they send typo field in here
            if(!$form_value) return $field["mf_swtich_disable_text"];
            return $form_value;
        }

        $field_type_with_list = array("mf-select", "mf-multi-select", "mf-radio", "mf-checkbox");
        if (!in_array($field["widgetType"], $field_type_with_list)) {
            // the rest we will return as it is
            return $form_value;
        }

        // Try get the readable value from these widgets:
        // select, multi select, radio, checkbox
        $value_list = explode(",", $form_value);
        $readable_values = array();

        foreach ($value_list as $raw_value) {
            foreach ($field["mf_input_list"] as $input_ref) {
                if (isset($input_ref["mf_input_option_value"]) && $input_ref["mf_input_option_value"] == $raw_value) {
                    array_push($readable_values, $input_ref["mf_input_option_text"]);
                } elseif (isset($input_ref["value"]) && $input_ref["value"] == $raw_value) {
                    array_push($readable_values, $input_ref["label"]);
                }
            }
        }
        return implode(", ", $readable_values);
    }

    /**
     * Extracting Metform field values into a dictionary
     * containing key value pairs
     */
    private function extract_fields($form_id, $form_values)
    {
        // ref: ./wp-content/plugins/metform/core/entries/form-data.php
        $map_data = \MetForm\Core\Entries\Action::instance()->get_fields($form_id);
        $form_data = json_decode(json_encode($map_data), true);

        $body = array();
        foreach ($form_data as $index => $field) {
            $key = $field["mf_input_label"];
            $raw_value = isset($form_values[$index]) ? $form_values[$index] : null;
            $value = $this->extract_value_by_type($field, $raw_value);
                
            if ($value === null) continue;

            // Only include if the form data is exist
            $body[$key] = $value;
        }
        return $body;
    }

    /** 
     * This is a Metform callback after the lead is submitted.
     * Calls a non blocking http API request to privyr with lead data.
     */
    public function submit_to_privyr($form_id, $form_values, $form_settings, $attributes)
    {
        // $form_name = $form_data["settings"]["form_title"];
        $form_name = $form_settings["form_title"];
        $privyr_api = new Privyr_API('metform', $form_name);

        $endpoint = $privyr_api->build_api_endpoint();
        $payload = $this->extract_fields($form_id, $form_values);

        $privyr_api->submit_lead_to_privyr($endpoint, $payload, Content_Type::JSON);
    }
}
