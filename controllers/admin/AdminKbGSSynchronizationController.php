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

class AdminKbGSSynchronizationController extends ModuleAdminController
{
    /*
    * PHP 8.2: declare shop_id to avoid dynamic property deprecation
    * 01-08-2026
    */
    public $shop_id;

    //Class Constructor
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->display = 'view';
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* To fetch the shop_id if the shop_id not found than use the 1 as the default shop_id
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

        parent::__construct();

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
        $this->addJS($this->getModuleDirUrl() .$this->module->name. '/views/js/script.js');
        $this->addJS($this->getModuleDirUrl() .$this->module->name. '/views/js/velovalidation.js');
        $this->addJS($this->getModuleDirUrl() .$this->module->name. '/views/js/validate_admin.js');
        $this->addCSS($this->getModuleDirUrl() .$this->module->name. '/views/css/style.css');
    }
    
    //function defination to display view
    public function renderView()
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* To fetch the link for based on the shop_id
	*/ 
        $this->context->smarty->assign(array(
            'sync_products_listing_url' => $this->context->link->getModuleLink($this->module->name, 'cron', array('action' => 'syncProductsListing', 'secure_key'=> Configuration::get('KB_GS_SECURE_KEY1', null, null, (int)$this->shop_id))),
            'sync_feed_listing_url' => $this->context->link->getModuleLink($this->module->name, 'cron', array('action' => 'syncFeedsListing', 'secure_key'=> Configuration::get('KB_GS_SECURE_KEY1', null, null, (int)$this->shop_id))),
            'sync_product_status_url' => $this->context->link->getModuleLink($this->module->name, 'cron', array('action' => 'syncProductStatus', 'secure_key'=> Configuration::get('KB_GS_SECURE_KEY1', null, null, (int)$this->shop_id))),
            'sync_gs_queue' => $this->context->link->getModuleLink($this->module->name, 'cron', array('action' => 'syncGsQueue',  'secure_key'=> Configuration::get('KB_GS_SECURE_KEY1', null, null, (int)$this->shop_id))),
            'sync_local_url' => $this->context->link->getModuleLink($this->module->name, 'cron', array('action' => 'syncLocal',  'secure_key'=> Configuration::get('KB_GS_SECURE_KEY1', null, null, (int)$this->shop_id))),
            
            
        ));
		/*
		* Replaced Tools::jsonDecode by json_decode
		* Tools::jsonDecode has been removed in PrestaShop8
		* TGfeb2023 Used-json_decode
		* @date 25-02-2023
		* @author Tanisha Gupta
		*/ 
        $kb_general = json_decode(Configuration::get('kbgs_general_setting',null,null, (int)$this->shop_id), true);
        $this->context->smarty->assign('sync_type', $kb_general['sync_type']);
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name. '/views/templates/admin/synchronization.tpl');
    }
    
    //function to display page header toolbar
    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = $this->module->l('Google Shopping Synchronization', 'AdminKbGSSynchronizationController');
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
        
        parent::initPageHeaderToolbar();
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
	* @date 11-11-2024
	* Updated code to fetch the saved config based on the shop_id
	*/ 
        if ((bool) Configuration::get('PS_SSL_ENABLED',null, null, (int)$this->shop_id) && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }
}
