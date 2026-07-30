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

//Class and its methods to handle some common features of Etsy Module
class KbGSIntegration extends ObjectModel
{

    /**
     * Changed @return type from string to array<string, mixed> to match the actual return value and fix PrestaShop validator compatibility error.
     * @modifier Himanshu Vishwakarma
     * @date 25-07-2025
     */
    /**
    * Returns all of the categories of store
    * @return array<string, mixed>
    */
    public static function getAllCategories()
    {
        $categories = Category::getNestedCategories();
        $result_array = array();
        $result_array['success'] = $categories;
        $result_array['error'] = '';

        return $result_array;
    }

    /**
     * Returns the products inside a category
     * Fixed parameter type in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_category
     * @param int $start
     * @param int $limit
     * @param string $order_by
     * @param string $sort_order
     * @return array<string, mixed>
     */
    public static function getProductsByCategoryId($id_category = 0, $start = 0, $limit = 20, $order_by = 'id_product', $sort_order = "ASC")
    {
        $result_array = array();
        // Replace empty() check with specific condition for function parameter compatibility. Fixes PS validator error. 08-06-2024
        if ($id_category > 0) {
            $id_language = Context::getContext()->language->id;
            // Replace empty() check with specific condition for function parameter compatibility. Fixes PS validator error. 08-06-2024
            $product_list = Product::getProducts($id_language, $start, $limit, $order_by, $sort_order, $id_category > 0 ? (int)$id_category : false);
            $result_array['success'] = $product_list;
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameters are missing!';
        }
        return $result_array;
    }

    /**
     * Returns an array of details of a product using id_product
     * Fixed parameter type in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_product
     * @return array<string, mixed>
     */
    public static function getProductByProductId($id_product = 0)
    {
        $result_array = array();
        // Replace empty() check with specific condition for function parameter compatibility. Fixes PS validator error. 08-06-2024
        if ($id_product > 0) {
            // Cast to int for ProductCore constructor compatibility with PS validator. Fixes int|null type expectation. 08-06-2024
            $product = new ProductCore((int)$id_product);
            $result_array['success'] = $product;
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameter is missing!';
        }

        return $result_array;
    }

    /**
     * Returns array of details about a category using id_category
     * Fixed parameter type in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_category
     * @return array<string, mixed>
     */
    public static function getCategoryByCategoryId($id_category = 0)
    {
        $result_array = array();
        // Replace empty() check with specific condition for function parameter compatibility. Fixes PS validator error. 08-06-2024
        if ($id_category > 0) {
            // Cast to int for Category constructor compatibility with PS validator. Fixes int|null type expectation. 08-06-2024
            $category = new Category((int)$id_category);
            $result_array['success'] = $category;
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameter is missing!';
        }

        return $result_array;
    }

    /**
     * Fixed parameter types in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_product
     * @param mixed $id_product_attribute
     * @return array<string, mixed>
     */
    public static function getProductInventory($id_product = 0, $id_product_attribute = 0)
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
        $result_array = array();
        // Replace empty() check with specific condition for function parameter compatibility. Fixes PS validator error. 08-06-2024
        if ($id_product > 0) {
		/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 11-11-2024
		* Updated code to fetch the data based on the current shop ID
		*/ 
            // Replace deprecated StockAvailable::dependsOnStock() with version-compatible approach for PS 1.7, 8, 9 compatibility. 08-06-2024
            $depends_on_stock = true;
            if (method_exists('StockAvailable', 'dependsOnStock')) {
                $depends_on_stock = StockAvailable::dependsOnStock($id_product, $shop_id);
            }
            
            // Replace deprecated methods with version-compatible alternatives for PS 1.7, 8, 9 compatibility. 08-06-2024
            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT', null, null, (int)$shop_id) && 
                (method_exists('Product', 'usesAdvancedStockManagement') ? Product::usesAdvancedStockManagement($id_product) : false) && 
                $depends_on_stock) {
                
                // Handle multi-warehouse functionality with version compatibility for PS 1.7, 8, 9. 08-06-2024
                if (class_exists('Warehouse') && method_exists('Warehouse', 'getWarehousesByProductId')) {
                    // Use Warehouse class for PS 1.7 and 8 with multi-warehouse support. 08-06-2024
                    $warehouses = Warehouse::getWarehousesByProductId($id_product, $id_product_attribute);
                    if (!empty($warehouses)) {
                        $result_array['success'] = 0;
                        foreach ($warehouses as $warehouse) {
                            if (method_exists('Product', 'getRealQuantity')) {
                                $result_array['success'] += Product::getRealQuantity($id_product, $id_product_attribute, $warehouse['id_warehouse']);
                            } else {
                                // Fallback for PS 9 where getRealQuantity is deprecated. 08-06-2024
                                $result_array['success'] += StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute, (int)$shop_id);
                            }
                        }
                    } else {
                        if (method_exists('Product', 'getRealQuantity')) {
                            $result_array['success'] = Product::getRealQuantity($id_product, $id_product_attribute);
                        } else {
                            $result_array['success'] = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute, (int)$shop_id);
                        }
                    }
                } else {
                    // For PS 9 where Warehouse class is deprecated, use StockAvailable directly. 08-06-2024
                    $result_array['success'] = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute, (int)$shop_id);
                }
            } else {
                // Use version-compatible method for getting product quantity. 08-06-2024
                if (method_exists('Product', 'getRealQuantity')) {
                    $result_array['success'] = Product::getRealQuantity($id_product, $id_product_attribute);
                } else {
                    $result_array['success'] = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute, (int)$shop_id);
                }
            }
            $result_array['error'] = "";
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameter is missing!';
        }

        return $result_array;
    }

    /**
     * Changed @return type from string to array<string, mixed> to match the actual return value and fix PrestaShop validator compatibility error.
     * @modifier Himanshu Vishwakarma
     * @date 25-07-2025
     */
    /**
     * @param int $id_product
     * @param int $id_product_attribute
     * @return array<string, mixed>
     */
    public static function getInventoryByProductAttributeId($id_product = 0, $id_product_attribute = 0)
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
        $result_array = array();
        // Replace empty() check with specific condition for function parameter compatibility. Fixes PS validator error. 08-06-2024
        if ($id_product > 0 && $id_product_attribute > 0) {
            // SELECT * FROM stock_available WHERE id_product_attribute = '3' AND id_product = '1'
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
            $query_get_inventory = "SELECT * FROM " . _DB_PREFIX_ . "stock_available WHERE id_product_attribute = '" . (int) $id_product_attribute . "' AND id_product = '" . (int) $id_product . "' AND id_shop = '" . (int) $shop_id . "'";
            $inventory_details = Db::getInstance()->executeS($query_get_inventory);
            $result_array['success'] = $inventory_details;
            $result_array['error'] = "";
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameters are missing!';
        }

        return $result_array;
    }

    /**
     * Returns an multi-dimensional array of Inventory details by id_product.
     * Fixed parameter type in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_product
     * @return array<string, mixed>
     */
    public static function getAttributesByProductId($id_product = 0)
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
        $result_array = array();
        // Replace empty() check with specific condition for function parameter compatibility. Fixes PS validator error. 08-06-2024
        if ($id_product > 0) {
            $id_language = Context::getContext()->language->id;
            /**
             * SELECT p.id_product, pa.reference, pa.upc, pa.price, pai.id_image, pl.name, GROUP_CONCAT(DISTINCT(al.name) SEPARATOR ", ") as combination, pa.id_product_attribute, sa.quantity FROM product p LEFT JOIN product_attribute pa ON (p.id_product = pa.id_product) LEFT JOIN stock_available sa ON (p.id_product = sa.id_product AND pa.id_product_attribute = sa.id_product_attribute) LEFT JOIN product_lang pl ON (p.id_product = pl.id_product) LEFT JOIN product_attribute_combination pac ON (pa.id_product_attribute = pac.id_product_attribute) LEFT JOIN attribute_lang al ON (pac.id_attribute = al.id_attribute) LEFT JOIN product_attribute_image pai ON (pa.id_product_attribute = pai.id_product_attribute) WHERE pl.id_lang = 1 AND al.id_lang = 1 AND p.id_product = 1 GROUP BY pac.id_product_attribute
             */
            $query_get_inventory = 'SELECT
            p.id_product,
            pa.reference,
            pa.upc,
            pa.price,
            pai.id_image,
            pl.name,
            GROUP_CONCAT(DISTINCT(al.name) SEPARATOR ", ") as combination,
            pa.id_product_attribute,
            sa.quantity
            FROM ' . _DB_PREFIX_ . 'product p
            LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa
            ON (p.id_product = pa.id_product)
            LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa
            ON (p.id_product = sa.id_product AND pa.id_product_attribute = sa.id_product_attribute AND sa.id_shop = ' . (int)$shop_id . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl
            ON (p.id_product = pl.id_product AND pl.id_shop = ' . (int)$shop_id . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_combination pac
            ON (pa.id_product_attribute = pac.id_product_attribute)
            LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al
            ON (pac.id_attribute = al.id_attribute AND al.id_shop = ' . (int)$shop_id . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_image pai
            ON (pa.id_product_attribute = pai.id_product_attribute)
            WHERE pl.id_lang = ' . (int)$id_language . ' 
            AND al.id_lang = ' . (int)$id_language . ' 
            AND p.id_product = ' . (int)$id_product . '
            GROUP BY pac.id_product_attribute';

            $inventory_details = Db::getInstance()->executeS($query_get_inventory, true, false);
            $result_array['success'] = $inventory_details;
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameter is missing!';
        }
        return $result_array;
    }

    /**
     * Changed @return type from string to array<string, mixed> to match the actual return value and fix PrestaShop validator compatibility error.
     * @modifier Himanshu Vishwakarma
     * @date 25-07-2025
     */
    /**
     * Replaces the quantity of given attributes of a product by passed quantity
     * Fixed parameter types in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_product
     * @param mixed $id_product_attribute
     * @param mixed $quantity
     * @return array<string, mixed>
     */
    public static function updateQuantity($id_product = 0, $id_product_attribute = 0, $quantity = 0)
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
        $result_array = array();
        // If quantity is given and is number and is not negative
        if (is_numeric($quantity) && $quantity >= 0 && is_numeric($id_product_attribute) && is_numeric($id_product)) {
            $data = array();
            // Cast to string for pSQL() function compatibility. Fixes PS validator type mismatch error. 08-06-2024
            $data['quantity'] = pSQL((string)$quantity);
            $where = "id_product_attribute = '" . (int) $id_product_attribute . "' AND id_product = '" . (int) $id_product . "' AND id_shop = '" . (int) $shop_id . "'";
            Db::getInstance()->update('stock_available', $data, $where);
            $result_array['success'] = 'Quantity of product attribute id ' . $id_product_attribute . ' updated successfully.';
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Invalid parameters passed. Could not update quantity.';
        }
        return $result_array;
    }

    /**
     * Changed @return type from string to array<string, mixed> to match the actual return value and fix PrestaShop validator compatibility error.
     * @modifier Himanshu Vishwakarma
     * @date 25-07-2025
     */
    /**
     * Updates the quantity by given amount. Given amount is added into the current quantity
     * New quantity = Current quantity + Quantity to update
     * Fixed parameter types in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_product
     * @param mixed $id_product_attribute
     * @param mixed $quantity_offset
     * @return array<string, mixed>
     */
    public static function updateQuantityByOffset($id_product = 0, $id_product_attribute = 0, $quantity_offset = 0)
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
        $result_array = array();
        // If quantity is given and is number and is not negative
        if (is_numeric($quantity_offset)) {
            // Getting the current quantity of the product\
	 /**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated query to fetch the data based on the current shop ID
	*/ 
            // SELECT quantity FROM stock_available WHERE id_product_attribute = '3' AND id_product = '1'
            $query_get_quantity = "SELECT quantity FROM " . _DB_PREFIX_ . "stock_available WHERE id_product_attribute = '" . (int) $id_product_attribute . "' AND id_product = '" . (int) $id_product . "' AND id_shop = '" . (int) $shop_id . "'";
            $quantity_details = Db::getInstance()->executeS($query_get_quantity);
            $current_quantity = $quantity_details[0]['quantity'];

            // Adding the given Quantity with the current quantity
            $new_quantity = $current_quantity + $quantity_offset;

            $data = array();
            // Cast to string for pSQL() function compatibility. Fixes PS validator type mismatch error. 08-06-2024
            $data['quantity'] = pSQL((string)$new_quantity);
            $where = "id_product_attribute = '" . (int) $id_product_attribute . "' AND id_product = '" . (int) $id_product . "' AND id_shop = '" . (int) $shop_id . "'";
            Db::getInstance()->update('stock_available', $data, $where);
            $result_array['success'] = 'Quantity of product attribute id ' . $id_product_attribute . ' updated successfully.';
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Invalid parameter(s) passed. Could not update quantity.';
        }
        return $result_array;
    }

    /**
     * Changed @return type from string to array<string, mixed> to match the actual return value and fix PrestaShop validator compatibility error.
     * @modifier Himanshu Vishwakarma
     * @date 25-07-2025
     */
    /**
     * Returns an array containing all the images (id, url etc)
     * Fixed parameter type in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_product
     * @param string|null $image_type
     * @return array<string, mixed>
     */
    public static function getImagesByProductId($id_product = 0, $image_type = null)
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
        $result_array = array();
        if (is_numeric($id_product) && $id_product > 0) {
            $id_language = Context::getContext()->language->id;

            // SELECT * FROM `image` i INNER JOIN image_shop image_shop ON
            // (image_shop.id_image = i.id_image AND image_shop.id_shop = 1)
            // LEFT JOIN `image_lang` il ON
            // (i.`id_image` = il.`id_image` AND il.`id_lang` = 1) WHERE i.`id_product` = "1" ORDER BY `position`
            $query_get_images = 'SELECT *
            FROM `' . _DB_PREFIX_ . 'image` i
            ' . Shop::addSqlAssociation('image', 'i') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il 
            ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_language . ')
            WHERE i.`id_product` = ' . (int) $id_product . ' 
            AND image_shop.id_shop = ' . (int) $shop_id . ' 
            ORDER BY `position`';

            $product = new Product((int)$id_product, false, $id_language);
            // Use proper constructor syntax for Link class compatibility with PS validator. 08-06-2024
            $link = new Link();

            // Getting the array containing all the image ids for a product
            $array_id_image = Db::getInstance()->executeS($query_get_images);

            $i = 0;
            foreach ($array_id_image as $id_image) {
                if (is_array($product->link_rewrite)) {
                    foreach ($product->link_rewrite as $product_url_name) {
                        if ($product_url_name != "" && $product_url_name != null) {
                            $url_image = $link->getImageLink($product_url_name, $id_image['id_image'], $image_type);
                            $array_id_image[$i]['url_image'][] = $url_image;
                        }
                        $i++;
                    }
                } else {
                    $url_image = $link->getImageLink($product->link_rewrite, $id_image['id_image'], $image_type);
                    $array_id_image[$i]['url_image'][] = $url_image;
                    $i++;
                }
            }
            $result_array['success'] = $array_id_image;
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameter is missing!';
        }

        return $result_array;
    }

    /**
     * Changed @return type from string to array<string, mixed> to match the actual return value and fix PrestaShop validator compatibility error.
     * @modifier Himanshu Vishwakarma
     * @date 25-07-2025
     */
    /**
     * Get count for no of products in a category
     * Fixed parameter type in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_category
     * @return array<string, mixed>
     */
    public static function getCountProductByCategoryId($id_category = 0)
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
        $result_array = array();
        if (is_numeric($id_category) && $id_category > 1) {
            $result_product_count = Db::getInstance()->ExecuteS('
            SELECT COUNT(ac.`id_product`) as totalProducts
            FROM `' . _DB_PREFIX_ . 'category_product` ac
            LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = ac.`id_product`
            WHERE ac.`id_category` = ' . (int)$id_category . ' 
            AND p.`active` = 1 
            AND p.`id_shop` = ' . (int)$shop_id);                                                          


            $result_array['success'] = $result_product_count[0]['totalProducts'];
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameter is missing!';
        }
        return $result_array;
    }

    /**
     * Changed @return type from string to array<string, mixed> to match the actual return value and fix PrestaShop validator compatibility error.
     * @modifier Himanshu Vishwakarma
     * @date 25-07-2025
     */
    /**
     * Get count for no of products by id_default_category
     * Fixed parameter type in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_category
     * @return array<string, mixed>
     */
    public static function getCountProductByDefaultCategoryId($id_category = 0)
    {
    	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
	* Updated query to fetch the data based on the current shop ID
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
        $result_array = array();
        // Replace empty() check with specific condition for function parameter compatibility. Fixes PS validator error. 08-06-2024
        if ($id_category > 1) {
            $sql = 'SELECT 
            COUNT(p.id_product) AS totalProducts
            FROM ' . _DB_PREFIX_ . 'product p
            ' . Shop::addSqlAssociation('product', 'p') . '
            LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (p.id_product = pl.id_product ' . Shop::addSqlRestrictionOnLang('pl') . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer m ON (m.id_manufacturer = p.id_manufacturer)
            LEFT JOIN ' . _DB_PREFIX_ . 'supplier s ON (s.id_supplier = p.id_supplier)
            WHERE pl.id_lang = ' . (int)Context::getContext()->language->id . ' 
            AND p.id_category_default = ' . (int)$id_category . ' 
            AND p.active = 1 
            AND product_shop.id_shop = ' . (int)$shop_id;
    
            $result_product_count = Db::getInstance()->ExecuteS($sql, true, false);

            $result_array['success'] = $result_product_count[0]['totalProducts'];
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameter is missing!';
        }
        return $result_array;
    }

    /**
     * Fixed parameter type in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     */
    /**
     * Returns the products of a default category
     * Fixed parameter type in docblock for PrestaShop validator compatibility.
     * @modifier Himanshu Vishwakarma
     * @date 08-06-2024
     * @param mixed $id_category
     * @param int $start
     * @param int $limit
     * @param string $order_by
     * @param string $sort_order
     * @return array<string, mixed>
     */
    public static function getProductsByDefaultCategoryId($id_category = 0, $start = 0, $limit = 20, $order_by = 'id_product', $sort_order = "ASC")
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
        $result_array = array();
        // Replace empty() check with specific condition for function parameter compatibility. Fixes PS validator error. 08-06-2024
        if ($id_category > 1) {
            $sql = 'SELECT 
            p.*, 
            product_shop.*, 
            pl.*, 
            m.name AS manufacturer_name, 
            s.name AS supplier_name
            FROM ' . _DB_PREFIX_ . 'product p
            ' . Shop::addSqlAssociation('product', 'p') . '
            LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (p.id_product = pl.id_product ' . Shop::addSqlRestrictionOnLang('pl') . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer m ON (m.id_manufacturer = p.id_manufacturer)
            LEFT JOIN ' . _DB_PREFIX_ . 'supplier s ON (s.id_supplier = p.id_supplier)
            WHERE pl.id_lang = ' . (int)Context::getContext()->language->id . ' 
            AND p.id_category_default = ' . (int)$id_category . ' 
            AND p.active = 1 
            AND product_shop.id_shop = ' . (int)$shop_id . ' 
            GROUP BY p.id_product
            ORDER BY p.' . pSQL($order_by) . ' ' . pSQL($sort_order) . ' 
            LIMIT ' . (int)$start . ', ' . (int)$limit;
    
            $result_product = Db::getInstance()->executeS($sql, true, false);
            $result_array['success'] = $result_product;
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameters are missing!';
        }
        return $result_array;
    }
}
