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

// Class to handle the 'kb_gs_products_list' table
class KbGSProductList extends ObjectModel
{
    public $id_gs_products_list;
    public $id_gs_profiles;
    public $id_product;
    public $reference;
    public $id_product_attribute;
    public $listing_status;
    public $listing_id;
    public $listing_image_id;
    public $material;
    public $pattern;
    public $product_condition;
    public $gender;
    public $product_type;
    public $age_group;
    public $product_listing_method;
    public $adult_content;
    public $color;
    public $size;
    public $size_type;
    public $size_system;
    public $promotion_id;
    public $is_product_info_override;
    public $renew_flag;
    public $delete_flag;
    public $update_flag;
    public $add_flag;
    public $date_add;
    public $date_listed;
    public $date_last_renewed;
    public $listing_error;
    public $id_shop;

    public static $definition = array(
        'table' => 'kb_gs_products_list',
        'primary' => 'id_gs_products_list',
        'fields' => array(
            'id_gs_products_list' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_gs_profiles' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'id_product' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'reference' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isReference'
            ),
            'id_product_attribute' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'listing_status' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'listing_id' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'listing_image_id' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'material' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'pattern' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'product_condition' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'gender' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'product_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'age_group' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'product_listing_method' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'adult_content' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'color' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'size' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'size_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'size_system' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'promotion_id' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'is_product_info_override' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isBool'
            ),
            'renew_flag' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isBool'
            ),
            'add_flag' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isBool'
            ),
            'update_flag' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isBool'
            ),
            'delete_flag' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isBool'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ),
            'date_listed' => array(
                'type' => self::TYPE_DATE,
            ),
            'date_last_renewed' => array(
                'type' => self::TYPE_DATE,
            ),
            'listing_error' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 07-11-2024
	* Defining the newly created column
	*/
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
        ),
    );
}
