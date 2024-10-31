<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

class Privyr_Gravity_Form
{
    /**
     * gravity form handles `list` type values very poorly, so we need to use regex to get the values.
     * Input: str => 1[1]/"item1"/2[4]/"item2"/
     * Output: array => ( [0] => item1 [1] => item2 )
     */
    private function get_list_values($string)
    {
        // geting values in  between /" and "/
        preg_match_all('/"(.*?)"/', $string, $match);
        return $match[1];
    }


    /**
     * Reformat gravity form fields into dict key value pair
     * So it is easier to be consumed by our webhook API
     */
    private function format_fields_payload($entry, $data)
    {
        $body = array();
        foreach ($data as $field) {
            $inputs = $field->get_entry_inputs();
            $key = _privyr_get_form_key($field, 'label', array('type', 'id'));
            if (is_array($inputs)) {
                $temp = array();
                foreach ($inputs as $input) {
                    $value = rgar($entry, (string) $input['id']);
                    if (!empty($value)) {
                        $temp[] = $value;
                    }
                }
                // Whenever field type is name, combine the array of names into a single full name string instead
                if ($field->type == 'name') {
                    $body[$key] = implode(" ", $temp);
                } else {
                    $body[$key] = json_encode(array_values($temp), JSON_UNESCAPED_SLASHES);
                }
            } else {
                $value = rgar($entry, (string) $field->id);

                if ($field->type == 'list') {
                    $body[$key] = json_encode(array_values($this->get_list_values($value)), JSON_UNESCAPED_SLASHES);
                } else {
                    $body[$key] = $value;
                }
            }
        }
        return $body;
    }



    /** 
     * Calls a non blocking http API request to privyr with lead data.
     */
    public function submit_to_privyr($entry, $form)
    {
        $form_name = $form["title"];
        $privyr_api = new Privyr_API('gravity_form', $form_name);
        $endpoint = $privyr_api->build_api_endpoint($form_name);
        $payload = $this->format_fields_payload($entry, $form['fields']);
        $privyr_api->submit_lead_to_privyr($endpoint, $payload, Content_Type::FORM_URLENCODED);
    }
}
