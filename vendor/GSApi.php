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
	/**
	* @Author Ravi Kant Gupta
	* @date 17-09-2024
	* To sync the products using the Content API on the Google Merchant Center
	*/ 
class GSApi
{
    protected $merchant_id;
    protected $service;
    protected $gs_configs = array();
    protected $attribute_mapping = array();
    protected $product = null;
    protected $context = null;


    //Default Language
    private $content_language = 'en';

    //Default Country
    private $target_country = 'IN';

    protected $back_url = '';

    public function __construct($gs_config)
    {
        $this->gs_configs = $gs_config;
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
        $token_info = Configuration::get('Kbgs_token_info',null, null, (int)$shop_id);
        $token_info = stripslashes($token_info);
        $this->context = Context::getContext();
        if (AdminController::$currentIndex) {
            $this->back_url = AdminController::$currentIndex . '&token='
                . Tools::getAdminTokenLite('AdminModules') . '&configure=kbgoogleshopping';
        }
        if($token_info != 'null' && $token_info != '' ){
       $client = new Google_Client();
        $client->setApplicationName($gs_config['application_name']);
        $client->setClientId($gs_config['client_id']);
        $client->setClientSecret($gs_config['client_secret']);
        if ($token_info) {
            $client->setAccessToken($token_info);
        }

        $client->setScopes('https://www.googleapis.com/auth/content');

        if ($client->isAccessTokenExpired()) {
            try {
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the data based on the current shop ID
	*/ 
                $token_info_obj = Configuration::get('Kbgs_refresh_token',null, null, (int)$shop_id);
                if ($token_info_obj) {
                    $client->refreshToken(stripslashes($token_info_obj));
                    $token_info = $client->getAccessToken();
                    Configuration::updateValue('Kbgs_token_info', json_encode($token_info), false, null, (int)$shop_id);
                    $client->setAccessToken(json_encode($token_info));
                }
            } catch (Google_IO_Exception $ex) {
                $this->context->cookie->kbgs_redirect_error = sprintf('Error: %s', $ex->getMessage());
                Tools::redirectAdmin($this->back_url);
            } catch (Google_Auth_Exception $ex) {
                $this->context->cookie->kbgs_redirect_error = sprintf('Error: %s', $ex->getMessage());
                Tools::redirectAdmin($this->back_url);
            } catch (Google_Service_Exception $ex) {
                $this->context->cookie->kbgs_redirect_error = sprintf('Error: %s', $ex->getMessage());
                Tools::redirectAdmin($this->back_url);
            } catch (Google_Exception $ex) {
                $this->context->cookie->kbgs_redirect_error = sprintf('Error: %s', $ex->getMessage());
                Tools::redirectAdmin($this->back_url);
            } catch (Exception $ex) {
                $this->context->cookie->kbgs_redirect_error = sprintf('Error: %s', $ex->getMessage());
                Tools::redirectAdmin($this->back_url);
            }
        }

        $this->merchant_id = $gs_config['merchant_id'];
        $this->service = new Google_Service_ShoppingContent($client);
    }
    
    }
	/**
	* @Author Ravi Kant Gupta
	* @date 20-09-2024
	* To list the products on the google
	*/ 
    function listProductOnGoogleShopping($productData)
    {
        // Create a new Google_Service_ShoppingContent_Product instance
        $product = new Google_Service_ShoppingContent_Product();

        // Mandatory fields
        $product->setOfferId($productData['google_offer_id']);  // Unique ID for the product
        $product->setTitle($productData['product_title']);  // Set the product title
        $product->setDescription($productData['product_description']);  // Set the product description
        $product->setLink($productData['product_link']);  // Product URL

        // Set product price and currency
        $product->setPrice(new Google_Service_ShoppingContent_Price([
            'value' => $productData['price']['value'],
            'currency' => $productData['price']['currency']
        ]));

        $product->setAvailability($productData['availability']);  // Availability (in stock or out of stock)

        // Set the product condition ('new', 'used', 'refurbished')
        $product->setCondition($productData['product_condition']);

        // Set the channel and other mandatory fields
        $product->setChannel('online');  
        $product->setContentLanguage($productData['lang']);  
        $product->setTargetCountry($productData['country']);  

        // Optional fields
        if (!empty($productData['gtin'])) {
            $product->setGtin($productData['gtin']);  // Set the GTIN
        }

        if (!empty($productData['material'])) {
            $product->setMaterial($productData['material']);  // Set material
        }

        if (!empty($productData['pattern'])) {
            $product->setPattern($productData['pattern']);  // Set pattern
        }

        if (!empty($productData['gender'])) {
            $product->setGender($productData['gender']);  // Set gender
        }

        if (!empty($productData['age_group'])) {
            $product->setAgeGroup($productData['age_group']);  // Set age group
        }

        if (!empty($productData['adult'])) {
            $product->setAdult($productData['adult']);  // Adult content flag
        }

        if (!empty($productData['color'])) {
            $product->setColor($productData['color']);  // Set product color
        }

        if (!empty($productData['size'])) {
            $product->setSizes($productData['size']);  // Set product size
        }

        if (!empty($productData['size_type'])) {
            $product->setSizeType($productData['size_type']);  // Set size type
        }

        if (!empty($productData['size_system'])) {
            $product->setSizeSystem($productData['size_system']);  // Set size system
        }
        if (!empty($productData['image_link'])) {
            $product->setImageLink($productData['image_link']);  // Set primary image link
        }
        if (!empty($productData['additional_images']) && is_array($productData['additional_images'])) {
            $product->setAdditionalImageLinks($productData['additional_images']);  // Set additional image URLs directly
        }

        if (!empty($productData['tax'])) {
            $product->setTaxes($productData['tax']);  // Set tax details
        }

        if (!empty($productData['shipping_weight']) && is_array($productData['shipping_weight'])) {
            $shippingWeight = new Google_Service_ShoppingContent_ProductShippingWeight();

            // Set the value and unit from the array
            if (isset($productData['shipping_weight']['value'])) {
                $shippingWeight->setValue($productData['shipping_weight']['value']);
            }

            if (isset($productData['shipping_weight']['unit'])) {
                $shippingWeight->setUnit($productData['shipping_weight']['unit']);
            }

            // Set the shipping weight on the product
            $product->setShippingWeight($shippingWeight);
        }


        if (!empty($productData['brand'])) {
            $product->setBrand($productData['brand']);  // Set brand name
        }

        if (!empty($productData['item_group_id'])) {
            $product->setItemGroupId($productData['item_group_id']);  // Set item group ID
        }

        if (!empty($productData['product_type'])) {
            $product->setProductTypes($productData['product_type']);  // Set product type
        }

        // Custom labels (up to 5 allowed)
        if (!empty($productData['custom_label_0'])) {
            $product->setCustomLabel0($productData['custom_label_0']);
        }
        if (!empty($productData['custom_label_1'])) {
            $product->setCustomLabel1($productData['custom_label_1']);
        }
        if (!empty($productData['custom_label_2'])) {
            $product->setCustomLabel2($productData['custom_label_2']);
        }
        if (!empty($productData['custom_label_3'])) {
            $product->setCustomLabel3($productData['custom_label_3']);
        }
        if (!empty($productData['custom_label_4'])) {
            $product->setCustomLabel4($productData['custom_label_4']);
        }

        // Set Google product category
        $product->setGoogleProductCategory($productData['google_product_category']);

        // Promotion ID (if applicable)
        if (!empty($productData['promotion_id'])) {
            $product->setPromotionIds([$productData['promotion_id']]);
        }
        // Create a batch request entry
        $entry = new Google_Service_ShoppingContent_ProductsCustomBatchRequestEntry([
            'batchId' => time(),
            'merchantId' => $this->merchant_id,
            'method' => 'insert',
            'product' => $product,
        ]);
        // Create the batch request
        $batchRequest = new Google_Service_ShoppingContent_ProductsCustomBatchRequest([
            'entries' => [$entry]
        ]);

        // Execute the batch request
        try {
            $response = $this->service->products->custombatch($batchRequest);
            return $response;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }
	/**
	* @Author Ravi Kant Gupta
	* @date 20-09-2024
	* To prepare the listing data and send the same to google for listing
	*/ 
    public function fetchProductData($listing_array = array(), $type = null)
    {
        $products_data = array();
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
        foreach ($listing_array as $product) {

            if ($product['product_listing_method'] == 'api' || $type == 'csv' || $type == 'json') {

                $product_details = array();

                if ($product['delete_flag'] == 1) {
                    continue;
                }
                if ($product['add_flag'] == 1 || $product['update_flag'] == 1  || $type == 'csv' || $type == 'json') {
                    $product_obj = new Product($product['id_product'], false, $product['id_lang']);
                    $product_details = array();
                    if ($product_obj->active) {
                        if ($product_obj->hasAttributes()) {
                            $attributes_groups = $product_obj->getAttributesGroups($product['id_lang']);
                            $combination_images = $product_obj->getCombinationImages($product['id_lang']);
                            $combinations = $product_obj->getAttributeCombinations($product['id_lang']);

                            $attribute_processed = array();
                            foreach ($combinations as $attr) {
                                if (in_array($attr['id_product_attribute'], $attribute_processed)) {
                                    continue;
                                }
                                $product_details = $this->prepareProductData($product_obj, $product, $attr['id_product_attribute'], $attributes_groups, $combination_images);
                                $attribute_processed[] = $attr['id_product_attribute'];
                            }
                        } else {
                            $product_details = $this->prepareProductData($product_obj, $product);
                        }

                        if ($type == 'csv' || $type == 'json') {
                            if (!empty($product_details)) {
                                $products_data[] = $product_details;
                            }
                            continue;
                        }
                    }
                    try {
                        // Ensure product details are not empty before proceeding
                        if (empty($product_details)) {
                            continue;
                        }

                        // Attempt to sync the product with Google Shopping
                        $response = $this->listProductOnGoogleShopping($product_details);

                        // Check if the response contains entries
                        if (isset($response->entries) && is_array($response->entries)) {
                            // Loop through the entries
                            foreach ($response->entries as $entry) {
                                // Check if the product exists in the current entry
                                if (isset($entry->product)) {
                                    // Access the product ID
                                    $productId = $entry->product->id;
					/**
					* To make the module multi-store compatible
					* @Author Ravi Kant Gupta
					* @date 11-11-2024
					* Updated query to fetch the data based on the current shop ID
					*/ 
                                    // Update the product listing information in the database
                                    $sql = 'UPDATE ' . _DB_PREFIX_ . 'kb_gs_products_list 
                                        SET listing_id = "' . pSQL($productId) . '", 
                                            listing_status = "Listed",date_listed = "' . date("Y-m-d H:i:s") . '",
                                            add_flag = "0",
                                            update_flag = "0",
                                            listing_error= ""
                                        WHERE id_product = ' . (int) $product['id_product'] . ' AND id_shop = ' . (int) $shop_id;

                                    // Execute the query
                                    Db::getInstance()->execute($sql);
                                }
                            }
                        }
                    } catch (Exception $e) {
                        echo  $e->getMessage() . PHP_EOL;

                        // Update the product listing information in the database
                        $sql = 'UPDATE ' . _DB_PREFIX_ . 'kb_gs_products_list 
                            SET listing_error = "' . pSQL($e->getMessage()) . '" 
                            WHERE id_product = ' . (int) $product['id_product'] . ' AND id_shop = ' . (int) $shop_id;
                        // Execute the query
                        Db::getInstance()->execute($sql);
                    }
                }
            }
        }
	// If the type is set than return the products data
        if ($type == 'csv' || $type == 'json') {
            return $products_data;
        }
	// Fetching all the products that are marked as deleted
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "kb_gs_products_list WHERE delete_flag = '1' AND listing_id != '' AND id_shop = " . (int) $shop_id;
        $products = DB::getInstance()->executeS($sql);
        foreach ($products as $product) {
	// To delete the products listing from the google
            $this->deleteProduct($product['listing_id']);
        }
    }
	/**
	* @Author Ravi Kant Gupta
	* @date 20-09-2024
	* To prepare the listing data 
	*/ 
    public function prepareProductData($product_obj, $listing, $id_product_attribute = 0, $attributes_groups = array(), $combination_images = array())
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $product_data = array();
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
        $product_data['lang'] = $this->context->language->iso_code;
        // Get general settings from the configuration
        $kb_general = json_decode(Configuration::get('kbgs_general_setting', null, null, (int)$shop_id), true);

        $country_iso_code = Country::getIsoById($listing['id_country']);
        $product_data['country'] = $country_iso_code;
        
        // Fix for the currency conversion issue
        $currency = new Currency($listing['id_currency']);
        $currency_iso_code = $currency->iso_code;
        
        $product_data['currency'] = $currency_iso_code;
        // Prepare Google Offer ID
        $product_data['google_offer_id'] = $listing['generated_listing_id'];

        // If product attribute exists, update the Google Offer ID accordingly
        if ($id_product_attribute > 0) {
            $product_data['google_offer_id'] .= '_' . $id_product_attribute;
        }

        if ($listing['adult'] == 'yes') {
            $product_data['adult'] = true;
        } else {
            $product_data['adult'] = false;
        }

        // Prepare the product title (if exists in listing, else fallback to product name)
        if (!empty($listing['product_title'])) {
            $product_data['product_title'] = $listing['product_title'];
        } else {
            $product_data['product_title'] = $product_obj->name;
        }
        // Prepare the product condition (if exists in listing, else fallback to product condition)
        if (!empty($listing['product_condition'])) {
            $product_data['product_condition'] = $listing['product_condition'];
        } else {
            $product_data['product_condition'] = $product_obj->condition;
        }

        // Prepare the description (if available, else use short description)
        if (!empty($product_obj->description)) {
            $product_data['product_description'] = mb_substr(html_entity_decode($product_obj->description), 0, 5000, 'UTF-8');
        } else {
            $product_data['product_description'] = mb_substr(html_entity_decode($product_obj->description_short), 0, 5000, 'UTF-8');
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

        // Get product link (based on product attribute existence)
        if ($id_product_attribute > 0) {
            $product_data['product_link'] = $this->context->link->getProductLink($product_obj, null, null, null, null, null, $id_product_attribute);
        } else {
            $product_data['product_link'] = $this->context->link->getProductLink($product_obj);
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the data based on the current shop ID
	*/ 
        // Get product availability
        $has_quantity = true;
        $allow_oosp = $product_obj->isAvailableWhenOutOfStock(StockAvailable::outOfStock($product_obj->id, $shop_id));

        if ($id_product_attribute > 0) {
            if (!is_array($attributes_groups) || !$attributes_groups) {
                $has_quantity = false;
            } else {
                $combination = new Combination($id_product_attribute);
                $attribute_qty = StockAvailable::getQuantityAvailableByProduct(null, (int) $id_product_attribute, $shop_id);
                if ((int) $attribute_qty <= 0) {
                    $has_quantity = false;
                }
            }
        } else {
            $qty = StockAvailable::getQuantityAvailableByProduct($product_obj->id, 0, $shop_id);
            if ($qty <= 0) {
                $has_quantity = false;
            }
        }

        // Check catalog mode and availability
        $availability = 'out of stock';
        if ($product_obj->available_for_order) {
            if (Configuration::get('PS_CATALOG_MODE', null, null, (int)$shop_id)) {
                $availability = 'out of stock';
            } elseif ($has_quantity || $allow_oosp == 1) {
                $availability = 'in stock';
            }
        }

        if ($availability == 'out of stock' && $kb_general['exclude_out_of_stock']) {
            return false;
        }

        $product_data['availability'] = $availability;

        // Get price and perform currency conversion
        if ($id_product_attribute > 0) {
            $price = $product_obj->getPrice(true, $id_product_attribute);
        } else {
            $price = $product_obj->getPrice();
        }

        $price = Tools::convertPriceFull($price, Context::getContext()->currency, $currency);
        $precision = ($currency->precision > 2) ? 2 : $currency->precision;
        $product_data['price'] = array(
            'value' => round($price, $precision),
            'currency' => $currency_iso_code
        );

        // Get the product images
        $cover = Product::getCover($product_obj->id, $this->context);
        if ($cover) {
            $product_data['image_link'] = $this->context->link->getImageLink($product_obj->link_rewrite, $cover['id_image'], $kb_general['image_size']);
        }

        if ($id_product_attribute == 0) {
            $images = $product_obj->getImages((int)$listing['id_lang']);
            if ($images) {
                $product_data['additional_images'] = array();
                foreach ($images as $img) {
                    if (!$cover || $cover['id_image'] != $img['id_image']) {
                        $additional_image = $this->context->link->getImageLink($product_obj->link_rewrite, $img['id_image'], $kb_general['image_size']);
                        $product_data['additional_images'][] = $additional_image;
                    }
                }
            }
        }

        // Add size and color attributes if applicable
        if ($id_product_attribute > 0) {
            foreach ($attributes_groups as $row) {
                if ($row['id_product_attribute'] == $id_product_attribute) {
                    if ($listing['size'] == $row['id_attribute_group']) {
                        $product_data['size'] = $row['attribute_name'];
                    }
                    if ($listing['color'] == $row['id_attribute_group']) {
                        $product_data['color'] = $row['attribute_name'];
                    }
                }
            }
        }

        // GTIN handling
        $gtin_key = $listing['gtin'];
        if ($id_product_attribute > 0 && !empty($combination->{$gtin_key}) && !empty($kb_general['sync_gtin'])) {
            $product_data['gtin'] = $combination->{$gtin_key};
        } elseif (!empty($product_obj->{$gtin_key}) && !empty($kb_general['sync_gtin'])) {
            $product_data['gtin'] = $product_obj->{$gtin_key};
        }

        // Category and product type handling
        $path = array();
        $this->getCategoryPath1($listing['gs_category'], $path);
        krsort($path);
        $category_path = implode(' > ', $path);
        $product_data['google_product_category'] = $listing['gs_category'];

        if (!empty($listing['product_type'])) {

            $product_data['product_type'] = $listing['product_type'];
        } else {
            $product_data['product_type'] = $category_path;
        }

        // Brand name
        $brand_name = $this->getProductBrand($product_obj->id_manufacturer);
        if (!empty($brand_name)) {
            $product_data['brand'] = $brand_name;
        }

        // Handle item group ID for variants
        if ($id_product_attribute > 0) {
            $product_data['item_group_id'] = $listing['generated_listing_id'];
        }
        if (!Tools::isEmpty($listing['size_system'])) {
            $product_data['size_system'] = $listing['size_system'];
        }
        if (!Tools::isEmpty($listing['size_type'])) {
            $product_data['size_type'] = $listing['size_type'];
        }
        if (!Tools::isEmpty($listing['age_group'])) {
            $product_data['age_group'] = $listing['age_group'];
        }
        if (!Tools::isEmpty($listing['gender'])) {
            $product_data['gender'] = $listing['gender'];
        }

        if (!Tools::isEmpty($listing['material'])) {
            $product_data['material'] = $listing['material'];
        }
        if (!Tools::isEmpty($listing['pattern'])) {
            $product_data['pattern'] = $listing['pattern'];
        }
        if (!Tools::isEmpty($listing['promotion_id'])) {
            $product_data['promotion_id'] = $listing['promotion_id'];
        }

        // Custom labels
        if (!Tools::isEmpty($listing['custom_label_0'])) {
            $product_data['custom_label_0'] = $listing['custom_label_0'];
        }
        if (!Tools::isEmpty($listing['custom_label_1'])) {
            $product_data['custom_label_1'] = $listing['custom_label_1'];
        }
        if (!Tools::isEmpty($listing['custom_label_2'])) {
            $product_data['custom_label_2'] = $listing['custom_label_2'];
        }
        if (!Tools::isEmpty($listing['custom_label_3'])) {
            $product_data['custom_label_3'] = $listing['custom_label_3'];
        }
        if (!Tools::isEmpty($listing['custom_label_4'])) {
            $product_data['custom_label_4'] = $listing['custom_label_4'];
        }


        // Shipping details
        if (isset($listing['shipping']) && $listing['shipping']) {
            $product_data['shipping'] = array();
		/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 11-11-2024
		* Updated query to fetch the data based on the current shop ID
		*/ 
            $id_zone = Country::getIdZone(Configuration::get('PS_COUNTRY_DEFAULT', null, null, (int)$shop_id));
            $gs_shipping = explode(',', $listing['shipping']);
            foreach ($gs_shipping as $id_carrier) {
                $carrier = new Carrier($id_carrier, $listing['id_lang']);
                if (Validate::isLoadedObject($carrier)) {
                    $cost = 0;
                    if (!$carrier->is_free) {
                        if ($carrier->shipping_method == Carrier::SHIPPING_METHOD_WEIGHT) {
                            $cost_2 = $carrier->getDeliveryPriceByWeight($product_obj->weight, $id_zone);
                        } else {
                            $product_price = ($id_product_attribute > 0) ? $product_obj->getPrice(false, $id_product_attribute) : $product_obj->getPrice(false);
                            $cost_2 = $carrier->getDeliveryPriceByPrice($product_price, $id_zone, $listing['id_lang']);
                        }
                        $cost += $cost_2;
                    }
                    if ($carrier->shipping_handling) {
                        $cost += Configuration::get('PS_SHIPPING_HANDLING', null, null, (int)$shop_id);
                    }
                    $product_data['shipping'][] = array(
                        'price' => $cost . ' ' . $currency_iso_code,
                        'country' => $country_iso_code,
                        'service' => $carrier->name
                    );
                }
            }
        }

        // Tax details
        if ($product_obj->id_tax_rules_group > 0) {
            $product_data['tax'] = array(
                'rate' => Tax::getProductTaxRate($product_obj->id),
                'country' => $country_iso_code
            );
        }

        // Add shipping weight
        $product_data['shipping_weight'] = array(
            'value' => $product_obj->weight,
            'unit' => Configuration::get('PS_WEIGHT_UNIT', null, null, (int)$shop_id)
        );

        return $product_data;
    }
	/**
	* @Author Ravi Kant Gupta
	* @date 20-09-2024
	* To get the products brand name
	*/ 
    public function getProductBrand($id_manufacturer)
    {
        $manufacturer = new Manufacturer($id_manufacturer);
        return $manufacturer->name;
    }
    /**
     * This function is responsible to get the complete path of google category 
     * @date 09-02-2023
     * @author Tanisha Gupta
     * @params category_code 
     * @param $path 
     */
    public function getCategoryPath1($category_code, &$path = array())
    {
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
        $leaf_category = 'SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_categories WHERE category_code = ' . (int) $category_code . ' AND id_shop = ' . (int) $shop_id;
        $leaf_category_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($leaf_category);
        if (!empty($leaf_category_data['id_parent'])) {
            $path[] = $leaf_category_data['name'];
            $this->getCategoryPath1($leaf_category_data['id_parent'], $path);
        } else {
            $path[] = $leaf_category_data['name'];
        }
    }
	/**
	* @Author Ravi Kant Gupta
	* @date 21-09-2024
	* To delete the products listing from Google and updating delete flag =2
	*/ 

    /*
     * Check if product exists on Google Merchant Center before attempting deletion
     * @modifier Manish
     * @date 24-02-2025
     */
    public function checkProductExists($productId)
    {
        try {
            $this->service->products->get($this->merchant_id, $productId);
            return true; // Product exists
        } catch (Exception $exception) {
            $error_message = $exception->getMessage();
            // If product doesn't exist, return false
            if (strpos($error_message, 'Invalid item id') !== false || 
                strpos($error_message, 'not found') !== false ||
                strpos($error_message, 'does not exist') !== false) {
                return false;
            }
            // For other errors, assume product exists and let deletion attempt handle it
            return true;
        }
    }

    public function deleteProduct($productId)
    {
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

        /*
        * Check if product exists on Google Merchant Center before attempting deletion
        * Skip deletion if product is not present on Merchant Center
        * @modifier Manish
        * @date 24-02-2026
        * MPFeb2026-module_specific_issue
         */
        if (!$this->checkProductExists($productId)) {
            // Product doesn't exist on Merchant Center, mark as successfully deleted
            $sql = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET delete_flag = '2' WHERE listing_id = '" . $productId . "' AND id_shop = " . (int) $shop_id;
            Db::getInstance()->execute($sql);
            return;
        }
        try {
            // Perform the delete request
            $res = $this->service->products->delete($this->merchant_id, $productId);
            if ($res->getStatusCode() === 204) {
                $sql = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET delete_flag = '2' WHERE listing_id = '" . $productId . "' AND id_shop = " . (int) $shop_id;
                Db::getInstance()->execute($sql);
                echo 'Product deleted successfully' . PHP_EOL;
            }
            print_r($res);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            
            /*
             * Handle case where product is already deleted from Merchant Center
             * If Google returns "Invalid item id" error, treat it as successful deletion
             * @modifier Manish
             * @date 24-02-2026
             * MPFeb2026-module_specific_issue
             */
            if (strpos($error_message, 'Invalid item id') !== false) {
                // Product already deleted from Merchant Center, mark as successfully deleted
                $sql = "UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET delete_flag = '2' WHERE listing_id = '" . $productId . "' AND id_shop = " . (int) $shop_id;
                Db::getInstance()->execute($sql);
                echo 'Product already deleted from Merchant Center - marked as deleted in module' . PHP_EOL;
            } else {
                echo 'Error deleting product: ' . $error_message . PHP_EOL;
            }
        }
    }
}
