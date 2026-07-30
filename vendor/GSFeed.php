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

require_once 'GSBase.php';

class GSFeed extends GSBase
{

    protected $item_group_id = '';
    protected $feed_content = '';
    public function __construct($gs_config, $only_download = true)
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Added check for All store and default group selection
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
        
        if (!$only_download) {
            parent::__construct($gs_config);
        } else {
            $this->gs_configs = $gs_config;
        }
    }
    /*
     * Function defination to generate xml feed
     * @date 06-04-2023
     * @author
     * @commenter Tanisha Gupta
     * @param $listing, array of products
     * @param $start
     * @param $processes
     * @return string, feed xml
     */
    public function generateXMLFeed($listing, $start = 0, $processed = 50, $type = null)
    {

        $this->context = Context::getContext();
        if (empty($listing)) {
            return;
        }
        $has_product = false;
        foreach ($listing as $product) {
        /**
        * @Author Ravi Kant Gupta
        * @date 26-09-2024
        * To skip the creation of the feed of the products that have listing method as API
        */ 
        if($type != 'xml'){
            if($product['product_listing_method'] == 'api') {
                continue;
            }
        }

            $this->product = new Product($product['id_product'], false, $product['id_lang']);
            if ($this->product->active) {
                $has_product = true;
                /**
                 * Check if product has attribute, then send product attributes. Otherwise send single product in a feed
                 * @date 06-04-2023
                 * @modifier Tanisha Gupta
                 */
                if ($this->product->hasAttributes()) {
                    $attributes_groups = $this->product->getAttributesGroups($product['id_lang']);
                    $combination_images = $this->product->getCombinationImages($product['id_lang']);
                    $combinations = $this->product->getAttributeCombinations($product['id_lang']);
                    
                    $attribute_processed = array();
                    foreach ($combinations as $attr) {
                        if (in_array($attr['id_product_attribute'], $attribute_processed)) {
                            continue;
                        }
                        /**
                         * Create xml for the product attribute
                         * @date 06-04-2023
                         * @modifier Tanisha Gupta
                         */
                        $this->item_group_id = $this->createSingleProductXML($this->product, $product, $attr['id_product_attribute'], $attributes_groups, $combination_images);
                        $attribute_processed[] = $attr['id_product_attribute'];
                    }
                }else{
                     /**
                    * Create xml for the product without combination
                    * @date 06-04-2023
                    * @modifier Tanisha Gupta
                    */
                    $this->item_group_id = $this->createSingleProductXML($this->product, $product);
                }
            }
        }

        return $this->feed_content;
    }

    //function defination to single product xml
    public function createSingleProductXML($product_obj, $listing, $id_product_attribute = 0, $attributes_groups = array(), $combination_images = array())
    {
        $xml = '';
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
        $kb_general = json_decode(Configuration::get('kbgs_general_setting', null, null, (int)$this->shop_id), true);
        $country_iso_code = Country::getIsoById($listing['id_country']);

        /**
        * Fix for the currency conversion issue
        * GS002-09032024 gs-profile-create-notice
        * @date 09-03-2024
        * @author Ashish
        */
        $currency = new Currency($listing['id_currency']);
        $currency_iso_code = $currency->iso_code;

        $google_offer_id = $listing['generated_listing_id'];
        $xml .= '<item>' . PHP_EOL;
        /**
         * IF product attribute exists, then google id is storename_productid_attributeid
         * else google id is storename_productid
         * @date 09-02-2023
         * @author Tanisha Gupta
         */
        if ($id_product_attribute > 0) {
            $xml .= '<g:id>' . $google_offer_id . '_' . $id_product_attribute . '</g:id>' . PHP_EOL;
        } else {
            $xml .= '<g:id>' . $google_offer_id . '</g:id>' . PHP_EOL;
        }

        /**
        * Customized Title Issue Fix
        * GS008-12032024 gs-customized-title-fix
        * @date 12-03-2024
        * @author Ashish
        */
        if(!empty($listing['product_title'])) {
            $xml .= '<g:title><![CDATA[' . substr($listing['product_title'], 0, 150) . ']]></g:title>' . PHP_EOL;
        } else {
            $xml .= '<g:title><![CDATA[' . substr($product_obj->name, 0, 150) . ']]></g:title>' . PHP_EOL;
        }
        
        /**
         * If description is empty then send the short description(summary) to the google shopping
         * Convert into the encoding. To avoid error if description string has special characters, then google merchant gives the error. 
         * @date 09-02-2023
         * @author Tanisha Gupta
         */
        /**
         * Changed utf8_encode(substr(html_entity_decode to mb_substr(html_entity_decode
         * GS007-10032024 gs-invalid-encoding
         * @date 10-03-2024
         * @author Ashish
         */
         
        if (!empty($product_obj->description)) {
            $xml .= '<g:description><![CDATA[' . mb_substr(html_entity_decode($product_obj->description), 0, 5000, 'UTF-8') . ']]></g:description>' . PHP_EOL;
        } else {
            $xml .= '<g:description><![CDATA[' . mb_substr(html_entity_decode($product_obj->description_short), 0, 5000, 'UTF-8')  . ']]></g:description>' . PHP_EOL;
        }
        //End here
        $cover = Product::getCover($product_obj->id, $this->context);
        $image_link = '';
        $additional_images = [];
        
        if ($cover) {
            $image_link = $this->context->link->getImageLink($product_obj->link_rewrite, $cover['id_image'], $kb_general['image_size']);
        }

        if ($id_product_attribute == 0) {
            $images = $this->product->getImages((int) $listing['id_lang']);
            if ($images) {
                foreach ($images as $img) {
                    // Check if the image is not the cover image
                    if (!$cover || $cover['id_image'] != $img['id_image']) {
                        // Store the additional image links in an array
                        $additional_images[] = $this->context->link->getImageLink($product_obj->link_rewrite, $img['id_image'], $kb_general['image_size']);
                    }
                }
            }
        }
        $utm_campaign = '';
        if (!empty($kb_general['utm_campaign'])) {
            $utm_campaign = $kb_general['utm_campaign'];
        }
        $utm_source = '';
        if (!empty($kb_general['utm_source'])) {
            $utm_source = $kb_general['utm_source'];
        }
        $utm_medium = '';
        if (!empty($kb_general['utm_medium'])) {
            $utm_medium = $kb_general['utm_medium'];
        }
        $str = '';
        $str .= ($utm_campaign != '') ? 'utm_campaign=' . $utm_campaign : '';
        $str .= ($utm_source != '') ? '&utm_source=' . $utm_source : '';
        $str .= ($utm_medium != '') ? '&utm_medium=' . $utm_medium : '';
        $str = urlencode($str);
        //changes by tarun to resolve product link error
        if (isset($str) && $str != '') {
            $key = '?' . $str;
        } else {
            $key = '';
        }
        /**
         * Changes if product attribute id exists create link for product attribute otherwise, for simple product link
         * @date 23-03-2023
         * @author Tanisha Gupta
         */
        /**
         * Added the fixes for the issue of the product link being send in default langugae. 
         * We are now sending the product link in the selected language.
         * ASOct2024 GSlanguage
         * @date 18-10-2024
         * @author Amit SIngh
         */
        if ($id_product_attribute > 0) {
            $xml .= '<g:link><![CDATA[' . $this->context->link->getProductLink($product_obj, null, null, null, $listing['id_lang'], null, $id_product_attribute) . $key . ']]></g:link>' . PHP_EOL;
        } else {
            $xml .= '<g:link><![CDATA[' . $this->context->link->getProductLink($product_obj, null, null, null, $listing['id_lang']) . $key . ']]></g:link>' . PHP_EOL;
        }
        //$xml .= '<g:link><![CDATA[' . $this->context->link->getProductLink($product_obj) . $key . ']]></g:link>' . PHP_EOL;
        //changes over
	/**
	* @Author Ravi Kant Gupta
	* @date 20-09-2024
	* Added the product condition field in the feed
	*/ 
        if(!empty($listing['product_condition'])) {
            $xml .= '<g:condition>'.$listing['product_condition'].'</g:condition>' . PHP_EOL;
        } else {
            $xml .= '<g:condition>' . $product_obj->condition . '</g:condition>' . PHP_EOL;
        }
        $has_quantity = true;
        $allow_oosp = $product_obj->isAvailableWhenOutOfStock(StockAvailable::outOfStock($product_obj->id));
        if ($id_product_attribute > 0) {
            if (!is_array($attributes_groups) || !$attributes_groups) {
                $has_quantity = false;
            } else {
                $combination = new Combination($id_product_attribute);
                foreach ($attributes_groups as $k => $row) {
                    if ($row['id_product_attribute'] == $id_product_attribute) { 
                        if ($listing['size'] == $row['id_attribute_group']) {
                            $xml .= '<g:size>' . $row['attribute_name'] . '</g:size>' . PHP_EOL;
                        }
                        if ($listing['color'] == $row['id_attribute_group']) {
                            $xml .= '<g:color>' . $row['attribute_name'] . '</g:color>' . PHP_EOL;
                        }
                    }
                }
                $gtin_key = $listing['gtin'];

                /**
                * If Sync GTIN enabled then only sync the GTIN Field
                * GS003-09032024 gs-sync-gtin
                * @date 09-03-2024
                * @author Ashish
                */
                if(!empty($combination->{$gtin_key})) {
                    if(!empty($kb_general['sync_gtin'])) {
                        $xml .= '<g:gtin>' . $combination->{$gtin_key} . '</g:gtin>' . PHP_EOL;
                    }
                }
		/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 11-11-2024
		* Updated code to fetch the data based on the current shop ID
		*/ 
                $attribute_qty = StockAvailable::getQuantityAvailableByProduct(null, (int) $id_product_attribute, $this->shop_id);
                if ((int) $attribute_qty <= 0) {
                    $has_quantity = false;
                }
                $availibilty = 'out of stock';
                
                /*
                * Replaced Tools::jsonDecode by json_decode
                * Tools::jsonDecode has been removed in PrestaShop8
                * TGfeb2023 Used-json_decode
                * @date 25-02-2023
                * @author Tanisha Gupta
                */ 
                $kb_general = json_decode(Configuration::get('kbgs_general_setting',null, null, (int)$this->shop_id), true);
                
                $availibilty = 'out of stock';

                //If Item is available_for_order then only check other conditions otherwise Out of stock.
                //If catalog mode then set the products as Out of Stock.
                //If Item is having quantity then set as In stock
                if($product_obj->available_for_order) {
                    if(Configuration::get('PS_CATALOG_MODE', null, null, (int)$this->shop_id)) {
                        $availibilty = 'out of stock';
                    } else if($has_quantity) {
                        $availibilty = 'in stock';
                    } else if($allow_oosp == 1) {
                        $availibilty = 'in stock';
                    }
                }

                if ($availibilty == 'out of stock' && $kb_general['exclude_out_of_stock']) {
                    return false;
                }
                $xml .= '<g:availability>' . $availibilty . '</g:availability>' . PHP_EOL;
            }
            $price = $product_obj->getPrice(true, $id_product_attribute);
        } else {
            // Changes done by kanishka kannoujia on 21-04-2022 the feed issue fix
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the data based on the current shop ID
	*/ 
            $qty = StockAvailable::getQuantityAvailableByProduct($product_obj->id, 0,$this->shop_id);
            if ($qty <= 0) {
                $has_quantity = false;
            }
            $price = $product_obj->getPrice();
            $gtin_key = $listing['gtin'];
            
            /**
            * If Sync GTIN enabled then only sync the GTIN Field
            * GS003-09032024 gs-sync-gtin
            * @date 09-03-2024
            * @author Ashish
            */
            if(!empty($product_obj->{$gtin_key})) {
                if(!empty($kb_general['sync_gtin'])) {
                    $xml .= '<g:gtin>' . $product_obj->{$gtin_key} . '</g:gtin>' . PHP_EOL;
                }
            }

            /*
            * Replaced Tools::jsonDecode by json_decode
            * Tools::jsonDecode has been removed in PrestaShop8
            * TGfeb2023 Used-json_decode
            * @date 25-02-2023
            * @author Tanisha Gupta
            */ 
            $kb_general = json_decode(Configuration::get('kbgs_general_setting',null, null, (int)$this->shop_id), true);
            
            /**
            * Removed $allow_oosp condition if $allow_oosp is false, then product which has quantity is showing as out of stock
            * beacuse availibilty was not updated for this product
            * @date 09-02-2023
            * @author Tanisha Gupta
            */

            $availibilty = 'out of stock';

            //If Item is available_for_order then only check other conditions otherwise Out of stock.
            //If catalog mode then set the products as Out of Stock.
            //If Item is having quantity then set as In stock
            if($product_obj->available_for_order) {
                if(Configuration::get('PS_CATALOG_MODE',null, null, (int)$this->shop_id)) {
                    $availibilty = 'out of stock';
                } else if($has_quantity) {
                    $availibilty = 'in stock';
                } else if($allow_oosp == 1) {
                    $availibilty = 'in stock';
                }
            }
            
            $xml .= '<g:availability>' . $availibilty . '</g:availability>' . PHP_EOL;

            if ($availibilty == 'out of stock' && $kb_general['exclude_out_of_stock']) {
                return false;
            }
            //$xml .= '<g:id>' . $google_offer_id . '</g:id>' . PHP_EOL;
			//End by kanishka kannoujia for the feed issue fix
        }		

        /**
        * Fix for the currency conversion issue & decimal issue
        * GS002-09032024 gs-profile-create-notice
        * @date 09-03-2024
        * @author Ashish
        */
        $price = Tools::convertPriceFull($price, Context::getContext()->currency, $currency);
        if($currency->precision > 2) {
            $precision = 2;
        } else {
            $precision = $currency->precision;
        }
        $price = round($price, $precision);
        
        $xml .= '<g:price>' . $price . ' ' . $currency_iso_code . '</g:price>' . PHP_EOL;       
        if (!empty($image_link)) {
            $xml .= '<g:image_link><![CDATA[' . $image_link . ']]></g:image_link>' . PHP_EOL;
        }
        /**
        * To add the product's additonal images
        * @date 20-09-2024
        * @author Ravi Kant Gupta
        */

        if (!empty($additional_images)) {
            foreach ($additional_images as $additional_image) {
                $xml .= '<g:additional_image_link><![CDATA[' . $additional_image . ']]></g:additional_image_link>' . PHP_EOL;
            }
        }

        if(!empty($listing['promotion_id'])) {
            $xml .= '<g:promotion_id>'.$listing['promotion_id'].'</g:promotion_id>' . PHP_EOL;
        }


        $xml .= '<g:google_product_category>' . $listing['gs_category'] . '</g:google_product_category>' . PHP_EOL;
        /**
         * To add product type into the feed 
         * @date 09-02-2023
         * @author Tanisha Gupta
         */
        $path = array();
        $this->getCategoryPath1($listing['gs_category'], $path);
        krsort($path);
        $category_path = '';
        $category_path = implode(' > ', $path);

        if(!empty($listing['product_type'])) {
            $xml .= '<g:product_type>'.$listing['product_type'].'</g:product_type>' . PHP_EOL;
        }else{
            $xml .= '<g:product_type><![CDATA[' . $category_path . ']]></g:product_type>' . PHP_EOL;
        }

        //End here
        //$xml .= '<g:product_type><![CDATA[' . $listing['gs_category'] . ']]></g:product_type>' . PHP_EOL;

        $brand_name = parent::getProductBrand($product_obj->id_manufacturer);
        if (!empty($brand_name)) {
            $xml .= '<g:brand><![CDATA[' . $brand_name . ']]></g:brand>' . PHP_EOL;
        }
        //Set Variation Data
        if ($id_product_attribute > 0) {
            /**
             * Replaced $this->item_group_id with the $google_offer_id as now we are not sending xml for simple product if product has combination
             * @date 09-02-2023
             * @author Tanisha Gupta
             */
            $xml .= '<g:item_group_id><![CDATA[' . $google_offer_id . ']]></g:item_group_id>' . PHP_EOL;
        }
        if (!Tools::isEmpty($listing['gender'])) {
            $xml .= '<g:gender><![CDATA[' . $listing['gender'] . ']]></g:gender>' . PHP_EOL;
        }
        if (!Tools::isEmpty($listing['age_group'])) {
            $xml .= '<g:age_group><![CDATA[' . $listing['age_group'] . ']]></g:age_group>' . PHP_EOL;
        }
        $xml .= '<g:adult><![CDATA[' . $listing['adult'] . ']]></g:adult>' . PHP_EOL;
        if (!Tools::isEmpty($listing['size_type'])) {
            $xml .= '<g:size_type><![CDATA[' . $listing['size_type'] . ']]></g:size_type>' . PHP_EOL;
        }
        if (!Tools::isEmpty($listing['size_system'])) {
            $xml .= '<g:size_system><![CDATA[' . $listing['size_system'] . ']]></g:size_system>' . PHP_EOL;
        }

        if (!Tools::isEmpty($listing['custom_label_0'])) {
            $xml .= '<g:custom_label_0><![CDATA[' . $listing['custom_label_0'] . ']]></g:custom_label_0>' . PHP_EOL;
        }
        if (!Tools::isEmpty($listing['custom_label_1'])) {
            $xml .= '<g:custom_label_1><![CDATA[' . $listing['custom_label_1'] . ']]></g:custom_label_1>' . PHP_EOL;
        }
        if (!Tools::isEmpty($listing['custom_label_2'])) {
            $xml .= '<g:custom_label_2><![CDATA[' . $listing['custom_label_2'] . ']]></g:custom_label_2>' . PHP_EOL;
        }
        if (!Tools::isEmpty($listing['custom_label_3'])) {
            $xml .= '<g:custom_label_3><![CDATA[' . $listing['custom_label_3'] . ']]></g:custom_label_3>' . PHP_EOL;
        }
        if (!Tools::isEmpty($listing['custom_label_4'])) {
            $xml .= '<g:custom_label_4><![CDATA[' . $listing['custom_label_4'] . ']]></g:custom_label_4>' . PHP_EOL;
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the data based on the current shop ID
	*/ 
        if (isset($listing['shipping']) && $listing['shipping']) {
            $id_zone = Country::getIdZone(Configuration::get('PS_COUNTRY_DEFAULT', null, null, (int)$this->shop_id)); 
            $gs_shipping = explode(',', $listing['shipping']);
            foreach ($gs_shipping as $id_carrier) {
                $carrier = new Carrier($id_carrier, $listing['id_lang']);
                if (Validate::isLoadedObject($carrier)) {
                    $cost = 0;
                    if (!$carrier->is_free) {
                        if ($carrier->shipping_method == Carrier::SHIPPING_METHOD_WEIGHT) {
                            $cost_2 = $carrier->getDeliveryPriceByWeight($product_obj->weight, $id_zone);
                        } else {
                            if ($id_product_attribute > 0) {
                                $product_price = $product_obj->getPrice(false, $id_product_attribute);
                            } else {
                                $product_price = $product_obj->getPrice(false);
                            }

                            $cost_2 = $carrier->getDeliveryPriceByPrice($product_price, $id_zone, $listing['id_lang']);
                        }
                        $cost += $cost_2;
                    }
                    if ($carrier->shipping_handling) {
                        $cost += Configuration::get('PS_SHIPPING_HANDLING',null, null, (int)$this->shop_id);
                    }
                    $xml .= '<g:shipping>' . PHP_EOL;
                    $xml .= '<g:price>' . $cost . ' ' . $currency_iso_code . '</g:price>' . PHP_EOL;
                    $xml .= '<g:country><![CDATA[' . $country_iso_code . ']]></g:country>' . PHP_EOL;
                    $xml .= '<g:service><![CDATA[' . $carrier->name . ']]></g:service>' . PHP_EOL;
                    $xml .= '</g:shipping>' . PHP_EOL;
                }
            }
        }

        if ($product_obj->id_tax_rules_group > 0) {
            $xml .= '<g:tax>' . PHP_EOL;
            $xml .= '<g:rate>' . Tax::getProductTaxRate($product_obj->id) . '</g:rate>' . PHP_EOL;
            $xml .= '<g:country><![CDATA[' . $country_iso_code . ']]></g:country>' . PHP_EOL;
            $xml .= '</g:tax>' . PHP_EOL;
        }

        $xml .= '<g:shipping_weight><![CDATA[' . $product_obj->weight . ' ' . Configuration::get('PS_WEIGHT_UNIT',null, null, (int)$this->shop_id) . ']]></g:shipping_weight>' . PHP_EOL;
        $xml .= '</item>' . PHP_EOL;
        $this->feed_content .= $xml;

        return $google_offer_id;
    }
    /**
     * This function is responsible to get the complete path of google category 
     * @date 09-02-2023
     * @author Tanisha Gupta
     * @params category_code 
     * @param $path 
     */
    public function getCategoryPath1($category_code, &$path = array()) {
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
        $leaf_category = 'SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE category_code = ' . (int) $category_code . ' AND id_shop = ' . (int) $this->shop_id;
        $leaf_category_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($leaf_category);
            if(!empty($leaf_category_data['id_parent'])) {
                $path[] = $leaf_category_data['name'];
                $this->getCategoryPath1($leaf_category_data['id_parent'], $path);
            }else{
                $path[] = $leaf_category_data['name'];
            }
    }
}
