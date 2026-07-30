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

class KbGoogleShoppingGetRefreshTokenModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        $this->content_only = false;
    }
    
    //function defination load the content
    public function initContent()
    {
    	/**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
            if($shop_id == null){
                $shop_id = 1;
            }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the data based on the current shop ID
	*/ 
        if (Tools::getValue('secure_key') == Configuration::get('KB_GS_SECURE_KEY5', null, null, (int)$shop_id)) {
            $get_product_cron_url = $this->context->link->getModuleLink(
                $this->module->name,
                'getrefreshtoken',
                array('secure_key'=> Configuration::get('KB_GS_SECURE_KEY5', null, null, (int)$shop_id))
            );
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
		* @date 11-11-2024
		* Updated code to fetch the data based on the current shop ID
		*/ 
            $kb_connect_config = json_decode(Configuration::get('kbgs_connect_config', null, null, (int)$shop_id), true);
            try {
                $client = new Google_Client();
                $client->setApplicationName($kb_connect_config['application_name']);
                $client->setClientId($kb_connect_config['client_id']);
                $client->setClientSecret($kb_connect_config['client_secret']);
                $client->setRedirectUri(filter_var($get_product_cron_url, FILTER_SANITIZE_URL));
                $client->setScopes('https://www.googleapis.com/auth/content');
                $client->setAccessType('offline');
                if (Tools::getIsset('code')) {
                    $code = Tools::getValue('code');
                    $client->authenticate($code);
                    $token_info = $client->getAccessToken();
                    // changes done by kanishka kannoujia on 21-04-2022 to stores NULL corresponsding to the id_shop and is_shop_group
                    //Configuration::updateValue('Kbgs_token_info', Tools::jsonEncode($token_info));
                    /*
                    * Replacec Tools::jsonEncode by json_encode
                    * Tools::jsonEncode has been removed in PrestaShop8
                    * TGfeb2023 Used-json_encode
                    * @date 25-02-2023
                    * @author Tanisha Gupta
                    */  
                    Configuration::updateValue('Kbgs_token_info', json_encode($token_info), false, 0, (int)$shop_id);
                    if (isset($token_info['refresh_token'])) {
                        $refresh_token = $token_info['refresh_token'];
                        // changes done by kanishka kannoujia on 21-04-2022 to stores NULL corresponsding to the id_shop and is_shop_group
                        //Configuration::updateValue('Kbgs_refresh_token', $refresh_token);
                        Configuration::updateValue('Kbgs_refresh_token', $refresh_token, false, 0, (int)$shop_id);
                        die($this->module->l('Token has been saved. Please refresh the module configuration page and upload your products.'));
                    } else {
			/**
			* @Author Ravi Kant Gupta
			* @date 27-09-2024
			* Added instructions to fix the token not generated issue and also added the tutorial video URL
			*/ 
                        die($this->module->l('Refresh token is not generated. Please contact the support to fix the issue. Or you can follow the below steps to generate the refresh token. OR Go to My Account (https://myaccount.google.com) > Security > Third-party apps with account access > Google Shopping > Remove Access > Reconnect Google Shopping. Video Tutorial: https://share.nmblc.cloud/cf4cf97c'));
                    }
                } elseif (Tools::getIsset('connectGS')) {
                    Tools::redirect($client->createAuthUrl());
                } else {
                    die($this->module->l('Invalid Request'));
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            die($this->module->l('Invalid Request'));
        }
    }
}
