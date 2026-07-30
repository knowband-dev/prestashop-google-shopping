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
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSFeeds.php');
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSProfiles.php');

class AdminKbGSFeedManagementController extends ModuleAdminController
{

    //Class Constructor
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'kb_gs_feeds';
        $this->className = 'KbGSFeeds';
        $this->identifier = 'id_gs_feed';
        $this->module_configured = false;
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
        
        parent::__construct();
        $this->toolbar_title = $this->module->l('Feed Management', 'AdminKbGSFeedManagementController');
        $this->module_configured = KbGSModule::checkInstallation();
        if ($this->module_configured) {
            $this->icon = array(0 => 'disabled.gif', 1 => 'enabled.gif');
            $this->fields_list = array(
                'id_gs_feed' => array(
                    'title' => $this->module->l('Feed ID', 'AdminKbGSFeedManagementController'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs'
                ),
                'feed_label' => array(
                    'title' => $this->module->l('Feed Name', 'AdminKbGSFeedManagementController'),
                    'align' => 'left',
                ),
                'profile_title' => array(
                    'search' => true,
                    'title' => $this->module->l('Profile Title', 'AdminKbGSFeedManagementController'),
                    'align' => 'left'
                ),
                'date_add' => array(
                    'title' => $this->module->l('Added On', 'AdminKbGSFeedManagementController'),
                    'search' => true,
                    'align' => 'left',
                    'type' => 'datetime',
                    'filter_key' => 'a!date_add',
                ),
                'date_upd' => array(
                    'title' => $this->module->l('Updated On', 'AdminKbGSFeedManagementController'),
                    'search' => true,
                    'align' => 'left',
                    'type' => 'datetime',
                    'filter_key' => 'a!date_upd',
                )
            );

            $this->_select = 'ecm.profile_title';
            $this->_join = ' LEFT JOIN ' . _DB_PREFIX_ . 'kb_gs_profiles ecm ON (ecm.id_gs_profiles = a.id_gs_profiles)';
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
            $this->_where = 'AND a.id_shop = ' . (int)$this->shop_id;

            //This is to show notification messages to admin
            if (!Tools::isEmpty(trim(Tools::getValue('kbgsConf')))) {
                new KbGSModule(Tools::getValue('kbgsConf'), 'conf');
            }

            if (!Tools::isEmpty(trim(Tools::getValue('kbgsError')))) {
                new KbGSModule(Tools::getValue('kbgsError'), 'error');
            }
        } else {
            $this->display = 'view';
        }
    }
    
    //Display Settings error.
    public function renderView()
    {
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name. '/views/templates/admin/_configure/helpers/view/install_error.tpl');
    }

    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        
        $this->addJS($this->getModuleDirUrl() . 'kbgoogleshopping/views/js/script.js');
        /*
        * Include modal js file only if the count is more than 0.
        * @date 22-07-2026
        * @author Amit Singh
        */
        if ($this->checkCount()) {
            $this->addJS($this->getModuleDirUrl() . 'kbgoogleshopping/views/js/modal.js');
        }
        $this->addJS($this->getModuleDirUrl() . 'kbgoogleshopping/views/js/velovalidation.js');
        $this->addJS($this->getModuleDirUrl() . 'kbgoogleshopping/views/js/validate_admin.js');
        $this->addCSS($this->getModuleDirUrl() . 'kbgoogleshopping/views/css/style.css');
    }

    //function to render helper list
    public function renderList()
    {
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $this->shop_id = (int)Context::getContext()->shop->id;
		/*
		* Replaced Tools::jsonDecode by json_decode
		* Tools::jsonDecode has been removed in PrestaShop8
		* TGfeb2023 Used-json_decode
		* @date 25-02-2023
		* @author Tanisha Gupta
		*/ 
	/**
	* @Author Ravi Kant Gupta
	* @date 27-09-2024
	* To show the error message if the automatic sync is disabled on the feed page
	*/  
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
        $gs_config = json_decode(Configuration::get('kbgs_connect_config',null,null, (int) $this->shop_id ), true);
        $automatic_upload = isset($gs_config['automatic_upload']) ? $gs_config['automatic_upload'] : '0';

        // Initialized the variable
        $content = '';
        if ($automatic_upload == '0') {
            Context::getContext()->controller->errors[] = $this->module->l("This feature is required only when Automatic product sync is enabled.", 'AdminKbGSFeedManagementController');
            return;
        }
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        //$this->addRowAction('status');
        /*
        * Appending the modal tpl file to render it on front end.
        * @date 22-07-2026
        * @author Amit Singh
        */
        $content .= parent::renderList();
        $content .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/modal.tpl');
        return $content;
    }

    /** Display Enable/Disable action link  */
    public function displayStatusLink($token = null, $id = null, $name = null)
    {
        $kbProfile = new KbGSFeeds($id);
        if ($kbProfile->active) {
            $status = 'Disable';
            $icon = 'circle-o';
        } else {
            $status = 'Enable';
            $icon = 'circle';
        }
        if (!array_key_exists($status, self::$cache_lang)) {
            self::$cache_lang[$status] = sprintf($this->module->l('%s', 'AdminKbGSFeedManagementController'), $status);
        }

        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminKbGSFeedManagement') . '&' . $this->identifier . '=' . $id . '&status=' . $status,
            'action' => self::$cache_lang[$status],
            'icon' => $icon
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/list/list_action.tpl');
    }

    //function to render form
    public function renderForm()
    {
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $this->shop_id = (int)Context::getContext()->shop->id;
		/*
		* Replaced Tools::jsonDecode by json_decode
		* Tools::jsonDecode has been removed in PrestaShop8
		* TGfeb2023 Used-json_decode
		* @date 25-02-2023
		* @author Tanisha Gupta
		*/ 
        $kb_general = json_decode(Configuration::get('kbgs_general_setting',null,null, (int) $this->shop_id ), true);
        if ($kb_general['sync_type'] == 'api') {
            return;
        }
        $available_profile = KbGSProfiles::getAllProfile();
        array_unshift($available_profile, array('id_gs_profiles' => null, 'profile_title' => $this->module->l('Select')));

        $days = array();
        for ($i = 1; $i <= 31; $i++) {
            $days[] = array(
                'id' => $i,
                'day' => $i,
            );
        }

        $hours = array();
        for ($i = 0; $i < 24; $i++) {
            $hours[] = array(
                'id' => $i,
                'hour' => $i,
            );
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => !Tools::isEmpty(trim(Tools::getValue('id_gs_feed'))) ? $this->module->l('Update Feed', 'AdminKbGSFeedManagementController') : $this->module->l('Add New Feed', 'AdminKbGSFeedManagementController'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Select Profile', 'AdminKbGSFeedManagementController'),
                    'name' => 'id_gs_profiles',
                    'required' => true,
                    'options' => array(
                        'query' => $available_profile,
                        'id' => 'id_gs_profiles',
                        'name' => 'profile_title'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Feed Label', 'AdminKbGSFeedManagementController'),
                    'name' => 'feed_label',
                    'hint' => $this->module->l('Enter the feed name', 'AdminKbGSFeedManagementController'),
                    'required' => true,
                    'col' => 4,
                    /**
                     * Made changes so feed name will be not be modified.
                     * If 'id_gs_feed' is not empty, 'readonly' is set to true; otherwise, it is set to false 
                     * @date 14-06-2023
                     * @author Tanisha Gupta
                     */
                    'readonly' => !Tools::isEmpty(trim(Tools::getValue('id_gs_feed'))) ? true : false
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Feed Upload Schedule', 'AdminKbGSFeedManagementController'),
                    'name' => 'upload_type',
                    'required' => true,
                    'hint' => $this->module->l('Set the feed upload schedule', 'AdminKbGSFeedManagementController'),
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'daily',
                                'name' => $this->module->l('Daily', 'AdminKbGSFeedManagementController'),
                            ),
                            array(
                                'id' => 'weekly',
                                'name' => $this->module->l('Weekly', 'AdminKbGSFeedManagementController'),
                            ),
                            array(
                                'id' => 'monthly',
                                'name' => $this->module->l('Monthly', 'AdminKbGSFeedManagementController'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Set Day of Month', 'AdminKbGSFeedManagementController'),
                    'name' => 'day_of_month',
                    'required' => true,
                    'hint' => $this->module->l('Set the day of month. Value between(1-31)', 'AdminKbGSFeedManagementController'),
                    'options' => array(
                        'query' => $days,
                        'id' => 'id',
                        'name' => 'day'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Set Day of Week', 'AdminKbGSFeedManagementController'),
                    'name' => 'weekday',
                    'required' => true,
                    'hint' => $this->module->l('Set the day of week', 'AdminKbGSFeedManagementController'),
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'sunday',
                                'name' => $this->module->l('Sunday', 'AdminKbGSFeedManagementController'),
                            ),
                            array(
                                'id' => 'monday',
                                'name' => $this->module->l('Monday', 'AdminKbGSFeedManagementController'),
                            ),
                            array(
                                'id' => 'tuesday',
                                'name' => $this->module->l('Tuesday', 'AdminKbGSFeedManagementController'),
                            ),
                            array(
                                'id' => 'wednesday',
                                'name' => $this->module->l('Wednesday', 'AdminKbGSFeedManagementController'),
                            ),
                            array(
                                'id' => 'thursday',
                                'name' => $this->module->l('Thursday', 'AdminKbGSFeedManagementController'),
                            ),
                            array(
                                'id' => 'friday',
                                'name' => $this->module->l('Friday', 'AdminKbGSFeedManagementController'),
                            ),
                            array(
                                'id' => 'saturday',
                                'name' => $this->module->l('Saturday', 'AdminKbGSFeedManagementController'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Set Hour', 'AdminKbGSFeedManagementController'),
                    'name' => 'upload_hour',
                    'required' => true,
                    'hint' => $this->module->l('Set the hour of day. Value between(0-23)', 'AdminKbGSFeedManagementController'),
                    'options' => array(
                        'query' => $hours,
                        'id' => 'id',
                        'name' => 'hour'
                    ),
                ),
            ),
            'submit' => array(
                'class' => 'btn btn-default pull-right',
                'title' => $this->module->l('Save', 'AdminKbGSFeedManagementController'),
            )
        );

        $tpl = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/velovalidation.tpl'
        );

        return $tpl . parent::renderForm();
    }

    //function to submit the new form data
    public function processAdd()
    {
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $this->shop_id = (int)Context::getContext()->shop->id;
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            /*
                * Added condition to check the feed creation limit
                * @date 22-07-2026
                * @author Amit Singh
                */
            if (!$this->checkCount()) {
            $customError = array();
            $id_gs_profiles = Tools::getValue('id_gs_profiles');
            $feed_label = trim(Tools::getValue('feed_label'));
            $upload_type = Tools::getValue('upload_type');
            $day_of_month = Tools::getValue('day_of_month');
            $weekday = Tools::getValue('weekday');
            $upload_hour = Tools::getValue('upload_hour');
            $kbfeedbyId = KbGSFeeds::getFeedByProfileId($id_gs_profiles);
            if (empty($kbfeedbyId)) {
                $kbfeed = new KbGSFeeds();
                $kbfeed->id_gs_profiles = $id_gs_profiles;
                $kbfeed->feed_label = $feed_label;
                $kbfeed->upload_type = $upload_type;
                $kbfeed->upload_hour = $upload_hour;
                $kbfeed->day_of_month = $day_of_month;
                $kbfeed->weekday = $weekday;
                $kbfeed->id_shop = (int)$this->shop_id;
                $kbfeed->active = 1;
                if ($kbfeed->add()) {
                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSFeedManagement') . '&kbgsConf=13');
                }
            } else {
                $customError[] = 10;
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSFeedManagement') . '&kbgsError=' . implode(",", $customError));
             }
		        /*
                * Displaying error message
                * @date 22-07-2026
                * @author Amit Singh
                */
            } else {
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSFeedManagement') . '&kbgsError=98');
            }
        }
    }

    //function to update the form data
    public function processUpdate()
    {
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $this->shop_id = (int)Context::getContext()->shop->id;
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $customError = array();
            $id_gs_profiles = Tools::getValue('id_gs_profiles');
            $feed_label = trim(Tools::getValue('feed_label'));
            $upload_type = Tools::getValue('upload_type');
            $day_of_month = Tools::getValue('day_of_month');
            $weekday = Tools::getValue('weekday');
            $upload_hour = Tools::getValue('upload_hour');
            $kbfeedbyId = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . $this->table . ' WHERE id_gs_profiles=' . (int) $id_gs_profiles . ' AND id_gs_feed!=' . (int) Tools::getValue('id_gs_feed') .' AND id_shop=' . (int) $this->shop_id);

            if (empty($kbfeedbyId)) {
                $kbfeed = new KbGSFeeds(Tools::getValue('id_gs_feed'));
                $kbfeed->id_gs_profiles = $id_gs_profiles;
                $kbfeed->feed_label = $feed_label;
                $kbfeed->upload_type = $upload_type;
                $kbfeed->upload_hour = $upload_hour;
                $kbfeed->day_of_month = $day_of_month;
                $kbfeed->weekday = $weekday;
                $kbfeed->id_shop = (int)$this->shop_id;
                if ($kbfeed->update()) {
                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSFeedManagement') . '&kbgsConf=14');
                }
            } else {
                $customError[] = 10;
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSFeedManagement') . '&kbgsError=' . implode(",", $customError));
            }
        }
    }

    //Function definition to handle Form Submission
    public function postProcess()
    {	
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $this->shop_id = (int)Context::getContext()->shop->id;

        /**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated code to save the shop_id
	*/ 
        if (Tools::getValue('update' . $this->table)) {
            $KbGSFeeds = new KbGSFeeds(Tools::getValue('id_gs_feed'));
            if ($KbGSFeeds->active) {
                $KbGSFeeds->active = 0;
                $KbGSFeeds->id_shop = (int)$this->shop_id;
                $KbGSFeeds->update();
                $auditLogEntryString = $this->module->l('Feed -', 'AdminKbGSFeedManagementController') . ' <b>' . $KbGSFeeds->feed_label . '</b>' . $this->module->l('Disabled', 'AdminKbGSFeedManagementController');
                $auditMethodName = 'AdminKbGSFeedManagement::postProcess()';
                KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSFeedManagement') . '&kbgsConf=15');
            } else {
                $KbGSFeeds->active = 1;
                $KbGSFeeds->id_shop = (int)$this->shop_id;
                $KbGSFeeds->update();
                //Audit Log Entry
                $auditLogEntryString = $this->module->l('Feed -', 'AdminKbGSFeedManagementController') . ' <b>' . $KbGSFeeds->feed_label . '</b>' . $this->module->l('Enabled', 'AdminKbGSFeedManagementController');
                $auditMethodName = 'AdminKbGSFeedManagement::postProcess()';
                KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSFeedManagement') . '&kbgsConf=11');
            }
        }
        parent::postProcess();
    }

    /** Display Category Mapping action link */
    public function displayCategoryLink($token = null, $id = null, $name = null)
    {
        $category = 'profilecategory';
        if (!array_key_exists($category, self::$cache_lang)) {
            self::$cache_lang[$category] = $this->module->l('View Mapped Category', 'AdminKbGSFeedManagementController');
        }

        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminKbGSProfileCategoryMapping') . '&' . $this->identifier . '=' . $id . '&' . $category,
            'action' => self::$cache_lang[$category],
            'icon' => 'search-plus',
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/list/list_action.tpl');
    }

    /** Function used display toolbar in page header */
    public function initPageHeaderToolbar()
    {
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $this->shop_id = (int)Context::getContext()->shop->id;
        if ($this->module_configured) {
			/*
			* Replaced Tools::jsonDecode by json_decode
			* Tools::jsonDecode has been removed in PrestaShop8
			* TGfeb2023 Used-json_decode
			* @date 25-02-2023
			* @author Tanisha Gupta
			*/ 
			/*
			* To hide the add feed button if the automatic upload is disabled
			* @date 27-09-2024
			* @author Ravi Kant Gupta
			*/ 
			/**
			* To make the module multi-store compatible
			* @Author Ravi Kant Gupta
			* @date 12-11-2024
			* Updated query to fetch the data based on the current shop ID
			*/ 
            $gs_config = json_decode(Configuration::get('kbgs_connect_config',null,null, (int) $this->shop_id ), true);
            $automatic_upload = isset($gs_config['automatic_upload']) ? $gs_config['automatic_upload'] : '0';

            if ($automatic_upload == '1') {
            $kb_general = json_decode(Configuration::get('kbgs_general_setting',null,null, (int) $this->shop_id ), true);
            if ($kb_general['sync_type'] != 'api') {
                $this->page_header_toolbar_btn['new_template'] = array(
                    'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                    'desc' => $this->module->l('Add new Feed', 'AdminKbGSFeedManagementController'),
                    'icon' => 'process-icon-new'
                );
            }
        }
    }
        /*
         * Added the message to show on the top of the page
         * @date 22-07-2026
         * @author Amit Singh
         */
        $msg1 = $this->module->l('You can only create 1 feed in the free version. Kindly purchase the ');
        $link1 = $this->module->l('paid version ');
        $msg2 = $this->module->l('to create more feeds. Click');
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
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
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
	* Updated query to fetch the data based on the current shop ID
	*/ 
        if ((bool) Configuration::get('PS_SSL_ENABLED',null,null, (int) $this->shop_id ) && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }

    //Function to delete the record
    public function processDelete()
    {
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $this->shop_id = (int)Context::getContext()->shop->id;
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
        if (Tools::isSubmit('delete' . $this->table)) {
            $gsFeedID = Db::getInstance()->getValue('SELECT gs_feed_id FROM ' . _DB_PREFIX_ . 'kb_gs_feeds WHERE id_gs_feed=' . (int) Tools::getValue('id_gs_feed') . ' AND id_shop=' . (int) $this->shop_id);
            if ($gsFeedID != '') {
                KbGSModule::deleteGSFeed($gsFeedID);
            }
            return parent::processDelete();
        }
    }

    /*
    * To count the number of feeds available in the database
    * @date 22-07-2026
    * @author Amit Singh
    */
    public function checkCount()
    {
        $count = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'kb_gs_feeds');
        if ($count >= 1) {
            return true;
        } else {
            return false;
        }
    }
}
