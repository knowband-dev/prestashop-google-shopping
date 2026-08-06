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
/**
* @Author Ravi Kant Gupta
* @date 20-09-2024
* To update the products specific details
*/  
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSProfileCategory.php');
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSProductList.php');


class AdminKbGSProductsListingController extends ModuleAdminController
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
	* @date 11-11-2024
	* To fetch the shop_id if the shop_id not found than use the 1 as the default shop_id
	*/ 
        $this->shop_id = (int)Shop::getContextShopID();
        if($this->shop_id == null){
            $this->shop_id = 1;
        }
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->module_configured = false;

        parent::__construct();
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        $this->toolbar_title = $this->module->l('Products Listing', 'AdminKbGSProductsListingController');
        $this->module_configured = KbGSModule::checkInstallation();

        if ($this->module_configured) {
            $this->table = 'kb_gs_products_list';
            $this->identifier = 'id_gs_products_list';

            $this->icon = array(0 => 'disabled.gif', 1 => 'enabled.gif');
            
            $this->fields_list = array(
                'id_gs_products_list' => array(
                    'title' => $this->module->l('ID', 'AdminKbGSProductsListingController'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs'
                ),
                'image' => array(
                    'title' => $this->module->l('Image', 'AdminKbGSProductsListingController'),
                    'align' => 'center',
                    'orderby' => false,
                    'filter' => false,
                    'search' => false,
                    'callback' => 'showCoverImage'
                ),
                'name' => array(
                    'title' => $this->module->l('Name', 'AdminKbGSProductsListingController'),
                    'filter_key' => 'pl!name'
                ),
                'reference' => array(
                    'title' => $this->module->l('GTIN', 'AdminKbGSProductsListingController'),
                ),
                'profile_title' => array(
                    'title' => $this->module->l('Profile', 'AdminKbGSProductsListingController'),
                ),
                'listing_status' => array(
                    'title' => $this->module->l('Listing Status', 'AdminKbGSProductsListingController'),
                    'type' => 'select',
                    'list' => array('Pending' => $this->module->l('Pending', 'AdminKbGSProductsListingController'), 'Listed' => $this->module->l('Listed', 'AdminKbGSProductsListingController'), 'Inactive' => $this->module->l('Inactive', 'AdminKbGSProductsListingController')),
                    'filter_key' => 'listing_status',
                    'callback' => 'gsListingStatus',
                ),
                'listing_id' => array(
                    'title' => $this->module->l('Listing ID', 'AdminKbGSProductsListingController')
                ),
                'date_listed' => array(
                    'title' => $this->module->l('Listed On', 'AdminKbGSProductsListingController'),
                    'type' => 'datetime'
                )
            );
			/*
			* Replaced Tools::jsonDecode by json_decode
			* Tools::jsonDecode has been removed in PrestaShop8
			* TGfeb2023 Used-json_decode
			* @date 25-02-2023
			* @author Tanisha Gupta
			*/ 
            $general_conf = json_decode(Configuration::get('kbgs_general_setting', null, null,  (int) $this->shop_id), true);
            $this->_select .= 'pl.`name`, i.`id_image` as image, gp.profile_title';
            $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (a.`id_product` = pl.`id_product`) AND id_lang = ' . (int) $general_conf['gs_default_lang'];
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` ims ON (a.`id_product` = ims.`id_product` AND ims.`cover` = 1 AND ims.id_shop = ' . (int) $this->shop_id . ')';
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (ims.`id_image` = i.`id_image`)';
            //$this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON (pac.`id_product_attribute` = a.`id_product_attribute`)';
            //$this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (al.`id_attribute` = pac.`id_attribute`)';
            $this->_join .= ' INNER JOIN `' . _DB_PREFIX_ . 'kb_gs_profiles` gp ON (a.`id_gs_profiles` = gp.`id_gs_profiles`)';
	         /**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 11-11-2024
		* Updated code to fetch the data based on the shop_id
		*/
	    $this->_where = 'AND a.id_shop = ' . (int)$this->shop_id;
            $this->_orderBy = 'a.id_gs_products_list';
            $this->_orderWay = 'ASC';
            $this->_group = 'GROUP BY a.id_gs_products_list';

            //Line added to remove link from list row
            $this->module->list_no_link = true;

            $this->bulk_actions = array(
                'activate' => array(
                    'text' => $this->module->l('Enable selected'),
                    'icon' => 'icon-power-off text-success',
                    'confirm' => $this->module->l('Are you sure to enable selected items?')
                ),
                'deactivate' => array(
                    'text' => $this->module->l('Disable selected'),
                    'icon' => 'icon-power-off text-danger',
                    'confirm' => $this->module->l('Are you sure to enable selected items?')
                ),
            );

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
	/**
	* @Author Ravi Kant Gupta
	* @date 25-09-2024
	* Added form for loading product specific
	*/  

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
            $product_id = Tools::getValue('id_gs_products_list');
            if (Tools::getValue('id_gs_products_list') && Tools::getIsset('id_gs_products_list')) {
                $id_gs_products_list = Tools::getValue('id_gs_products_list');
            }
            $KbGSProductList = new KbGSProductList($id_gs_products_list);
            $overide_allowed = true;

            if (!empty($KbGSProductList)) {
                if($KbGSProductList->listing_id != ''){
                    $overide_allowed = false;
                }
                $this->fields_value = array(
                  'override_product_info' => $KbGSProductList->is_product_info_override,
                    'material' => $KbGSProductList->material,
                    'pattern' => $KbGSProductList->pattern,
                    'product_condition' => $KbGSProductList->product_condition,
                    'gender' => $KbGSProductList->gender,
                    'product_type' => $KbGSProductList->product_type,
                    'listing_type' => $KbGSProductList->product_listing_method,
                    'age_group' => $KbGSProductList->age_group,
                    'adult' => $KbGSProductList->adult_content,
                    'color' => $KbGSProductList->color,
                    'size' => $KbGSProductList->size,
                    'size_type' => $KbGSProductList->size_type,
                    'size_system' => $KbGSProductList->size_system,
                    'promotion_id' => $KbGSProductList->promotion_id,

                );
            }
                        
            $gsFeatureArray = array();
            $product_condition = array();
            $product_condition = array(
                array(
                    'id' => null,
                    'name' => $this->module->l('Select', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'new',
                    'name' => $this->module->l('New', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'used',
                    'name' => $this->module->l('Used', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'refurbished',
                    'name' => $this->module->l('Refurbished', 'AdminKbGSProductsListingController'),
                ),
            );

            $gsFeatureArray[] = array(
                'id_feature' => null,
                'name' => $this->module->l('Select', 'AdminKbGSProductsListingController'),
            );
            foreach (Feature::getFeatures($this->context->language->id) as $feature) {
                $gsFeatureArray[] = $feature;
            }
            
            $gsGenderArray = array(
                array(
                    'id' => null,
                    'name' => $this->module->l('Select', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'male',
                    'name' => $this->module->l('Male', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'female',
                    'name' => $this->module->l('Female', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'unisex',
                    'name' => $this->module->l('Unisex', 'AdminKbGSProductsListingController'),
                ),
            );
            
            $gsAgeGroupArray = array(
                array(
                    'id' => null,
                    'name' => $this->module->l('Select', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'newborn',
                    'name' => $this->module->l('Newborn', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'infant',
                    'name' => $this->module->l('Infant', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'toddler',
                    'name' => $this->module->l('Toddler', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'kids',
                    'name' => $this->module->l('Kids', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'adult',
                    'name' => $this->module->l('Adult', 'AdminKbGSProductsListingController'),
                ),
            );
    
            $gsProductAttributeArray = AttributeGroup::getAttributesGroups($this->context->language->id);
            array_unshift($gsProductAttributeArray, array('id_attribute_group' => null, 'name' => $this->module->l('Select', 'AdminKbGSProductsListingController')));
             
            $gsSizeSystemArray = array(
                array(
                    'id' => null,
                    'name' =>$this->module->l('Select', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'AU',
                    'name' => $this->module->l('AU', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'BR',
                    'name' => $this->module->l('BR', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'CN',
                    'name' => $this->module->l('CN', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'DE',
                    'name' => $this->module->l('DE', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'EU',
                    'name' => $this->module->l('EU', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'FR',
                    'name' => $this->module->l('FR', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'IT',
                    'name' => $this->module->l('IT', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'JP',
                    'name' => $this->module->l('JP', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'MEX',
                    'name' => $this->module->l('MEX', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'UK',
                    'name' => $this->module->l('UK', 'AdminKbGSProductsListingController'),
                ),
                array(
                    'id' => 'GB',
                    'name' => $this->module->l('GB', 'AdminKbGSProductsListingController'),
                ),
            );
            
            $this->fields_form = array(
                'legend' => array(
                    'title' => $this->module->l("Update Product's Additional Information", 'AdminKbGSProductsListingController'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch', 
                        'label' => $this->module->l('Do you want to override product info?', 'AdminKbGSProductsListingController'),
                        'name' => 'override_product_info', // You can change the name to something more relevant
                        'required' => false, // Checkboxes are usually not required, but adjust as needed
                        'col' => 5,
                        'is_bool' => true, // Indicates that the field is a boolean value
                        'values' => array(
                            array(
                                'id' => 'override_product_info_on',
                                'value' => 1, // Value when checked
                                'label' => $this->module->l('Yes')
                            ),
                            array(
                                'id' => 'override_product_info_off',
                                'value' => 0, // Value when unchecked
                                'label' => $this->module->l('No')
                            )
                        ),
                        'default' => 0 // Default value (unchecked)
                    ),                    
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Material', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Select Material', 'AdminKbGSProductsListingController'),
                        'name' => 'material',
                        'id' => 'product_material',
                        'options' => array(
                            'query' => $gsFeatureArray,
                            'id' => 'id_feature',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Pattern', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Select Pattern', 'AdminKbGSProductsListingController'),
                        'name' => 'pattern',
                        'id' => 'product_pattern',
                        'options' => array(
                            'query' => $gsFeatureArray,
                            'id' => 'id_feature',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Product Condition', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Select Product Condition', 'AdminKbGSProductsListingController'),
                        'name' => 'product_condition',
                        'id' => 'product_specifics_condition',
                        'options' => array(
                            'query' => $product_condition,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Gender', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Select Gender', 'AdminKbGSProductsListingController'),
                        'name' => 'gender',
                        'id' => 'product_specific_gender',
                        'options' => array(
                            'query' => $gsGenderArray,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('Product Type', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Enter Product Type', 'AdminKbGSProductsListingController'),
                        'name' => 'product_type',
                        'id' => 'product_specific_type',
                        'col' => 5,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Age Group', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Select Age Group', 'AdminKbGSProductsListingController'),
                        'name' => 'age_group',
                        'id' => 'product_specific_age_group',
                        'options' => array(
                            'query' => $gsAgeGroupArray,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Adult Content', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Select Adult Content', 'AdminKbGSProductsListingController'),
                        'name' => 'adult',
                        'id' => 'product_specific_adult',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'no',
                                    'name' => $this->module->l('No', 'AdminKbGSProductsListingController'),
                                ),
                                array(
                                    'id' => 'yes',
                                    'name' => $this->module->l('Yes', 'AdminKbGSProductsListingController'),
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Color', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Select Color', 'AdminKbGSProductsListingController'),
                        'name' => 'color',
                        'id' => 'product_specific_color',
                        'options' => array(
                            'query' => $gsProductAttributeArray,
                            'id' => 'id_attribute_group',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Size', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Select Size', 'AdminKbGSProductsListingController'),
                        'name' => 'size',
                        'id' => 'product_specific_size',
                        'options' => array(
                            'query' => $gsProductAttributeArray,
                            'id' => 'id_attribute_group',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Size Type', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Select Size Type', 'AdminKbGSProductsListingController'),
                        'name' => 'size_type',
                        'id' => 'product_specific_size_type',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => null,
                                    'name' =>$this->module->l('Select', 'AdminKbGSProductsListingController'),
                                ),
                                array(
                                    'id' => 'regular',
                                    'name' => $this->module->l('Regular', 'AdminKbGSProductsListingController'),
                                ),
                                array(
                                    'id' => 'petite',
                                    'name' => $this->module->l('Petite', 'AdminKbGSProductsListingController'),
                                ),
                                array(
                                    'id' => 'plus',
                                    'name' => $this->module->l('Plus', 'AdminKbGSProductsListingController'),
                                ),
                                array(
                                    'id' => 'big and tall',
                                    'name' => $this->module->l('Big and Tall', 'AdminKbGSProductsListingController'),
                                ),
                                array(
                                    'id' => 'maternity',
                                    'name' => $this->module->l('Maternity', 'AdminKbGSProductsListingController'),
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Size System', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Select Size System', 'AdminKbGSProductsListingController'),
                        'name' => 'size_system',
                        'id' => 'product_specific_size_system',
                        'options' => array(
                            'query' => $gsSizeSystemArray,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->module->l('Promotion ID', 'AdminKbGSProductsListingController'),
                        'hint' => $this->module->l('Enter Promotion ID', 'AdminKbGSProductsListingController'),
                        'name' => 'promotion_id',
                        'id' => 'product_specific_promotion_id',
                        'col' => 5,
                    ),

                ),
                'submit' => array(
                    'class' => 'btn btn-default pull-right',
                    'title' => $this->module->l('Save', 'AdminKbGSProductsListingController'),
                )
            );
            /**
            *  To show the product listing type option in the profile form.
            * @date 17-09-2024
            * @author Ravi Kant Gupta
            */
            //Get the configuration settings
	    	/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 12-11-2024
		* Updated code to fetch the saved config data based on the shop_id
		*/
            $kbsetting = json_decode(Configuration::get('kbgs_connect_config',null,null, (int) $this->shop_id ), true);
            $automatic_upload = isset($kbsetting['automatic_upload']) ? $kbsetting['automatic_upload'] : '0';

            //Get the token information
            $token_connect_info = json_decode(Configuration::get('Kbgs_token_info',null,null, (int) $this->shop_id ), true);
    
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
                    'id' => 'product_listing_type',
                    'options' => array(
                        'query' => $options, 
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                );
                // Only show the product listing type option if the Automatic product sync is enabled product is not synced on Google
                if (isset($token_connect_info['access_token']) && $token_connect_info['access_token'] != '' && $overide_allowed) {
                    // Insert the select input at the desired position (e.g., after the first element)
                    array_splice($this->fields_form['input'], 7, 0, array($product_listing_type));
                }
            
            $this->context->smarty->assign(
                array(
                    'kb_gs_profile_controller' => $this->context->link->getAdminLink('AdminKbGSProductsListing', true),
                    'loader' => $this->getModuleDirUrl().$this->module->name.'/views/img/spinner.gif',
                    'id_gs_profiles' => $id_gs_profiles,
                    'kb_gs_feed_controller' => $this->context->link->getAdminLink('AdminKbGSFeedManagement', true),
                )
            );

            
            return parent::renderForm();
        }
    
    //Display Settings error.
    public function renderView()
    {
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name. '/views/templates/admin/_configure/helpers/view/install_error.tpl');
    }

    public function initPageHeaderToolbar()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        if ($this->module_configured) {
            $secure_key = Configuration::get('KB_GS_SECURE_KEY1',null,null, (int) $this->shop_id);
            $this->page_header_toolbar_btn['update_profile_products'] = array(
                'href' => $this->context->link->getModuleLink(
                    'kbgoogleshopping',
                    'cron',
                    array(
                        'secure_key' => $secure_key,
                        'action' => 'syncLocal'
                        )
                ),
                'desc' => $this->module->l('Local Sync', 'AdminKbGSProductsListingController'),
                'icon' => 'process-icon-refresh',
                'target' => '_blank',
            );

            $this->page_header_toolbar_btn['sync_products'] = array(
                'href' => $this->context->link->getModuleLink(
                    'kbgoogleshopping',
                    'cron',
                    array(
                        'secure_key' => $secure_key,
                        'action' => 'syncFeedsListing')
                ),
                'desc' => $this->module->l('Sync Products', 'AdminKbGSProductsListingController'),
                'icon' => 'process-icon-refresh',
                'target' => '_blank',
            );

            $this->page_header_toolbar_btn['sync_status'] = array(
                'href' => $this->context->link->getModuleLink(
                    'kbgoogleshopping',
                    'cron',
                    array(
                        'secure_key' => $secure_key,
                        'action' => 'syncProductStatus')
                ),
                'desc' => $this->module->l('Sync Listing Status', 'AdminKbGSProductsListingController'),
                'icon' => 'process-icon-refresh',
                'target' => '_blank',
            );
        }
        /**
        * Added the message to show on the top of the page
        * @date 22-07-2026
        * @author Amit Singh
        */
        $msg1 = $this->module->l('You can only sync 10 products in the free version. Kindly purchase the ');
        $link1 = $this->module->l('paid version ');
        $msg2 = $this->module->l('sync all products. Click');
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

    //function to display listing status
    public function gsListingStatus($tr, $echo)
    {
        if ($tr == 'Inactive') {
            $str = $this->module->l('Inactive', 'AdminKbGSProductsListingController');
        } elseif ($tr == 'Pending') {
            $str = $this->module->l('Pending', 'AdminKbGSProductsListingController');
        } elseif ($tr == 'Listed') {
            $str = $this->module->l('Listed', 'AdminKbGSProductsListingController');
        } elseif ($tr == 'Expired') {
            $str = $this->module->l('Expired', 'AdminKbGSProductsListingController');
        }
        return $str;
    }

    //function to display attributes of the products
    public function showAttributes($id_row, $row_data)
    {
        $output = '';
        if (!empty($row_data['id_product_attribute'])) {
            // Cast to int for Product constructor compatibility with PS validator. Fixes int|null type expectation. 08-06-2024
            $productAttribute = new Product((int)$row_data['id_product']);
            $attributesList = $productAttribute->getAttributeCombinationsById($row_data['id_product_attribute'], $this->context->employee->id_lang);

            if (!empty($attributesList)) {
                foreach ($attributesList as $attributesList) {
                    if (!empty($output)) {
                        $output .= ' | ';
                    }
                    $output .= '<b>' . $attributesList['group_name'] . ':</b> ' . $attributesList['attribute_name'];
                }
            }
        }
        return $output;
    }

    //function to display cover image of the product
    public function showCoverImage($id_row, $row_data)
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        if (!empty($row_data['id_product'])) {
            // Cast to int for Product constructor compatibility with PS validator. Fixes int|null type expectation. 08-06-2024
            $product = new Product((int)$row_data['id_product']);
            $coverImage = $product->getCover($row_data['id_product']);

            if (!empty($coverImage)) {
                $path_to_image = _PS_IMG_DIR_ . 'p/' . Image::getImgFolderStatic($coverImage['id_image']) . (int) $coverImage['id_image'] . '.' . $this->imageType;
                return ImageManagerCore::thumbnail($path_to_image, 'product_mini_' . $row_data['id_product'] . '_' . $this->shop_id . '.' . $this->imageType, 45, $this->imageType);
            }
        }
    }

    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS($this->getModuleDirUrl() . 'kbgoogleshopping/views/js/script.js');
        $this->addCSS($this->getModuleDirUrl() . 'kbgoogleshopping/views/css/style.css');
    }

    //function to render list
    public function renderList()
    {
        //$this->addRowAction('renew'); //Renew list not required. Code commented by Ashish on 17th Aug 2019
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('error');
        return parent::renderList();
    }

    //function to render toolbar of the helper list
    public function initToolbar()
    {
        parent::initToolbar();

        unset($this->toolbar_btn['new']);
    }

    public function postProcess()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
	/**
	* @Author Ravi Kant Gupta
	* @date 20-09-2024
	* To store the product's specifics in the table
	*/  
        if (Tools::getValue('submitAdd'.$this->table)) {
           $id =  Tools::getValue('product_id');
            $KbGSProductList = new KbGSProductList($id);
            
            $listing_type = 'feed';
            $add_flag = 1;
            $update_flag = 0;
            if($KbGSProductList->listing_id != ''){
                $listing_type = $KbGSProductList->product_listing_method;
                $add_flag = 0;
                $update_flag = 1;
            }else{
                $listing_type = Tools::getValue('listing_type');
            }
            if(!empty($KbGSProductList->id_gs_products_list)){
                $overrideProductInfo = Tools::getValue('override_product_info');
                $KbGSProductList->material = $overrideProductInfo == 0 ? '' : Tools::getValue('material');
                $KbGSProductList->pattern = $overrideProductInfo == 0 ? '' : Tools::getValue('pattern');
                $KbGSProductList->product_condition = $overrideProductInfo == 0 ? '' : Tools::getValue('product_condition');
                $KbGSProductList->gender = $overrideProductInfo == 0 ? '' : Tools::getValue('gender');
                $KbGSProductList->product_type = $overrideProductInfo == 0 ? '' : Tools::getValue('product_type');
                $KbGSProductList->age_group = $overrideProductInfo == 0 ? '' : Tools::getValue('age_group');
                $KbGSProductList->product_listing_method = $overrideProductInfo == 0 ? '' : $listing_type;
                $KbGSProductList->adult_content = $overrideProductInfo == 0 ? '' : Tools::getValue('adult');
                $KbGSProductList->color = $overrideProductInfo == 0 ? '' : Tools::getValue('color');
                $KbGSProductList->size = $overrideProductInfo == 0 ? '' : Tools::getValue('size');
                $KbGSProductList->size_type = $overrideProductInfo == 0 ? '' : Tools::getValue('size_type');
                $KbGSProductList->size_system = $overrideProductInfo == 0 ? '' : Tools::getValue('size_system');
                $KbGSProductList->promotion_id = $overrideProductInfo == 0 ? '' : Tools::getValue('promotion_id');
                $KbGSProductList->add_flag = $overrideProductInfo == 0 ? '' : $add_flag;
                $KbGSProductList->update_flag = $overrideProductInfo == 0 ? '' : $update_flag;
                $KbGSProductList->is_product_info_override = $overrideProductInfo;
                $KbGSProductList->id_shop = (int) $this->shop_id;
                $KbGSProductList->update();
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProductsListing') . '&kbgsConf=19');
            }else{
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProductsListing') . '&kbgsError=100');
            }
        }else if ($this->action == 'bulkactivate') {
            $this->processBulkactivate();
        } else if ($this->action == 'bulkdeactivate') {
            $this->processBulkdeactivate();
        } else if (!Tools::isEmpty(trim(Tools::getValue('action'))) && !Tools::isEmpty(trim(Tools::getValue('id_gs_products_list')))) { //Product Renewal
            //Get Product Name
	        	/**
			* To make the module multi-store compatible
			* @Author Ravi Kant Gupta
			* @date 11-11-2024
			* Updated code to fetch the data based on the shop_id
			*/
            $selectSQL = "SELECT pl.name FROM " . _DB_PREFIX_ . "kb_gs_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.id_gs_products_list = '" . (int) Tools::getValue('id_gs_products_list') . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "' AND pl.id_shop = '" . (int) $this->shop_id . "'";
            $getProductListingDetails = DB::getInstance()->executeS($selectSQL, true, false);

            if (Tools::getValue('action') == 'renew') {
                $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE id_gs_products_list = '" . (int) Tools::getValue('id_gs_products_list') . "' AND (delete_flag = '1' OR delete_flag = '2') AND id_shop = '" . (int) $this->shop_id . "'";
                $checkDeleteFlag = DB::getInstance()->executeS($selectSQL, true, false);

                if (!empty($checkDeleteFlag) && $checkDeleteFlag[0]['count'] == 0) {
                    if (DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET WHERE id_gs_products_list = '" . (int) Tools::getValue('id_gs_products_list') . "' AND id_shop = '" . (int) $this->shop_id . "'")) {
                        $selectid_prod = "SELECT epl.id_product FROM " . _DB_PREFIX_ . "kb_gs_products_list epl WHERE epl.id_gs_products_list = '" . (int) Tools::getValue('id_gs_products_list') . "' AND epl.id_shop = '" . (int) $this->shop_id . "'";
                        $getprodid = DB::getInstance()->executeS($selectid_prod, true, false);

                        //Audit Log Entry
                        $auditLogEntryString = $this->module->l('Product Enabled -', 'AdminKbGSProductsListingController') . ' <b>' . $getProductListingDetails[0]['name'] . '</b>' . $this->module->l('Recorded Successfully', 'AdminKbGSProductsListingController');
                        $auditMethodName = 'AdminKbGSProductsListing::postProcess()';
                        KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProductsListing') . '&kbgsConf=4');
                    }
                } else {
                    //Audit Log Entry
                    $auditLogEntryString = $this->module->l('Product Enabled -', 'AdminKbGSProductsListingController') . ' <b>' . $getProductListingDetails[0]['name'] . '</b>' . $this->module->l('Failed', 'AdminKbGSProductsListingController');
                    $auditMethodName = 'AdminKbGSProductsListing::postProcess()';
                    KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProductsListing') . '&kbgsError=2');
                }
            }
	    	/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 12-11-2024
		* Updated code to fetch the data based on the shop_id
		*/
            if (Tools::getValue('action') == 'halt') {
                $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE id_gs_products_list = '" . (int) Tools::getValue('id_gs_products_list') . "' AND delete_flag = '1' AND id_shop = '" . (int) $this->shop_id . "'";
                $checkDeleteFlag = DB::getInstance()->executeS($selectSQL, true, false);

                if (!empty($checkDeleteFlag) && $checkDeleteFlag[0]['count'] == 0) {
                    if (DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET renew_flag = '0' WHERE id_gs_products_list = '" . (int) Tools::getValue('id_gs_products_list') . "' AND id_shop = '" . (int) $this->shop_id . "'")) {
                        //Audit Log Entry
                        $auditLogEntryString = $this->module->l('Renewal of Product -', 'AdminKbGSProductsListingController') . '<b>' . $getProductListingDetails[0]['name'] . '</b> ' . $this->module->l('Stopped Successfully', 'AdminKbGSProductsListingController');
                        $auditMethodName = 'AdminKbGSProductsListing::postProcess()';
                        KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProductsListing') . '&kbgsConf=5');
                    }
                } else {
                    //Audit Log Entry
                    $auditLogEntryString = $this->module->l('Halt Renewal of Product -', 'AdminKbGSProductsListingController') . '<b>' . $getProductListingDetails[0]['name'] . '</b> ' . $this->module->l('Failed', 'AdminKbGSProductsListingController');
                    $auditMethodName = 'AdminKbGSProductsListing::postProcess()';
                    KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProductsListing') . '&kbgsError=3');
                }
            }

            if (Tools::getValue('action') == 'relist') {
                if (DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET listing_status = 'Pending', delete_flag = '0', renew_flag = '0' WHERE id_gs_products_list = '" . (int) Tools::getValue('id_gs_products_list') . "' AND id_shop = '" . (int) $this->shop_id . "' AND id_shop = '" . (int) $this->shop_id . "'")) {
                    //Audit Log Entry
                    $auditLogEntryString = $this->module->l('Listing of Product -', 'AdminKbGSProductsListingController') . '<b>' . $getProductListingDetails[0]['name'] . '</b> ' . $this->module->l('Resumed Successfully', 'AdminKbGSProductsListingController');
                    $auditMethodName = 'AdminKbGSProductsListing::postProcess()';
                    KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProductsListing') . '&kbgsConf=6');
                }
            }
        } else {
            parent::postProcess();
        }
    }
    
    protected function processBulkactivate()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $dbQuery = Db::getInstance();
            foreach ($this->boxes as $id_gs_products_list) {
	    	/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 12-11-2024
		* Updated code to fetch the data based on the shop_id
		*/
                /**
                 * DB query was not being formulated correctly and was not working as expected.
                 * Updated the query to make it work as expected.
                 * @modifier Himanshu Vishwakarma
                 * @date 18-03-2025
                 */
                $sql = 'UPDATE ' . _DB_PREFIX_ . 'kb_gs_products_list 
                SET listing_status = "Pending" 
                WHERE id_gs_products_list = ' . (int) $id_gs_products_list . ' 
                AND id_shop = ' . (int) $this->shop_id;

                $dbQuery->execute($sql);
            }
        }
        $link = AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminKbGSProductsListing')."&kbgsConf=17";
        Tools::redirectAdmin($link);
    }

    protected function processBulkdeactivate()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $dbQuery = Db::getInstance();
            foreach ($this->boxes as $id_gs_products_list) {
                /**
                 * DB query was not being formulated correctly and was not working as expected.
                 * Updated the query to make it work as expected.
                 * @modifier Himanshu Vishwakarma
                 * @date 18-03-2025
                 */
                $sql = 'UPDATE ' . _DB_PREFIX_ . 'kb_gs_products_list 
                SET listing_status = "Inactive" 
                WHERE id_gs_products_list = ' . (int) $id_gs_products_list . ' 
                AND id_shop = ' . (int) $this->shop_id;

                $dbQuery->execute($sql);
            }
        }
        $link = AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminKbGSProductsListing')."&kbgsConf=18";
        Tools::redirectAdmin($link);
    }


    //Function definition to delete Product Listing
    public function processDelete()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        if (!Tools::isEmpty(trim(Tools::getValue('id_gs_products_list')))) {
            //Get Product Name
	    	/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 12-11-2024
		* Updated code to fetch the data based on the shop_id
		*/
            $selectSQL = "SELECT pl.name FROM " . _DB_PREFIX_ . "kb_gs_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.id_gs_products_list = '" . (int) Tools::getValue('id_gs_products_list') . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "' AND pl.id_shop = '" . (int) $this->shop_id . "'";
            $getProductListingDetails = DB::getInstance()->executeS($selectSQL, true, false);

            //SQL Query to delete Shipping Template
            $deleteProductListingSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET delete_flag = '1', renew_flag = '0', listing_status = 'Inactive' WHERE id_gs_products_list = '" . (int) Tools::getValue('id_gs_products_list') . "' AND id_shop = '" . (int) $this->shop_id . "'";
            if (Db::getInstance()->execute($deleteProductListingSQL)) {
                //Audit Log Entry
                $auditLogEntryString = $this->module->l('Listing of Product -', 'AdminKbGSProductsListingController') . ' <b>' . $getProductListingDetails[0]['name'] . '</b>' . $this->module->l('Deleted', 'AdminKbGSProductsListingController');
                $auditMethodName = 'AdminKbGSProductsListing::processDelete()';
                KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProductsListing') . '&kbgsConf=7');
            } else {
                //Audit Log Entry
                $auditLogEntryString = $this->module->l('Deletion of Product -', 'AdminKbGSProductsListingController') . '<b>' . $getProductListingDetails[0]['name'] . '</b>' . $this->module->l('Failed', 'AdminKbGSProductsListingController');
                $auditMethodName = 'AdminKbGSProductsListing::processDelete()';
                KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProductsListing') . '&kbgsError=4');
            }
        }
    }

    /** Display view action link */
    public function displayRenewLink($token = null, $id = null, $name = null)
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE id_gs_products_list = '" . (int) $id . "' AND id_shop = '" . (int) $this->shop_id . "'";
        $productDetails = DB::getInstance()->executeS($selectSQL, true, false);

        $action = 'Renew';
        if (!empty($productDetails) && $productDetails[0]['renew_flag'] == '1') {
            $action = 'Halt';
        }

        if (!array_key_exists($action, self::$cache_lang)) {
            self::$cache_lang[$action] = sprintf($this->module->l('%s', 'AdminKbGSProductsListingController'), $action);
        }

        if ($action == 'Renew') {
            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminlink('AdminKbGSProductsListing') . '&' . $this->identifier . '=' . $id . '&action=renew',
                'action' => self::$cache_lang['Renew'],
                'icon' => 'refresh'
            ));

            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/list/list_action.tpl');
        } else {
            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminlink('AdminKbGSProductsListing') . '&' . $this->identifier . '=' . $id . '&action=halt',
                'action' => self::$cache_lang['Halt'],
                'icon' => 'ban'
            ));

            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/list/list_action.tpl');
        }
    }

    /** Display view action link */
    public function displayDeleteLink($token = null, $id = null, $name = null)
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
	* @date 12-11-2024
	* Updated code to fetch the data based on the shop_id
	*/
        $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE id_gs_products_list = '" . (int) $id . "' AND id_shop = '" . (int) $this->shop_id . "'";
        $productDetails = DB::getInstance()->getRow($selectSQL, true, false);

        $action = 'Disable';
        if (!empty($productDetails) && ($productDetails['listing_status'] == 'Inactive')) {
            $action = 'Enable';
        }

        if (!array_key_exists($action, self::$cache_lang)) {
            self::$cache_lang[$action] = sprintf($this->module->l('%s', 'AdminKbGSProductsListingController'), $action);
        }

        if ($action == 'Enable') {
            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminlink('AdminKbGSProductsListing') . '&' . $this->identifier . '=' . $id . '&action=relist',
                'action' => self::$cache_lang[$action],
                'icon' => 'enable'
            ));

            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/list/list_action.tpl');
        } else {
            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminlink('AdminKbGSProductsListing') . '&' . $this->identifier . '=' . $id . '&delete' . $this->table,
                'action' => self::$cache_lang[$action],
                'icon' => 'disable'
            ));
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/list/list_action.tpl');
        }
    }

    /** Display view listing error link */
    public function displayErrorLink($token = null, $id = null, $name = null)
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
	/**
	* @Author Ravi Kant Gupta
	* @date 16-09-2024
	* Fixed the undefined index error on the product page
	*/  
        $selectSQL = "SELECT listing_error , listing_status FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE id_gs_products_list = '" . (int) $id . "' AND id_shop = '" . (int) $this->shop_id . "'";
        $productDetails = DB::getInstance()->executeS($selectSQL, true, false);
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check to avoid the errors
	*/
        if(!empty($productDetails)){
        if ($productDetails[0]['listing_error'] != "" && $productDetails[0]['listing_status'] != 'Inactive') {
            if (!array_key_exists('Error', self::$cache_lang)) {
                self::$cache_lang['Error'] = $this->module->l('View Error', 'AdminKbGSProductsListingController');
            }

            $this->context->smarty->assign(array(
                'href' => 'etsy-error-' . $id,
                'action' => self::$cache_lang['Error'],
                'icon' => 'search-plus',
                'text' => !empty($productDetails[0]['listing_error']) ? $productDetails[0]['listing_error'] : $this->module->l('No Listing Error Found.', 'AdminKbGSProductsListingController')
            ));

            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/list/list_action_view_error.tpl');
        } else {
            return;
        }
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

        if ((bool) Configuration::get('PS_SSL_ENABLED',null,null, (int) $this->shop_id ) && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    } 
}
