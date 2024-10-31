<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

class Privyr_Formidable
{
    private function extract_value_by_type($item_meta, $field)
    {
        $base_value = $item_meta[$field->id];
        // Combine first & last name into one single string
        if ($field->type == 'name') {
            return trim("{$base_value['first']} {$base_value['last']}");
        }
        return $base_value;
    }

    /**
     * Extracting Formidable field values into a dictionary
     * containing key value pairs
     */
    private function extract_fields($form_id)
    {
        $item_meta = $_POST['item_meta'];
        $fields = FrmField::get_all_for_form($form_id);

        $body = array();
        foreach ($fields as $field) {
            $key = $field->name;
            $body[$key] = $this->extract_value_by_type($item_meta, $field);
        }
        return $body;
    }

    /** 
     * This is a formidable callback after the lead is submitted.
     * Calls a non blocking http API request to privyr with lead data.
     */
    public function submit_to_privyr($entry_id, $form_id)
    {
        $form = FrmForm::getOne( $form_id );
        $form_name = $form->name;

        $privyr_api = new Privyr_API('formidable_form', $form_name);
        $endpoint = $privyr_api->build_api_endpoint();
        $payload = $this->extract_fields($form_id);
        $privyr_api->submit_lead_to_privyr($endpoint, $payload, Content_Type::JSON);
    }
}
