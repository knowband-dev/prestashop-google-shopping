<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
* Load correct version of the Google Client lib according to the PS version
* GS005-1003024 load-GS-Lib
* @date 10-03-2024
* @author Ashish
*/
if(_PS_VERSION_ > 8) {
    require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/vendor/lib/google_client8/autoload.php');
} else {
    require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/vendor/lib/google_client/autoload.php');
}

require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSModule.php');

class AdminKbGSConnectionSettingsController extends ModuleAdminController
{
    /*
    * PHP 8.2: declare shop_id to avoid dynamic property deprecation
    * 01-08-2026
    */
    public $shop_id;

    //Class Constructor
    public function __construct()
    {
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $this->shop_id = (int)Shop::getContextShopID();
        if($this->shop_id == null){
            $this->shop_id = 1;
        }
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $this->shop_id = (int)Context::getContext()->shop->id;
        $this->context = Context::getContext();
        $this->bootstrap = true;
        
        parent::__construct();
        $this->toolbar_title = $this->module->l('Connection Setting', 'AdminKbGSConnectionSettingsController');
        //This is to show notification messages to admin
        if (!Tools::isEmpty(trim(Tools::getValue('kbgsConf')))) {
            new KbGSModule(Tools::getValue('kbgsConf'), 'conf');
        }
        /**
        * Fix issue of disconnect token
        * TGApr2023 disconnect_Token
        * @date 05-04-2023
        * @author Tanisha Gupta
        */
        if (Tools::getValue('action') == 'disconnect') {
            Configuration::deleteByName('Kbgs_token_info', null, null, (int) $this->shop_id);
            $auditLogEntryString = $this->module->l('Disconnected successfully', 'AdminKbGSConnectionSettingsController');
            $auditMethodName = 'AdminKbGSConnectionSettings::Disconnect()';
            KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);
            $success = $this->module->l('Disconnected Successfully.', 'KbGSModule');
            $this->context->cookie->__set('kb_redirect_success', $success);
            Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSConnectionSettings'));
        }  

        if (!Tools::isEmpty(trim(Tools::getValue('kbgsError')))) {
            new KbGSModule(Tools::getValue('kbgsError'), 'error');
        }
    }

    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJS($this->getModuleDirUrl() .$this->module->name. '/views/js/script.js');
        $this->addJS($this->getModuleDirUrl() .$this->module->name. '/views/js/velovalidation.js');
        $this->addJS($this->getModuleDirUrl() .$this->module->name. '/views/js/validate_admin.js');
        $this->addCSS($this->getModuleDirUrl() .$this->module->name. '/views/css/style.css');
    }

    //Function definition to render a form
    public function initContent()
    {
        if (isset($this->context->cookie->kbtest_redirect_error)) {
            $this->errors[] = $this->context->cookie->kbtest_redirect_error;
            unset($this->context->cookie->kbtest_redirect_error);
        }
          
        if (isset($this->context->cookie->kb_redirect_success)) {
            $this->confirmations[] = $this->context->cookie->kb_redirect_success;
            unset($this->context->cookie->kb_redirect_success);
        }
        //Buttons List
        /*
        * Replacec Tools::jsonDecode by json_decode
        * Tools::jsonDecode has been removed in PrestaShop8
        * TGfeb2023 Used-json_decode
        * @date 25-02-2023
        * @author Tanisha Gupta
        */ 
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
        $gs_connect = json_decode(Configuration::get('kbgs_connect_config',null,null, (int) $this->shop_id ), true);
        $token_connect_info = json_decode(Configuration::get('Kbgs_token_info',null,null, (int) $this->shop_id ), true);
        /**
         * Decode token to check whether refresh token exists or not
         * TGApr2023 decode_Token
         * @date 05-04-2023
         * @author Tanisha Gupta
         */
        $token_info = json_decode(Configuration::get('Kbgs_token_info',null,null, (int) $this->shop_id ),true);
        
        if (!empty($gs_connect)) {
            /**
            * Added condition to check refresh token exists or not.
            * If refersh token was not set, still module was showing module as connect
            * TGApr2023 disconnect_Token
            * @date 05-04-2023
            * @author Tanisha Gupta
            */
            if (!empty($token_info) && isset($token_info['refresh_token'])) {
                $is_token_expired = KbGSModule::isTokenExpired($token_info, $gs_connect);
                if (!$is_token_expired) {
                    $buttonsList = array(
                        'class' => 'btn btn-default pull-right',
                        'name' => 'disconnect_btn',
                        'id' => 'disconnect_btn',
                        'js' => "disconnect()",
                        'title' => $this->module->l('Disconnect', 'AdminKbGSConnectionSettingsController'),
                        'icon' => 'process-icon-ban'
                    );
                } else {
                    $buttonsList = array(
                        'class' => 'btn btn-default pull-right',
                        'name' => 'submit_gs_renewconnect_btn',
                        'id' => 'submit_gs_renewconnect_btn',
                        'title' => $this->module->l('Renew Token', 'AdminKbGSConnectionSettingsController'),
                        'icon' => 'process-icon-refresh'
                    );
                }
            } else {
                $buttonsList = array(
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submit_gs_connect_btn',
                    'id' => 'submit_gs_connect_btn',
                    'title' => $this->module->l('Connect', 'AdminKbGSConnectionSettingsController'),
                    'icon' => 'process-icon-refresh'
                );
            }
        } else {
            $buttonsList = array(
                'class' => 'btn btn-default pull-right',
                'name' => 'submit_gs_connect_btn',
                'id' => 'submit_gs_connect_btn112',
                'title' => $this->module->l('Connect', 'AdminKbGSConnectionSettingsController'),
                'icon' => 'process-icon-refresh'
            );
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Connection Setting', 'AdminKbGSConnectionSettingsController'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'disconnect_url'
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'savebtn'
                    ),
		 /**
		 * @Author Ravi Kant Gupta
		 * @date 20-09-2024
		 * To show the automatic sync enable disable field on the connection page
		 */  
                    array(
                        'type' => 'switch',
                        'label' => $this->module->l(' Automatic Upload Feed on the Google', 'AdminKbGSConnectionSettingsController'),
                        'name' => 'automatic_upload',
                        'values' => array(
                            array(
                                'id' => 'enable_on',
                                'value' => 1
                            ),
                            array(
                                'id' => 'enable_off',
                                'value' => 0
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('Google Application Name', 'AdminKbGSConnectionSettingsController'),
                        'name' => 'application_name',
                        'hint' => $this->module->l('Name of the application/project, you have made for google shopping.', 'AdminKbGSConnectionSettingsController'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('Google Client ID', 'AdminKbGSConnectionSettingsController'),
                        'hint' => $this->module->l('Client ID required to setup connection between store and Google Merchant', 'AdminKbGSConnectionSettingsController'),
                        'name' => 'client_id',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('Google Client Secret', 'AdminKbGSConnectionSettingsController'),
                        'hint' => $this->module->l('Client Secret Secret required to setup connection between store and Google Merchant', 'AdminKbGSConnectionSettingsController'),
                        'name' => 'client_secret',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('Google Merchant ID', 'AdminKbGSConnectionSettingsController'),
                        'name' => 'merchant_id',
                        'required' => true,
                    ),
                ),
                'buttons' => array(
                    $buttonsList,
                    array(
                        'class' => 'btn btn-default pull-right',
                        'name' => 'submit_gs_connectionform',
                        'id' => 'submit_gs_connectionform',
                        'title' => $this->module->l('Save', 'AdminKbGSConnectionSettingsController'),
                        'icon' => 'process-icon-save'
                    )
                )
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->default_form_language = $this->context->language->id;
        /*
        * Replacec Tools::jsonDecode by json_decode
        * Tools::jsonDecode has been removed in PrestaShop8
        * TGfeb2023 Used-json_decode
        * @date 25-02-2023
        * @author Tanisha Gupta
        */ 
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
        $kbgs_values = json_decode(Configuration::get('kbgs_connect_config',null,null, (int) $this->shop_id ), true);
        //Load Current Value
        $helper->fields_value['disconnect_url'] = $this->context->link->getAdminlink('AdminKbGSConnectionSettings') . '&action=disconnect';
        $helper->fields_value['application_name'] = (!empty($kbgs_values))?$kbgs_values['application_name']:'';
        $helper->fields_value['client_id'] = (!empty($kbgs_values))?$kbgs_values['client_id']:'';
        $helper->fields_value['client_secret'] =(!empty($kbgs_values))? $kbgs_values['client_secret']:'';
        $helper->fields_value['merchant_id'] = (!empty($kbgs_values))?$kbgs_values['merchant_id']:'';
	/**
	* @Author Ravi Kant Gupta
	* @date 20-09-2024
	* Adding value in the form
	*/  
    if(!empty($kbgs_values) && isset($kbgs_values['automatic_upload'])){
        $helper->fields_value['automatic_upload'] = $kbgs_values['automatic_upload'];
    }else{
        $helper->fields_value['automatic_upload'] = '0';
    }
        $helper->fields_value['savebtn'] = '';
        if (empty($token_connect_info)) {
            $this->context->smarty->assign('is_redirect_url', true);
        }
	/**
	* @Author Ravi Kant Gupta
	* @date 20-09-2024
	* To show the instructions on the connection page
	*/  
        $this->context->smarty->assign('redirect_url', $this->context->link->getModuleLink($this->module->name, 'getrefreshtoken', array('secure_key'=> Configuration::get('KB_GS_SECURE_KEY5',null,null, (int) $this->shop_id ))));
        $this->content .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/velovalidation.tpl');
        $this->content .= $helper->generateForm(array($fields_form));
        $redirection_instruction_content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/instructions.tpl');
         $this->content .= $redirection_instruction_content;
        
         /*
        * Added the message to show on the top of the page
        * @date 22-07-2026
        * @author Amit Singh
        */
        $msg1 = $this->module->l('This is a free version for demo purpose only. Kindly purchase the ');
        $link1 = $this->module->l('paid version ');
        $msg2 = $this->module->l('to use all features. Click');
        $link2 = $this->module->l('here');
        $msg3= $this->module->l(' to connect with us');
        $this->context->smarty->assign('msg1', $msg1);
        $this->context->smarty->assign('link1', $link1);
        $this->context->smarty->assign('msg2', $msg2);
        $this->context->smarty->assign('link2', $link2);
        $this->context->smarty->assign('msg3', $msg3);
        Context::getContext()->controller->warnings[] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/warning.tpl');
        

        parent::initContent();
    }

    //Function definition to handle Form Submission
    public function postProcess()
    {
        //Handle form submission
        if (Tools::isSubmit('submitAddconfiguration')) {
            $kbgs_values = array();
            $application_name = trim(Tools::getValue('application_name'));
            $client_id = trim(Tools::getValue('client_id'));
            $client_secret = trim(Tools::getValue('client_secret'));
            $merchant_id = trim(Tools::getValue('merchant_id'));
            $savebtn = Tools::getValue('savebtn');
	    // Fetching saved value of the input field
            $automatic_upload = Tools::getValue('automatic_upload');
            if (!empty($savebtn) && $savebtn == 'saveConnect') {
                Tools::redirect($this->context->link->getModuleLink($this->module->name, 'getrefreshtoken', array('connectGS' => 'true', 'secure_key'=> Configuration::get('KB_GS_SECURE_KEY5',null,null, (int) $this->shop_id ))));
            } elseif (!empty($savebtn) && $savebtn == 'saveConfiguration') {
                $kbgs_values['application_name'] = $application_name;
                $kbgs_values['client_id'] = $client_id;
                $kbgs_values['client_secret'] = $client_secret;
                $kbgs_values['merchant_id'] = $merchant_id;
		// Updating value
                $kbgs_values['automatic_upload'] = $automatic_upload;
                
                //existing_configuration
                /*
                * Replaced Tools::jsonDecode by json_decode
                * Tools::jsonDecode has been removed in PrestaShop8
                * TGfeb2023 Used-json_decode
                * @date 25-02-2023
                * @author Tanisha Gupta
                */  
                $kbsetting = json_decode(Configuration::get('kbgs_connect_config'), true);
                 /*
                * Added condition to check if the client_secret and client_id is present in the database
                * @date 18-11-2024
                * @author Ravi Kant Gupta
                */  
                if(!empty($kbsetting)){                
                if (($kbsetting['client_secret'] != $client_secret) ||
                    ($kbsetting['client_id'] != $client_id)) {
                    Configuration::deleteByName('Kbgs_token_info', null, null, (int) $this->shop_id);
                }
            }
                /*
                * Replacec Tools::jsonEncode by json_encode
                * Tools::jsonEncode has been removed in PrestaShop8
                * TGfeb2023 Used-json_encode
                * @date 25-02-2023
                * @author Tanisha Gupta
                */   
		/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 12-11-2024
		* Updated query to save the config data based on the current shop ID
		*/ 
                Configuration::updateValue('kbgs_connect_config', json_encode($kbgs_values), false, null, (int) $this->shop_id);
                
                //Audit Log Entry
                $auditLogEntryString = $this->module->l('Connection Settings updated for Google Shopping Module. Updated Values are as -', 'AdminKbGSConnectionSettingsController').' <br>'. $this->module->l('Application Name: ', 'AdminKbGSConnectionSettingsController') . $application_name .'<br>'.$this->module->l('Client ID: ', 'AdminKbGSConnectionSettingsController') . $client_id. '<br>'.$this->module->l('Client Secret: ', 'AdminKbGSConnectionSettingsController') . $client_secret . '<br>'.$this->module->l('Merchant ID: ', 'AdminKbGSConnectionSettingsController') . $merchant_id;
                $auditMethodName = 'AdminKbGSConnectionSettings::postProcess()';
                KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSConnectionSettings').'&kbgsConf=16');
            } elseif (!empty($savebtn) && $savebtn == 'saveRenew') {
                $this->renewToken();
                 //Audit Log Entry
                $auditLogEntryString = $this->module->l('Access Token is renewed successfully', 'AdminKbGSConnectionSettingsController');
                $auditMethodName = 'AdminKbGSConnectionSettings::renewToken()';
                KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);
            }
        }
        parent::postProcess();
    }
    
    //function to renew the access token
    public function renewToken()
    {
        /*
        * Replaced Tools::jsonDecode by json_decode
        * Tools::jsonDecode has been removed in PrestaShop8
        * TGfeb2023 Used-json_decode
        * @date 25-02-2023
        * @author Tanisha Gupta
        */ 
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated query to save the config data based on the current shop ID
	*/ 
        $kb_connect_config = json_decode(Configuration::get('kbgs_connect_config',null, null,  (int) $this->shop_id ), true);
        try {
            $token_info_json = Configuration::get('Kbgs_token_info',null, null,  (int) $this->shop_id );
            $refresh_token = Configuration::get('Kbgs_refresh_token',null, null,  (int) $this->shop_id );
            $client = new Google_Client();
            $client->setApplicationName($kb_connect_config['application_name']);
            $client->setClientId($kb_connect_config['client_id']);
            $client->setClientSecret($kb_connect_config['client_secret']);
            $client->setScopes('https://www.googleapis.com/auth/content');
            $client->setAccessType('offline');
            $client->setAccessToken(stripslashes($token_info_json));
            $client->refreshToken(stripslashes($refresh_token));
            $token_info_json = $client->getAccessToken();
            Configuration::updateValue('Kbgs_token_info', json_encode($token_info_json), false, null, (int) $this->shop_id);
           
        } catch (Exception $e) {
            $this->context->cookie->__set(
                'kbtest_redirect_error',
                $e->getMessage()
            );
            Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSConnectionSettings'));
        }
    }

    private function getModuleDirUrl()
    {
        $module_dir = '';
        if ($this->checkSecureUrl()) {
            $module_dir = Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        } else {
            $module_dir = Tools::getShopDomain(true) . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        }
        return $module_dir;
    }

    private function checkSecureUrl()
    {
        $custom_ssl_var = 0;

        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] == 'on') {
                $custom_ssl_var = 1;
            }
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $custom_ssl_var = 1;
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated query to save the config data based on the current shop ID
	*/ 
        if ((bool) Configuration::get('PS_SSL_ENABLED',null,null, (int) $this->shop_id ) && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }
}
