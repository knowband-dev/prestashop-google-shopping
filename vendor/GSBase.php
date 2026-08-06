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

class GSBase
{
    protected $merchant_id;
    protected $service;
    protected $gs_configs = array();
    protected $attribute_mapping = array();
    protected $product = null;
    protected $context = null;
    
    const UNIQUE_IDENTIFIER = 'reference';
    const CHANNEL = 'online';
    
    //Default Language
    private $content_language = 'en';
    
    //Default Country
    private $target_country = 'IN';
    
    const GS_REDIRECT_URI = 'urn:ietf:wg:oauth:2.0:oob';
    const GS_SCOPES = 'https://www.googleapis.com/auth/content';
    const GS_ACCESS_TYPE = 'offline';
    const GS_PRODUCT_IMG_SIZE = 'medium_default';
    
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
        $token_info = Configuration::get('Kbgs_token_info',null, null, (int)$this->shop_id);
        $token_info = stripslashes($token_info);
        $this->context = Context::getContext();
        if (AdminController::$currentIndex) {
            $this->back_url = AdminController::$currentIndex . '&token='
                . Tools::getAdminTokenLite('AdminModules') . '&configure=kbgoogleshopping';
        }
        $client = new Google_Client();
        $client->setApplicationName($gs_config['application_name']);
        $client->setClientId($gs_config['client_id']);
        $client->setClientSecret($gs_config['client_secret']);
        if ($token_info) {
            $client->setAccessToken($token_info);
        }

        $client->setScopes('https://www.googleapis.com/auth/content');
        $client->setAccessType('offline');
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Updated code to fetch the data based on the current shop ID
	*/ 
        if($client->isAccessTokenExpired()) {
            try {
                $token_info_obj = Configuration::get('Kbgs_refresh_token', null, null, (int)$this->shop_id);
                if ($token_info_obj) {
                    $client->refreshToken(stripslashes($token_info_obj));
                    $token_info = $client->getAccessToken();
					/*
					* Replaced Tools::jsonEncode by json_encode
					* Tools::jsonEncode has been removed in PrestaShop8
					* TGfeb2023 Used-json_encode
					* @date 25-02-2023
					* @author Tanisha Gupta
					*/  
                    Configuration::updateValue('Kbgs_token_info', json_encode($token_info), false, null, $this->shop_id);
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

    protected function buildProductId($gs_offer_id) {
        return sprintf('%s:%s:%s:%s', self::CHANNEL, $this->getContentLanguage(),
            $this->getTargetCountry(), $gs_offer_id);
    }
    
    public function setContentLanguage($lang_code)
    {
        $this->content_language = $lang_code;
        return $this;
    }
    
    public function setTargetCountry($country_code)
    {
        $this->target_country = $country_code;
        return $this;
    }
    
    public function getContentLanguage()
    {
        return $this->content_language;
    }
    
    public function getTargetCountry()
    {
        return $this->target_country;
    }
    
    //function defination to generate google offer id
    /*
    * PHP 8: optional $id_product_attribute before required $id_lang; give $id_lang a default
    * 01-08-2026
    */
    protected function generateGoogleOfferId($id_product_attribute = 0, $id_lang = null)
    {
        $offer_id = '';
        if ((int)$id_product_attribute > 0) {
            $attribute = new Combination($id_product_attribute);
            $combinations = $this->product->getAttributeCombinationsById($id_product_attribute, $id_lang);
            $com_str = '';
            if ($combinations) {
                foreach ($combinations as $com) {
                    $com_str .= $com['attribute_name'].'-';
                }

                $com_str = rtrim($com_str, '-');
            }
            if (self::UNIQUE_IDENTIFIER == 'reference') {
                if (!empty($attribute->reference)) {
                    $offer_id = $attribute->reference;
                } else {
                    $offer_id = $this->product->reference . '-' . $com_str;
                }
            } elseif (self::UNIQUE_IDENTIFIER == 'ean13') {
                if (!empty($attribute->ean13)) {
                    $offer_id = $attribute->ean13;
                } else {
                    $offer_id = $this->product->ean13 . '-' . $com_str;
                }
            } elseif (self::UNIQUE_IDENTIFIER == 'upc') {
                if (!empty($attribute->upc)) {
                    $offer_id = $attribute->upc;
                } else {
                    $offer_id = $this->product->upc . '-' . $com_str;
                }
            } else {
                $offer_id = self::generateRandomNumber() . '-' . $com_str;
            }
            rtrim($offer_id, '-');
        }else {
            if (self::UNIQUE_IDENTIFIER == 'reference') {
                $offer_id = $this->product->reference;
            } elseif (self::UNIQUE_IDENTIFIER == 'ean13') {
                $offer_id = $this->product->ean13;
            } elseif (self::UNIQUE_IDENTIFIER == 'upc') {
                $offer_id = $this->product->upc;
            } else {
                $offer_id = self::generateRandomNumber();
            }
        }
        
        return $offer_id;
    }

    
    private function getRefreshToken(Google_Client $client)
    {
        $client->setRedirectUri(self::GS_REDIRECT_URI);
        $client->setScopes(self::GS_SCOPES);
        $client->setAccessType(self::GS_ACCESS_TYPE); // So that we get a refresh token
        $module = Module::getInstanceByName('kbgoogleshopping');
        echo str_repeat('*', 40);
        echo $module->l('Your refresh token was missing or invalid, fetching a new one', 'GSBase').'\n';
        printf($module->l('Visit the following URL and log in', 'GSBase').':\n\n\t%s\n\n', $client->createAuthUrl());
        echo $module->l('Then save the resulting code into module configuration and again try.', 'GSBase');
    }

    // Retry a function with back off
    protected function retry($function, $parameter, $max_attempts = 5)
    {
        $attempts = 1;

        while ($attempts <= $max_attempts) {
            try {
                return call_user_func(array($this, $function), $parameter);
            } catch (Google_IO_Exception $ex) {
                sleep($attempts * $attempts);
                $attempts++;
            } catch (Google_Auth_Exception $ex) {
                sleep($attempts * $attempts);
                $attempts++;
            } catch (Google_Service_Exception $ex) {
                sleep($attempts * $attempts);
                $attempts++;
            } catch (Google_Exception $ex) {
                sleep($attempts * $attempts);
                $attempts++;
            } catch (Exception $ex) {
                sleep($attempts * $attempts);
                $attempts++;
            }
        }
    }
    
    public static function generateRandomNumber()
    {
        $length = 8;
        $code = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxlength = Tools::strlen($chars);
        if ($length > $maxlength) {
            $length = $maxlength;
        }
        $i = 0;
        while ($i < $length) {
            $char = Tools::substr($chars, mt_rand(0, $maxlength - 1), 1);
            if (!strstr($code, $char)) {
                $code .= $char;
                $i++;
            }
        }
        return $code;
    }
    
    public static function getGoogleCategory($id_product_default_category)
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
        $sql = 'Select gs.* from '._DB_PREFIX_.'kb_gs_category_mapping as cm 
            INNER JOIN '._DB_PREFIX_.'kb_gs_category as gs on (cm.id_gs_category = gs.id_gs_category) 
            where cm.id_category = '. (int) $id_product_default_category . ' and cm.id_shop = '. (int) $shop_id;
        
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }
    
    public static function getProductBrand($id_manufacturer)
    {
        $manufacturer = new Manufacturer($id_manufacturer);
        return $manufacturer->name;
    }

}