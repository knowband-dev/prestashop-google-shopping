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

class GSSingleAction extends GSBase
{

    protected $item_group_id = '';
    // Changes done by Kanishka Kannoujia on 21-04-2022 because the products are not shown listed in our module even after listed in the Google Merchant
    // const PAGE_SIZE = 5000;
    const PAGE_SIZE = 251;

    protected $page_token = '';

    //function defination to remove product from Google Shopping
    public function removeProduct($row)
    {
        $module = Module::getInstanceByName('kbgoogleshopping');
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
        $error = false;
        $response = array();
        $remove_count = 0;
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
        $kbGSProfile = Db::getInstance()->getRow('SELECT * FROM WHERE id_gs_profiles=' . (int) $row['id_gs_profiles'] . ' AND id_shop = ' . (int) $shop_id);
        $lang_iso_code = Language::getIsoById($kbGSProfile['id_lang']);
        $country_iso_code = Country::getIsoById($kbGSProfile['id_country']);

        $this->setContentLanguage($lang_iso_code)
                ->setTargetCountry($country_iso_code);

        /*
         * Fixed product deletion issue by using listing_id instead of id_gs_offer_id
         * The listing_id contains the actual Google Shopping product ID returned from Google
         * @modifier Manish
         * @date 24-02-2026
         */
        $googl_offer_id = $row['listing_id'];

        /*
         * Check if product exists on Google Merchant Center before attempting deletion
         * Skip deletion if product is not present on Merchant Center
         * @modifier Manish
         * @date 24-02-2026
         */
        if (!$this->checkProductExists($googl_offer_id)) {
            // Product doesn't exist on Merchant Center, mark as successfully deleted
            $remove_count++;
            return array('error' => false, 'id_product' => $row['id_product'], 'google_offer_id' => $googl_offer_id, 'message' => 'Product not found on Merchant Center - skipped deletion');
        }

        try {
            $this->service->products->delete($this->merchant_id, $googl_offer_id);
            $remove_count++;
            return array('error' => false, 'id_product' => $row['id_product'], 'google_offer_id' => $googl_offer_id);
        } catch (Google_Service_Exception $exception) {
            $error_message = $exception->getMessage();
            
            /*
             * Handle case where product is already deleted from Merchant Center
             * If Google returns "Invalid item id" error, treat it as successful deletion
            * @modifier Manish
            * @date 24-02-2026
             */
            if (strpos($error_message, 'Invalid item id') !== false) {
                // Product already deleted from Merchant Center, mark as successfully deleted
                $remove_count++;
                return array('error' => false, 'id_product' => $row['id_product'], 'google_offer_id' => $googl_offer_id, 'message' => 'Product already deleted from Merchant Center');
            }
            
            $error = true;
            return array('error' => true, 'id_product' => $row['id_product'], 'message' => $error_message);
        
        }


        $mesg = '';
        if ($error) {
            if ($remove_count == 0) {
                $mesg .= $module->l('Error while removing product from google shopping.', 'GSSingleAction');
            } else {
                $mesg .= $module->l('Error while removing product or its attributes from google shopping.', 'GSSingleAction');
            }
        } else {
            $mesg .= $module->l('Product has been deleted successfully.', 'GSSingleAction');
        }
        $response['error'] = $error;
        $response['message'] = $mesg;

        return $response;
    }

    /*
    * Check if product exists on Google Merchant Center before attempting deletion
    * @modifier Manish
    * @date 24-02-2026
     */
    public function checkProductExists($listing_id)
    {
        try {
            $this->service->products->get($this->merchant_id, $listing_id);
            return true; // Product exists
        } catch (Google_Service_Exception $exception) {
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

    //function defination to add product on Google Shopping
    public function addProduct($id_product, $listing)
    {
        $module = Module::getInstanceByName('kbgoogleshopping');
        $this->context = Context::getContext();
        $this->product = new Product($id_product, false, $listing['id_lang']);
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
        $kb_general = json_decode(Configuration::get('kbgs_general_setting', null, null, (int)$shop_id), true);
        $gtin = $listing['gtin'];
        if ($gtin == 'upc') {
            if (empty($this->product->upc) || !Validate::isUpc($this->product->upc)) {
                return array('error' => true, 'message' => $module->l('UPC is required for this product.', 'GSSingleAction'));
            }
        } else if ($gtin == 'ean13') {
            if (empty($this->product->ean13) || !Validate::isEan13($this->product->ean13)) {
                return array('error' => true, 'message' => $module->l('EAN13 is required for this product.', 'GSSingleAction'));
            }
        }
        $product = $this->createSingleProduct($this->product, 0, array(), array(), $listing);
        try {
            $google_response = $this->uploadonGoogleProduct($product);
            if ((!isset($google_response->id) || $google_response->id == '') && $warnings = $google_response->getWarnings()) {
                $msg = '';
                foreach ($warnings as $warning) {
                    $msg .= $warning->getMessage();
                }
                return array('error' => true, 'message' => sprintf($module->l('Product(#%s) Uploading Error: ', 'GSSingleAction'), $this->product->id) . $msg);
            }
        } catch (Google_IO_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Google_Auth_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Google_Service_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Google_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        }

        $this->item_group_id = $google_response->offerId;

        $response = array(
            'error' => false,
            'google_offer_id' => $this->item_group_id,
            'id_product' => $this->product->id
        );

        if ($this->product->hasAttributes()) {
            $attributes_groups = $this->product->getAttributesGroups($listing['id_lang']);
            $combination_images = $this->product->getCombinationImages($listing['id_lang']);
            $combinations = $this->product->getAttributeCombinations($listing['id_lang']);
            $attribute_processed = array();
            foreach ($combinations as $attr) {
                if (in_array($attr['id_product_attribute'], $attribute_processed)) {
                    continue;
                }
                $attribute_processed[] = $attr['id_product_attribute'];
                $product_variation = $this->createSingleProduct($this->product, $attr['id_product_attribute'], $attributes_groups, $combination_images, $listing);
                try {
                    $variation_response = $this->uploadonGoogleProduct($product_variation);
                    if ((!isset($variation_response->id) || $variation_response->id == '') && $warnings = $variation_response->getWarnings()) {
                        $msg = '';
                        foreach ($warnings as $warning) {
                            $msg .= $warning->getMessage();
                        }
                        return array('error' => true, 'message' => sprintf($module->l('Product(#%s) Variation Uploading Error: ', 'GSSingleAction'), $this->product->id) . $msg);
                    } else {
                        $response = array(
                            'error' => false,
                            'google_offer_id' => $variation_response->offerId,
                            'id_product_attribute' => $attr['id_product_attribute']
                        );
                    }
                } catch (Google_IO_Exception $ex) {
                    $response = array('error' => true, 'message' => $ex->getMessage());
                }
            }
        }

        return $response;
    }

    //function to upload single product on google shopping
    /*
    * PHP 8: optional params before required $listing treated as required; give $listing a default
    * 01-08-2026
    */
    public function createSingleProduct($product_obj, $id_product_attribute = 0, $attributes_groups = array(), $combination_images = array(), $listing = null)
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
        $kb_general = json_decode(Configuration::get('kbgs_general_setting', null, null, (int)$shop_id), true);
        $lang_iso_code = Language::getIsoById($listing['id_lang']);
        $country_iso_code = Country::getIsoById($listing['id_country']);
        $currency = Currency::getCurrency($listing['id_currency']);
        $currency_iso_code = $currency['iso_code'];

        $this->setContentLanguage($lang_iso_code)
                ->setTargetCountry($country_iso_code);

        $google_offer_id = $this->generateGoogleOfferId($id_product_attribute, $listing['id_lang']);
        $product = new Google_Service_ShoppingContent_Product();
        $product->setContentLanguage($this->getContentLanguage());
        $product->setTargetCountry($this->getTargetCountry());
        $product->setOfferId($google_offer_id);
        $product->setTitle($product_obj->name);
        $product->setDescription($product_obj->description);
        $cover = Product::getCover($product_obj->id, $this->context);

        if ($cover) {
            $image_link = $this->context->link->getImageLink($product_obj->link_rewrite, $cover['id_image'], $kb_general['image_size']);
        }

        if ($id_product_attribute == 0) {
            $images = $product_obj->getImages((int) $listing['id_lang']);
            if ($images) {
                foreach ($images as $img) {
                    if (!$cover || $cover['id_image'] != $img['id_image']) {
                        $additional_image = $this->context->link->getImageLink($product_obj->link_rewrite, $img['id_image'], $kb_general['image_size']);
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
        $product_url = $this->context->link->getProductLink($product_obj, null, null, null, $listing['id_lang']) . '?' . $str;

        $product->setLink($product_url);
        $product->setChannel(parent::CHANNEL);
        $product->setCondition($product_obj->condition);

        $allow_oosp = $product_obj->isAvailableWhenOutOfStock((int) $product_obj->out_of_stock);
        $has_quantity = true;
        if ($id_product_attribute > 0) {
            if (!is_array($attributes_groups) || !$attributes_groups) {
                $has_quantity = false;
            } else {
                $combination = new Combination($id_product_attribute);
                foreach ($attributes_groups as $k => $row) {
			/**
			* To make the module multi-store compatible
			* @Author Ravi Kant Gupta
			* @date 11-11-2024
			* Updated code to fetch the data based on the current shop ID
			*/ 
                    if ($row['id_product_attribute'] == $id_product_attribute) {
                        $attribute_qty = StockAvailable::getQuantityAvailableByProduct(
                                        null, (int) $id_product_attribute, $shop_id
                        );
                        if ((int) $attribute_qty <= 0) {
                            $has_quantity = false;
                        }
                        $id_image = (int) $combination_images[$row['id_product_attribute']][0]['id_image'];
                        $image_link = $this->context->link->getImageLink($product_obj->link_rewrite, $id_image, $kb_general['image_size']);
                        if ($combination->{$listing['gtin']} != '') {
                            $product->setGtin($combination->{$listing['gtin']});
                        }
                        if ($listing['size'] == $row['id_attribute_group']) {
                            $product->setSizes(array($row['attribute_name']));
                        }
                        if ($listing['color'] == $row['id_attribute_group']) {
                            $product->setColor(array($row['attribute_name']));
                        }
                    }
                }
            }
            $price = $product_obj->getPrice(true, $id_product_attribute);
        } else {
            $qty = StockAvailable::getQuantityAvailableByProduct($product_obj->id, 0);
            if ($qty <= 0) {
                $has_quantity = false;
            }
            $price = $product_obj->getPrice();

            $product->setGtin($product_obj->{$listing['gtin']});
        }

        $price_obj = new Google_Service_ShoppingContent_Price();
        $price_obj->setValue($price);
        $price_obj->setCurrency($currency_iso_code);
        $product->setPrice($price_obj);

        $availibilty = 'out of stock';
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the data based on the current shop ID
	*/ 
        if ($allow_oosp && $has_quantity && $product_obj->available_for_order && !Configuration::get('PS_CATALOG_MODE', null, null, (int)$shop_id)) {
            $availibilty = 'in stock';
        }
        $product->setAvailability($availibilty);

        if (!empty($image_link)) {
            $product->setImageLink($image_link);
        }

        $product->setGoogleProductCategory($listing['gs_category']);
        $product->setProductType($listing['gs_category']);
        $brand_name = parent::getProductBrand($product_obj->id_manufacturer);
        if (!empty($brand_name)) {
            $product->setBrand($brand_name);
        }

        //Set Variation Data
        if ($id_product_attribute > 0) {
            $product->setItemGroupId($this->item_group_id);
        }

        if (!Tools::isEmpty($listing['gender'])) {
            $product->setGender($listing['gender']);
        }
        if (!Tools::isEmpty($listing['age_group'])) {
            $product->setAgeGroup($listing['age_group']);
        }
        if ($listing['age_group'] == 'yes') {
            $product->setAdult(true);
        } else {
            $product->setAdult(false);
        }
        if (!Tools::isEmpty($listing['size_type'])) {
            $product->setSizeType($listing['size_type']);
        }
        if (!Tools::isEmpty($listing['size_system'])) {
            $product->setSizeSystem($listing['size_system']);
        }

        $product->setCustomLabel0($listing['custom_label_0']);
        $product->setCustomLabel1($listing['custom_label_1']);
        $product->setCustomLabel2($listing['custom_label_2']);
        $product->setCustomLabel3($listing['custom_label_3']);
        $product->setCustomLabel4($listing['custom_label_4']);

        if (isset($listing['shipping']) && !empty($listing['shipping'])) {
            $shipping_arrays = array();
            $id_zone = Country::getIdZone($listing['id_lang']);
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

                            $cost_2 = $carrier->getDeliveryPriceByPrice($product_price, $id_zone, $listing['id_currency']);
                        }
                        $cost += $cost_2;
                    }
			/**
			* To make the module multi-store compatible
			* @Author Ravi Kant Gupta
			* @date 11-11-2024
			* Updated code to fetch the data based on the current shop ID
			*/ 
                    if ($carrier->shipping_handling) {
                        $cost += Configuration::get('PS_SHIPPING_HANDLING', null, null, (int)$shop_id);
                    }
                    $shipping_price = new Google_Service_ShoppingContent_Price();
                    $shipping_price->setValue($cost);
                    $shipping_price->setCurrency($currency_iso_code);

                    $shipping = new Google_Service_ShoppingContent_ProductShipping();
                    $shipping->setPrice($shipping_price);
                    $shipping->setCountry($this->getTargetCountry());
                    $shipping->setService($carrier->name);
                    $shipping_arrays[] = $shipping;
                }
            }
            if ($shipping_arrays) {
                $product->setShipping($shipping_arrays);
            }
        }


        if ($product_obj->id_tax_rules_group > 0) {
            $tax_obj = new Google_Service_ShoppingContent_ProductTax();
            $tax_obj->setRate(Tax::getProductTaxRate($product_obj->id));
            $tax_obj->setCountry($this->getTargetCountry());
            $product->setTaxes(array($tax_obj));
        }

        $shipping_weight = new Google_Service_ShoppingContent_ProductShippingWeight();
        $shipping_weight->setValue($product_obj->weight);
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the data based on the current shop ID
	*/ 
        $shipping_weight->setUnit(Configuration::get('PS_WEIGHT_UNIT',null, null, (int)$shop_id));

        $product->setShippingWeight($shipping_weight);
        return $product;
    }

    //function to upload product on Google Shopping
    public function uploadonGoogleProduct(Google_Service_ShoppingContent_Product $product)
    {
        $response = $this->service->products->insert($this->merchant_id, $product);

        return $response;
    }

    //function to fetch product list from Google Shopping
    public function getProductList()
    {
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
        $parameters = array('maxResults' => self::PAGE_SIZE - 1);
        if (!empty($this->page_token)) {
            $parameters['pageToken'] = $this->page_token;
        }
        try {
            $response = $this->service->products->listProducts($this->merchant_id, $parameters);
        } catch (Google_IO_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Google_Auth_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Google_Service_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Google_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        }
        if ($listings = $response->getResources()) {
            foreach ($listings as $list) {
                $offer_id = $list->offerId;
                if (!empty($list->itemGroupId)) {
                    $offer_id = $list->itemGroupId;
                }
		/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 11-11-2024
		* Updated query to fetch the data based on the current shop ID
		*/ 
                if (!empty($offer_id)) {
                    $itemData = Db::getInstance()->getRow('SELECT id_gs_products_list FROM ' . _DB_PREFIX_ . 'kb_gs_products_list WHERE generated_listing_id = "' . pSQL($offer_id) . '" AND id_shop = ' . (int) $shop_id);
			/**
			* @Author Ravi Kant Gupta
			* @date 27-09-2024
			* Not update the listing id of the products whose listing id is already added to avoid the replace the listing id of the Products listed through API
			*/ 
                    /* If listing_id already exist in the table, Don;t update the date_listed column */
                            // Check if listing_id is empty
                            if (empty($itemData['listing_id'])) {
                                // Update when listing_id is null, blank, or empty
                                Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'kb_gs_products_list SET listing_id = "' . pSQL($offer_id) . '", listing_status = "Listed", date_listed = "' . date("Y-m-d H:i:s") . '" WHERE generated_listing_id = "' . pSQL($offer_id) . '" AND (listing_id IS NULL OR listing_id = "") AND id_shop = ' . (int) $shop_id); 
                            } else {
                                Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'kb_gs_products_list SET listing_id = "' . pSQL($offer_id) . '", listing_status = "Listed" WHERE generated_listing_id = "' . pSQL($offer_id) . '" AND (listing_id IS NULL OR listing_id = "" ) AND id_shop = ' . (int) $shop_id); 
                            }

                        }
            }
            if (!empty($response->nextPageToken)) {
                $this->page_token = $response->nextPageToken;
                $this->getProductList();
            } else {
                return true;
            }
        }
        return true;
    }
}