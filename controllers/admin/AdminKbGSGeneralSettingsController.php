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

//Include Google Module Class to inherit some common functions and callbacks
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSModule.php');

class AdminKbGSGeneralSettingsController extends ModuleAdminController
{
    //Class Constructor
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
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
        parent::__construct();
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        $this->toolbar_title = $this->module->l('General Settings', 'AdminKbGSGeneralSettingsController');
        //This is to show notification messages to admin
        if (!Tools::isEmpty(trim(Tools::getValue('kbgsConf')))) {
            new KbGSModule(Tools::getValue('kbgsConf'), 'conf');
        }

        if (!Tools::isEmpty(trim(Tools::getValue('kbgsError')))) {
            new KbGSModule(Tools::getValue('kbgsError'), 'error');
        }
    }

    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJS($this->getModuleDirUrl() . $this->module->name . '/views/js/script.js');
        $this->addJS($this->getModuleDirUrl() . $this->module->name . '/views/js/velovalidation.js');
        $this->addJS($this->getModuleDirUrl() . $this->module->name . '/views/js/validate_admin.js');
        $this->addCSS($this->getModuleDirUrl() . $this->module->name . '/views/css/style.css');
    }

    //Function definition to render a form
    public function initContent()
    {
        /**
         * Added condition to check if getFormatedName method exists or not. if not use the getFormattedName
         * And assign the image formatted name into the variable and use the same into the helper form
         * TGmarch2023 Exist-getFormatedName
         * @date 30-03-2023
         * @author Tanisha Gupta
         */
        if (method_exists(ImageType::class, 'getFormatedName')) {
            $home = ImageType::getFormatedName('home');
            $large = ImageType::getFormatedName('large');
        } else {
            $home = ImageType::getFormattedName('home');
            $large = ImageType::getFormattedName('large');
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('General Setting', 'AdminKbGSGeneralSettingsController'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->module->l('Enable the plugin', 'AdminKbGSGeneralSettingsController'),
                        'name' => 'enable',
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
                        'type' => 'select',
                        'label' => $this->module->l('Image Size of Product', 'AdminKbGSGeneralSettingsController'),
                        'name' => 'image_size',
                        'hint' => $this->module->l('Select size of image ', 'AdminKbGSGeneralSettingsController'),
                        'options' => array(
                            'query' => array(
                                /**
                                 * Used the variable that set above
                                 * TGmarch2023 Exist-getFormatedName
                                 * @date 30-03-2023
                                 * @author Tanisha Gupta
                                 */
                                array(
                                    'id' => $home,
                                    'name' => $home
                                ),
                                array(
                                    'id' =>  $large,
                                    'name' =>  $large,
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->module->l('Exclude Out of Stock Products', 'AdminKbGSGeneralSettingsController'),
                        'name' => 'exclude_out_of_stock',
                        'values' => array(
                            array(
                                'id' => 'exclude_out_of_stock_on',
                                'value' => 1
                            ),
                            array(
                                'id' => 'exclude_out_of_stock_off',
                                'value' => 0
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('Exclude Products with Price less than', 'AdminKbGSGeneralSettingsController'),
                        'name' => 'exclude_product_less',
                        'col' => 4,
                        'prefix' => $this->context->currency->sign,
                        'hint' => $this->module->l('Exclude product whose price is less than this value', 'AdminKbGSGeneralSettingsController'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->module->l('Exclude Products who does not have EAN13/JAN or UPC', 'AdminKbGSGeneralSettingsController'),
                        'name' => 'exclude_product_gtin',
                        'values' => array(
                            array(
                                'id' => 'exclude_product_gtin_on',
                                'value' => 1
                            ),
                            array(
                                'id' => 'exclude_product_gtin_off',
                                'value' => 0
                            )
                        ),
                    ),
                    /**
                     * New field whehter to sync the GTIN
                     * GS003-09032024 gs-sync-gtin
                     * @date 09-03-2024
                     * @author Ashish
                     */
                    array(
                        'type' => 'switch',
                        'label' => $this->module->l('Whether Sync the GTIN to Google?', 'AdminKbGSGeneralSettingsController'),
                        'hint' => $this->module->l('Disable the field if you do not have valid GTIN number on the products otherwise google merchant will throw the errors for products', 'AdminKbGSGeneralSettingsController'),
                        'name' => 'sync_gtin',
                        'values' => array(
                            array(
                                'id' => 'sync_gtin_on',
                                'value' => 1
                            ),
                            array(
                                'id' => 'sync_gtin_off',
                                'value' => 0
                            )
                        ),
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->module->l('Sync Type', 'AdminKbGSGeneralSettingsController'),
                        'name' => 'sync_type',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('utm_campaign Parameter', 'AdminKbGSGeneralSettingsController'),
                        'name' => 'utm_campaign',
                        'col' => 4,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('utm_source Parameter', 'AdminKbGSGeneralSettingsController'),
                        'name' => 'utm_source',
                        'col' => 4,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('utm_medium  Parameter', 'AdminKbGSGeneralSettingsController'),
                        'name' => 'utm_medium',
                        'col' => 4,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Select your default language', 'AdminKbGSGeneralSettingsController'),
                        'name' => 'gs_default_lang',
                        'options' => array(
                            'query' => Language::getLanguages(true),
                            'id' => 'id_lang',
                            'name' => 'name'
                        ),
                    ),

                ),
                'submit' => array(
                    'title' => $this->module->l('Save', 'AdminKbGSGeneralSettingsController'),
                    'class' => 'btn btn-default pull-right form_kb_add_general'
                ),
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->default_form_language = $this->context->language->id;

        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;

        //Load Current Value
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
	* Updated code to fetch the data based on the shop_id
	*/
        $gs_general_setting = json_decode(Configuration::get('kbgs_general_setting',null,null, (int) $this->shop_id ), true);
        $helper->fields_value['enable'] = (!empty($gs_general_setting)) ? $gs_general_setting['enable'] : '';
        $helper->fields_value['image_size'] = (!empty($gs_general_setting)) ? $gs_general_setting['image_size'] : '';
        /*
        * Addded changes to remove the set the value of the fields in free version
        * @date 22-07-2026
        * @author Amit Singh
        */
        $helper->fields_value['exclude_out_of_stock'] = '';
        $helper->fields_value['exclude_product_less'] = '';
        $helper->fields_value['exclude_product_gtin'] = (!empty($gs_general_setting)) ? $gs_general_setting['exclude_product_gtin'] : '';

        //GS003-09032024 gs-sync-gtin
        $helper->fields_value['sync_gtin'] = (!empty($gs_general_setting['sync_gtin'])) ? $gs_general_setting['sync_gtin'] : 0;

        $helper->fields_value['gs_default_lang'] = (!empty($gs_general_setting)) ? $gs_general_setting['gs_default_lang'] : '';
        $helper->fields_value['utm_campaign'] = '';
        $helper->fields_value['sync_type'] = (!empty($gs_general_setting)) ? $gs_general_setting['sync_type'] : 'feed';
        $helper->fields_value['utm_source'] = '';
        $helper->fields_value['utm_medium'] = '';
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
        
        $this->content .= $helper->generateForm(array($fields_form));
        $this->content .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/velovalidation.tpl');

        // Added changes to append the modal
        $this->content .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/modal.tpl');

        parent::initContent();
    }

    //Function definition to handle Form Submission
    public function postProcess()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        if (Tools::isSubmit('submitAddconfiguration')) {
            $kbgeneral = array();
            $kbgeneral['enable'] = Tools::getValue('enable');
            $kbgeneral['image_size'] = Tools::getValue('image_size');
            /*
            * Addded changes to remove the set the value of the fields in free version
            * @date 22-07-2026
            * @author Amit Singh
            */
            $kbgeneral['exclude_out_of_stock'] = '';
            $kbgeneral['exclude_product_less'] = '';
            $kbgeneral['exclude_product_gtin'] = Tools::getValue('exclude_product_gtin');

            //GS003-09032024 gs-sync-gtin
            $kbgeneral['sync_gtin'] = Tools::getValue('sync_gtin');

            $kbgeneral['sync_type'] = Tools::getValue('sync_type');
            /*
            * Addded changes to remove the set the value of the fields in free version
            * @date 22-07-2026
            * @author Amit Singh
            */
            $kbgeneral['utm_campaign'] = '';
            $kbgeneral['utm_source'] = '';
            $kbgeneral['utm_medium'] = '';
            $kbgeneral['gs_default_lang'] = trim(Tools::getValue('gs_default_lang'));
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
		* Updated code to fetch the data based on the shop_id
		*/
            $gs_general_setting = json_decode(Configuration::get('kbgs_general_setting',null,null, (int) $this->shop_id ), true);
            if (Tools::getValue('gs_default_lang') != $gs_general_setting['gs_default_lang']) {
                //$this->addGoogleCategories();
            }
            /*
			* Replacec Tools::jsonEncode by json_encode
			* Tools::jsonEncode has been removed in PrestaShop8
			* TGfeb2023 Used-json_encode
			* @date 25-02-2023
			* @author Tanisha Gupta
			*/
            Configuration::updateValue('kbgs_general_setting', json_encode($kbgeneral),false,null, (int) $this->shop_id );
            Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSGeneralSettings') . '&kbgsConf=16');
        }
        parent::postProcess();
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
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
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
	* Updated code to fetch the data based on the shop_id
	*/
        if ((bool) Configuration::get('PS_SSL_ENABLED',null,null, (int) $this->shop_id ) && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }
}
