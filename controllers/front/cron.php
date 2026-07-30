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


require_once(_PS_ROOT_DIR_ . '/init.php');
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSModule.php');


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

class KbGoogleShoppingCronModuleFrontController extends ModuleFrontController
{

    public $php_self = 'cron';
    public $records_to_sync = 50000;

    //function defination to execute first
    public function init()
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

        parent::init();

        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', -1);
        @set_time_limit(0);

        if (!Tools::isEmpty(Tools::getValue('debug')) && Tools::getValue('debug') == 1) {
            @ini_set('display_errors', 1);
        }

        if (!empty(Tools::getValue('records'))) {
            $this->records_to_sync = Tools::getValue('records');
        }

        if (Tools::getValue('secure_key') != Configuration::get('KB_GS_SECURE_KEY1', null, null, (int)$shop_id)) {
            echo $this->module->l('Sorry!!! Secure key not matched.', 'cron');
        } else {
            /*
            * Replaced Tools::jsonDecode by json_decode
            * Tools::jsonDecode has been removed in PrestaShop8
            * TGfeb2023 Used-json_decode
            * @date 25-02-2023
            * @author Tanisha Gupta
            */
            $kb_general = json_decode(Configuration::get('kbgs_general_setting', null, null, (int)$shop_id), true);
            if ($kb_general['enable'] == 0) {
                echo $this->module->l('Sorry!!! The module is not enabled.', 'cron');
            } else if (!Tools::isEmpty(trim(Tools::getValue('action')))) {
                $action = Tools::getValue('action');
                switch ($action) {
                    case 'syncProductsListing':
                        $this->syncProductsListing();
                        break;
                    case 'syncFeedsListing':
                        $this->syncFeedsListing();
                        break;
                    case 'syncProductStatus':
                        $this->syncProductStatus();
                        break;
                    case 'syncGsQueue':
                        $this->syncGsQueue();
                        break;
                    case 'syncLocal':
                        $this->syncLocal();
                        break;
                    case 'syncCategories':
                        $this->syncCategories();
                        break;
			/**
			* @Author Ravi Kant Gupta
			* @date 26-09-2024
			* To handle the feed download
			*/ 
                    case 'downloadFeed':
                        $this->downloadFeed();
                        break;
                }
                echo $this->module->l('Cron executed successfully.', 'cron');
            }
        }
        die();
    }

    /**
     * This function is used to add Google Categories in the table
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    private function syncCategories()
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
        if (Db::getInstance()->execute("TRUNCATE TABLE " . _DB_PREFIX_ . "kb_gs_categories")) {
            /*
            * Replaced Tools::jsonDecode by json_decode
            * Tools::jsonDecode has been removed in PrestaShop8
            * TGfeb2023 Used-json_decode
            * @date 25-02-2023
            * @author Tanisha Gupta
            */
            $kb_general = json_decode(Configuration::get('kbgs_general_setting', null, null, (int)$shop_id), true);
            $lang_data = new Language($kb_general['gs_default_lang']);
            $iso_code = Tools::strtolower($lang_data->iso_code);
            if (!in_array($iso_code, array("en", "fr", "it", "es"))) {
                $iso_code = "en";
            }
            $library_path = _PS_MODULE_DIR_ . $this->module->name . '/vendor/';
            require_once $library_path . 'excel_reader.php';
            require_once $library_path . 'oleread.inc';

            $excel = new PhpExcelReader;
            if (file_exists($library_path . '/taxonomy.' . $iso_code . '.xls')) {
                $excel->read($library_path . '/taxonomy.' . $iso_code . '.xls');
            } else {
                $excel->read($library_path . '/taxonomy.en.xls');
            }
            $totalExcelData = $excel->sheets[0];
            $current_timestamp = date('Y-m-d H:i:s', time());
            $shops = Shop::getShops();
            foreach ($totalExcelData['cells'] as $row) {
                $id_parent = 0;
                $category_code = 0;
                $parent_name = '';
                if (count($row) > 1) {
                    $category_code = $row[1];
                    $parent_name = $row[count($row) - 1];
                }
		/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 12-11-2024
		* To insert the data for all the available stores
		*/
                foreach ($shops as $shop) {
                    $shop_id = $shop['id_shop'];  
                if (!empty($parent_name)) {
                    $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE name = "' . pSQL($parent_name) . '" AND id_shop=' . (int)$shop_id;
                    $parent_row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
                    if ($parent_row) {
                        $id_parent = $parent_row['category_code'];
                    }
                }
                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'kb_gs_categories (category_code,name,id_parent, date_add, date_upd, id_shop) 
                    values(' . (int)$category_code . ', "' . pSQL($row[count($row)]) . '",' . (int)$id_parent . ', "'
                    . pSQL($current_timestamp) . '", "' . pSQL($current_timestamp) . '","' . (int)$shop_id . '")';

                @Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
            }
         }
        }
    }

    /**
     * This function is add product to the module
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    private function syncLocal()
    {
        $this->listFeedProducts();
    }

    /**
     * This function is used to create feeds or  send on merchant account
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    private function syncFeedsListing($queue = false, $queuetype = null)
    {
        if ($this->listFeedProducts()) {
            $this->createFeedListings($queue, $queuetype);
        }
    }

    /**
     * This function is used to fetch the listed products
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    private function listFeedProducts()
    {
        if (KbGSModule::getAllProfileProducts()) {
            return true;
        }
        return false;
    }

    /**
     * This function defination to create/update feed on merchant account listing and create or update xml for created profiles
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
	/**
	* @Author Ravi Kant Gupta
	* @date 27-09-2024
	* Passed addtional parameter so that it can handle the download feed functionality
	*/ 
    public function createFeedListings($queue = false, $queuetype = null, $type = null, $profile_id = null)
    {


        $start = 0;
        $limit = $this->records_to_sync;
        /**
         * Fetching the product list which needs to be send to google 
         * @date 06-04-2023
         * @commenter Tanisha Gupta
         */
        $productList = KbGSModule::getFeedProductsToListOnGS(true, $start, $limit, $profile_id);
	/**
	* @Author Ravi Kant Gupta
	* @date 27-09-2024
	* Added check if the profile's product are not available then show the error message
	*/ 
        if($profile_id){
            if(count($productList) == 0){
                echo $this->module->l('No products found for this profile', 'cron');
                die;
            }
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
        $listingArray = array();
        if (!empty($productList)) {
            $new_listing_arr = '';
            foreach ($productList as $productsList) {
                // Changes add by Kanishka Kannoujia on 21-04-2022, when product is disable from product listing then that product no more will add to the feed for listing.
                if ($productsList['listing_status'] == 'Inactive') {
                    continue;
                }
                // Changes end here
                /*
                * Replaced Tools::jsonDecode by json_decode
                * Tools::jsonDecode has been removed in PrestaShop8
                * TGfeb2023 Used-json_decode
                * @date 25-02-2023
                * @author Tanisha Gupta
                */
                $kb_general = json_decode(Configuration::get('kbgs_general_setting', null, null, (int)$shop_id), true);
                // Cast to int for Product constructor compatibility with PS validator. Fixes int|null type expectation. 08-06-2024
                $product = new Product((int)$productsList['id_product'], false, Context::getContext()->language->id);
                $productInventory = KbGSIntegration::getProductInventory($productsList['id_product']);
                /**
                 * If product is out of stock and exclude out of stock setting is enabled, then don't include the product and continue the loop
                 * @date 06-04-2023
                 * @commenter Tanisha Gupta
                 */
                if ($productInventory['success'] <= 0) {
                    if ($kb_general['exclude_out_of_stock']) {
                        continue;
                    }
                }
                /**
                 * If product id is empty, then don't include the product and continue the loop
                 * @date 06-04-2023
                 * @commenter Tanisha Gupta
                 */
                if (empty($product->id)) {
                    continue;
                }
                /**
                 * If product price is less than the exclude_product_less, then don't include the product and continue the loop
                 * @date 06-04-2023
                 * @commenter Tanisha Gupta
                 */
                $price = Product::getPriceStatic($productsList['id_product'], true, null, 2, null, false, true);
                if ($price <= $kb_general['exclude_product_less']) {
                    continue;
                }
		/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 11-11-2024
		* Updated query to fetch the data based on the current shop ID
		*/ 
                $gtin = Db::getInstance()->getValue('SELECT gtin FROM ' . _DB_PREFIX_ . 'kb_gs_profiles WHERE id_gs_profiles=' . (int)$productsList['id_gs_profiles'] . ' AND id_shop=' . (int)$shop_id);
                /**
                 * If product gtin is empty and exclude gtin(if empty) setting is enabled, then don't include the product and continue the loop
                 * @date 06-04-2023
                 * @commenter Tanisha Gupta
                 */
                if ($kb_general['exclude_product_gtin']) {
                    if ($product->{$gtin} == '') {
                        continue;
                    }
                }
                $new_listing_arr = KbGSModule::prepareArrayToCreateListingOnGS($productsList, Context::getContext()->language->id);
                if (!empty($new_listing_arr)) {
                    $listingArray[] = $new_listing_arr;
                }
            }
        }

        /**
         * Fix for the update of the feed in the case of 0 products.
         * GS006-09032024 gs-zero-product-update-feed
         * @date 09-03-2024
         * @author Ashish
         */
        //if (isset($listingArray) && count($listingArray) > 0) {
	/**
	* @Author Ravi Kant Gupta
	* @date 27-09-2024
	* To handle the feed download in the differnt format
	*/ 
        if ($type != null) {
            $response =   KbGSModule::getCreateFeedListings($listingArray, $type);
            return $response;
        } else {
            if (KbGSModule::getCreateFeedListings($listingArray)) {
                return true;
            }
        }
        //}
        return true;
    }

    /**
     * This function defination to sync product status
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     */
    private function syncProductStatus()
    {
        KbGSModule::fetchGSProduct();
    }
	/**
	* @Author Ravi Kant Gupta
	* @date 27-09-2024
	* To handle the feed download in different format
	*/ 
    private function downloadFeed()
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
        $type = Tools::getValue('type');
        $profile_id = Tools::getValue('id_gs_profiles');
	// Checking for the profile id
        if(empty($profile_id)){

            echo $this->module->l('Profile ID not found', 'cron');
            die;
        }
        if(!in_array($type, array('json', 'csv', 'xml'))){
            echo $this->module->l('Not a valid file type', 'cron');
            die;
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
        $result = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_profiles WHERE id_gs_profiles=' . (int) $profile_id . ' AND id_shop=' . (int) $shop_id);
       
        if(!empty($result) && $result[0]['feed_generated'] == '') {
            echo $this->module->l('Feed not generated', 'cron');
            die;
        }
        $feed_data =  $this->createFeedListings(false, null, $type, $profile_id);
        if(!empty($feed_data)){
        if ($type == 'json') {
            $this->jsonFeed($feed_data);
        }
        if ($type == 'csv') {
            $this->csvFeed($feed_data);
        }
        if ($type == 'xml') {
            $this->xmlFeed($feed_data, $profile_id . '_feed.xml');
        }
    }
    }
	/**
	* @Author Ravi Kant Gupta
	* @date 26-09-2024
	* To handle the feed download in different json format
	*/ 
    private function jsonFeed($products)
    {
        $jsonFeed = [];

        // Iterate over each product in the array
        foreach ($products as $product) {
            $productJson = [];

            if (isset($product["google_offer_id"])) {
                $productJson["offer_id"] = $product["google_offer_id"];
            }

            if (isset($product["product_title"])) {
                $productJson["title"] = $product["product_title"];
            }

            if (isset($product["product_description"])) {
                $productJson["description"] = strip_tags($product["product_description"]); // Remove HTML tags
            }

            if (isset($product["product_link"])) {
                $productJson["link"] = $product["product_link"];
            }

            if (isset($product["availability"])) {
                $productJson["availability"] = $product["availability"];
            }

            if (isset($product["product_condition"])) {
                $productJson["condition"] = $product["product_condition"];
            }

            if (isset($product["price"]["value"]) && isset($product["price"]["currency"])) {
                $productJson["price"] = [
                    "value" => $product["price"]["value"],
                    "currency" => $product["price"]["currency"]
                ];
            }

            if (isset($product["image_link"])) {
                $productJson["image_link"] = $product["image_link"];
            }

            if (isset($product["google_product_category"])) {
                $productJson["google_product_category"] = $product["google_product_category"];
            }

            if (isset($product["product_type"])) {
                $productJson["product_type"] = $product["product_type"];
            }

            $productJson["adult"] = isset($product["adult"]) ? ($product["adult"] ? "yes" : "no") : null;

            if (isset($product["brand"])) {
                $productJson["brand"] = $product["brand"];
            }

            if (isset($product["item_group_id"])) {
                $productJson["item_group_id"] = $product["item_group_id"];
            }

            if (isset($product["size_system"])) {
                $productJson["size_system"] = $product["size_system"];
            }

            if (isset($product["size_type"])) {
                $productJson["size_type"] = $product["size_type"];
            }

            if (isset($product["gender"])) {
                $productJson["gender"] = $product["gender"];
            }

            if (isset($product["promotion_id"])) {
                $productJson["promotion_id"] = $product["promotion_id"];
            }

            if (isset($product["shipping_weight"]["value"]) && isset($product["shipping_weight"]["unit"])) {
                $productJson["shipping_weight"] = [
                    "value" => $product["shipping_weight"]["value"],
                    "unit" => $product["shipping_weight"]["unit"]
                ];
            }

            if (isset($product["tax"]["rate"]) && isset($product["tax"]["country"])) {
                $productJson["tax"] = [
                    "rate" => $product["tax"]["rate"],
                    "country" => $product["tax"]["country"]
                ];
            }

            // Add additional image links if they exist and are not empty
            if (isset($product["additional_images"]) && !empty($product["additional_images"])) {
                $productJson["additional_image_links"] = $product["additional_images"];
            }

            $jsonFeed[] = $productJson;
        }

        // Encode the array to JSON format
        $jsonOutput = json_encode($jsonFeed, JSON_PRETTY_PRINT);

        // Set the appropriate headers for file download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="product_feed.json"');
        header('Content-Length: ' . strlen($jsonOutput));

        // Output the JSON for download
        echo $jsonOutput;
        exit;
    }
	/**
	* @Author Ravi Kant Gupta
	* @date 26-09-2024
	* To handle the feed download in different csv format
	*/ 
    private function csvFeed($products){
            // Define the headers for the CSV file
            $csvHeaders = [
                "offer_id", "title", "description", "link", "availability", "condition", "price_value", 
                "price_currency", "image_link", "google_product_category", "product_type", "adult", "brand", 
                "item_group_id", "size_system", "size_type", "gender", "promotion_id", "shipping_weight_value", 
                "shipping_weight_unit", "tax_rate", "tax_country", "additional_image_links"
            ];
        
            // Open a file pointer connected to the output stream
            $file = fopen('php://output', 'w');
        
            // Add the headers to the CSV
            fputcsv($file, $csvHeaders);
            
            // Iterate over each product in the array
            foreach ($products as $product) {
                $csvRow = [];
        
                // Check each field and set a default empty value if not set
                $csvRow[] = isset($product["google_offer_id"]) ? $product["google_offer_id"] : '';
                $csvRow[] = isset($product["product_title"]) ? $product["product_title"] : '';
                $csvRow[] = isset($product["product_description"]) ? strip_tags($product["product_description"]) : ''; // Strip HTML tags
                $csvRow[] = isset($product["product_link"]) ? $product["product_link"] : '';
                $csvRow[] = isset($product["availability"]) ? $product["availability"] : '';
                $csvRow[] = isset($product["product_condition"]) ? $product["product_condition"] : '';
        
                // Handle price
                $csvRow[] = isset($product["price"]["value"]) ? $product["price"]["value"] : '';
                $csvRow[] = isset($product["price"]["currency"]) ? $product["price"]["currency"] : '';
        
                // Add remaining fields
                $csvRow[] = isset($product["image_link"]) ? $product["image_link"] : '';
                $csvRow[] = isset($product["google_product_category"]) ? $product["google_product_category"] : '';
                $csvRow[] = isset($product["product_type"]) ? $product["product_type"] : '';
        
                // Adult field as yes/no
                $csvRow[] = isset($product["adult"]) ? ($product["adult"] ? 'yes' : 'no') : '';
        
                // Additional fields
                $csvRow[] = isset($product["brand"]) ? $product["brand"] : '';
                $csvRow[] = isset($product["item_group_id"]) ? $product["item_group_id"] : '';
                $csvRow[] = isset($product["size_system"]) ? $product["size_system"] : '';
                $csvRow[] = isset($product["size_type"]) ? $product["size_type"] : '';
                $csvRow[] = isset($product["gender"]) ? $product["gender"] : '';
                $csvRow[] = isset($product["promotion_id"]) ? $product["promotion_id"] : '';
        
                // Shipping weight
                $csvRow[] = isset($product["shipping_weight"]["value"]) ? $product["shipping_weight"]["value"] : '';
                $csvRow[] = isset($product["shipping_weight"]["unit"]) ? $product["shipping_weight"]["unit"] : '';
        
                // Tax details
                $csvRow[] = isset($product["tax"]["rate"]) ? $product["tax"]["rate"] : '';
                $csvRow[] = isset($product["tax"]["country"]) ? $product["tax"]["country"] : '';
        
                // Handle additional image links as a comma-separated string
                if (isset($product["additional_images"]) && !empty($product["additional_images"])) {
                    $csvRow[] = implode(",", $product["additional_images"]);
                } else {
                    $csvRow[] = ''; // If no additional images, leave the field empty
                }
        
                // Write the row to the CSV file
                fputcsv($file, $csvRow);
            }
        
            // Close the output stream
            fclose($file);
        
            // Set the appropriate headers for file download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="product_feed.csv"');
            exit;
        }
	/**
	* @Author Ravi Kant Gupta
	* @date 26-09-2024
	* To handle the feed download in different xml format
	*/ 
       private function xmlFeed($xmlFeed, $filename = 'feed.xml') {
            // Set the appropriate headers for XML download
            header('Content-Type: application/xml');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($xmlFeed));
            
            // Output the XML feed
            echo $xmlFeed;
            exit;
        }

    }

