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
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSProfiles.php');
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSProfileCategory.php');

class AdminKbGSProfileManagementController extends ModuleAdminController
{
    //Class Constructor
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'kb_gs_profiles';
        $this->className = 'KbGSProfiles';
        $this->identifier = 'id_gs_profiles';
        $this->module_configured = false;
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
        parent::__construct();
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        $this->toolbar_title = $this->module->l('Profile Management', 'AdminKbGSProfileManagementController');

        $this->module_configured = KbGSModule::checkInstallation();
        
        if ($this->module_configured) {
            $this->fields_list = array(
                'id_gs_profiles' => array(
                    'title' => $this->module->l('Profile ID', 'AdminKbGSProfileManagementController'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs'
                ),
                'profile_title' => array(
                     'search' => true,
                    'title' => $this->module->l('Profile Title', 'AdminKbGSProfileManagementController'),
                    'align' => 'text'
                ),
                'feed_generated' => array(
                    'title' => $this->module->l('Feed Download', 'AdminKbGSProfileManagementController'),
                    'align' => 'center',
                    'search' => false,
                    'callback' => 'getFeedURL',
                ),
		/**
		* @Author Ravi Kant Gupta
		* @date 18-09-2024
		* Added listing type column on the profile page
		*/  
                'listing_type' => array(
                    'title' => $this->module->l('Listing Type', 'AdminKbGSProfileManagementController'),
                    'align' => 'center',
                    'search' => false,
                ),
                'date_add' => array(
                    'title' => $this->module->l('Added On', 'AdminKbGSProfileManagementController'),
                    'search' => true,
                    'align' => 'text',
                    'type' => 'datetime',
                ),
                'date_upd' => array(
                    'title' => $this->module->l('Updated On', 'AdminKbGSProfileManagementController'),
                    'search' => true,
                    'align' => 'text',
                    'type' => 'datetime',
                )
            );
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the saved data based on the shop_id
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
    
    //function to display feed link on the list
    public function getFeedURL($echo, $tr)
    {
        if ($echo != '') {
	/**
	* @Author Ravi Kant Gupta
	* @date 27-09-2024
	* To show the copy feed URL button
	*/  
            // Generate the feed URL
            $feedUrl = $this->getModuleDirUrl() . 'kbgoogleshopping/feeds/cron_feed_' . $tr['id_gs_profiles'] . '.xml';
            // Return the HTML with download link and copy button
            return '
                <button onclick="copyFeedUrl(\'' . htmlspecialchars($feedUrl, ENT_QUOTES, 'UTF-8') . '\')">' . $this->module->l('Copy Feed URL', 'AdminKbGSProfileManagementController') . '</button>
                <script>
                    function copyFeedUrl(url) {
                        navigator.clipboard.writeText(url).then(function() {
                            alert("' . $this->module->l('Feed URL copied to clipboard!', 'AdminKbGSProfileManagementController') . '");
                        }, function(err) {
                            alert("' . $this->module->l('Failed to copy the URL', 'AdminKbGSProfileManagementController') . '");
                        });
                    }
                </script>
            ';
        }
        return '';
    }
    
    
    public function getProfileStatus($echo, $tr)
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the data based on the shop_id
	*/ 
        $result = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_feeds WHERE id_gs_profiles = ' . (int) $tr['id_gs_profiles'] . ' AND id_shop = ' . (int) $this->shop_id);
        if (!empty($result)) {
            return $this->module->l('Active', 'AdminKbGSProfileManagementController');
        } else {
            return $this->module->l('Feed Creation Pending', 'AdminKbGSProfileManagementController');
        }
    }

    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJS($this->getModuleDirUrl() . 'kbgoogleshopping/views/js/script.js');
        /*
        * To include the js file if the profile counts is more than 0.
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

    //Function definition to render list
    public function renderList()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;

        // Initialized the variable
        $content = '';
        /*
        * Issue: Was getting undefined $msg1, $link1, $msg2, $link2, $msg3 variable error on accessing the profile section when no profile was created.
        * Fixes: Initialized the variable before the if-else block so that they can be accessed in the if-else block
        * @date 22-07-2026
        * @author Amit Singh
        */
        $msg1 = $this->module->l('You can only create 1 profile in the free version. Kindly purchase the ');
        $link1 = $this->module->l('paid version ');
        $msg2 = $this->module->l('to create more than 1 profiles. Click');
        $link2 = $this->module->l('here');
        $msg3= $this->module->l(' to connect with us');
        $this->context->smarty->assign('msg1', $msg1);
        $this->context->smarty->assign('link1', $link1);
        $this->context->smarty->assign('msg2', $msg2);
        $this->context->smarty->assign('link2', $link2);
        $this->context->smarty->assign('msg3', $msg3);

    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated code to fetch the data based on the shop_id
	*/
        $importedCategries = Db::getInstance()->getRow('SELECT count(*) as totalCategories FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE id_shop = ' . (int) $this->shop_id);
        if ($importedCategries['totalCategories'] > 0) {
            $this->addRowAction('edit');
	/**
	* @Author Ravi Kant Gupta
	* @date 24-09-2024
	* Added feed download button for different format
	*/  
            $this->addRowAction('feed_xml');  
            $this->addRowAction('feed_json'); 
            $this->addRowAction('feed_csv');  
            $this->addRowAction('delete');
            
            /* Line commented by Ashish on 17th Aug 2019 as its not required */
            //$this->addRowAction('status');
            //$this->addRowAction('category');
	/**
	* @Author Ravi Kant Gupta
	* @date 27-09-2024
	* To only show message when the automatic sync is disabled
	*/  
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated code to fetch the data based on the shop_id
	*/
            $gs_config = json_decode(Configuration::get('kbgs_connect_config',null,null,$this->shop_id), true);
            $automatic_upload = isset($gs_config['automatic_upload']) ? $gs_config['automatic_upload'] : '0';
            // if ($automatic_upload == 1) {
            //     Context::getContext()->controller->informations[] = $this->module->l('Products of those profiles will not be synced on Google for which Feed is not created. Kindly ensure to create the feed for each profile under Feed Management.', 'AdminKbGSProfileManagementController');
            // }
            // Context::getContext()->controller->warnings[] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/warning.tpl');
            $content .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/modal.tpl');
            $content .= parent::renderList();
            return $content;
        } else {
            $this->context->smarty->assign('sync_categories', $this->context->link->getModuleLink($this->module->name, 'cron', array('action' => 'syncCategories',  'secure_key'=> Configuration::get('KB_GS_SECURE_KEY1',null,null,$this->shop_id))));
            $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name. '/views/templates/admin/_configure/helpers/view/custom_error.tpl');
            /*
         * Added the message to show on the top of the page
         * @date 22-07-2026
        * @author Amit Singh
        */
            $content .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/modal.tpl');
           return $content;
        }
    }
    //function defination to render a form
    public function renderForm()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        $id_gs_profiles = '';
        $obj = '';
        if (Tools::getValue('id_gs_profiles') && Tools::getIsset('id_gs_profiles')) {
            $id_gs_profiles = Tools::getValue('id_gs_profiles');
            $obj = $this->loadObject(true);
        }

        
        
	/**
	* @Author Ravi Kant Gupta
	* @date 22-09-2024
	* Only allow selection of listing type option when the feed is not generated 
	*/  
        $profile_id = Tools::getValue('id_gs_profiles');
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated code to fetch the data based on the shop_id
	*/
        $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_profiles WHERE id_gs_profiles = '" . (int) $profile_id . "' AND id_shop = '" . (int) $this->shop_id . "'";
        $profile = DB::getInstance()->getRow($selectSQL);
        $is_listing_type_allowed = true;
        if(!empty($profile)){
            if($profile['feed_generated'] != ''){
                $is_listing_type_allowed = false;
            }
        }


        $kbProfileCategory = KbGSProfileCategory::getMainProfileCategory($id_gs_profiles);
        $categoryTreeSelection = array();
        if (!empty($kbProfileCategory)) {
            $categoryTreeSelection = explode(',', $kbProfileCategory['prestashop_category']);
            $this->fields_value = array(
                'gs_category' => $kbProfileCategory['gs_category_code'],
                'material' => $kbProfileCategory['material'],
                'pattern' => $kbProfileCategory['pattern'],
                'gender' => $kbProfileCategory['gender'],
                'age_group' => $kbProfileCategory['age_group'],
                'adult' => $kbProfileCategory['adult'],
                'color' => $kbProfileCategory['color'],
                'size' => $kbProfileCategory['size'],
                'size_type' => $kbProfileCategory['size_type'],
                'size_system' => $kbProfileCategory['size_system'],
		// Updated form value
                'listing_type' => $profile['listing_type'],
                'shipping[]' => explode(',', $obj->shipping),
            );
        }
        
        $gs_countries = KbGSProfiles::getGSCountries();
        $available_country = array();
        foreach ($gs_countries as $country) {
            $iso_code = $country['iso_code'];
            $id_country = Country::getByIso($iso_code, true);
            if (!empty($id_country)) {
                $available_country[] = $country;
            }
        }
        $root = Category::getRootCategory();

        //Generating the tree for the first column
        $tree = new HelperTreeCategories('prestashop_category'); //The string in param is the ID used by the generated tree
        $tree->setUseCheckBox(true)
                ->setAttribute('is_category_filter', $root->id)
                ->setRootCategory($root->id)
                ->setSelectedCategories($categoryTreeSelection)
                ->setInputName('prestashop_category')
                ->setFullTree(true); //Set the name of input. The option "name" of $fields_form doesn't seem to work with "categories_select" type

        $categoryTreePresta = $tree->render();
        
        $available_gs_category = KbGSModule::getRootGSCategories();
        $leaf_category1 = array();
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated code to fetch the data based on the shop_id
	*/
        if (isset($kbProfileCategory['gs_category_code'])) {
            $leaf_category1 = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'kb_gs_categories WHERE id_parent=(SELECT id_parent FROM '._DB_PREFIX_.'kb_gs_categories WHERE category_code='.(int)$kbProfileCategory['gs_category_code'].' LIMIT 0,1) AND id_shop = ' . (int) $this->shop_id);
        }

        /**
        * Hide undefined index warning at the of create profile.
        * GS002-09032024 gs-profile-create-notice
        * @date 09-03-2024
        * @author Ashish
        */
        if(!empty($kbProfileCategory['gs_category_code'])) {
            $this->context->smarty->assign('left_category0', $kbProfileCategory['gs_category_code']);
        } else {
            $this->context->smarty->assign('left_category0', '');
        }
       /**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated queries to fetch the data based on the shop_id
	*/
        $this->context->smarty->assign('left_category1', $leaf_category1);
        if (!empty($leaf_category1)) {
            if ($leaf_category1[0]['id_parent'] != 0) {
                $leaf_category2 = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE category_code=' . (int) $leaf_category1[0]['id_parent'] .' AND id_shop = ' . (int) $this->shop_id);
                $this->context->smarty->assign('left_category2', $leaf_category2);
                if (!empty($leaf_category2)) {
                    $leaf_category3 = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE id_parent=' . (int) $leaf_category2['id_parent'] .' AND id_shop = ' . (int) $this->shop_id);
                    $this->context->smarty->assign('left_category3', $leaf_category3);
                    if (!empty($leaf_category3)) {
                        if ($leaf_category3[0]['id_parent'] != 0) {
                            $leaf_category4 = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE category_code=' . (int) $leaf_category3[0]['id_parent'] .' AND id_shop = ' . (int) $this->shop_id);
                            $this->context->smarty->assign('left_category4', $leaf_category4);
                            if (!empty($leaf_category4)) {
                                $leaf_category5 = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE id_parent=' . (int) $leaf_category4['id_parent'] .' AND id_shop = ' . (int) $this->shop_id);
                                $this->context->smarty->assign('left_category5', $leaf_category5);
                                if ($leaf_category5[0]['id_parent'] != 0) {
                                    $leaf_category6 = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE category_code=' . (int) $leaf_category5[0]['id_parent'] .' AND id_shop = ' . (int) $this->shop_id);
                                    $this->context->smarty->assign('left_category6', $leaf_category6);
                                    if (!empty($leaf_category6)) {
                                        $leaf_category7 = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE id_parent=' . (int) $leaf_category6['id_parent'] .' AND id_shop = ' . (int) $this->shop_id);
                                        $this->context->smarty->assign('left_category7', $leaf_category7);
                                        if ($leaf_category7[0]['id_parent'] != 0) {
                                            $leaf_category8 = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE category_code=' . (int) $leaf_category7[0]['id_parent'] .' AND id_shop = ' . (int) $this->shop_id);
                                            $this->context->smarty->assign('left_category8', $leaf_category8);
                                            if (!empty($leaf_category8)) {
                                                $leaf_category9 = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE id_parent=' . (int) $leaf_category8['id_parent'] .' AND id_shop = ' . (int) $this->shop_id);
                                                $this->context->smarty->assign('left_category9', $leaf_category9);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $gsFeatureArray = array();
        $gsFeatureArray[] = array(
            'id_feature' => null,
            'name' => $this->module->l('Select', 'AdminKbGSProfileManagementController'),
        );
        foreach (Feature::getFeatures($this->context->language->id) as $feature) {
            $gsFeatureArray[] = $feature;
        }
        
        $gsGenderArray = array(
            array(
                'id' => null,
                'name' => $this->module->l('Select', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'male',
                'name' => $this->module->l('Male', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'female',
                'name' => $this->module->l('Female', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'unisex',
                'name' => $this->module->l('Unisex', 'AdminKbGSProfileManagementController'),
            ),
        );
        
        $gsAgeGroupArray = array(
            array(
                'id' => null,
                'name' => $this->module->l('Select', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'newborn',
                'name' => $this->module->l('Newborn', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'infant',
                'name' => $this->module->l('Infant', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'toddler',
                'name' => $this->module->l('Toddler', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'kids',
                'name' => $this->module->l('Kids', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'adult',
                'name' => $this->module->l('Adult', 'AdminKbGSProfileManagementController'),
            ),
        );

        $gsProductAttributeArray = AttributeGroup::getAttributesGroups($this->context->language->id);
        array_unshift($gsProductAttributeArray, array('id_attribute_group' => null, 'name' => $this->module->l('Select', 'AdminKbGSProfileManagementController')));
         
        $gsSizeSystemArray = array(
            array(
                'id' => null,
                'name' =>$this->module->l('Select', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'AU',
                'name' => $this->module->l('AU', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'BR',
                'name' => $this->module->l('BR', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'CN',
                'name' => $this->module->l('CN', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'DE',
                'name' => $this->module->l('DE', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'EU',
                'name' => $this->module->l('EU', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'FR',
                'name' => $this->module->l('FR', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'IT',
                'name' => $this->module->l('IT', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'JP',
                'name' => $this->module->l('JP', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'MEX',
                'name' => $this->module->l('MEX', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'UK',
                'name' => $this->module->l('UK', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'GB',
                'name' => $this->module->l('GB', 'AdminKbGSProfileManagementController'),
            ),
        );
	/**
	* @Author Ravi Kant Gupta
	* @date 17-09-2024
	* Added form condtion options value
	*/  
        $product_condition = array();
        $product_condition = array(
            array(
                'id' => null,
                'name' => $this->module->l('Select', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'new',
                'name' => $this->module->l('New', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'used',
                'name' => $this->module->l('Used', 'AdminKbGSProfileManagementController'),
            ),
            array(
                'id' => 'refurbished',
                'name' => $this->module->l('Refurbished', 'AdminKbGSProfileManagementController'),
            ),
        );
        $this->fields_form = array(
            'legend' => array(
                'title' => !Tools::isEmpty(trim(Tools::getValue('id_gs_profiles'))) ? $this->module->l('Update Profile', 'AdminKbGSProfileManagementController') : $this->module->l('Add New Profile', 'AdminKbGSProfileManagementController'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Country', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select country to create profile', 'AdminKbGSProfileManagementController'),
                    'name' => 'id_country',
                    'required' => true,
                    'options' => array(
                        'query' => $available_country,
                        'id' => 'id_country',
                        'name' => 'country_name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Language', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select language to create profile', 'AdminKbGSProfileManagementController'),
                    'name' => 'id_lang',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array(
                                'id_lang' => null,
                                'name' => null
                             )
                        ),
                        'id' => 'id_lang',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Currency', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select currency to create profile', 'AdminKbGSProfileManagementController'),
                    'name' => 'id_currency',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array(
                                'id_lang' => null,
                                'name' => null
                             )
                        ),
                        'id' => 'id_lang',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Profile Title', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Provide Profile Title', 'AdminKbGSProfileManagementController'),
                    'name' => 'profile_title',
                    'maxlength' => 255,
                    'col' => 5,
                    'required' => true
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'gs_category'
                ),
                array(
                    'type' => 'categories_select',
                    'label' => $this->module->l('Store category', 'AdminKbGSProfileManagementController'),
                    'desc' => $this->module->l('Select Store Category', 'AdminKbGSProfileManagementController'),
                    'name' => 'prestashop_category',
                    'required' => true,
                    'category_tree' => $categoryTreePresta //This is the category_tree called in form.tpl
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('GTIN', 'AdminKbGSProfileManagementController'),
                    'required' => true,
                    'name' => 'gtin',
                    'desc' => $this->module->l('Kindly select the proper GTIN. GTIN should be of 0, 8, 12, 14 digits (UPC, EAN, JAN, or ISBN).', 'AdminKbGSProfileManagementController') . '<br/>' . $this->module->l('For more information, click here: ', 'AdminKbGSProfileManagementController') . '<a href="https://support.google.com/merchants/answer/6324461" target="_blank">' . $this->module->l('Google Support', 'AdminKbGSProfileManagementController') . '</a>',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'reference',
                                'name' => $this->module->l('Reference', 'AdminKbGSProfileManagementController'),
                            ),
                            array(
                                'id' => 'upc',
                                'name' => $this->module->l('UPC', 'AdminKbGSProfileManagementController'),
                            ),
                            array(
                                'id' => 'ean13',
                                'name' => $this->module->l('EAN13', 'AdminKbGSProfileManagementController'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Customize Product Title', 'AdminKbGSProfileManagementController'),
                    'name' => 'customize_product_title',
                    'required' => true,
                    'col' => 5,
                    'default_value' => '{product_title}',
                    'desc' => $this->module->l('Customize the product title by using the following place-holders. Place-holders like {sample} will be replace by dynamic content at the time of execution.', 'AdminKbGSProfileManagementController'),
                ),
		/**
		* @Author Ravi Kant Gupta
		* @date 17-09-2024
		* Created input fields
		*/  
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Product Type', 'AdminKbGSProfileManagementController'),
                    'name' => 'product_type',
                    'col' => 5,
                    'hint' => $this->module->l('Enter Product Type', 'AdminKbGSProfileManagementController'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Product Condition', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select Product Condition', 'AdminKbGSProfileManagementController'),
                    'name' => 'product_condition',
                    'id' => 'product_condition',
                    'options' => array(
                        'query' => $product_condition,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Promotion ID', 'AdminKbGSProfileManagementController'),
                    'name' => 'promotion_id',
                    'col' => 5,
                    'hint' => $this->module->l('Enter Promotion ID', 'AdminKbGSProfileManagementController'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Material', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select Material', 'AdminKbGSProfileManagementController'),
                    'name' => 'material',
                    'options' => array(
                        'query' => $gsFeatureArray,
                        'id' => 'id_feature',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Pattern', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select Pattern', 'AdminKbGSProfileManagementController'),
                    'name' => 'pattern',
                    'options' => array(
                        'query' => $gsFeatureArray,
                        'id' => 'id_feature',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Gender', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select Gender', 'AdminKbGSProfileManagementController'),
                    'name' => 'gender',
                    'options' => array(
                        'query' => $gsGenderArray,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Age Group', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select Age Group', 'AdminKbGSProfileManagementController'),
                    'name' => 'age_group',
                    'options' => array(
                        'query' => $gsAgeGroupArray,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Adult Content', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select Adult Content', 'AdminKbGSProfileManagementController'),
                    'name' => 'adult',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'no',
                                'name' => $this->module->l('No', 'AdminKbGSProfileManagementController'),
                            ),
                            array(
                                'id' => 'yes',
                                'name' => $this->module->l('Yes', 'AdminKbGSProfileManagementController'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Color', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select Color', 'AdminKbGSProfileManagementController'),
                    'name' => 'color',
                    'options' => array(
                        'query' => $gsProductAttributeArray,
                        'id' => 'id_attribute_group',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Size', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select Size', 'AdminKbGSProfileManagementController'),
                    'name' => 'size',
                    'options' => array(
                        'query' => $gsProductAttributeArray,
                        'id' => 'id_attribute_group',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Size Type', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select Size Type', 'AdminKbGSProfileManagementController'),
                    'name' => 'size_type',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => null,
                                'name' =>$this->module->l('Select', 'AdminKbGSProfileManagementController'),
                            ),
                            array(
                                'id' => 'regular',
                                'name' => $this->module->l('Regular', 'AdminKbGSProfileManagementController'),
                            ),
                            array(
                                'id' => 'petite',
                                'name' => $this->module->l('Petite', 'AdminKbGSProfileManagementController'),
                            ),
                            array(
                                'id' => 'plus',
                                'name' => $this->module->l('Plus', 'AdminKbGSProfileManagementController'),
                            ),
                            array(
                                'id' => 'big and tall',
                                'name' => $this->module->l('Big and Tall', 'AdminKbGSProfileManagementController'),
                            ),
                            array(
                                'id' => 'maternity',
                                'name' => $this->module->l('Maternity', 'AdminKbGSProfileManagementController'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Size System', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select Size System', 'AdminKbGSProfileManagementController'),
                    'name' => 'size_system',
                    'options' => array(
                        'query' => $gsSizeSystemArray,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Product Shipping', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Select Shipping', 'AdminKbGSProfileManagementController'),
                    'name' => 'shipping[]',
                    'required' => true,
                    'multiple' => true,
                    'options' => array(
                        'query' => Carrier::getCarriers($this->context->language->id, true),
                        'id' => 'id_carrier',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Custom Label 0', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Enter Custom Label 0', 'AdminKbGSProfileManagementController'),
                    'name' => 'custom_label_0',
                    'col' => 5,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Custom Label 1', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Enter Custom Label 1', 'AdminKbGSProfileManagementController'),
                    'name' => 'custom_label_1',
                    'col' => 5,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Custom Label 2', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Enter Custom Label 2', 'AdminKbGSProfileManagementController'),
                    'name' => 'custom_label_2',
                    'col' => 5,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Custom Label 3', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Enter Custom Label 3', 'AdminKbGSProfileManagementController'),
                    'name' => 'custom_label_3',
                    'col' => 5,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Custom Label 4', 'AdminKbGSProfileManagementController'),
                    'hint' => $this->module->l('Enter Custom Label 4', 'AdminKbGSProfileManagementController'),
                    'name' => 'custom_label_4',
                    'col' => 5,
                ),
            ),
            'submit' => array(
                'class' => 'btn btn-default pull-right',
                'title' => $this->module->l('Save', 'AdminKbGSProfileManagementController'),
            )
        );
        /**
        *  To show the product listing type option in the profile form.
        * @date 17-09-2024
        * @author Ravi Kant Gupta
        */
        //Get the configuration settings
        $kbsetting = json_decode(Configuration::get('kbgs_connect_config',null,null,$this->shop_id), true);
        $automatic_upload = isset($kbsetting['automatic_upload']) ? $kbsetting['automatic_upload'] : '0';

        //Get the token information
        $token_connect_info = json_decode(Configuration::get('Kbgs_token_info',null,null,$this->shop_id), true);



        //Get the upload method options
        $options = array(
            array(
                'id_option' => 'feed',
                'name' => $this->module->l('Feed', 'AdminKbGSProfileCategoryMappingController')
            ),
        );
        //Check if the automatic upload is enabled
        if($automatic_upload == 1) {
            $options[] = array(
                'id_option' => 'api',
                'name' => $this->module->l('API', 'AdminKbGSProfileCategoryMappingController')
            );
        }
        //Add the product listing type field
          $product_listing_type = array(
                'type' => 'select',
                'label' => $this->module->l('Product Listing Method', 'AdminKbGSProfileCategoryMappingController'),
                'hint' => $this->module->l('Select the product listing method', 'AdminKbGSProfileCategoryMappingController'),
                'name' => 'listing_type',
                'options' => array(
                    'query' => $options, 
                    'id' => 'id_option',
                    'name' => 'name'
                ),
            );
		/**
		* @Author Ravi Kant Gupta
		* @date 17-09-2024
		* Only allow selection of listing type option when the feed is not generated and module is connected to google
		*/  
            if (isset($token_connect_info['access_token']) && $token_connect_info['access_token'] != '' && $is_listing_type_allowed) {
                // Insert the select input at the desired position (e.g., after the first element)
                array_splice($this->fields_form['input'], 7, 0, array($product_listing_type));
            }
        
        $this->context->smarty->assign(
            array(
                'kb_gs_profile_controller' => $this->context->link->getAdminLink('AdminKbGSProfileManagement', true),
                'loader' => $this->getModuleDirUrl().$this->module->name.'/views/img/spinner.gif',
                'id_gs_profiles' => $id_gs_profiles,
                'kb_gs_feed_controller' => $this->context->link->getAdminLink('AdminKbGSFeedManagement', true),
                'kbgsCategory' => $available_gs_category,
            )
        );
        $tpl = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'kbgoogleshopping/views/templates/admin/kb_gs_profile.tpl'
        );
        
        $customize_product_title = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'kbgoogleshopping/views/templates/admin/customize_product_title.tpl'
        );
        
        return $tpl.parent::renderForm().$customize_product_title;
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
        if (Tools::getValue('update'.$this->table)) {
            $kbProfile = new KbGSProfiles(Tools::getValue('id_gs_profiles'));
            if ($kbProfile->active) {
                $kbProfile->active = 0;
                $kbProfile->update();
                $auditLogEntryString = $this->module->l('Profile -', 'AdminKbGSProfileManagementController'). '<b>' .$kbProfile->profile_title . '</b>'. $this->module->l('Disabled.', 'AdminKbGSProfileManagementController');
                $auditMethodName = 'AdminKbGSProfileManagement::postProcess()';
                KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProfileManagement') . '&kbgsConf=10');
            } else {
                $kbProfile->active = 1;
                $kbProfile->update();
                //Audit Log Entry
                $auditLogEntryString = $this->module->l('Profile -', 'AdminKbGSProfileManagementController'). '<b>' .$kbProfile->profile_title  . '</b>'. $this->module->l('Enabled.', 'AdminKbGSProfileManagementController');
                $auditMethodName = 'AdminKbGSProfileManagement::postProcess()';
                KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProfileManagement') . '&kbgsConf=11');
            }
        }
        if (Tools::getValue('ajax') && Tools::getValue('ajaxGetCurrencyLang')) {
            return $this->getGSCurrencyLanguage();
        }
        
        if (Tools::getValue('ajax') && Tools::getValue('ajaxGetGSCategory')) {
            return $this->fetchGSSubCategories();
        }
        //Handle form submission
        if (Tools::isSubmit('downloadFeed')) {
            $id_gs_profiles = Tools::getValue('id_gs_profiles');
            $feed_file_path = Db::getInstance()->getValue('SELECT feed_generated FROM '._DB_PREFIX_.'kb_gs_profiles WHERE id_gs_profiles='.(int)$id_gs_profiles . ' AND id_shop=' . $this->shop_id);
            if (Tools::file_exists_no_cache($feed_file_path)) {
                header("Content-Transfer-Encoding: Binary");
                header('Content-Type: application/excel');
                header('Content-Disposition: attachment; filename=feed.xml');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                readfile($feed_file_path);
            }
            exit();
        }
        parent::postProcess();
    }
    
    //function defination to add new form
    public function processAdd()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        if (Tools::isSubmit('submitAdd'.$this->table)) {
            /*
         * Added check so that if 1 profile is already created than do not add the data in to the database
         * @date 22-07-2026
         * @author Amit Singh
         */

            if (!$this->checkCount()) {
            $customErrors = array();
            $storeCategoryExist = 0;
            $formError = 0;
            $id_country = Tools::getValue('id_country');
            $id_lang = Tools::getValue('id_lang');
            $id_currency = Tools::getValue('id_currency');
            $profile_title = trim(Tools::getValue('profile_title'));
            $gtin= Tools::getValue('gtin');
            $gs_category = Tools::getValue('gs_category');
            $prestashop_category = Tools::getValue('prestashop_category');
            $customize_product_title = trim(Tools::getValue('customize_product_title'));
            $material = Tools::getValue('material');
            $pattern = Tools::getValue('pattern');
            $gender = Tools::getValue('gender');
            $age_group = Tools::getValue('age_group');
            $adult = Tools::getValue('adult');
            $color = Tools::getValue('color');
            $size = Tools::getValue('size');
            $size_type = Tools::getValue('size_type');
            $size_system = Tools::getValue('size_system');
            $shipping = Tools::getValue('shipping');
            $custom_label_0 = trim(Tools::getValue('custom_label_0'));
            $custom_label_1 = trim(Tools::getValue('custom_label_1'));
            $custom_label_2 = trim(Tools::getValue('custom_label_2'));
            $custom_label_3 = trim(Tools::getValue('custom_label_3'));
            $custom_label_4 = trim(Tools::getValue('custom_label_4'));
            /**
             * To save the product listing type , promotion id, product type in the database.
             * @date 17-09-2024
             * @author Ravi Kant Gupta
             */
            $listing_type = trim(Tools::getValue('listing_type'));
            $promotion_id = trim(Tools::getValue('promotion_id'));
            $product_type = trim(Tools::getValue('product_type'));
            $product_condition = trim(Tools::getValue('product_condition'));
            if (!empty($prestashop_category)) {
                foreach ($prestashop_category as $storeCat) {
	    	/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 12-11-2024
		* Updated code to fetch the data based on the shop_id
		*/
                    $selectSQL = Db::getInstance()->executeS("SELECT count(*) as count FROM " . _DB_PREFIX_ . "kb_gs_category_mapping gcm INNER JOIN " . _DB_PREFIX_ . "kb_gs_profiles gp ON gcm.id_gs_profiles = gp.id_gs_profiles WHERE FIND_IN_SET(" . pSQL($storeCat) . ", prestashop_category) AND id_country = '" . $id_country . "' AND gcm.id_shop = '" . $this->shop_id . "'");
                    if ($selectSQL[0]['count'] > 0) {
                        $storeCategoryExist = 1;
                    }
                }
                $prestashop_category = implode(',', $prestashop_category);
            }
            if ($storeCategoryExist) {
                $formError = 1;
                $customErrors[] = 7;
            }
            
            if (!empty($shipping)) {
                $shipping = implode(',', $shipping);
            }
            
            if (!$formError) {
                $kbprofile = new KbGSProfiles();
                $kbprofile->id_country = $id_country;
                $kbprofile->id_lang = $id_lang;
                $kbprofile->id_currency = $id_currency;
                $kbprofile->profile_title = $profile_title;
                $kbprofile->gtin = $gtin;
                $kbprofile->customize_product_title = $customize_product_title;
                $kbprofile->shipping = $shipping;
                $kbprofile->custom_label_0 = $custom_label_0;
                $kbprofile->custom_label_1 = $custom_label_1;
                $kbprofile->custom_label_2 = $custom_label_2;
                $kbprofile->custom_label_3 = $custom_label_3;
                $kbprofile->custom_label_4 = $custom_label_4;
                $kbprofile->id_shop = (int)$this->shop_id; ;
                /**
                 * To save the product listing type , promotion id, product type in the database.
                 * @date 17-09-2024
                 * @author Ravi Kant Gupta
                 */
                if($listing_type != '') {
                    $kbprofile->listing_type = $listing_type;
                } 
                $kbprofile->promotion_id = $promotion_id;
                $kbprofile->product_type = $product_type;
                $kbprofile->product_condition = $product_condition;
                $kbprofile->active = 1;
                if ($kbprofile->add()) {
                    $id_gs_profiles = $kbprofile->id;
                    $kbProfileCategory = new KbGSProfileCategory();
                    $kbProfileCategory->id_gs_profiles = $id_gs_profiles;
                    $kbProfileCategory->gs_category_code = $gs_category;
                    $kbProfileCategory->prestashop_category = $prestashop_category;
                    $kbProfileCategory->material = $material;
                    $kbProfileCategory->pattern = $pattern;
                    $kbProfileCategory->gender = $gender;
                    $kbProfileCategory->age_group = $age_group;
                    $kbProfileCategory->adult = $adult;
                    $kbProfileCategory->color = $color;
                    $kbProfileCategory->size = $size;
                    $kbProfileCategory->size_system = $size_system;
                    $kbProfileCategory->size_type = $size_type;
                    $kbProfileCategory->is_global = 1;
		    	/**
			* To make the module multi-store compatible
			* @Author Ravi Kant Gupta
			* @date 11-11-2024
			* Updated code to save the data based on the shop_id
			*/
                    $kbProfileCategory->id_shop = (int)$this->shop_id;
                    $kbProfileCategory->add();
                }
                
                //Audit Log Entry
                $auditLogEntryString = $this->module->l('New Profile created successfully', 'AdminKbGSProfileManagementController');
                $auditMethodName = 'AdminKbGSProfileManagement::processAdd()';
                KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminKbGSProfileManagement').'&kbgsConf=9');
            } else {
                 Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProfileManagement').'&kbgsError=' . implode(",", $customErrors));
            }
            /**
            * TO display the error message
            * @date 22-07-2026
            * @author Amit Singh
            */
            } else {
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProfileManagement') . '&kbgsError=99');
            }
        }
    }
    
    //function defination to update form data
    public function processUpdate()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        if (Tools::isSubmit('submitAdd'.$this->table)) {
            $customErrors = array();

            $id_gs_profiles = Tools::getValue('id_gs_profiles');
            $storeCategoryExist = 0;
            $ProfileCategoryExist = 0;
            $storeCategoryProfileExist = 0;
            $formError = 0;
            $id_country = Tools::getValue('id_country');
            $id_lang = Tools::getValue('id_lang');
            $id_currency = Tools::getValue('id_currency');
            $profile_title = trim(Tools::getValue('profile_title'));
            $gtin = Tools::getValue('gtin');
            $gs_category = Tools::getValue('gs_category');
            $prestashop_category = Tools::getValue('prestashop_category');
            $customize_product_title = trim(Tools::getValue('customize_product_title'));
            $material = Tools::getValue('material');
            $pattern = Tools::getValue('pattern');
            $gender = Tools::getValue('gender');
            $age_group = Tools::getValue('age_group');
            $adult = Tools::getValue('adult');
            $color = Tools::getValue('color');
            $size = Tools::getValue('size');
            $size_type = Tools::getValue('size_type');
            $size_system = Tools::getValue('size_system');
            $shipping = Tools::getValue('shipping');
            $custom_label_0 = trim(Tools::getValue('custom_label_0'));
            $custom_label_1 = trim(Tools::getValue('custom_label_1'));
            $custom_label_2 = trim(Tools::getValue('custom_label_2'));
            $custom_label_3 = trim(Tools::getValue('custom_label_3'));
            $custom_label_4 = trim(Tools::getValue('custom_label_4'));
            /**
             * To save the product listing type , promotion id, product type in the database.
             * @date 17-09-2024
             * @author Ravi Kant Gupta
             */
            $listing_type = trim(Tools::getValue('listing_type'));
            $promotion_id = trim(Tools::getValue('promotion_id'));
            $product_type = trim(Tools::getValue('product_type'));
            $product_condition = trim(Tools::getValue('product_condition'));
            if (!empty($prestashop_category)) {
                foreach ($prestashop_category as $key => $value) {
                    if (!Tools::isEmpty(trim(Tools::getValue('id_gs_profiles')))) {
                        $selectSQL = Db::getInstance()->getRow('SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'kb_gs_category_mapping gcm INNER JOIN ' . _DB_PREFIX_ . 'kb_gs_profiles gp ON gcm.id_gs_profiles = gp.id_gs_profiles WHERE FIND_IN_SET("' . pSQL($value) . '", prestashop_category)  AND id_country = "' . $id_country . '" AND gcm.id_gs_profiles !=' . (int) Tools::getValue('id_gs_profiles') . ' AND gcm.id_shop = ' . (int) $this->shop_id);
                        if ($selectSQL['count'] > 0) {
                            $storeCategoryExist = 1;
                        }
                    }
                }
                $prestashop_category = implode(',', $prestashop_category);
            }
            if (!empty($shipping)) {
                $shipping = implode(',', $shipping);
            }
            
            if ($ProfileCategoryExist) {
                $formError = 1;
                $customErrors[] = 28;
            } elseif ($storeCategoryExist) {
                $formError = 1;
                $customErrors[] = 7;
            } elseif ($storeCategoryProfileExist) {
                $formError = 1;
                $customErrors[] = 31;
            }

            if (!$formError) {
                $kbprofile = new KbGSProfiles($id_gs_profiles);
                $kbprofile->id_country = $id_country;
                $kbprofile->id_lang = $id_lang;
                $kbprofile->id_currency = $id_currency;
                $kbprofile->profile_title = $profile_title;
                $kbprofile->gtin = $gtin;
                $kbprofile->customize_product_title = $customize_product_title;
                $kbprofile->shipping = $shipping;
                $kbprofile->custom_label_0 = $custom_label_0;
                $kbprofile->custom_label_1 = $custom_label_1;
                $kbprofile->custom_label_2 = $custom_label_2;
                $kbprofile->custom_label_3 = $custom_label_3;
                $kbprofile->custom_label_4 = $custom_label_4 ;
	    	/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 12-11-2024
		* Updated code to save the data based on the shop_id
		*/
                $kbprofile->id_shop = (int)$this->shop_id; ;

                /**
                 * To save the product listing type , promotion id, product type in the database.
                 * @date 17-09-2024
                 * @author Ravi Kant Gupta
                 */
                if($listing_type != '') {
                    $kbprofile->listing_type = $listing_type;
                } 
                $kbprofile->product_type = $product_type;                
                $kbprofile->promotion_id = $promotion_id;                
                $kbprofile->product_condition = $product_condition;                
                $kbprofile->active = 1;
                if ($kbprofile->update()) {
                    $kbMainCat = KbGSProfileCategory::getMainProfileCategory($id_gs_profiles);
                    if (!empty($kbMainCat)) {
                        $kbProfileCategory = new KbGSProfileCategory($kbMainCat['id_profile_category']);
                    } else {
                        $kbProfileCategory = new KbGSProfileCategory();
                    }
                    $id_gs_profiles = $kbprofile->id;
                    $kbProfileCategory->id_gs_profiles = $id_gs_profiles;
                    $kbProfileCategory->gs_category_code = $gs_category;
                    $kbProfileCategory->prestashop_category = $prestashop_category;
                    $kbProfileCategory->material = $material;
                    $kbProfileCategory->pattern = $pattern;
                    $kbProfileCategory->gender = $gender;
                    $kbProfileCategory->age_group = $age_group;
                    $kbProfileCategory->adult = $adult;
                    $kbProfileCategory->color = $color;
                    $kbProfileCategory->size = $size;
                    $kbProfileCategory->size_system = $size_system;
                    $kbProfileCategory->size_type = $size_type;
                    $kbProfileCategory->is_global = 1;
	    	/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 12-11-2024
		* Updated code to save the data based on the shop_id
		*/
                    $kbProfileCategory->id_shop = (int)$this->shop_id;
                    $kbProfileCategory->save();
                }
                
                //Audit Log Entry
                $auditLogEntryString = $this->module->l('Profile updated successfully', 'AdminKbGSProfileManagementController');
                $auditMethodName = 'AdminKbGSProfileManagement::processUpdate()';
                KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminKbGSProfileManagement').'&kbgsConf=8');
            } else {
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProfileManagement').'&kbgsError=' . implode(",", $customErrors));
            }
        }
    }
    
    //Function defination to delete record
    public function processDelete()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        if (Tools::isSubmit('delete'.$this->table)) {
		/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 11-11-2024
		* Updated code to perform the oprerations on the saved data based on the shop_id
		*/ 
            $id_gs_profiles = Tools::getValue('id_gs_profiles');
            $result = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_feeds WHERE id_gs_profiles = ' . (int) $id_gs_profiles . ' AND id_shop = ' . (int) $this->shop_id);
            if (!empty($result)) {
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProfileManagement').'&kbgsError=11');
            } else {
                Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'kb_gs_products_list WHERE id_gs_profiles = ' . (int) $id_gs_profiles . ' AND id_shop = ' . (int)  $this->shop_id);
                Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'kb_gs_category_mapping WHERE id_gs_profiles = ' . (int) $id_gs_profiles . ' AND id_shop = ' . (int)  $this->shop_id);
                return parent::processDelete();
            }
            /* Code Commneted By Ashish on 18th Aug 2019 as its not required. Added to check not to delete the profile if feed is added
            $feed_path = Db::getInstance()->getValue('SELECT feed_generated FROM '._DB_PREFIX_.'kb_gs_profiles WHERE id_gs_profiles='.(int)$id_gs_profiles);
            $current_path_file = _PS_MODULE_DIR_.'kbgoogleshopping/feeds/current_file_'.$id_gs_profiles.'.txt';
            Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'kb_gs_feeds WHERE id_gs_profiles='.(int)$id_gs_profiles);
            Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'kb_gs_category_mapping WHERE id_gs_profiles='.(int)$id_gs_profiles);
            if (Tools::file_exists_no_cache($feed_path)) {
                unlink($feed_path);
            }
            if (Tools::file_exists_no_cache($current_path_file)) {
                unlink($current_path_file);
            }
            */
        }
    }
    
    //function defination to display sub categories of the Google Category
    public function fetchGSSubCategories()
    {
        $data = array();
        $gs_category_code = Tools::getValue('gs_category_code');
        $gs_sub_category = array();
        if (!empty($gs_category_code)) {
            $gs_sub_category = KbGSModule::getSubGSCategories($gs_category_code);
        }
        
        if (!empty($gs_sub_category)) {
            $data['sub_category'] = $gs_sub_category;
            $data['has_child'] = true;
        } else {
            $data['has_child'] = false;
        }
        
        return die(json_encode($data));
    }
    
    //function defination to display currency and language based on the country
    public function getGSCurrencyLanguage()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        $is_lang_exit = false;
        $id_country = Tools::getValue('id_country');
        //for language
        $gs_language = KbGSProfiles::getGSLanguageByCountryID($id_country);
        $avialable_lang = Language::getLanguages(true, $this->shop_id, true);
        $languages = array();
        if (!empty($gs_language)) {
            foreach ($gs_language as $GSlang) {
                $avil_id_lang = Language::getIdByIso($GSlang['iso_code']);
                if (!empty($avil_id_lang)) {
                    if (in_array($avil_id_lang, $avialable_lang)) {
                        $GSlang['id_lang'] = $avil_id_lang;
                        $languages[] = $GSlang;
                    }
                }
            }
        }
        if (!empty($languages) && is_array($languages)) {
            $is_lang_exit = true;
        }
        
        //for currency
        $is_currency_exit = false;
        $gs_currency = KbGSProfiles::getGSCurrencyByCountryID($id_country);
        $store_currency = Currency::getCurrencies(false, true);
        $avialable_currency = array();
        if (!empty($store_currency)) {
            foreach ($store_currency as $storeCurrency) {
                $avialable_currency[] = $storeCurrency['id_currency'];
            }
        }
        $currency = array();
        if (!empty($gs_currency)) {
            foreach ($gs_currency as $gsCurrency) {
                $avil_id_currency = Currency::getIdByIsoCode($gsCurrency['iso_code'], $this->shop_id);
                if ($avil_id_currency != 0) {
                    if (in_array($avil_id_currency, $avialable_currency)) {
                        $gsCurrency['id_currency'] = $avil_id_currency;
                        $currency[] = $gsCurrency;
                    }
                }
            }
        }
        if (!empty($currency) && is_array($currency)) {
            $is_currency_exit = true;
        }
        $data = array(
            'lang_exit' => $is_lang_exit,
            'languages' => $languages,
            'currency_exit' => $is_currency_exit,
            'currency' => $currency,
        );
        return die(json_encode($data));
    }
    
    
    /** Display Enable/Disable action link */
    public function displayStatusLink($token = null, $id = null, $name = null)
    {
        $kbProfile = new KbGSProfiles($id);
        $status = 'Enable';
        $icon = 'circle';
        if ($kbProfile->active == 1) {
            $status = 'Disable';
            $icon = 'circle-o';
        }
        if (!array_key_exists($status, self::$cache_lang)) {
            self::$cache_lang[$status] = sprintf($this->module->l('%s', 'AdminKbGSProfileManagementController'), $status);
        }

        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminKbGSProfileManagement') . '&' . $this->identifier . '=' . $id . '&status=' . $status,
            'action' => self::$cache_lang[$status],
            'icon' => $icon
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/list/list_action.tpl');
    }
    
	/**
	* @Author Ravi Kant Gupta
	* @date 18-09-2024
	* To handle the feed download in the xml format
	*/ 
    public function displayFeed_XMLLink($token = null, $id = null, $name = null)
   {  
    /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;        
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the data based on the shop_id
	*/
       $result = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_profiles WHERE id_gs_profiles=' . (int) $id . ' AND id_shop=' . (int) $this->shop_id);

    if(!empty($result) && $result[0]['feed_generated'] != '') {
            $kbProfile = new KbGSProfiles($id);
            $status = $this->module->l('Download Feed in XML format', 'AdminKbGSProfileManagementController');
            $path = $this->context->link->getModuleLink($this->module->name, 'cron', array('action' => 'downloadFeed', 'secure_key'=> Configuration::get('KB_GS_SECURE_KEY1',null,null,$this->shop_id), 'type' => 'xml', 'id_gs_profiles' => $id));
            $icon = 'download';
            $this->context->smarty->assign(array(
                'href' => $path,
                'action' => $status,
                'icon' => $icon
            ));
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/list/list_action.tpl');
     }
    }
	/**
	* @Author Ravi Kant Gupta
	* @date 18-09-2024
	* To handle the feed download in the json format
	*/ 
    public function displayFeed_JSONLink($token = null, $id = null, $name = null)
   {
    /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the data based on the shop_id
	*/
    $result = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_profiles WHERE id_gs_profiles=' . (int) $id . ' AND id_shop=' . (int) $this->shop_id);

    if(!empty($result) && $result[0]['feed_generated'] != '') {
            $kbProfile = new KbGSProfiles($id);
            $status = $this->module->l('Download Feed in JSON format', 'AdminKbGSProfileManagementController');
            $path = $this->context->link->getModuleLink($this->module->name, 'cron', array('action' => 'downloadFeed', 'secure_key'=> Configuration::get('KB_GS_SECURE_KEY1',null,null,$this->shop_id), 'type' => 'json', 'id_gs_profiles' => $id));
            $icon = 'download';
            $this->context->smarty->assign(array(
                'href' => $path,
                'action' => $status,
                'icon' => $icon
            ));
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/list/list_action.tpl');
     }
    }
	/**
	* @Author Ravi Kant Gupta
	* @date 18-09-2024
	* To handle the feed download in the csv format
	*/  
    public function displayFeed_CSVLink($token = null, $id = null, $name = null)
   {
    /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
       $result = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_profiles WHERE id_gs_profiles=' . (int) $id . ' AND id_shop=' . (int) $this->shop_id);
       
       if(!empty($result) && $result[0]['feed_generated'] != '') {
                $kbProfile = new KbGSProfiles($id);
            $status = $this->module->l('Download Feed in CSV format', 'AdminKbGSProfileManagementController');
            $path = $this->context->link->getModuleLink($this->module->name, 'cron', array('action' => 'downloadFeed', 'secure_key'=> Configuration::get('KB_GS_SECURE_KEY1',null,null,$this->shop_id), 'type' => 'csv', 'id_gs_profiles' => $id));
            $icon = 'download';
            $this->context->smarty->assign(array(
                'href' => $path,
                'action' => $status,
                'icon' => $icon
            ));
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/list/list_action.tpl');
     }
    }
    
    /** Display Category Mapping action link */
    public function displayCategoryLink($token = null, $id = null, $name = null)
    {
        $category = 'profilecategory';
        if (!array_key_exists($category, self::$cache_lang)) {
            self::$cache_lang[$category] = $this->module->l('View Mapped Category', 'AdminKbGSProfileManagementController');
        }

        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminKbGSProfileCategoryMapping') . '&' . $this->identifier . '=' . $id . '&'.$category,
            'action' => self::$cache_lang[$category],
            'icon' => 'search-plus',
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/list/list_action.tpl');
    }
    
    /** Function used display toolbar in page header */
    public function initPageHeaderToolbar()
    {
        if ($this->module_configured) {
            if (!Tools::isEmpty(Tools::getValue('addkb_gs_profiles')) && !Tools::isEmpty(Tools::getValue('updatekb_gs_profiles'))) {
                $this->page_header_toolbar_btn['new_template'] = array(
                    'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                    'desc' => $this->module->l('Add new Profile', 'AdminKbGSProfileManagementController'),
                    'icon' => 'process-icon-new'
                );
            }
        }
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
        
        if ((bool) Configuration::get('PS_SSL_ENABLED',null,null,$this->shop_id) && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function checkCount()
    {
        $count = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'kb_gs_profiles');
        if ($count >= 1) {
            return true;
        } else {
            return false;
        }
    }
}
