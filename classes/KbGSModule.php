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

require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSIntegration.php');
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/vendor/GSSingleAction.php');
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/vendor/GSFeedSchedule.php');
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/vendor/GSFeed.php');
/**
 * @Author Ravi Kant Gupta
 * @date 17-09-2024
 * Imported file for sending the products to API
 */
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/vendor/GSApi.php');

//Class and its methods to handle some common features of Etsy Module
class KbGSModule extends Module
{
    // Declare class properties for PS validator compatibility. 08-06-2024
    protected $feed_content = '';
    protected $_kbgsConf = array();
    protected $_kbgsError = array();
    public $productVariationsInserted = 0;
    public $productVariationsUpdated = 0;
    public $productsInserted = 0;
    public $productsUpdated = 0;
    public $listingsCreated = 0;
    public $listingsUpdated = 0;

    public function __construct($index = null, $type = null)
    {
        parent::__construct();
        $module = Module::getInstanceByName('kbgoogleshopping');
        //List of confirmations
        $this->_kbgsConf = array(
            '1' => $module->l('Settings saved & connected to Google Shopping successfully.', 'KbGSModule'),
            '2' => $module->l('Disconnected Successfully.', 'KbGSModule'),
            '3' => $module->l('Order settings saved successfully.', 'KbGSModule'),
            '4' => $module->l('Listing renewal recorded successfully.', 'KbGSModule'),
            '5' => $module->l('Listing renewal stopped successfully.', 'KbGSModule'),
            '6' => $module->l('Product has been enabled successfully.', 'KbGSModule'),
            '7' => $module->l('Product has been disabled successfully.', 'KbGSModule'),
            '8' => $module->l('Profile has been updated successfully.', 'KbGSModule'),
            '9' => $module->l('Profile has been added successfully. Kindly go to Feed Management section to add the feed corresponding to this profile to list products on google.', 'KbGSModule'),
            '10' => $module->l('Profile has been disabled successfully.', 'KbGSModule'),
            '11' => $module->l('Profile has been enabled successfully.', 'KbGSModule'),
            '12' => $module->l('Profile has been deleted successfully.', 'KbGSModule'),
            '13' => $module->l('Feed has been created successfully', 'KbGSModule'),
            '14' => $module->l('Feed has been updated successfully', 'KbGSModule'),
            '15' => $module->l('Feed has been disabled successfully.', 'KbGSModule'),
            '16' => $module->l('General Setting has been saved successfully.', 'KbGSModule'),
            '17' => $module->l('Products have been activated successfully.', 'KbGSModule'),
            '18' => $module->l('Products have been deactivated successfully.', 'KbGSModule'),
            '19' => $module->l('Settings saved successfully.', 'KbGSModule'),
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 17-09-2024
	 * To show success message after editing the product
	 */
            '20' => $module->l('Products information has been saved successfully.', 'KbGSModule'),
        );

        //List of errors
        $this->_kbgsError = array(
            '1' => $module->l('Settings saved but connection could not be established. Try again.', 'KbGSModule'),
            '2' => $module->l('Listing renewal failed. Try to relist product instead.', 'KbGSModule'),
            '3' => $module->l('Listing halt failed. Try to relist product instead.', 'KbGSModule'),
            '4' => $module->l('Product Listing could not be deleted.', 'KbGSModule'),
            '5' => $module->l('Please provide valid Profile Title. Length should be between 0 - 255.', 'KbGSModule'),
            '6' => $module->l('Please select store categories to map with Profile.', 'KbGSModule'),
            '7' => $module->l('Profile already exists for atleast one of selected store categories.', 'KbGSModule'),
            '8' => $module->l('Profile could not be deleted.', 'KbGSModule'),
            '9' => $module->l('Something went wrong.', 'KbGSModule'),
            '10' => $module->l('Feed already exist with same Profile Id', 'KbGSModule'),
            '11' => $module->l('Profile can not be deleted becuase its mapped to feed. Delete the feed first to delete the profile.', 'KbGSModule'),
            '28' => $module->l('Profile and Google Shopping Category already exist.', 'KbGSModule'),
            '30' => $module->l('Main Category cannot be deleted for the profile.', 'KbGSModule'),
            '31' => $module->l('Store category already exist with other Google Shopping Category.', 'KbGSModule'),
            '99' => $module->l('You can only create 1 profile in the free version. Purchase the paid version to use all features', 'KbGSModule'),
            '98' => $module->l('You can only create 1 feed in the free version. Purchase the paid version to use all features', 'KbGSModule'),
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 17-09-2024
	 * To show error message after editing the product
	 */            
	    '100' => $module->l('Product details not found', 'KbGSModule'),
        );

        if (!empty($index) && !empty($type) && $type == 'conf') {
            $index = explode(",", $index);
            foreach ($index as $value) {
                Context::getContext()->controller->confirmations[] = $this->_kbgsConf[$value];
            }
        }

        if (!empty($index) && !empty($type) && $type == 'error') {
            $index = explode(",", $index);
            foreach ($index as $value) {
                Context::getContext()->controller->errors[] = $this->_kbgsError[$value];
            }
        }
    }

    //function to get root category of Google Shopping
    public static function getRootGSCategories()
    {
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection
	*/ 
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $data = Db::getInstance()->executeS('SELECT category_code, name FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE id_parent=0' . ' AND id_shop = ' . (int)$shop_id);
        return $data;
    }

    //function to get sub category of Google Shopping
    public static function getSubGSCategories($id_parent)
    {
        if (empty($id_parent)) {
            return; 
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection
	*/ 
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $data = Db::getInstance()->executeS('SELECT category_code, name FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE id_parent=' . (int) $id_parent . ' AND id_shop = ' .(int) $shop_id);
        return $data;
    }

    //Function definition to add an entry of Audit Log into Database
    public static function auditLogEntry($auditLogEntry = '', $auditMethodName = '', $auditLogUser = '')
    {
        $auditLogTime = date("Y-m-d H:i:s");
        if (!empty(Context::getContext()->employee->firstname) && !empty(Context::getContext()->employee->lastname)) {
            $auditLogUser = Context::getContext()->employee->firstname . ' ' . Context::getContext()->employee->lastname;
        } else {
            $auditLogUser = 'Default';
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection
	*/ 
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;

        // Replace empty() checks with specific conditions for function parameter compatibility. Fixes PS validator error. 08-06-2024
        if ($auditLogEntry !== '' && $auditLogUser !== '' && $auditMethodName !== '' && $auditLogTime !== '') {
            $auditLogSQL = "INSERT INTO " . _DB_PREFIX_ . "kb_gs_audit_log VALUES (NULL, '" . pSQL($auditLogEntry, true) . "', '" . pSQL($auditLogUser) . "', '" . pSQL($auditMethodName) . "', '" . pSQL($auditLogTime) . "', " . (int)$shop_id . ");";
            if (Db::getInstance()->execute($auditLogSQL)) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    //function defination to check token is expired or not
    public static function isTokenExpired($token_info, $gs_connect)
    {
        /**
         * Load correct version of the Google Client lib according to the PS version
         * GS005-1003024 load-GS-Lib
         * @date 10-03-2024
         * @author Ashish
         */
        if (_PS_VERSION_ > 8) {
            require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/vendor/lib/google_client8/autoload.php');
        } else {
            require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/vendor/lib/google_client/autoload.php');
        }
        /*
         * Fixed token handling to prevent "Undefined index: expires_in" error
         * Ensure token_info is properly formatted as array before passing to Google Client
         * 08-01-2025
         */
        if (!is_array($token_info)) {
            return true; // If token_info is not an array, consider it expired
        }
        
        // Ensure all required token fields are present
        if (!isset($token_info['access_token']) || !isset($token_info['expires_in'])) {
            return true; // Missing required fields, consider expired
        }
        $client = new Google_Client();
        $client->setApplicationName($gs_connect['application_name']);
        $client->setClientId($gs_connect['client_id']);
        $client->setClientSecret($gs_connect['client_secret']);
        $client->setAccessToken($token_info);

        $client->setScopes('https://www.googleapis.com/auth/content');
        $client->setAccessType('offline');
        if ($client->isAccessTokenExpired()) {
            return true;
        } else {
            return false;
        }
    }

    //Function definition for fetching store categories based on multiple category mapping
    public static function getStoreCategoryIds($getGSProfile)
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection
	*/ 
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $categoryIds = array();
        $profileCategory = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_category_mapping WHERE id_gs_profiles=' . (int) $getGSProfile['id_gs_profiles'] . ' AND id_shop = ' .(int) $shop_id);
        if (!Tools::isEmpty($profileCategory) && is_array($profileCategory)) {
            foreach ($profileCategory as $proCat) {
                $cat = explode(',', $proCat['prestashop_category']);
                // Replace redundant conditions with simplified logic for PS validator compatibility. 08-06-2024
                if (!empty($cat)) {
                    foreach ($cat as $innerCat) {
                        $categoryIds[] = $innerCat;
                    }
                }
            }
        }
        return $categoryIds;
    }

    //Function definition to update products status by profile status
    public static function updateProductStatusByProfileStatus()
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection
	*/ 
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_profiles WHERE active = '0' AND id_shop = " . $shop_id;
        // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
        $getGsProfiles = Db::getInstance()->executeS($selectSQL, true, false);

        //Check if profile details are available
        if (!empty($getGsProfiles)) {
            //Iteration for accessign each profile
            foreach ($getGsProfiles as $getGsProfile) {
                $updateSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET listing_status = 'Inactive', delete_flag = '1', renew_flag = '0' WHERE id_gs_profiles = '" . (int) $getGsProfile['id_gs_profiles'] . "' AND id_shop = " . (int)$shop_id;
                // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                Db::getInstance()->execute($updateSQL);
            }
        }
        return true;
    }

    public static function getFeedProducts()
    {
        //Audit Log Entry
        $module = Module::getInstanceByName('kbgoogleshopping');
        $auditLogEntryString = $module->l('Job execution statrted to add/update products and their variations into the Etsy Products Listing by existing profiles.', 'KbGSModule');
        $auditMethodName = 'KbGSModule::getAllProfileProducts()';
        KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);
    }

    /*
     * Function definition to get all products by profile
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    public static function getAllProfileProducts()
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection
	*/ 
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        //Audit Log Entry
        $module = Module::getInstanceByName('kbgoogleshopping');
        $auditLogEntryString = $module->l('Job execution statrted to add/update products into Products Listing by existing profiles.', 'KbGSModule');
        $auditMethodName = 'KbGSModule::getAllProfileProducts()';
        KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);

        if (self::updateProductStatusByProfileStatus()) {
            $productsInserted = 0;
            $productsUpdated = 0;
            $productVariationsInserted = 0;
            $productVariationsUpdated = 0;
            $validProducts = array();
            $validVariations = array();
            $listProducts = array();
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 18-09-2024
	 * Added condition to not include the deleted products
	 */    
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated query to fetch/update the data based on the current shop ID
	*/    
            // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET delete_flag = '1' where delete_flag != '2'" . " AND id_shop = " . (int)$shop_id);

            $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_profiles WHERE active = '1' AND id_shop = " . (int)$shop_id;
            // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
            $getGSProfiles = Db::getInstance()->executeS($selectSQL, true, false);
            //Check if profile details are available
            if (!empty($getGSProfiles)) {

                /*
                * Initialized variable to count the number of products added to the list
                * @date 22-07-2026
                * @author Amit Singh
                */
                $count = 0;
                //Iteration for accessign each profile
                foreach ($getGSProfiles as $getGSProfile) {
                    $categoryIds = self::getStoreCategoryIds($getGSProfile);
                    //Get Products and Variations list as per the categories selected under profile.
                    if (!empty($categoryIds)) {
                        foreach ($categoryIds as $value) {
                            $categoryProductCount = KbGSIntegration::getCountProductByDefaultCategoryId($value);
                            $categoryProductsList = KbGSIntegration::getProductsByDefaultCategoryId($value, 0, $categoryProductCount['success']);
                            if (isset($categoryProductsList['error']) && $categoryProductsList['error'] == '') {
                                foreach ($categoryProductsList['success'] as $categoryProduct) {
                                    $listProducts[] = $categoryProduct['id_product'];
                                    /*
                                    * Replaced Tools::jsonDecode by json_decode
                                    * Tools::jsonDecode has been removed in PrestaShop8
                                    * TGfeb2023 Used-json_decode
                                    * @date 25-02-2023
                                    * @author Tanisha Gupta
                                    */
                                    $kb_general = json_decode(Configuration::get('kbgs_general_setting',null, null, (int)$shop_id), true);
                                    // Cast to int for Product constructor compatibility with PS validator. Fixes int|null type expectation. 08-06-2024
                                    $product = new Product((int)$categoryProduct['id_product'], false, $getGSProfile['id_lang']);
                                    $productInventory = KbGSIntegration::getProductInventory($categoryProduct['id_product']);

                                    if ($product->active != 1) {
                                        continue;
                                    }

                                    /* Initialized variable to count the number of products added to the list
                                    * @date 22-07-2026
                                    * @author Amit Singh
                                    */
                                    if ($count >= 10) {
                                        return true;
                                    }
                                    // Cast to int for Product::getPriceStatic compatibility with PS validator. Fixes int|null type expectation. 08-06-2024
                                    $price = Product::getPriceStatic((int)$categoryProduct['id_product'], true, null, 2, null, false, true);
                                    
					/**
					* To make the module multi-store compatible
					* @Author Ravi Kant Gupta
					* @date 12-11-2024
					* Updated code to fetch the data based on the current shop ID
					*/ 
                                    $gtin = Db::getInstance()->getValue('SELECT gtin FROM ' . _DB_PREFIX_ . 'kb_gs_profiles WHERE id_gs_profiles=' . (int) $getGSProfile['id_gs_profiles'] . ' AND id_shop = ' .(int) $shop_id);
                                    /* Below line is commented by Ashish on 19th Aug. Products will be added on the list but they will not be listed. To help in debugging why products are not listed. */
                                    /*
                                    if ($kb_general['exclude_product_gtin']) {
                                        if ($product->{$gtin} == '') {
                                            continue;
                                        }
                                    }
                                    */
                                    $store_name = explode(" ", Context::getContext()->shop->name);
                                    $acronym = "";
                                    /*
                                    * Added a check for the string before accessing it
                                    * @modifier Himanshu Vishwakarma
                                    * @date 08-04-2025
                                    */
                                    // Replace redundant condition with simplified logic for PS validator compatibility. 08-06-2024
                                    if (!empty($store_name)) {
                                        foreach ($store_name as $w) {
                                            $w = trim($w);
                                            if (is_string($w) && strlen($w) > 0) {
                                                $acronym .= Tools::strtoupper($w[0]);
                                            }
                                        }
                                    }
                                    $generated_id = $acronym . "_" . $shop_id . "_" . $categoryProduct['id_product'];

                                    //Check if product already exists in DB Table
                                    $selectSQL = "SELECT count(*) as count, id_gs_products_list FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE id_product = '" . (int) $categoryProduct['id_product'] . "' AND id_gs_profiles = '" . $getGSProfile['id_gs_profiles'] . "' AND id_shop = " . (int)$shop_id;
                                    $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                                    if ($dataExistenceResult[0]['count'] == 0) {
                                        //Insert Product into Listing DB Table
                                        $insertSQL = "INSERT INTO " . _DB_PREFIX_ . "kb_gs_products_list SET id_gs_products_list = '', generated_listing_id = '" .  pSQL($generated_id) . "', id_gs_profiles = '" . (int) $getGSProfile['id_gs_profiles'] . "', id_product = '" . (int) $categoryProduct['id_product'] . "', reference = '" . pSQL($product->{$gtin}) . "', date_add = NOW() , id_shop = " . (int)$shop_id;
                                        // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                                        if (Db::getInstance()->execute($insertSQL)) {
                                            $validProducts[] = Db::getInstance()->Insert_ID();
                                            $productsInserted++;
                                        }
                                    } else {
                                        //Update Product into Listing DB Table
                                        $updateSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET delete_flag = '0', reference = '" . pSQL($product->{$gtin}) . "', generated_listing_id = '" .  pSQL($generated_id) . "' WHERE id_product = '" . (int) $categoryProduct['id_product'] . "' AND id_gs_profiles = '" . (int) $getGSProfile['id_gs_profiles'] . "' AND id_shop = " . (int)$shop_id;
                                        // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                                        if (Db::getInstance()->execute($updateSQL)) {
                                            $validProducts[] = $dataExistenceResult[0]['id_gs_products_list'];
                                            $productsUpdated++;
                                        }
                                    }
                                       /*
                                        * Incremented variable to count the number of products added to the list
                                        * @date 22-07-2026
                                        * @author Amit Singh
                                        */
                                        $count++;
                                }
                            }
                        }
                    }
                }
            }
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 17-09-2024
	 * Updated condition to only delete the products that are synced using feed. As, we need product details further for delisting it from the Google Shopping
	 */  	    
            //Set delete flag for the products which are not allowed to list on Etsy but present in the list
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated query to delete the data based on the current shop ID
	*/ 
            // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
            Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE delete_flag = '1' and product_listing_method != 'api' AND id_shop = " . (int)$shop_id);
        }

        //Audit Log Entry
        $auditLogEntryString = $module->l('Job execution completed. Responses are as', 'KbGSModule') . ' - <br>.' . $module->l('Total Products Added', 'KbGSModule') . '- ' . $productsInserted . ' <br>' . $module->l('Total Products Updated', 'KbGSModule') . ' - ' . $productsUpdated . ' <br>' . $module->l('Total Variations Inserted', 'KbGSModule') . ' - ' . $productVariationsInserted . ' <br>' . $module->l('Total Variations Updated', 'KbGSModule') . ' - ' . $productVariationsUpdated;
        KbGSModule::auditLogEntry($auditLogEntryString, $auditMethodName);

        return true;
    }

    //Function definition to get Products Variations by Product ID
    /**
     * Fixed parameter type in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_product
     * @return array
     */
    public static function getProductVariationsByProductID($id_product = 0)
    {
        
        $result = array();
        if (!empty($id_product)) {
            $productVariations = KbGSIntegration::getAttributesByProductId($id_product);

            if (isset($productVariations['error']) && $productVariations['error'] == '') {
                foreach ($productVariations['success'] as $productVariation) {
                    $result[] = array(
                        'id_product' => $id_product,
                        'id_product_attribute' => $productVariation['id_product_attribute'],
                        'reference' => $productVariation['reference']
                    );
                }
            }
        }

        return $result;
    }

    //Function definition to save products variations into Etsy Products Listing
    public static function saveProductVariationsInToDB(&$validVariations, $productVariations = array(), $id_gs_profiles = '', $productID = '')
    {
        $productVariationsInserted = 0;
        $productVariationsUpdated = 0;
        $listVariations = array();
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        if (isset($productVariations) && count($productVariations) > 0 && !empty($id_gs_profiles) && !empty($productID)) {
            foreach ($productVariations as $productVariation) {
                $listVariations[] = $productVariation['id_product_attribute'];
		/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 11-11-2024
		* Updated query to fetch the data based on the current shop ID
		*/ 
                //Check if Variation already exists in DB
                $selectSQL = "SELECT count(*) as count, id_gs_products_list FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE id_product = '" . (int) $productVariation['id_product'] . "' AND id_product_attribute = '" . (int) $productVariation['id_product_attribute'] . "' AND id_shop = " . (int)$shop_id;
                $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                if ($dataExistenceResult[0]['count'] == 0) {
                    //Insert Variation into DB
                    $insertSQL = "INSERT INTO " . _DB_PREFIX_ . "kb_gs_products_list SET id_gs_products_list = '', id_gs_profiles = '" . (int) $id_gs_profiles . "', id_product = '" . (int) $productVariation['id_product'] . "', reference = '" . pSQL($productVariation['reference']) . "', id_product_attribute = '" . (int) $productVariation['id_product_attribute'] . "', date_add = NOW() , id_shop = " . (int)$shop_id;
                                            // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                        if (Db::getInstance()->execute($insertSQL)) {
                            $validVariations[] = Db::getInstance()->Insert_ID();
                        $productVariationsInserted++;
                    }
                } else {
			/**
			* To make the module multi-store compatible
			* @Author Ravi Kant Gupta
			* @date 11-11-2024
			* Updated query to fetch the data based on the current shop ID
			*/ 
                    //Update Variation into DB
                    $updateSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET reference = '" . pSQL($productVariation['reference']) . "' WHERE id_product = '" . (int) $productVariation['id_product'] . "' AND id_product_attribute = '" . (int) $productVariation['id_product_attribute'] . "' AND id_shop = " . (int)$shop_id;
                    // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                    if (Db::getInstance()->execute($updateSQL)) {
                        $validVariations[] = $dataExistenceResult[0]['id_gs_products_list'];
                        $productVariationsUpdated++;
                    }
                }
            }
		/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 11-11-2024
		* Updated query to fetch the data based on the current shop ID
		*/ 
            if (!empty($listVariations)) {
                $deleteSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET listing_status = 'Inactive', renew_flag = '0', delete_flag = '1' WHERE id_product = '" . (int) $productID . "' AND id_product_attribute NOT IN (" . pSQL(implode(",", $listVariations)) . ") AND id_product_attribute != '0' AND id_shop = " . (int)$shop_id;
                // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                Db::getInstance()->execute($deleteSQL);
            }
        } else {
            $deleteSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET listing_status = 'Inactive', renew_flag = '0', delete_flag = '1' WHERE id_product = '" . (int) $productID . "' AND id_product_attribute != '0' AND id_shop = " . (int)$shop_id;
            // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
            Db::getInstance()->execute($deleteSQL);
        }

        $result = array(
            'status' => true,
            'variations_inserted' => $productVariationsInserted,
            'variations_updated' => $productVariationsUpdated,
            'valid_variations' => $validVariations
        );

        return $result;
    }

    /*
     * Function to get product list
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */

    public static function getFeedProductsToListOnGS($is_queue = false, $start = 0, $limit = null, $profile_id = null)
    {
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $str = '';
        if ($is_queue) {
            $str = ' LIMIT ' . (int) $start . ', ' . (int) $limit;
        }
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 27-09-2024
	 * Added condition to fetch the specific profiles products if the profile id is passed. This will be used when downloading the feed in different format.
	 */  
        if (!empty($profile_id)) {
            $str = ' AND id_gs_profiles = ' . (int) $profile_id;
        }
	// Added delete condition so that the products that are already deleted do not include in the feed.
        // changes done by kanishka kannoujia on 21-04-2022
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
      $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list 
        where listing_status != 'Inactive' and delete_flag ='0' AND id_shop = " . (int)$shop_id . "
        " . $str;
        // changes end here
        // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
        return Db::getInstance()->executeS($selectSQL, true, false);
    }

    //Function definition to get products from Google Products Table
    public static function getProductsToListOnGS($is_queue = false, $start = 0, $limit = null)
    {
        $str = '';
        if ($is_queue) {
            $str = ' LIMIT ' . (int) $start . ', ' . (int) $limit;
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Added check for All store and default group selection
	*/ 
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE listing_id IS NULL AND id_product_attribute = 0 AND renew_flag = '0' AND delete_flag = '0' AND id_shop = ".(int)$shop_id . $str;
        // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
        $getProductsToListOnEtsy = Db::getInstance()->executeS($selectSQL, true, false);

        return $getProductsToListOnEtsy;
    }

    /*
     * Function definition to prepare array to create listing on Google Shopping
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    public static function prepareArrayToCreateListingOnGS($product = array(), $lang = '', $updateListing = 0, $renewListing = 0)
    {
        $listingArray = array();
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        if (isset($product) && count($product) > 0 && !empty($lang)) {
            $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_profiles WHERE id_gs_profiles = '" . (int) $product['id_gs_profiles'] . "' AND id_shop = " . (int)$shop_id;
            // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
            $profileDetails = Db::getInstance()->executeS($selectSQL, true, false);
            $gs_product = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE id_gs_profiles = '" . (int) $product['id_gs_profiles'] . "' AND id_product = '" . (int) $product['id_product'] . "' AND id_shop = " . (int)$shop_id);

            $gs_category = '';
            $productInventory = KbGSIntegration::getProductInventory($product['id_product']);
            $productDetails = KbGSIntegration::getProductByProductId($product['id_product']);
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
	* Updated code to fetch the data based on the current shop ID
	*/ 
            $kb_general = json_decode(Configuration::get('kbgs_general_setting',null, null, (int)$shop_id), true);

            $profileCategory = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_category_mapping WHERE id_gs_profiles = ' . (int) $product['id_gs_profiles'] . ' AND id_shop = ' .(int) $shop_id);
            /**
             * Fetching google category code based on profile and store category code
             * @date 06-04-2023
             * @commenter Tanisha Gupta
             */
            $gs_category = self::getGSCategorybyProfileANDCategory($profileCategory, $product);
            if ($gs_category != 0) {
                $gs_category_mapping = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_category_mapping WHERE id_gs_profiles=' . (int) $product['id_gs_profiles'] . ' AND gs_category_code=' . (int) $gs_category . ' AND id_shop = ' .(int) $shop_id);
                $gs_currency_id = Currency::getIdByIsoCode($profileDetails[0]['id_currency'], $shop_id);
                $price = Product::getPriceStatic($product['id_product'], true, null, 2, null, false, true);
                $tags = array('</p>', '<br />', '<br>', '</div>', '</li>');
                $short_description = str_replace($tags, "\n", $productDetails['success']->description_short[$lang]);
                $short_description = strip_tags($short_description);
                $customize_title = $profileDetails[0]['customize_product_title'];
                if (!Tools::isEmpty($customize_title)) {
                    /**
                     * Added the fixes for the issue of the product title being send in default langugae. 
                     * We are now sending the product title in the selected language.
                     * ASOct2024 GSlanguage
                     * @date 18-10-2024
                     * @author Amit SIngh
                     */
                    $customize_title = str_replace('{product_title}', $productDetails['success']->name[$profileDetails[0]['id_lang']], $customize_title);
                    $customize_title = str_replace('{id_product}', $productDetails['success']->id, $customize_title);
                    $customize_title = str_replace('{manufacturer_name}', $productDetails['success']->manufacturer_name, $customize_title);
                    $customize_title = str_replace('{supplier_name}', $productDetails['success']->supplier_name, $customize_title);
                    $customize_title = str_replace('{reference}', $productDetails['success']->reference, $customize_title);
                    $customize_title = str_replace('{ean13}', $productDetails['success']->ean13, $customize_title);
                    $customize_title = str_replace('{short_description}', $short_description, $customize_title);
                    /**
                     * Fix for the currency conversion issue & decimal issue
                     * GS002-09032024 gs-profile-create-notice
                     * @date 09-03-2024
                     * @author Ashish
                     */
                    // Cast to int for Currency constructor compatibility with PS validator. Fixes int|null type expectation. 08-06-2024
                    $currencyObject = new Currency((int)$profileDetails[0]['id_currency']);
                    $converted_price = Tools::convertPriceFull($price, Context::getContext()->currency, $currencyObject) . " " . $currencyObject->iso_code;
                    $customize_title = str_replace('{price}', $converted_price, $customize_title);
                } else {
                    /**
                     * Added the fixes for the issue of the product title being send in default langugae. 
                     * We are now sending the product title in the selected language.
                     * ASOct2024 GSlanguage
                     * @date 18-10-2024
                     * @author Amit SIngh
                     */
                    $customize_title = $productDetails['success']->name[$profileDetails[0]['id_lang']];
                }
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 18-09-2024
	 * Added condition to check if the products specific details are overided than use the product's updated details else use the profile's details
	 */  
    		/**
			 * @Author Ravi Kant Gupta
			 * @date 27-11-2024
			 * To fix the issue due to which the product was not added using the API if the product info is not overrided
			 */  
                $add_flag = 1;
                $update_flag = 1;
                $delete_flag = $product['delete_flag'];

                if($delete_flag == '2'){
                    return array();
                }
		// Checking for product's overide condition
                if($product['is_product_info_override'] == '1'){
                    $material = $product['material'];
                    $pattern = $product['pattern'];
                    $gender = $product['gender'];
                    $age_group = $product['age_group'];
                    $adult_content = $product['adult_content'];
                    $color = $product['color'];
                    $size = $product['size'];
                    $size_type = $product['size_type'];
                    $size_system = $product['size_system'];
                    $product_type = $product['product_type'];
                    $product_listing_method = $product['product_listing_method'];
                    $product_condition = $product['product_condition'];
                    $promotion_id = $product['promotion_id'];
                    $add_flag = $product['add_flag'];
                    $update_flag = $product['update_flag'];
                }else{
                    $material = $gs_category_mapping[0]['material'];
                    $pattern = $gs_category_mapping[0]['pattern'];
                    $gender = $gs_category_mapping[0]['gender'];
                    $age_group = $gs_category_mapping[0]['age_group'];
                    $adult_content = $gs_category_mapping[0]['adult'];
                    $color = $gs_category_mapping[0]['color'];
                    $size = $gs_category_mapping[0]['size'];
                    $size_type = $gs_category_mapping[0]['size_type'];
                    $size_system =  $gs_category_mapping[0]['size_system'];
                    $promotion_id =  $profileDetails[0]['promotion_id'];
                    $product_listing_method =  $profileDetails[0]['listing_type'];
                    $product_type  = $profileDetails[0]['product_type'];
                    $product_condition = $profileDetails[0]['product_condition'];
                }

                // Get material name
                $lang_id = (int)$profileDetails[0]['id_lang'];  // Selected language ID

                // Fix FeatureValue::getFeatureValueLang() method calls for PS validator compatibility. Method expects only 1 parameter. 08-06-2024
                $materialValues = FeatureValue::getFeatureValueLang($material);

                // Loop through the array and find the one with the selected language ID
                $materialName = null;
                foreach ($materialValues as $value) {
                    if ($value['id_lang'] == $lang_id) {
                        $materialName = $value['value'];
                        break;  // Exit the loop once the desired language is found
                    }
                }
                // Get pattern name
                $patternValues = FeatureValue::getFeatureValueLang((int)$pattern);
                // Loop through the array and find the one with the selected language ID
                $patternlName = null;
                foreach ($patternValues as $value) {
                    if ($value['id_lang'] == $lang_id) {
                        $patternlName = $value['value'];
                        break;  // Exit the loop once the desired language is found
                    }
                }
                $listingArray = array(
                    'id_product' => $product['id_product'],
                    'id_gs_profiles' => $profileDetails[0]['id_gs_profiles'],
                    'id_country' => $profileDetails[0]['id_country'],
                    'id_lang' => $profileDetails[0]['id_lang'],
                    'generated_listing_id' => $gs_product['generated_listing_id'],
                    'id_currency' => $profileDetails[0]['id_currency'],
                    'customize_product_title' => $profileDetails[0]['customize_product_title'],
                    'material' => $materialName,
                    'pattern' =>  $patternlName,
                    'gender' => $gender,
                    'age_group' =>  $age_group,
                    'adult' =>  $adult_content,
                    'color' =>   $color,
                    'size' =>  $size ,
                    'size_type' =>  $size_type,
                    'size_system' =>  $size_system ,
                    'shipping' => $profileDetails[0]['shipping'],
                    'custom_label_0' => $profileDetails[0]['custom_label_0'],
                    'custom_label_1' => $profileDetails[0]['custom_label_1'],
                    'custom_label_2' => $profileDetails[0]['custom_label_2'],
                    'custom_label_3' => $profileDetails[0]['custom_label_3'],
                    'custom_label_4' => $profileDetails[0]['custom_label_4'],
                    'gs_category' => $gs_category,
                    'price' => $price,
                    'product_title' => $customize_title,
                    'gtin' => $profileDetails[0]['gtin'],
			 /**
			 * @Author Ravi Kant Gupta
			 * @date 18-09-2024
			 * Added fields in the product's array 
			 */  
                    'product_type' => $product_type,
                    'promotion_id' => $promotion_id,
                    'product_condition' => $product_condition,
                    'product_listing_method' => $product_listing_method,               
                    'listing_id' => $product['listing_id'],               
                    'update_flag' => $update_flag,                
                    'add_flag' => $add_flag ,                   
                    'delete_flag' => $delete_flag,          
                );

            
            }
        }
        return $listingArray;
    }

    /*
     * Function definition to fetch google category based on profile and store category
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    public static function getGSCategorybyProfileANDCategory($profileCategory, $product)
    {
        $gs_category = '';
        if (!Tools::isEmpty($profileCategory) && is_array($profileCategory)) {
            foreach ($profileCategory as $proCat) {
                // Cast to int for Product constructor compatibility with PS validator. Fixes int|null type expectation. 08-06-2024
                $current_product = new Product((int)$product['id_product']);
                $default_category = $current_product->id_category_default;
                $prestashop_category = explode(',', $proCat['prestashop_category']);
                if (in_array($default_category, $prestashop_category)) {
                    $gs_category = $proCat['gs_category_code'];
                    break;
                }
            }
        }
        return $gs_category;
    }

    /*
     * Function to create feed listing
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    public static function getCreateFeedListings($listingArray = array(), $type = null)
    {
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $module = Module::getInstanceByName('kbgoogleshopping');
        $auditLogEntryString = $module->l('Job execution started to create feed on Google Shopping', 'KbGSModule');
        $auditMethodName = 'KbGSModule::getCreateFeedListings()';
        self::auditLogEntry($auditLogEntryString, $auditMethodName);
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 26-09-2024
	 * Added condition to check if the feed is not created and automatic product sync is enabled
	 */  
        $gs_connection_config = json_decode(Configuration::get('kbgs_connect_config',null, null, (int)$shop_id), true);
        $automatic_upload = isset($gs_connection_config['automatic_upload']) ? $gs_connection_config['automatic_upload'] : '0';
        if($automatic_upload== 1){
        $gs_feeds = Db::getInstance()->executeS('SELECT a.*,p.* FROM ' . _DB_PREFIX_ . 'kb_gs_feeds a INNER JOIN ' . _DB_PREFIX_ . 'kb_gs_profiles p on (a.id_gs_profiles=p.id_gs_profiles) WHERE a.active=1 AND a.id_shop=' . (int) $shop_id);
        $array_listing = array();
	// // Show the message to create the feed
    //     if(empty($gs_feeds)){
    //         $msg = $module->l('Please create a feed and then try syncing the products.', 'KbGSModule');
    //         echo $msg;
    //     die;
    //     }
        foreach ($gs_feeds as $gsfeeds) {
            self::gsCreateFeed($gsfeeds);
        }
    }

        if (!empty($listingArray) && count($listingArray) > 0) {
            foreach ($listingArray as $listing) {
                if (isset($listing['id_product'])) {
                    $array_listing[$listing['id_gs_profiles']][] = $listing;
                }
            }
        }

        if (!empty($array_listing)) {
            foreach ($array_listing as $key => $listing) {
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 20-09-2024
	 * To sync the products using the Google Content API
	 */  
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated code to fetch the data based on the current shop ID
	*/ 
                $gs_config = json_decode(Configuration::get('kbgs_connect_config',null, null, (int)$shop_id), true);
                $token_connect_info = json_decode(Configuration::get('Kbgs_token_info',null, null, (int)$shop_id), true);
                $client_id = trim($gs_config['client_id']);
                $application_name = trim($gs_config['application_name']);
                $client_secret = trim($gs_config['client_secret']);
                $merchant_id = trim($gs_config['merchant_id']);
                if (
                    empty($client_id) ||
                    empty($application_name) ||
                    empty($client_secret) ||
                    empty($merchant_id)
                ) {
                    $msg = $module->l('You cannot upload new products or update existing products on google shopping when Google Settings are empty. Please follow the user manual', 'KbGSModule');
                        echo $msg;
                    die;
                }
                    
                     $api = new GSApi($gs_config);
                $products = array();
		// If the type is passed then return the data to download the feed
                if($type == 'json' || $type == 'csv'){
                   $products =  $api->fetchProductData($listing, $type);
                   return $products;
                }else{
                    if($automatic_upload == 1){
                    $products =  $api->fetchProductData($listing);
                    }
                }
            
                /*
                * Replaced Tools::jsonDecode by json_decode
                * Tools::jsonDecode has been removed in PrestaShop8
                * TGfeb2023 Used-json_decode
                * @date 25-02-2023
                * @author Tanisha Gupta
                */                
                $kb_general = json_decode(Configuration::get('kbgs_general_setting',null, null, (int)$shop_id), true);
                $obj = new GSFeed($kb_general);
                $feedContent = '';
                $feed_path = _PS_MODULE_DIR_ . 'kbgoogleshopping/feeds/cron_feed_' . $key . '.xml';
            /**
             * @Author Ravi Kant Gupta
             * @date 23-09-2024
             * To provide style to the feed generated
             */  
                $style_path = self::getModuleDirUrl() . 'kbgoogleshopping/views/img/feed.xsl';
                $feedContent .= $obj->generateXMLFeed($listing, 0,50, $type);

                if (!empty($feedContent)) {
                    $feed_content = '';
                    $feed_content .= '<?xml version="1.0"?>' . PHP_EOL;
                    $feed_content .= '<?xml-stylesheet type="text/xsl" href="'.$style_path.'"?>' . PHP_EOL;
                    $feed_content .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . PHP_EOL;
                    $feed_content .= '<channel>' . PHP_EOL;
                    $feed_content .= $feedContent;
                    $feed_content .= '</channel>' . PHP_EOL;
                    $feed_content .= '</rss>' . PHP_EOL;
		        // Added check if the type is passed then return the xml feed data
                    if($type == 'xml'){
                        return $feed_content;
                    }
                    $fp = fopen($feed_path, "w");
                    fwrite($fp, $feed_content);
                    fclose($fp);
                    Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'kb_gs_profiles set feed_generated="' . pSQL($feed_path) . '" WHERE id_gs_profiles=' . (int) $key . ' AND id_shop=' . (int) $shop_id);
                }
            }
        }
    }

    /* Function to create feed schedule on Google
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */

    public static function gsCreateFeed($gsFeed)
    {
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $feed_fetch_url = self::getModuleDirUrl() . 'kbgoogleshopping/feeds/cron_feed_' . ($gsFeed['id_gs_profiles']) . '.xml';
        $feed_fetch_path = _PS_MODULE_DIR_ . 'kbgoogleshopping/feeds/cron_feed_' . ($gsFeed['id_gs_profiles']) . '.xml';
        /**
         * Calling a function to update or create schedule on Google
         * @date 06-04-2023
         * @commenter Tanisha Gupta
         */
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 23-09-2024
	 * To sync only product having feed listing type
	 */  
        if($gsFeed['listing_type'] !='api'){
        $response = self::createFeedSchedule($gsFeed, $feed_fetch_url);
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
        if (!isset($response['error'])) {
            if (isset($response['feed_id'])) {
                Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'kb_gs_feeds set feed_path="' . pSQL($feed_fetch_path) . '",feed_url="' . pSQL($feed_fetch_url) . '", gs_feed_id=' . (int) $response['feed_id'] . ',feed_error="" WHERE id_gs_feed=' . (int) $gsFeed['id_gs_feed'] . ' AND id_shop=' . (int) $shop_id);
            }
        } else {
            if (isset($response['message'])) {
                echo "Error in Feed: " . $gsFeed['feed_label'] . " " . $response['message'] . "<br/>";
                Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'kb_gs_feeds set feed_path="' . pSQL($feed_fetch_path) . '",feed_url="' . pSQL($feed_fetch_url) . '", feed_error="' . pSQL($response['message']) . '" WHERE id_gs_feed=' . (int) $gsFeed['id_gs_feed'] . ' AND id_shop=' . (int) $shop_id);
            }
        }
        return true;
    }

    /*
     * function to delete feed
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    public static function deleteGSFeed($gsFeedID)
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
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $gs_config = json_decode(Configuration::get('kbgs_connect_config',null, null, (int)$shop_id), true);
        $feed = new GSFeedSchedule($gs_config);
        try {
            return $response = $feed->deleteFeed($gsFeedID);
        } catch (Google_Service_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        }
    }

    /*
     * Function to send request to google to create schedule feed
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    public static function createFeedSchedule($gsFeed, $feed_fetch_url)
    {
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $module = Module::getInstanceByName('kbgoogleshopping');
        $auditLogEntryString = $module->l('Job execution started to create feed schedule on Google Shopping', 'KbGSModule');
        $auditMethodName = 'KbGSModule::createFeedSchedule()';
        self::auditLogEntry($auditLogEntryString, $auditMethodName);
        /*
        * Replaced Tools::jsonDecode by json_decode
        * Tools::jsonDecode has been removed in PrestaShop8
        * TGfeb2023 Used-json_decode
        * @date 25-02-2023
        * @author Tanisha Gupta
        */
        $gs_config = json_decode(Configuration::get('kbgs_connect_config',null, null, (int)$shop_id), true);
        $feed_schedule = new GSFeedSchedule($gs_config);
        $create_flag = 0;
        if ($gsFeed['gs_feed_id'] != 0) {
            /**
             * Fetching the details of the already created schedule feed from google merchant account
             * @date 06-04-2023
             * @commenter Tanisha Gupta
             */
            $feed_details = $feed_schedule->getFeedSchedule($gsFeed['gs_feed_id'], $gsFeed, $feed_fetch_url);
            /**
             * $feed_details replaced with $feed_details && $feed_details == 1 to include an additional condition.
             * if $feed_details contains JSON data, the condition "if ($feed_details)" was still executed because JSON data is considered truthy in PHP
             * TGjune2023 Feed-Issue
             * @date 10-06-2023
             * @author Tanisha Gupta
             */
            if ($feed_details && $feed_details == 1) {
                try {
                    /**
                     * Updating the feed schedule on the google 
                     * @date 06-04-2023
                     * @commenter Tanisha Gupta
                     */
                    $response = $feed_schedule->updateFeedSchedule($gsFeed['gs_feed_id'], $gsFeed, $feed_fetch_url);
                    if (is_object($response)) {
                        return array('feed_id' => $gsFeed['gs_feed_id']);
                    } elseif (!empty($response)) {
                        return array('error' => true, 'message' => $response);
                    }
                } catch (Google_Service_Exception $ex) {
                    return array('error' => true, 'message' => $ex->getMessage());
                }
            } else {
                $create_flag = 1;
            }
        } else {
            $create_flag = 1;
        }

        /* Create Feed on Google */
        if ($create_flag == 1) {
            try {
                /**
                 * Creating new feed on the google merchant account
                 * @date 06-04-2023
                 * @commenter Tanisha Gupta
                 */
                $response = $feed_schedule->insertDataFeed($gsFeed, $feed_fetch_url);
                if (is_object($response)) {
                    return array('feed_id' => $response->getId());
                } elseif (!empty($response)) {
                    return array('error' => true, 'message' => $response);
                } else {
                    return array('error' => true, 'message' => 'Feed cannot be scheduled');
                }
            } catch (Google_Service_Exception $ex) {
                return array('error' => true, 'message' => $ex->getMessage());
            }
        }
    }

    /*
     * function to get feed based on profile
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    public static function getGsFeed($id_gs_profiles)
    {    
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/   
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        return Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_feeds WHERE id_gs_profiles=' . (int) $id_gs_profiles . ' AND id_shop = '. (int)$shop_id);
    }

    /*
     * Function definition to send request on Google Shopping to Create Products Listings
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */

    public static function gsCreateListings($listingArray = array())
    {
        $listingsCreated = 0;
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        //Audit Log Entry
        $module = Module::getInstanceByName('kbgoogleshopping');
        $auditLogEntryString = $module->l('Job execution started to create listings on Google Shopping.', 'KbGSModule');
        $auditMethodName = 'KbGSModule::gsCreateListings()';
        self::auditLogEntry($auditLogEntryString, $auditMethodName);

        if (!empty($listingArray) && count($listingArray) > 0) {
            foreach ($listingArray as $listing) {
                if (isset($listing['id_product'])) {
			/**
			* To make the module multi-store compatible
			* @Author Ravi Kant Gupta
			* @date 11-11-2024
			* Updated query to fetch the data based on the current shop ID
			*/ 
                    if (Configuration::get('kbgs_connect_config',null, null, (int)$shop_id) && Configuration::get('kbgs_connect_config',null, null, (int)$shop_id) != '') {
                        /*
                        * Replaced Tools::jsonDecode by json_decode
                        * Tools::jsonDecode has been removed in PrestaShop8
                        * TGfeb2023 Used-json_decode
                        * @date 25-02-2023
                        * @author Tanisha Gupta
                        */
                        $gs_config = json_decode(Configuration::get('kbgs_connect_config',null, null, (int)$shop_id), true);
                        $client_id = trim($gs_config['client_id']);
                        $application_name = trim($gs_config['application_name']);
                        $client_secret = trim($gs_config['client_secret']);
                        $merchant_id = trim($gs_config['merchant_id']);
                        if (
                            empty($client_id) ||
                            empty($application_name) ||
                            empty($client_secret) ||
                            empty($merchant_id)
                        ) {
                            $msg = 'You cannot upload new products or update existing products on google shopping when Google Settings are empty. Please follow the user manual';
                            die;
                        }
                        $response = self::addProductToGoogle($listing['id_product'], $gs_config, $listing);
                        if (isset($response['google_offer_id'])) {
                            $listingsCreated++;
                            $updateSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET listing_id = '" . pSQL($response['google_offer_id']) . "', listing_status = 'Listed', date_listed = NOW(), listing_error = '' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0' AND id_shop = ". (int)$shop_id;
                            // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                            Db::getInstance()->execute($updateSQL);
                        } elseif (isset($response['message']) && !empty($response['message'])) {
                            $listingError = $response['message'];
                            $updateSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0' AND id_shop = ". (int)$shop_id;
                            // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                            Db::getInstance()->execute($updateSQL);
                        }
                    }
                }
            }
        }

        //Audit Log Entry
        $auditLogEntryString = $module->l('Job execution completed to create listings on Google Shopping. <br>Total Listings Created: ', 'KbGSModule') . $listingsCreated;
        self::auditLogEntry($auditLogEntryString, $auditMethodName);

        return true;
    }

    //Function definition to send request on Google Shopping to Update Products Listings
    public static function gsUpdateListings($listingArray = array(), $deleteListing = false, $renewListing = false)
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $listingsUpdated = 0;
        $listingsDeleted = 0;
        $listingsRenewed = 0;
        $module = Module::getInstanceByName('kbgoogleshopping');
        //Audit Log Entry
        if ($deleteListing) {
            $auditLogEntryString = $module->l('Job execution started to delete listings from Google Shopping.', 'KbGSModule');
        } else if ($renewListing) {
            $auditLogEntryString = $module->l('Job execution started to renew listings on Google Shopping.', 'KbGSModule');
        } else {
            $auditLogEntryString = $module->l('Job execution started to update listings on Google Shopping.', 'KbGSModule');
        }
        $auditMethodName = 'KbGSModule::gsUpdateListings()';
        self::auditLogEntry($auditLogEntryString, $auditMethodName);
        if (!empty($listingArray) && count($listingArray) > 0) {
            foreach ($listingArray as $listing) {
                if (isset($listing['id_product'])) {
                    if (Configuration::get('kbgs_connect_config',null, null, (int)$shop_id) && Configuration::get('kbgs_connect_config',null, null, (int)$shop_id) != '') {
                        /*
                    * Replaced Tools::jsonDecode by json_decode
                    * Tools::jsonDecode has been removed in PrestaShop8
                    * TGfeb2023 Used-json_decode
                    * @date 25-02-2023
                    * @author Tanisha Gupta
                    */
                        $gs_config = json_decode(Configuration::get('kbgs_connect_config',null, null, (int)$shop_id), true);
                        $client_id = trim($gs_config['client_id']);
                        $application_name = trim($gs_config['application_name']);
                        $client_secret = trim($gs_config['client_secret']);
                        $merchant_id = trim($gs_config['merchant_id']);
                        if (
                            empty($client_id) ||
                            empty($application_name) ||
                            empty($client_secret) ||
                            empty($merchant_id)
                        ) {
                            $msg = 'You cannot upload new products or update existing products on google shopping when Google Settings are empty. Please follow the user manual';
                            die;
                        }
			/**
			* To make the module multi-store compatible
			* @Author Ravi Kant Gupta
			* @date 12-11-2024
			* Updated query to fetch the data based on the current shop ID
			*/ 
                        $response = self::addProductToGoogle($listing['id_product'], $gs_config, $listing);
                        if (isset($response['google_offer_id'])) {
                            $listingID = $response['google_offer_id'];
                            if (!empty($listingID)) {
                                if ($deleteListing) {
                                    $listingsDeleted++;
                                    $updateSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET delete_flag = '2', listing_error = '' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0' AND id_shop = ". (int)$shop_id;
                                } else if ($renewListing) {
                                    $listingsRenewed++;
                                    $updateSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET listing_id = '" . pSQL($listingID) . "', listing_status = 'Listed', renew_flag = '0', date_last_renewed = NOW(), listing_error = '' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0' AND id_shop = ". (int)$shop_id;
                                } else {
                                    $listingsUpdated++;
                                    $updateSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET listing_status = 'Listed', listing_error = '' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0' AND id_shop = ". (int)$shop_id;
                                }
                                // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                                Db::getInstance()->execute($updateSQL);
                            }
                        } elseif (isset($response['message']) && !empty($response['message'])) {
                            $listingError = $response['message'];
                            $updateSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0' AND id_shop = ". (int)$shop_id;
                            // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                            Db::getInstance()->execute($updateSQL);
                        }
                    }
                }
            }
        }

        //Audit Log Entry
        if ($deleteListing) {
            $auditLogEntryString = $module->l('Job execution completed to delete listings from Google Shopping.', 'KbGSModule') . ' <br>' . $module->l('Total Listings Deleted: ', 'KbGSModule') . $listingsDeleted;
        } elseif ($renewListing) {
            $auditLogEntryString = $module->l('Job execution completed to renew listings on Google Shopping.', 'KbGSModule') . ' <br>' . $module->l('Total Listings Renewed: ', 'KbGSModule') . $listingsRenewed;
        } else {
            $auditLogEntryString = $module->l('Job execution completed to update listings on Google Shopping.', 'KbGSModule') . ' <br>' . $module->l('Total Listings Updated: ', 'KbGSModule') . $listingsUpdated;
        }
        self::auditLogEntry($auditLogEntryString, $auditMethodName);

        return true;
    }

    //function to send request to add single product on Google Shopping
    public static function addProductToGoogle($id_product, $gs_config, $listing)
    {
        $obj = new GSSingleAction($gs_config);

        $response = $obj->addProduct($id_product, $listing);
        return $response;
    }

    //Function definition to get products from Google Products Table to Update on Google Shopping
    public static function getProductsToUpdateOnGS($is_queue = false, $start = 0, $limit = null)
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $str = '';
        if ($is_queue) {
            $str = ' LIMIT ' . (int) $start . ', ' . (int) $limit;
        }
        $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE listing_id IS NOT NULL AND listing_id != '' AND listing_status = 'Pending' AND id_product_attribute = 0 AND renew_flag = '0' AND delete_flag = '0'AND id_shop = ". (int)$shop_id." " . $str;
        // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
        return Db::getInstance()->executeS($selectSQL, true, false);
    }

    //Function definition to get products from Google Products Table to upload image
    public static function getProductsToUploadImageOnGS($newListings = false)
    {  
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
    	 $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;

	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
        if ($newListings) {
            $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE listing_id IS NOT NULL AND listing_id != '' AND listing_status = 'Listed' AND delete_flag = '0' AND date_listed > DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND id_shop = " . (int) $shop_id;
        } else {
            $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE listing_id IS NOT NULL AND listing_id != '' AND listing_status = 'Listed' AND delete_flag = '0' AND date_last_renewed > DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND id_shop = " . (int) $shop_id; 
        }
        // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
        $getProductsToUploadImageOnGS = Db::getInstance()->executeS($selectSQL, true, false);

        return $getProductsToUploadImageOnGS;
    }

    public static function getAttributePossibleValues()
    {
        return array(
            'gender' => array('male', 'female', 'unisex'),
            'age_group' => array('newborn', 'infant', 'toddler', 'kids', 'adult'),
            'adult' => array('true', 'false'),
            'material' => array(),
            'pattern' => array(),
            'size_type' => array('regular', 'petite', 'plus', 'big and tall', 'maternity'),
            'size_system' => array('US', 'UK', 'EU', 'DE', 'FR', 'JP', 'CN', 'IT', 'BR', 'MEX', 'AU')
        );
    }

    //Function definition to get products from Google Products Table to Update on Etsy Marketplace
    public static function getProductsToRenewOnGS($is_queue = false, $start = 0, $limit = null)
    {     
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $str = '';
        if ($is_queue) {
            $str = ' LIMIT ' . (int) $start . ', ' . (int) $limit;
        }
        $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE listing_id IS NOT NULL AND listing_id != '' AND id_product_attribute = 0 AND renew_flag = '1' AND delete_flag = '0' AND id_shop =".(int)$shop_id." " . $str;
        // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
        return Db::getInstance()->executeS($selectSQL, true, false);
    }

    //Function definition to get products from Google Products Table to Delete on Etsy Marketplace
    public static function getProductsToDeleteOnGS($is_queue = false, $start = 0, $limit = null)
    {

        $str = '';
        if ($is_queue) {
            $str = ' LIMIT ' . (int) $start . ', ' . (int) $limit;
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE listing_id IS NOT NULL AND listing_id != '' AND id_product_attribute = 0 AND renew_flag = '0' AND delete_flag = '1' AND id_shop = ".(int)$shop_id." " . $str ;
        // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
        return Db::getInstance()->executeS($selectSQL, true, false);
    }

    //Function defination to remove product from Google
    public static function removeProductFromGoogle($listingArray, $gs_config)
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;

        $module = Module::getInstanceByName('kbgoogleshopping');
        $listingsDeleted = 0;
        $del_obj = new GSSingleAction($gs_config);
        // Initialize response variable to prevent undefined variable error for PS validator compatibility. 08-06-2024
        $response = array('error' => '', 'message' => '');
        foreach ($listingArray as $row) {
            $response = $del_obj->removeProduct($row);
		 /**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 11-11-2024
		* Updated query to fetch the data based on the current shop ID
		*/ 
            if (empty($response['error'])) {
                $listingsDeleted++;
                $updateSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET delete_flag = '2', listing_error = '' WHERE id_product = '" . (int) $response['id_product'] . "' AND id_product_attribute = '0' AND id_shop = " . $shop_id;
                // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                Db::getInstance()->execute($updateSQL);
            } else {
                $listingError = $response['message'];
                $updateSQL = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $response['id_product'] . "' AND id_product_attribute = '0' AND id_shop = " . $shop_id;
                // Fix incorrect case for Db class reference for PS validator compatibility. 08-06-2024
                Db::getInstance()->execute($updateSQL);
            }
        }
        $auditMethodName = 'KbGSModule::removeProductFromGoogle()';
        $auditLogEntryString = $module->l('Job execution completed to delete listings from Google Shopping. <br> Total Listings Deleted: ', 'KbGSModule') . $listingsDeleted;
        self::auditLogEntry($auditLogEntryString, $auditMethodName);

        return array('error' => $response['error'], 'message' => $response['message']);
    }

    //Function definition to get products from Google Products Table to get their current status from Google Shopping
    public static function getProductsListedOnGS($is_queue = false, $start = 0, $limit = null)
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;

        $str = '';
        if ($is_queue) {
            $str = ' LIMIT ' . (int) $start . ', ' . (int) $limit;
        }
        $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE listing_id IS NOT NULL AND listing_id != '' AND id_product_attribute = 0 AND renew_flag = '0' AND delete_flag = '0'" . $str . " AND id_shop = " . $shop_id;
        return Db::getInstance()->executeS($selectSQL, true, false);
    }

    //Function definition to send request on Google to Get Products Listings by listing_id
    public static function gsGetListings($listingArray = array())
    {
        return true;
    }
    /**
     * This function is responsible to return the module directory path
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     * @return string
     */
    public static function getModuleDirUrl()
    {
        $module_dir = '';
        if (self::checkSecureUrl()) {
            $module_dir = Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        } else {
            $module_dir = Tools::getShopDomain(true) . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        }
        return $module_dir;
    }
    /**
     * This function is responsible to check whether site is secure or not
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     * @return boolean
     */
    public static function checkSecureUrl()
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $custom_ssl_var = 0;

        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] == 'on') {
                $custom_ssl_var = 1;
            }
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $custom_ssl_var = 1;
        }



        if ((bool) Configuration::get('PS_SSL_ENABLED',null, null, (int)$shop_id) && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * function defination to fetch product from Google Shopping
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    public static function fetchGSProduct()
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
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $gs_config = json_decode(Configuration::get('kbgs_connect_config',null, null, (int)$shop_id), true);
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 27-09-2024
	 * To show error message if the automatic sync is disabled
	 */  
    $automatic_upload = isset($gs_config['automatic_upload']) ? $gs_config['automatic_upload'] : '0';

        if($automatic_upload==0){
            $module = Module::getInstanceByName('kbgoogleshopping');
            echo $module->l('Please enable Automatic product sync to google setting.', 'cron');
            die;
        }
        $obj = new GSSingleAction($gs_config);
        $response = $obj->getProductList();
        return $response;
    }
    /**
     * This function is responsible to check whether module is installed/enabled or not
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     * @return boolean
     */
    public static function checkInstallation()
    {	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $token_info_json = Configuration::get('Kbgs_token_info',null, null, (int)$shop_id);
        $gs_config = json_decode(Configuration::get('kbgs_connect_config',null, null, (int)$shop_id), true);

        /*
        * Replaced Tools::jsonDecode by json_decode
        * Tools::jsonDecode has been removed in PrestaShop8
        * TGfeb2023 Used-json_decode
        * @date 25-02-2023
        * @author Tanisha Gupta
        */
        $gs_general_setting = json_decode(Configuration::get('kbgs_general_setting',null, null, (int)$shop_id), true);
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 27-09-2024
	 * Added condition so that the error message related to module connection coming on the profile and product message do not appear
	 */  
    $automatic_upload = isset($gs_config['automatic_upload']) ? $gs_config['automatic_upload'] : '0';

        if ($automatic_upload == 0 && $gs_general_setting['enable'] == 1 ) {
            return true;
        }

        if ($gs_general_setting['enable'] == 1 && $token_info_json != "") {
            return true;
        } else {
            return false;
        }
    }
}
