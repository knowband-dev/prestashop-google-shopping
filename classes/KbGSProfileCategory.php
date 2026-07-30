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

//Class and its methods to handle
class KbGSProfileCategory extends ObjectModel
{
    public $id_profile_category;
    public $id_gs_profiles;
    public $gs_category_code;
    public $prestashop_category;
    public $material;
    public $pattern;
    public $gender;
    public $age_group;
    public $adult;
    public $color;
    public $size;
    public $size_type;
    public $size_system;
    
    public $is_global;
    public $date_add;
    public $date_upd;
    public $id_shop;

    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'kb_gs_category_mapping',
        'primary' => 'id_profile_category',
        'fields' => array(
            'id_profile_category' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'id_gs_profiles' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'gs_category_code' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'prestashop_category' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
            'material' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'pattern' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            
            'gender' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml'
            ),
            'age_group' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml'
            ),
            'adult' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml'
            ),
            'color' => array(
               'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'size' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
           
            'size_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml'
            ),
            'size_system' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml'
            ),
            'is_global' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'copy_post' => false
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'copy_post' => false
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
        ),
    );
    
    public function __construct($id = null)
    {
        parent::__construct($id);
    }
    
    //function defination to get main category of the profile
    public static function getMainProfileCategory($id_gs_profiles)
    {
        if (empty($id_gs_profiles)) {
            return;
        }
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
         $data = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'kb_gs_category_mapping WHERE id_gs_profiles='.(int)$id_gs_profiles.' AND is_global=1' . ' AND id_shop = ' .(int) $shop_id);
        return $data;
    }

    //function to get all google country
    public static function getGSCountries()
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
        $data = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'kb_gs_countries' . ' WHERE id_shop = ' .(int) $shop_id);
        return $data;
    }
}
