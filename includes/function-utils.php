<?php

if (!function_exists('_privyr_get_form_key')) {
    /**
     * Get form label as a key of JSON object payload,
     * if the given primary field name is empty, then try known alternative fields
     * @param mixed $field array or Instance we will extract the key from 
     * @param string $primary_field_name field name that we will try to extract first
     * @param array $alternative_field_names another extraction try with different names
     */
    function _privyr_get_form_key($field, $primary_field_name, $alternative_field_names = array())
    {
        $is_field_array = is_array($field);
        $primary_key = $is_field_array ? $field[$primary_field_name] : $field->{$primary_field_name};
        $extracted_alternative_key = array();
        foreach ($alternative_field_names as $name) {
            $key_value = $is_field_array ? $field[$name] : $field->{$name};
            array_push($extracted_alternative_key, $key_value);
        }
        $alternative_key = join('_', $extracted_alternative_key); // ie. "email_1"
        return $primary_key != "" ? $primary_key : $alternative_key;
    }
}
