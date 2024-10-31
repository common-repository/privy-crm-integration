<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

class Privyr_CF7
{
    /**
     * Format and prepare the raw data from contact form 7 
     */
    private function get_posted_data($cf7)
    {
        $token = Privyr_Options::get_single_value('privyr_token');
        $cf_reference = Privyr_Options::get_single_value('cf_reference');
        if (!isset($cf7->posted_data) && class_exists('WPCF7_Submission') && !empty($token)) {
            // Contact Form 7 version 3.9 removed $cf7->posted_data and now
            // we have to retrieve it from an API
            $submission = WPCF7_Submission::get_instance();
            if ($submission) {
                return array(
                    'title' => $cf7->title(),
                    'form_data' => $submission->get_posted_data(),
                    'privyr_token' => $token,
                    'cf_reference' => $cf_reference,
                    'wp_cf_type' => "contact_form7"
                );
            }
            return (array)$cf7;
        }
        return null;
    }

    /** 
     * This is a wpcf7 callback after the lead is submitted.
     * Calls a non blocking http API request to privyr with lead data.
     */
    public function submit_cf7_to_privyr($contact_form)
    {
        $privyr_api = new Privyr_API('cf7');
        $webhook_url = Privyr_Constants::get_webhook_url('cf7');
        $payload = $this->get_posted_data($contact_form);
        $privyr_api->submit_lead_to_privyr($webhook_url, $payload, Content_Type::JSON);
    }
}
