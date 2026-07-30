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

class AdminKbGSProfileCategoryMappingController extends ModuleAdminController
{
    //Class Constructor
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->module->list_no_link = true;
        $this->table = 'kb_gs_category_mapping';
        $this->className = 'KbGSProfileCategory';
        $this->identifier = 'id_profile_category';
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
        if (KbGSModule::checkInstallation()) {
        } else {
            $this->fields_list = array(
                'id_profile_category' => array(
                    'search' => false,
                    'title' => $this->module->l('ID', 'AdminKbGSProfileCategoryMappingController'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs'
                ),
                'profile_title' => array(
                     'search' => true,
                    'title' => $this->module->l('Profile Title', 'AdminKbGSProfileCategoryMappingController'),
                    'align' => 'center'
                ),
                'name' => array(
                    'search' => false,
                    'title' => $this->module->l('Google Category', 'AdminKbGSProfileCategoryMappingController'),
                    'align' => 'center'
                ),
                'date_add' => array(
                    'title' => $this->module->l('Added On', 'AdminKbGSProfileCategoryMappingController'),
                    'search' => true,
                    'align' => 'center',
                    'type' => 'datetime',
                   'filter_key' => 'a!date_add',
                    'search' => false,
                ),
                'date_upd' => array(
                    'title' => $this->module->l('Updated On', 'AdminKbGSProfileCategoryMappingController'),
                    'search' => true,
                    'align' => 'center',
                    'type' => 'datetime',
                    'search' => false,
                    'filter_key' => 'a!date_upd',
                )
            );

            $this->_select = 'ecm.profile_title,ec.name';
            $this->_join = 'LEFT JOIN '._DB_PREFIX_.'kb_gs_profiles ecm ON (ecm.id_gs_profiles = a.id_gs_profiles)
                    LEFT JOIN ' . _DB_PREFIX_ . 'kb_gs_categories ec ON (ec.category_code = a.gs_category_code)';
            $this->_group = ' group by a.id_profile_category';
            $this->_where = ' And ecm.id_gs_profiles='.(int)Tools::getValue('id_gs_profiles');
	    	/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 12-11-2024
		* Updated code to fetch the data based on the shop_id
		*/
            $this->_where = 'AND a.id_shop = ' . (int)$this->shop_id;

            //This is to show notification messages to admin
            if (!Tools::isEmpty(trim(Tools::getValue('kbgsConf')))) {
                new KbGSModule(Tools::getValue('kbgsConf'), 'conf');
            }

            if (!Tools::isEmpty(trim(Tools::getValue('kbgsError')))) {
                new KbGSModule(Tools::getValue('kbgsError'), 'error');
            }
        }
    }

    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJS($this->getModuleDirUrl() . 'kbgoogleshopping/views/js/script.js');
        $this->addJS($this->getModuleDirUrl() . 'kbgoogleshopping/views/js/validate_admin.js');
        $this->addJS($this->getModuleDirUrl() . 'kbgoogleshopping/views/js/velovalidation.js');
        $this->addCSS($this->getModuleDirUrl() . 'kbgoogleshopping/views/css/style.css');
    }

    //Function definition to render list
    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        return parent::renderList();
    }
    
    //function to render toolbar of the helper list
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }
    
    //function to render form
    public function renderForm()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        $obj = $this->module->loadObject(true);
        $gs_countries = KbGSProfiles::getGSCountries();
        $available_country = array();
        foreach ($gs_countries as $country) {
            $iso_code = $country['iso_code'];
            $id_country = Country::getByIso($iso_code, true);
            if (!empty($id_country)) {
                $available_country[] = $country;
            }
        }
        $categoryTreeSelection = array();
        if (!empty($obj->prestashop_category)) {
            $categoryTreeSelection = explode(',', $obj->prestashop_category);
        }
        
        if (!empty($obj)) {
            $this->fields_value = array(
                'gs_category' => $obj->gs_category_code,
            );
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
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated code to fetch the data based on the shop_id
	*/
        $available_gs_category = KbGSModule::getRootGSCategories();
        $leaf_category1 = array();
        if (isset($obj->gs_category_code)) {
            $leaf_category1 = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'kb_gs_categories WHERE id_parent=(SELECT id_parent FROM '._DB_PREFIX_.'kb_gs_categories WHERE category_code='.(int)$obj->gs_category_code.' LIMIT 0,1) AND id_shop='.(int)$this->shop_id);
        }
        $this->context->smarty->assign('left_category0', $obj->gs_category_code);
        $this->context->smarty->assign('left_category1', $leaf_category1);
        if (!empty($leaf_category1)) {
            if ($leaf_category1[0]['id_parent'] != 0) {
                $leaf_category2 = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE category_code=' . (int) $leaf_category1[0]['id_parent'] . ' AND id_shop=' . (int) $this->shop_id);
                $this->context->smarty->assign('left_category2', $leaf_category2);
                if (!empty($leaf_category2)) {
                    $leaf_category3 = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE id_parent=' . (int) $leaf_category2['id_parent'] . ' AND id_shop=' . (int) $this->shop_id);
                    $this->context->smarty->assign('left_category3', $leaf_category3);
                    if (!empty($leaf_category3)) {
                        if ($leaf_category3[0]['id_parent'] != 0) {
                            $leaf_category4 = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE category_code=' . (int) $leaf_category3[0]['id_parent'] . ' AND id_shop=' . (int) $this->shop_id);
                            $this->context->smarty->assign('left_category4', $leaf_category4);
                            if (!empty($leaf_category4)) {
                                $leaf_category5 = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE id_parent=' . (int) $leaf_category4['id_parent'] . ' AND id_shop=' . (int) $this->shop_id);
                                $this->context->smarty->assign('left_category5', $leaf_category5);
                                if ($leaf_category5[0]['id_parent'] != 0) {
                                    $leaf_category6 = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE category_code=' . (int) $leaf_category5[0]['id_parent'] . ' AND id_shop=' . (int) $this->shop_id);
                                    $this->context->smarty->assign('left_category6', $leaf_category6);
                                    if (!empty($leaf_category6)) {
                                        $leaf_category7 = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE id_parent=' . (int) $leaf_category6['id_parent'] . ' AND id_shop=' . (int) $this->shop_id);
                                        $this->context->smarty->assign('left_category7', $leaf_category7);
                                        if ($leaf_category7[0]['id_parent'] != 0) {
                                            $leaf_category8 = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE category_code=' . (int) $leaf_category7[0]['id_parent'] . ' AND id_shop=' . (int) $this->shop_id);
                                            $this->context->smarty->assign('left_category8', $leaf_category8);
                                            if (!empty($leaf_category8)) {
                                                $leaf_category9 = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE id_parent=' . (int) $leaf_category8['id_parent'] . ' AND id_shop=' . (int) $this->shop_id);
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
            'name' => $this->module->l('Select', 'AdminKbGSProfileCategoryMappingController'),
        );
        foreach (Feature::getFeatures($this->context->language->id) as $feature) {
            $gsFeatureArray[] = $feature;
        }
        $gsGenderArray = array(
            array(
                'id' => null,
                'name' => $this->module->l('Select', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'male',
                'name' => $this->module->l('Male', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'female',
                'name' => $this->module->l('Female', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'unisex',
                'name' => $this->module->l('Unisex', 'AdminKbGSProfileCategoryMappingController'),
            ),
        );
        
        $gsAgeGroupArray = array(
            array(
                'id' => null,
                'name' => $this->module->l('Select', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'newborn',
                'name' => $this->module->l('Newborn', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'infant',
                'name' => $this->module->l('Infant', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'toddler',
                'name' => $this->module->l('Toddler', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'kids',
                'name' => $this->module->l('Kids', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'adult',
                'name' => $this->module->l('Adult', 'AdminKbGSProfileCategoryMappingController'),
            ),
        );
        
        $gsProductAttributeArray = AttributeGroup::getAttributesGroups($this->context->language->id);
        array_unshift($gsProductAttributeArray, array('id_attribute_group' => null, 'name' => $this->module->l('Select', 'AdminKbGSProfileCategoryMappingController')));
        
        $gsSizeSystemArray = array(
            array(
                'id' => null,
                'name' =>$this->module->l('Select', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'AU',
                'name' => $this->module->l('AU', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'BR',
                'name' => $this->module->l('BR', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'CN',
                'name' => $this->module->l('CN', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'DE',
                'name' => $this->module->l('DE', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'EU',
                'name' => $this->module->l('EU', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'FR',
                'name' => $this->module->l('FR', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'IT',
                'name' => $this->module->l('IT', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'JP',
                'name' => $this->module->l('JP', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'MEX',
                'name' => $this->module->l('MEX', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'UK',
                'name' => $this->module->l('UK', 'AdminKbGSProfileCategoryMappingController'),
            ),
            array(
                'id' => 'GB',
                'name' => $this->module->l('GB', 'AdminKbGSProfileCategoryMappingController'),
            ),
        );
        
        $this->fields_form = array(
            'legend' => array(
                'title' => !Tools::isEmpty(trim(Tools::getValue('id_profile_category'))) ? $this->module->l('Update Category', 'AdminKbGSProfileCategoryMappingController') : $this->module->l('Add New Category', 'AdminKbGSProfileCategoryMappingController'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_gs_profiles'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'gs_category'
                ),
                array(
                    'type' => 'categories_select',
                    'label' => $this->module->l('Store category', 'AdminKbGSProfileCategoryMappingController'),
                    'desc' => $this->module->l('Select Store Category', 'AdminKbGSProfileCategoryMappingController'),
                    'name' => 'prestashop_category',
                    'required' => true,
                    'category_tree' => $categoryTreePresta //This is the category_tree called in form.tpl
                ),
                
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Material', 'AdminKbGSProfileCategoryMappingController'),
                    'hint' => $this->module->l('Select Material', 'AdminKbGSProfileCategoryMappingController'),
                    'name' => 'material',
                    'options' => array(
                        'query' => $gsFeatureArray,
                        'id' => 'id_feature',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Pattern', 'AdminKbGSProfileCategoryMappingController'),
                    'hint' => $this->module->l('Select Pattern', 'AdminKbGSProfileCategoryMappingController'),
                    'name' => 'pattern',
                    'options' => array(
                        'query' => $gsFeatureArray,
                        'id' => 'id_feature',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Gender', 'AdminKbGSProfileCategoryMappingController'),
                    'hint' => $this->module->l('Select Gender', 'AdminKbGSProfileCategoryMappingController'),
                    'name' => 'gender',
                    'options' => array(
                        'query' => $gsGenderArray,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Age Group', 'AdminKbGSProfileCategoryMappingController'),
                    'hint' => $this->module->l('Select Age Group', 'AdminKbGSProfileCategoryMappingController'),
                    'name' => 'age_group',
                    'options' => array(
                        'query' => $gsAgeGroupArray,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Adult Content', 'AdminKbGSProfileCategoryMappingController'),
                    'hint' => $this->module->l('Select Adult Content', 'AdminKbGSProfileCategoryMappingController'),
                    'name' => 'adult',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'no',
                                'name' => $this->module->l('No', 'AdminKbGSProfileCategoryMappingController'),
                            ),
                            array(
                                'id' => 'yes',
                                'name' => $this->module->l('Yes', 'AdminKbGSProfileCategoryMappingController'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Color', 'AdminKbGSProfileCategoryMappingController'),
                    'hint' => $this->module->l('Select Color', 'AdminKbGSProfileCategoryMappingController'),
                    'name' => 'color',
                    'options' => array(
                        'query' => $gsProductAttributeArray,
                        'id' => 'id_attribute_group',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Size', 'AdminKbGSProfileCategoryMappingController'),
                    'hint' => $this->module->l('Select Size', 'AdminKbGSProfileCategoryMappingController'),
                    'name' => 'size',
                    'options' => array(
                        'query' => $gsProductAttributeArray,
                        'id' => 'id_attribute_group',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Size Type', 'AdminKbGSProfileCategoryMappingController'),
                    'hint' => $this->module->l('Select Size Type', 'AdminKbGSProfileCategoryMappingController'),
                    'name' => 'size_type',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => null,
                                'name' =>$this->module->l('Select', 'AdminKbGSProfileCategoryMappingController'),
                            ),
                            array(
                                'id' => 'regular',
                                'name' => $this->module->l('Regular', 'AdminKbGSProfileCategoryMappingController'),
                            ),
                            array(
                                'id' => 'petite',
                                'name' => $this->module->l('Petite', 'AdminKbGSProfileCategoryMappingController'),
                            ),
                            array(
                                'id' => 'plus',
                                'name' => $this->module->l('Plus', 'AdminKbGSProfileCategoryMappingController'),
                            ),
                            array(
                                'id' => 'big and tall',
                                'name' => $this->module->l('Big and Tall', 'AdminKbGSProfileCategoryMappingController'),
                            ),
                            array(
                                'id' => 'maternity',
                                'name' => $this->module->l('Maternity', 'AdminKbGSProfileCategoryMappingController'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Size System', 'AdminKbGSProfileCategoryMappingController'),
                    'hint' => $this->module->l('Select Size System', 'AdminKbGSProfileCategoryMappingController'),
                    'name' => 'size_system',
                    'options' => array(
                        'query' => $gsSizeSystemArray,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
            ),
            'submit' => array(
                'class' => 'btn btn-default pull-right',
                'title' => $this->module->l('Save', 'AdminKbGSProfileCategoryMappingController'),
            )
        );
        $this->context->smarty->assign(
            array(
                'kb_gs_profile_controller' => $this->context->link->getAdminLink('AdminKbGSProfileManagement', true),
                'loader' => $this->getModuleDirUrl().$this->module->name.'/views/img/spinner.gif',
                'id_profile_category' => Tools::getValue('id_profile_category'),
                'kbgsCategory' => $available_gs_category,
            )
        );
        $tpl = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'kbgoogleshopping/views/templates/admin/kb_gs_category.tpl'
        );
        return $tpl.parent::renderForm();
    }
    
    //function to add new form data
    public function processAdd()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        if (Tools::isSubmit('submitAdd'. $this->table)) {
            $customErrors = array();
            $profileGSCategory = Tools::getValue('gs_category');
            $material = Tools::getValue('material');
            $pattern = Tools::getValue('pattern');
            $gender = Tools::getValue('gender');
            $age_group = Tools::getValue('age_group');
            $adult = Tools::getValue('adult');
            $color = Tools::getValue('color');
            $size = Tools::getValue('size');
            $size_type = Tools::getValue('size_type');
            $size_system = Tools::getValue('size_system');
            $storeCategoriesList = '';
            
            if (Tools::getValue('prestashop_category')) {
                $storeCategories = Tools::getValue('prestashop_category');
                $storeCategoriesList = implode(",", $storeCategories);
            }
            $formError = 0;
            
            //Validate Store Categories
            if (empty($storeCategoriesList)) {
                $formError = 1;
                $customErrors[] = 6;
            } else {
                if (isset($storeCategories)) {
                    $storeCategoryExist = 0;
                    $ProfileCategoryExist = 0;
                    foreach ($storeCategories as $key => $value) {
                        //SQL to check details existence
			    	/**
				* To make the module multi-store compatible
				* @Author Ravi Kant Gupta
				* @date 11-11-2024
				* Updated code to fetch the data based on the shop_id
				*/
                        if (!Tools::isEmpty(trim(Tools::getValue('id_gs_profiles')))) {
                            $gsProfileCat = 'SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'kb_gs_category_mapping WHERE gs_category_code = ' . (int) $profileGSCategory . ' AND id_gs_profiles = ' . (int) Tools::getValue('id_gs_profiles') . ' AND id_shop=' . (int) $this->shop_id;
                            $selectSQL = 'SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'kb_gs_category_mapping WHERE FIND_IN_SET("' . pSQL($value) . '", prestashop_category) AND id_gs_profiles != ' . (int) Tools::getValue('id_gs_profiles') . ' AND id_shop=' . (int) $this->shop_id;
                            $ProfileCategory = 'SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'kb_gs_category_mapping WHERE FIND_IN_SET("' . pSQL($value) . '", prestashop_category) AND id_gs_profiles = ' . (int) Tools::getValue('id_gs_profiles') . ' AND id_shop=' . (int) $this->shop_id;

                            $dataExistenceProfileCat = Db::getInstance()->executeS($gsProfileCat, true, false);
                            $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                            $dataExistenceCategory = Db::getInstance()->executeS($ProfileCategory, true, false);
                            if ($dataExistenceProfileCat[0]['count'] >0) {
                                $ProfileCategoryExist = 1;
                            } elseif ($dataExistenceResult[0]['count'] > 0) {
                                $storeCategoryExist = 1;
                            } elseif ($dataExistenceCategory[0]['count'] > 0) {
                                $storeCategoryExist = 1;
                            }
                        }
                    }

                    if ($ProfileCategoryExist) {
                        $formError = 1;
                        $customErrors[] = 28;
                    } elseif ($storeCategoryExist) {
                        $formError = 1;
                        $customErrors[] = 7;
                    }
                }
            }

            if (!$formError) {
                $kbProfileCategory = new KbGSProfileCategory();
                $kbProfileCategory->gs_category_code = $profileGSCategory;
                $kbProfileCategory->prestashop_category = $storeCategoriesList;
                $kbProfileCategory->id_gs_profiles = Tools::getValue('id_gs_profiles');
                $kbProfileCategory->material = $material;
                $kbProfileCategory->pattern = $pattern;
                $kbProfileCategory->gender = $gender;
                $kbProfileCategory->age_group = $age_group;
                $kbProfileCategory->adult = $adult;
                $kbProfileCategory->color = $color;
                $kbProfileCategory->size = $size;
                $kbProfileCategory->size_system = $size_system;
                $kbProfileCategory->size_type = $size_type;
                $kbProfileCategory->is_global = 0;
		/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 11-11-2024
		* Updated code to fetch the save data based on the shop_id
		*/ 
                $kbProfileCategory->id_shop = (int)$this->shop_id;
                $kbProfileCategory->add();
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminKbGSProfileCategoryMapping').'&id_gs_profiles='.Tools::getValue('id_gs_profiles'));
            } else {
                //Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProfileCategoryMapping') . '&id_gs_profiles='.Tools::getValue('id_gs_profiles').'&kbgsError=' . implode(",", $customErrors));
            }
        }
    }
    
    //function to update form data
    public function processUpdate()
    {
        /**
         * Shop id was not changing when we switch between stores, so assigned shop_id from the context
         * @modifier Himanshu Vishwakarma
         * @date 12-02-2025
         */
        $this->shop_id = (int)Context::getContext()->shop->id;
        if (Tools::isSubmit('submitAdd'. $this->table)) {
            $customErrors = array();
            $profileGSCategory = Tools::getValue('gs_category');
            $material = Tools::getValue('material');
            $pattern = Tools::getValue('pattern');
            $gender = Tools::getValue('gender');
            $age_group = Tools::getValue('age_group');
            $adult = Tools::getValue('adult');
            $color = Tools::getValue('color');
            $size = Tools::getValue('size');
            $size_type = Tools::getValue('size_type');
            $size_system = Tools::getValue('size_system');
            $storeCategoriesList = '';
            if (Tools::getValue('prestashop_category')) {
                $storeCategories = Tools::getValue('prestashop_category');
                $storeCategoriesList = implode(",", $storeCategories);
            }
            $formError = 0;
            
            //Validate Store Categories
            if (empty($storeCategoriesList)) {
                $formError = 1;
                $customErrors[] = 6;
            } else {
                if (isset($storeCategories)) {
                    $storeCategoryExist = 0;
                    $ProfileCategoryExist = 0;
                    foreach ($storeCategories as $key => $value) {
                        //SQL to check details existence
			    	/**
				* To make the module multi-store compatible
				* @Author Ravi Kant Gupta
				* @date 12-11-2024
				* Updated code to fetch the data based on the shop_id
				*/
                        if (!Tools::isEmpty(trim(Tools::getValue('id_gs_profiles')))) {
                            $gsProfileCat = 'SELECT count(*) as count FROM '._DB_PREFIX_.'kb_gs_category_mapping WHERE gs_category_code ='.(int)$profileGSCategory.' AND id_gs_profiles = ' . (int) Tools::getValue('id_gs_profiles').' AND id_profile_category !='.(int)Tools::getValue('id_profile_category').' AND id_shop='.(int)$this->shop_id;
                            $selectSQL = 'SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'kb_gs_category_mapping WHERE FIND_IN_SET("' . pSQL($value) . '", prestashop_category) AND id_profile_category !='.(int)Tools::getValue('id_profile_category') . ' AND id_shop=' . (int) $this->shop_id;
                        }
                        $dataExistenceProfileCat = Db::getInstance()->executeS($gsProfileCat, true, false);
                        $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                        if ($dataExistenceProfileCat[0]['count'] >0) {
                            $ProfileCategoryExist = 1;
                        } elseif ($dataExistenceResult[0]['count'] > 0) {
                            $storeCategoryExist = 1;
                        }
                    }

                    if ($ProfileCategoryExist) {
                        $formError = 1;
                        $customErrors[] = 28;
                    } elseif ($storeCategoryExist) {
                        $formError = 1;
                        $customErrors[] = 7;
                    }
                }
            }

            if (!$formError) {
                $kbProfileCategory = new KbGSProfileCategory(Tools::getValue('id_profile_category'));
                $kbProfileCategory->gs_category_code = $profileGSCategory;
                $kbProfileCategory->prestashop_category = $storeCategoriesList;
                $kbProfileCategory->id_gs_profiles = Tools::getValue('id_gs_profiles');
                $kbProfileCategory->material = $material;
                $kbProfileCategory->pattern = $pattern;
                $kbProfileCategory->gender = $gender;
                $kbProfileCategory->age_group = $age_group;
                $kbProfileCategory->adult = $adult;
                $kbProfileCategory->color = $color;
                $kbProfileCategory->size = $size;
                $kbProfileCategory->size_system = $size_system;
                $kbProfileCategory->size_type = $size_type;
                $kbProfileCategory->is_global = 0;
                $kbProfileCategory->id_shop = (int)$this->shop_id;
                $kbProfileCategory->update();
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminKbGSProfileCategoryMapping').'&id_gs_profiles='.Tools::getValue('id_gs_profiles'));
            } else {
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProfileCategoryMapping') . '&id_gs_profiles='.Tools::getValue('id_gs_profiles').'&kbgsError=' . implode(",", $customErrors));
            }
        }
    }
    
    //function to delete record
    public function processDelete()
    {
        $customErrors = array();
        $kbcategory = new KbGSProfileCategory(Tools::getValue('id_profile_category'));
        if ($kbcategory->is_global) {
            $customErrors[] = 30;
            Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProfileCategoryMapping') . '&id_gs_profiles='.Tools::getValue('id_gs_profiles').'&kbgsError=' . implode(",", $customErrors));
        }
        parent::processDelete();
        Tools::redirectAdmin($this->context->link->getAdminlink('AdminKbGSProfileCategoryMapping') . '&id_gs_profiles='.Tools::getValue('id_gs_profiles').'&conf=1');
    }
    
    //function to display action link on the list
    public function displayEditLink($token, $id)
    {
        if (!array_key_exists('update', self::$cache_lang)) {
            self::$cache_lang['update'] = $this->module->l('Edit', 'AdminKbGSProfileCategoryMappingController');
        }

        $this->context->smarty->assign(
            array(
                    'href' => self::$currentIndex .
                    '&' . $this->identifier . '=' . $id .
                    '&update'.$this->table.'&id_gs_profiles='.Tools::getValue('id_gs_profiles').'&token=' . ($token != null ? $token : $this->token),
                    'action' => self::$cache_lang['update'],
                    )
        );

        return $this->context->smarty->fetch('helpers/list/list_action_edit.tpl');
    }
    
    //Function definition to handle Form Submission
    public function postProcess()
    {
        parent::postProcess();
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
            self::$cache_lang[$status] = $this->module->l($status, 'Helper');
        }

        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminKbGSProfileManagement') . '&' . $this->identifier . '=' . $id . '&status=' . $status,
            'action' => self::$cache_lang[$status],
            'icon' => $icon
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/list/list_action.tpl');
    }
    
    /** Display Category Mapping action link  */
    public function displayCategoryLink($token = null, $id = null, $name = null)
    {
        $category = 'profilecategory';
        if (!array_key_exists($category, self::$cache_lang)) {
            self::$cache_lang[$category] = $this->module->l('View Mapped Category', 'AdminKbGSProfileCategoryMappingController');
        }

        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminKbGSProfileCategoryMapping') . '&' . $this->identifier . '=' . $id . '&'.$category,
            'action' => self::$cache_lang[$category],
            'icon' => 'search-plus',
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/list/list_action.tpl');
    }
    
    /** Function used display toolbar in page header  */
    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['back_url'] = array(
            'href' => 'javascript: window.history.back();',
            'desc' => $this->module->l('Back', 'AdminKbGSProfileCategoryMappingController'),
            'icon' => 'process-icon-back'
        );
        
        $this->page_header_toolbar_btn['new_template'] = array(
            'href' => self::$currentIndex.'&add'.$this->table.'&id_gs_profiles='.Tools::getValue('id_gs_profiles').'&token='.$this->token,
            'desc' => $this->module->l('Add new Category', 'AdminKbGSProfileCategoryMappingController'),
            'icon' => 'process-icon-new'
        );
        
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

        if ((bool) Configuration::get('PS_SSL_ENABLED', null, null, (int)$this->shop_id) && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }
}
