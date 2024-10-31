<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Un-authorized access!');
}

class Privyr_Houzez
{
    /**
     * Remove any unused or **sensitive** fields from the payload,
     * We will closely follow the original webhook implementation, you can find it on:
     * ./wp-content/themes/houzez/framework/functions/helper_functions.php
     */
    private function clean_payload($form_data)
    {
        // Exclude these fields from the payload, refer to the line 7072
        // or find related code with "webhook" keyword on helper_functions.php
        $exclude_fields = array(
            // Common fields
            'action',

            // Exists only on houzez elementor widget form fields
            'webhook',
            'webhook_url',
            'redirect_to',
            'email_to',
            'email_subject',
            'email_to_cc',
            'email_to_bcc',
            'houzez_contact_form',

            // Exists only on houzez built-in form fields
            'target_email',
            'property_nonce',
            'prop_payment',
            'property_agent_contact_security',
            'contact_realtor_ajax',
            'is_listing_form',
            'submit',
            'realtor_page',
        );

        if (!empty($form_data) && is_array($form_data)) {
            foreach ($exclude_fields as $field) {
                if (isset($form_data[$field])) {
                    unset($form_data[$field]);
                }
            }
        }

        return $form_data;
    }

    /** 
     * Calls a non blocking http API request to privyr with lead data.
     */
    public function submit_to_privyr()
    {
        $privyr_api = new Privyr_API('houzez');
        $endpoint = $privyr_api->build_api_endpoint();
        $payload = $this->clean_payload($_POST);
        $privyr_api->submit_lead_to_privyr($endpoint, $payload, Content_Type::JSON);
    }
}

/**
 * A. Houzez Built-in fields
 * The doco link? Nope, found it simply by testing the submission and check the payload :v
 * 
 *    === Common ===
 *    'name',
 *    'email',
 *    'message',
 *    'target_email',
 *    'property_permalink',
 *    'property_title',
 *    'listing_id',
 *    'agent_id',
 *    'agent_type',
 *    
 *    === Only available with action "houzez_property_agent_contact" ===
 *    'property_id',
 *    'mobile',
 *    'user_type',
 *    
 *    === Only available with action "houzez_schedule_send_message" ===
 *    'schedule_tour_type',
 *    'schedule_date',
 *    'schedule_time',
 *    
 *    === Only available with action "houzez_contact_realtor" ===
 *    === Sample url: http://localhost:8080/?houzez_agent=vincent-fuller ===
 *    'source_link',
 * 
 * B. Elementor Widget Fields
 * For this, it can be as flexible as user define from the elementor builder page
 * but mostly will follow Houzez built-in fields
 */
