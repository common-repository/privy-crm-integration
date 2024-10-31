<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  die('Un-authorized access!');
}

/**
 * Detect plugin. For use in Admin area only.
 */
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.privyr.com
 * @since      0.1.0
 *
 * @package    Privyr_Crm
 * @subpackage Privyr_Crm/admin/partials
 */
function render_admin_page()
{
?>
  <div id="privyr-page" @vue:mounted="onMounted()">
    <div class="max-w-984 mx-auto bg-white text-dark text-center py-16 p-8 mt-8">
      <div class="max-w-2xl mx-auto">
        <img class="mb-6 mx-auto border-0" :src="logoUrl" style="max-width: 200px" />
        <h1 class="text-xxl font-bold text-dark mb-6 mt-8 truncate">Send Leads to Privyr CRM</h1>
        <p class="mb-12 text-base">
          Get new leads submitted via your WordPress contact forms sent to you instantly via email and
          the Privyr mobile app, giving you easy lead management and one-touch calls, WhatsApp, SMS, iMessage, and emails.
        </p>

        <!-- Token Field -->
        <div class="text-left mb-6">
          <label class="block text-gray-700 text-sm font-semibold mb-2" for="privyr_token">
            Privyr Account Token
          </label>
          <input
            id="privyr_token"
            v-model="privyrToken"
            class="privyr-text-input"
            :class="getTokenClasses()"
            type="text"
            placeholder="WP-ABCD1234"
            @keydown="isFormDirty = true"
          />
          <div class="text-xs text-red-700 tracking-wider font-normal mt-2" v-if="privyrTokenError">
            {{ privyrTokenError }}
          </div>
          <p class="my-1 text-gray-500">
            Get your Privyr Account Token under your <strong>Privyr Integrations tab >
              <a :href="webappIntegrationPageLink" target="_blank" class="privyr-external-link">
                WordPress Websites
              </a>
            </strong>
          </p>
        </div>

        <!-- Website Name Field -->
        <div class="text-left mb-6" v-if="isIntegrated">
          <label class="block text-gray-700 text-sm font-semibold mb-2" for="cf_reference">
            Website Name (Optional)
          </label>
          <input
            id="cf_reference"
            v-model="cfReference"
            class="privyr-text-input border-gray-500"
            type="text"
            placeholder="e.g. ACME Corporation Website"
            @keydown="isFormDirty = true"
          />
          <p class="my-1 text-gray-500">
            The Website Name field will be displayed on your new lead alerts and client details
            to let you know where the lead came from
          </p>
        </div>

        <div class="text-left mb-4">
          <label class="block text-gray-700 text-sm font-semibold mb-2">
            Contact Forms
          </label>
          <p v-if="isIntegrated" class="my-1">
            Select the WordPress contact form(s) you're using to connect them.
            Once connected, you can submit a test lead on your contact form to confirm it is received into your Privyr account.
          </p>
          <p v-else class="my-1">
            Privyr works with most of the popular WordPress contact form plugins.
            Simply enter your Privyr Account Token above and hit SAVE to connect your installed contact forms.
          </p>
        </div>

        <!-- 
          Integration Tag, 
          only shows for the first time when user not fill their token yet 
        -->
        <div class="text-left" v-if="!isIntegrated">
          <span
            v-for="(integration, index) in installedIntegrations"
            :key="index"
            class="bg-badge inline-block px-3 py-1 rounded-full font-bold mr-2 mb-2 uppercase"
          >
            <img class="inline float-left mr-2" style="max-height: 16px" :src="integration.iconUrl" />
            {{ integration.name }}
          </span>
        </div>

        <!-- List of integration toggle boxes -->
        <div class="text-left" v-else>
          <div v-for="(integration, index) in availableIntegrations" class="border mb-4 p-4" :class="getFieldClasses(integration)">
            <label class="privyr-integration-header flex" :for="integration.key">
              <img class="inline float-left mr-2" style="max-width: 16px" :src="integration.iconUrl" />
              <span class="flex-1">
                {{ integration.name }}
              </span>
              <label class="connected mr-4" v-if="integration.status === Status.Connected && integration.enabled">
                Connected!
              </label>
              <label class="text-sky-600 font-bold mr-4" v-else-if="integration.status === Status.Activated && integration.enabled">
                Save to Confirm
              </label>
              <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in self-end">
                <input
                  v-model="integration.enabled"
                  @change="handleOnToggleChange(index)"
                  type="checkbox"
                  name="toggle"
                  :id="integration.key"
                  class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white appearance-none cursor-pointer"
                />
                <label
                  :for="integration.key"
                  class="toggle-label block overflow-hidden w-8 h-4 rounded-full bg-gray-300 cursor-pointer"
                ></label>
              </div>
            </label>
            <div class="text-xs text-red-700 tracking-wider font-normal mt-2" v-if="integration.errorMessage">
              {{ integration.errorMessage }}
            </div>
          </div>
        </div>

        <button
          class="btn w-3/4 btn-secondary disabled:bg-gray-disabled disabled:text-gray-500"
          :class="isIntegrated ? 'mt-3' : 'mt-8'"
          :disabled="!isFormDirty || isSubmitting"
          @click="handleSubmit"
        >
          {{isSubmitting ? 'Saving...' : 'Save'}}
        </button>
      </div>
    </div>
    
    <!-- Help Footer Text -->
    <p class="text-center text-gray-400 text-sm mt-4">
    Check out our help guide for more details on 
      <a href="https://help.privyr.com/knowledge-base/wordpress-integration/" class="privyr-external-link" target="_blank">
        connecting WordPress to Privyr
      </a>
    </p>
    <p class="text-center text-gray-400 text-sm mt-4">
      Need help?
      <a href="mailto:support@privyr.com" class="privyr-external-link">
        Contact Privyr Support
      </a>
    </p>

    <!-- Toast -->
    <div class="flex justify-center fixed bottom-7 z-100" style="width: -webkit-fill-available">
      <div
        v-show="toast.visible"
        class="shadow-lg px-6 py-2 rounded-full flex items-center justify-center"
        :class="getToastStyles()"
      >
        <div class="toast-icon mr-4"></div>
        <div class="flex flex-col text-sm tracking-wide mr-6">
          <div class="font-medium">{{ toast.heading }}</div>
          <div>{{ toast.message }}</div>
        </div>
        <button class="ml-auto px-3 py-2 rounded-full focus:outline-none" @click="toast.visible = false">
          <div class="toast-close-icon"></div>
        </button>
      </div>
    </div>
  </div>

  <script>
    <?php
    $privyr_config = Privyr_options::get_values();
    $data_from_wp = array(
      "wordpressAjaxUrl" => esc_url(admin_url('admin-ajax.php')),
      "privyrWebhookSubscriptionUrl" =>  Privyr_Constants::get_webhook_subscription_url(),
      "webappIntegrationPageLink" => Privyr_Constants::WEBAPP_INTEGRATION_PAGE_LINK,
      "formActionName" => Privyr_Constants::WP_SAVE_HOOK_NAME,
      "logoUrl" => Privyr_Constants::get_logo_url(),
      "availableIntegrations" => Privyr_Options::get_available_integrations(),
      "privyrToken" => isset($privyr_config['privyr_token']) ? $privyr_config['privyr_token'] : '',
      "cfReference" => isset($privyr_config['cf_reference']) ? $privyr_config['cf_reference'] : '',
      "Status" => Privyr_Integration_Status::to_array()
    );
    ?>

    // Load variables from PHP into javascript realm;
    const dataFromWP = <?php echo json_encode($data_from_wp, JSON_HEX_TAG); ?>;

    PetiteVue.createApp({
      // === All embedded data from Wordpress PHP ===
      ...dataFromWP,

      // === Data or Computed ===
      isSubmitting: false,
      isIntegrated: !!dataFromWP.privyrToken,
      privyrTokenError: null,
      isFormDirty: false,
      toast: {
        visible: false,
        type: '',
        heading: '',
        message: ''
      },
      installedIntegrations: dataFromWP.availableIntegrations,

      // === Lifecycle ===
      onMounted() {
        const url = new URL(window.location.href)
        if (!url.searchParams.has('success')) return
        this.showToast({
          type: 'success',
          heading: 'Saved',
          message: 'Success! Your updates have been saved'
        })
      },

      // === Methods === 
      getFieldClasses(integration) {
        if (integration.enabled) {
          if(integration.status === dataFromWP.Status.Connected) {
            return 'border-green-400 bg-green-100'
          }
          if (integration.status === dataFromWP.Status.Activated) {
            return 'border-sky-400 bg-sky-100'
          }
        }
        return 'border-gray-200 bg-gray-50'
      },

      getInputClasses(integration) {
        if (integration.status === dataFromWP.Status.Connected) return 'border-green-400 connected'
        return 'border-gray-500'
      },

      getTokenClasses() {
        if (this.privyrTokenError) return 'border-red-400 text-red-700 error'
        return 'border-gray-500'
      },

      getToastStyles() {
        const styles = {
          error: 'toast-error bg-fire-light text-fire',
          success: 'toast-success bg-secondary-light text-secondary'
        }
        return styles[this.toast.type]
      },

      clearAllErrors() {
        this.privyrTokenError = null
        this.availableIntegrations.forEach((_, index) => {
          this.availableIntegrations[index].errorMessage = null
        })
      },

      getPayload() {
        return this.availableIntegrations
          .filter(integration => integration.status !== this.Status.NotExist)
          .filter(integration => integration.enabled)
          .reduce((prev, integration) => ({
            ...prev,
            [integration.key]: integration.enabled
          }), {
            privyr_token: this.privyrToken
          })

      },

      tryRegisterToken() {
        const payload = this.getPayload()
        return fetch(this.privyrWebhookSubscriptionUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
          })
          .then(response => response.json())
          .then((result) => {
            if (result.success && result.success !== "False") {
              return result
            }
            this.privyrTokenError = result.message
            const error = new Error('Oops! The Privyr Account Token is not valid. Please check and try again.')
            error.heading = 'Invalid Privyr Account Token'
            throw error
          })
      },

      saveFormIntoWP() {
        const payload = this.getPayload()
        return fetch(this.wordpressAjaxUrl, {
          method: 'POST',
          body: new URLSearchParams({
            ...payload,
            action: this.formActionName,
            cf_reference: this.cfReference
          })
        })
      },

      handleOnToggleChange(index) {
        const status = this.availableIntegrations[index].status
        const errorMessageMap = {
          [this.Status.NotExist]: 'Oops! This plugin or theme isn\'t currently activated on your WordPress site.', 
          [this.Status.Installed]: 'Oops! This plugin or theme isn\'t currently activated on your WordPress site.', 
        }
        const errorMessage = errorMessageMap[status]
        if (!errorMessage) {
          this.isFormDirty = true
          return
        }
        this.availableIntegrations[index].errorMessage = errorMessage

        // Revert the toggle value after short delay
        const delay = 200 // in ms
        setTimeout(() => {
          this.availableIntegrations[index].enabled = false
        }, delay)
      },

      handleSubmit() {
        this.clearAllErrors();
        this.isSubmitting = true
        this.tryRegisterToken()
          .then(this.saveFormIntoWP)
          .then(() => {
            window.location.search += "&success=true"
          })
          .catch((error) => {
            this.isSubmitting = false
            this.showToast({
              type: 'error',
              heading: error.heading,
              message: error.message.includes('not valid') ? error.message : null
            })
          })
      },

      showToast(params) {
        this.toast.visible = true
        this.toast.type = params.type
        this.toast.heading = params.heading || 'Errors Detected'
        this.toast.message = params.message || 'There were one or more errors detected. Please check and try again.'

        // Auto close after 3s
        const delay = 3000
        setTimeout(() => {
          this.toast.visible = false
        }, delay)
      }
    }).mount("#privyr-page")
  </script>
<?php
}
//<!-- This file should primarily consist of HTML with a little bit of PHP. -->
