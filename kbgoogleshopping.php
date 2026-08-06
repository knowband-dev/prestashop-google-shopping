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

    //First condition to check if PS Version defined
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSProductList.php');


    //Module class extends parent module class to use its methods and objects
    class KbGoogleShopping extends Module
    {

        const PARENT_TAB_CLASS = 'AdminKbGSModule';
        const SELL_CLASS_NAME = 'SELL';
        const MODEL_FILE = 'model.sql';

        protected $custom_errors = array();

        //Class Constructor
        /**
         * This constructor is having the basic info like the name of the module (like module folder’s name), the version, author, version compliancy range, description, uninstall confirm message and display name for the ps backend.
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         * @return bool
         */
        public function __construct()
        {
            $this->name = 'kbgoogleshopping';
            $this->tab = 'market_place';
            $this->version = '3.0.1';
            $this->author = 'Knowband';
            $this->need_instance = 0;
            $this->module_key = '6c094f174a7eaf17421dca4a4f9c5233';
            $this->author_address = '0x2C366b113bd378672D4Ee91B75dC727E857A54A6';
            $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
            $this->bootstrap = true;

            parent::__construct();

            $this->displayName = $this->l('Google Shopping');
            $this->description = $this->l('A feature that allow store owner to upload his products on google shopping.');
            $this->confirmUninstall = $this->l('Are you sure you want to uninstall ?');
        }
        /**
         * This function returns the error list
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         */
        public function getErrors()
        {
            return $this->custom_errors;
        }

        /** To install Database Table during install of the module */
        protected function installModel()
        {
            /**
            * Getting shop_id of the store
            * @date 13-12-2024
            * @author Ravi Kant Gupta
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
            $installation_error = false;
            if (!file_exists(_PS_MODULE_DIR_ . $this->name . '/' . self::MODEL_FILE)) {
                $this->custom_errors[] = $this->l('Model installation file not found.');
                $installation_error = true;
            } elseif (!is_readable(_PS_MODULE_DIR_ . $this->name . '/' . self::MODEL_FILE)) {
                $this->custom_errors[] = $this->l('Model installation file is not readable.');
                $installation_error = true;
            } elseif (!$sql = Tools::file_get_contents(_PS_MODULE_DIR_ . $this->name . '/' . self::MODEL_FILE)) {
                $this->custom_errors[] = $this->l('Model installation file is empty.');
                $installation_error = true;
            }

            if (!$installation_error) {
                /** Replace _PREFIX_ and ENGINE_TYPE with default Prestashop values */
                $sql = str_replace(
                    array('_PREFIX_', 'ENGINE_TYPE'),
                    array(_DB_PREFIX_, _MYSQL_ENGINE_),
                    $sql
                );
                $sql = preg_split("/;\s*[\r\n]+/", trim($sql));
                foreach ($sql as $query) {
                    if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->execute(trim($query))) {
                        $installation_error = true;
                    }
                }
                /**
                 * Added sql query to add the id_shop column in the audit log table
                 * @date 01-11-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "id_shop"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_audit_log"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_audit_log ADD `id_shop` int NOT NULL");
                }
                /**
                 * Added sql query to update the id_shop column in the audit log table if the value is null or 0 to migrate the data of the shop
                 * @date 13-12-2024
                 * @author Ravi Kant Gupta
                 */
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_audit_log SET `id_shop` = " . $shop_id . " WHERE `id_shop` IS NULL OR `id_shop` = 0");

                /**
                 * Added sql query to add the id_shop column in the categories table
                 * @date 01-11-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "id_shop"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_categories"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_categories ADD `id_shop` int NOT NULL");
                }
                /**
                 * Added sql query to update the id_shop column if the value is null or 0 to migrate the data of the shop
                 * @date 13-12-2024
                 * @author Ravi Kant Gupta
                 */
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_categories SET `id_shop` = " . $shop_id . " WHERE `id_shop` IS NULL OR `id_shop` = 0");

                /**
                 * Added sql query to add the id_shop column in the category mapping table
                 * @date 01-11-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "id_shop"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_category_mapping"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_category_mapping ADD `id_shop` int NOT NULL");
                }
                /**
                 * Added sql query to update the id_shop column if the value is null or 0 to migrate the data of the shop
                 * @date 13-12-2024
                 * @author Ravi Kant Gupta
                 */
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_category_mapping SET `id_shop` = " . $shop_id . " WHERE `id_shop` IS NULL OR `id_shop` = 0");

                /**
                 * Added sql query to add the id_shop column in the country table
                 * @date 01-11-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "id_shop"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_countries"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_countries ADD `id_shop` int NOT NULL");
                }
                /**
                 * Added sql query to update the id_shop column if the value is null or 0 to migrate the data of the shop
                 * @date 13-12-2024
                 * @author Ravi Kant Gupta
                 */
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_countries SET `id_shop` = " . $shop_id . " WHERE `id_shop` IS NULL OR `id_shop` = 0");

                /**
                 * Added sql query to add the id_shop column in the currency table
                 * @date 01-11-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "id_shop"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_currencies"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_currencies ADD `id_shop` int NOT NULL");
                }
                /**
                 * Added sql query to update the id_shop column if the value is null or 0 to migrate the data of the shop
                 * @date 13-12-2024
                 * @author Ravi Kant Gupta
                 */
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_currencies SET `id_shop` = " . $shop_id . " WHERE `id_shop` IS NULL OR `id_shop` = 0");

                /**
                 * Added sql query to add the id_shop column in the feeds table
                 * @date 01-11-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "id_shop"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_feeds"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_feeds ADD `id_shop` int NOT NULL");
                }
                /**
                 * Added sql query to update the id_shop column if the value is null or 0 to migrate the data of the shop
                 * @date 13-12-2024
                 * @author Ravi Kant Gupta
                 */
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_feeds SET `id_shop` = " . $shop_id . " WHERE `id_shop` IS NULL OR `id_shop` = 0");

                /**
                 * Added sql query to add the id_shop column in the language table
                 * @date 01-11-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "id_shop"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_languages"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_languages ADD `id_shop` int NOT NULL");
                }
                /**
                 * Added sql query to update the id_shop column if the value is null or 0 to migrate the data of the shop
                 * @date 13-12-2024
                 * @author Ravi Kant Gupta
                 */
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_languages SET `id_shop` = " . $shop_id . " WHERE `id_shop` IS NULL OR `id_shop` = 0");

                
                /**
                 * Added sql query to add the id_shop column in the product list table
                 * @date 01-11-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "id_shop"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_products_list"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_products_list ADD `id_shop` int NOT NULL");
                }
                /**
                 * Added sql query to update the id_shop column if the value is null or 0 to migrate the data of the shop
                 * @date 13-12-2024
                 * @author Ravi Kant Gupta
                 */
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_products_list SET `id_shop` = " . $shop_id . " WHERE `id_shop` IS NULL OR `id_shop` = 0");

                /**
                 * Added sql query to add the id_shop column in the profiles table
                 * @date 01-11-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "id_shop"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_profiles"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_profiles ADD `id_shop` int NOT NULL");
                }
                /**
                 * Added sql query to update the id_shop column if the value is null or 0 to migrate the data of the shop
                 * @date 13-12-2024
                 * @author Ravi Kant Gupta
                 */
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_profiles SET `id_shop` = " . $shop_id . " WHERE `id_shop` IS NULL OR `id_shop` = 0");

                /**
                 * Added sql query to add the id_shop column in the queue table
                 * @date 01-11-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "id_shop"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_queue"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_queue ADD `id_shop` int NOT NULL");
                }
                  /**
                 * Added sql query to update the id_shop column if the value is null or 0 to migrate the data of the shop
                 * @date 13-12-2024
                 * @author Ravi Kant Gupta
                 */              
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_queue SET `id_shop` = " . $shop_id . " WHERE `id_shop` IS NULL OR `id_shop` = 0");

                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                                WHERE COLUMN_NAME = "generated_listing_id"
                                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_products_list"
                                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                    Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_products_list ADD `generated_listing_id` VARCHAR(250) NOT NULL AFTER `listing_id`");
                }
                /**
                 * Added alter query to add correct lang name and iso code for the gs language id 8 
                 * @date 31-03-2023
                 * @author Tanisha Gupta
                 */
                $check_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                                WHERE COLUMN_NAME = "id_gs_languages"
                                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_languages"
                                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $check_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($check_col_sql);
                
                if ($check_col) {
                
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "kb_gs_languages SET `lang_name` = 'Brazil', `iso_code` = 'BR' WHERE `id_gs_languages` = 8");
                    
                }
            /**
                 * Added alter query to add the column listing_type in the profile table
                 * @date 17-09-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "listing_type"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_profiles"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_profiles ADD `listing_type` VARCHAR(250) NULL DEFAULT 'feed' AFTER `gtin`");
                }
            /**
                 * Added alter query to add the column product_type in the profile table
                 * @date 18-09-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "product_type"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_profiles"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_profiles ADD `product_type` VARCHAR(250) NULL AFTER `gtin`");
                }
            /**
                 * Added alter query to add the column product_condition in the profile table
                 * @date 19-09-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "product_condition"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_profiles"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_profiles ADD `product_condition` VARCHAR(250) NULL AFTER `gtin`");
                }
            /**
                 * Added alter query to remove Unique index from the listing_image_id column in the product list table
                 * @date 18-09-2024
                 * @author Ravi Kant Gupta
                 */
                    // Check if the index on listing_image_id exists in the table
                    $check_index_sql = 'SELECT COUNT(*) 
                                        FROM information_schema.STATISTICS
                                        WHERE INDEX_NAME = "listing_image_id"
                                        AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_products_list"
                                        AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                    $index_exists = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($check_index_sql);

                    if ((int) $index_exists != 0) {
                        // Drop the index if it exists
                        Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'kb_gs_products_list DROP INDEX listing_image_id');
                    }

            /**
                 * Added alter query to add the column promotion_id in the profile table
                 * @date 18-09-2024
                 * @author Ravi Kant Gupta
                 */
                $generated_id_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                WHERE COLUMN_NAME = "promotion_id"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_profiles"
                AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                $generated_id_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($generated_id_col_sql);

                if ((int) $generated_id_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_profiles ADD `promotion_id` VARCHAR(250) NULL AFTER `gtin`");
                }
                /**
                 * Added alter query to add multiple columns in the product list table
                 * @date 18-09-2024
                 * @author Ravi Kant Gupta
                 */
                $columns_to_add = [
                    'material' => 'VARCHAR(255)',
                    'pattern' => 'VARCHAR(255)',
                    'product_condition' => 'VARCHAR(255)',
                    'gender' => 'VARCHAR(255)',
                    'product_type' => 'VARCHAR(255)',
                    'age_group' => 'VARCHAR(255)',
                    'product_listing_method' => 'VARCHAR(255)',
                    'adult_content' => 'VARCHAR(255)',
                    'color' => 'VARCHAR(255)',
                    'size' => 'VARCHAR(255)',
                    'size_type' => 'VARCHAR(255)',
                    'size_system' => 'VARCHAR(255)',
                    'promotion_id' => 'VARCHAR(255)',
                    'add_flag' => "enum('0','1','2') NOT NULL DEFAULT '1'",
                    'update_flag' => "enum('0','1','2') NOT NULL DEFAULT '0'",
                    'is_product_info_override' => "VARCHAR(5) DEFAULT '0'"
                ];

                foreach ($columns_to_add as $column_name => $column_type) {
                    $column_exists_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                        WHERE COLUMN_NAME = "' . pSQL($column_name) . '"
                        AND TABLE_NAME = "' . _DB_PREFIX_ . 'kb_gs_products_list"
                        AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
                    
                    $column_exists = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($column_exists_sql);
                    
                    if ((int)$column_exists == 0) {
                        Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "kb_gs_products_list ADD `$column_name` $column_type");
                    }
                }
            }
            if ($installation_error) {
                return false;
            } else {
                return true;
            }
        }

        //Function definition to install the module
        public function install()
        {
            /** Create Database table and if there is some problem then display error message */
            if (!$this->installModel()) {
                $this->custom_errors[] = $this->l('Error occurred while installing/upgrading modal.');
                return false;
            }

            if (!parent::install() ||
                    !$this->registerHook('displayBackOfficeHeader') ||
                    !$this->registerHook('actionProductDelete') ||
                    !$this->registerHook('actionProductUpdate')
                    ){
                    /**
                     * Commented below line because the corresponding method hookActionProductUpdate has not been defined in the Module.
                     * TGfeb2023 unused-hook
                     * @date 25-02-2023
                     * @author Tanisha Gupta
                     */
                    //!$this->registerHook('actionProductUpdate')) {
                return false;
            }


            //Admin tabs for Google Shopping module
            $this->installGSTabs();

	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 06-11-2024
	* To add the default configurations for all the stores available
	*/
            // Retrieve all shops
            $shops = Shop::getShops();

            foreach ($shops as $shop) {
                $shopId = (int)$shop['id_shop'];

                if (!Configuration::get('KB_GS_SECURE_KEY1', null, null, $shopId)) {
                    Configuration::updateValue('KB_GS_SECURE_KEY1', $this->GSKeyGenerator(), false, null, $shopId);
                }
                if (!Configuration::get('KB_GS_SECURE_KEY2', null, null, $shopId)) {
                    Configuration::updateValue('KB_GS_SECURE_KEY2', $this->GSKeyGenerator(), false, null, $shopId);
                }
                if (!Configuration::get('KB_GS_SECURE_KEY3', null, null, $shopId)) {
                    Configuration::updateValue('KB_GS_SECURE_KEY3', $this->GSKeyGenerator(), false, null, $shopId);
                }
                if (!Configuration::get('KB_GS_SECURE_KEY4', null, null, $shopId)) {
                    Configuration::updateValue('KB_GS_SECURE_KEY4', $this->GSKeyGenerator(), false, null, $shopId);
                }
                if (!Configuration::get('KB_GS_SECURE_KEY5', null, null, $shopId)) {
                    Configuration::updateValue('KB_GS_SECURE_KEY5', $this->GSKeyGenerator(), false, null, $shopId);
                }

                /**
                 * Saved default values for the configuration at the time of installation
                 * GS004-24022024 default-settings
                 * @date 24 Feb 2024
                 */
                if (!Configuration::get('kbgs_connect_config', null, null, $shopId)) {
                    $kbgs_values = array();
                    $kbgs_values['application_name'] = '';
                    $kbgs_values['client_id'] = '';
                    $kbgs_values['client_secret'] = '';
                    $kbgs_values['merchant_id'] = '';
                    $kbgs_values['automatic_upload'] = '0';
                    Configuration::updateValue('kbgs_connect_config', json_encode($kbgs_values), false, null, $shopId);
                }

                if (!Configuration::get('kbgs_general_setting', null, null, $shopId)) {
                    $kbgeneral = array();
                    $kbgeneral['enable'] = 0;
                    $kbgeneral['image_size'] = 0;
                    $kbgeneral['exclude_out_of_stock'] = 0;
                    $kbgeneral['exclude_product_less'] = '';
                    $kbgeneral['exclude_product_gtin'] = 0;
                    $kbgeneral['sync_gtin'] = 0;
                    $kbgeneral['sync_type'] = 'feed';
                    $kbgeneral['utm_campaign'] = '';
                    $kbgeneral['utm_source'] = '';
                    $kbgeneral['utm_medium'] = '';
                    $kbgeneral['gs_default_lang'] = '';
                    Configuration::updateValue('kbgs_general_setting', json_encode($kbgeneral), false, null, $shopId);
                }
            }

            /** Create Database table and if there is some problem then display error message */
            if (!$this->insertDataGoogleShopping()) {
                $this->custom_errors[] = $this->l('Error occurred while installing Google Shopping Data.');
                return false;
            }

            return true;
        }
        /**
         * This function is resonsible to install the module tab which shows in the left menu
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         */
        public function installGSTabs()
        {
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                if ($this->installModuleTabs('AdminKbGSModule', $this->l('Google Shopping'), 0)) {
                    //Code to add submenus
                    $subMenuList = $this->adminSubMenus16();

                    if (isset($subMenuList)) {
                        foreach ($subMenuList as $subMenuList) {
                            $this->installModuleTabs($subMenuList['class'], $subMenuList['name'], $subMenuList['parent_id']);
                        }
                    }
                }

                $lang = Language::getLanguages();
                $tab = new Tab();
                $tab->class_name = 'AdminKbGSProfileCategoryMapping';
                $tab->module = $this->name;
                $tab->active = 0;
                $tab->id_parent = Tab::getIdFromClassName('AdminKbGSProfileManagement');
                foreach ($lang as $l) {
                    $tab->name[$l['id_lang']] = $this->l('Category Mapping');
                }
                $tab->save();
            } else {
                $parentTab = new Tab();
                $parentTab->name = array();
                foreach (Language::getLanguages(true) as $lang) {
                    $parentTab->name[$lang['id_lang']] = $this->l('Google Shopping');
                }

                $parentTab->class_name = self::PARENT_TAB_CLASS;
                $parentTab->module = $this->name;
                $parentTab->active = 1;
                $parentTab->icon = 'bookmark';
                $parentTab->id_parent = Tab::getIdFromClassName(self::SELL_CLASS_NAME);
                $parentTab->add();

                $id_parent_tab = (int) Tab::getIdFromClassName(self::PARENT_TAB_CLASS);
                $admin_menus = $this->adminSubMenus();

            foreach ($admin_menus as $menu) {
                $tab = new Tab();
                foreach (Language::getLanguages(true) as $lang) {
                    $tab->name[$lang['id_lang']] = $this->l($menu['name']);
                }

                $tab->class_name = $menu['class_name'];
                $tab->module = $this->name;
                $tab->active = $menu['active'];
                $tab->id_parent = $id_parent_tab;
                $tab->add($this->id);
            }

        
        return true;
    }
    }
        /**
         * This function is resonsible to generate the secure key which is used later in cron or redirect url
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         */
        protected function GSKeyGenerator($length = 32)
        {
            $random = '';
            for ($i = 0; $i < $length; $i++) {
                $random .= chr(mt_rand(33, 126));
            }
            return md5($random);
        }
        /**
         * This function is resonsible to insert data in the table
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         */
        public function insertDataGoogleShopping()
        {
            Db::getInstance()->execute('TRUNCATE ' . _DB_PREFIX_ . 'kb_gs_countries');
            Db::getInstance()->execute('TRUNCATE ' . _DB_PREFIX_ . 'kb_gs_currencies');
            Db::getInstance()->execute('TRUNCATE ' . _DB_PREFIX_ . 'kb_gs_languages');
            //For country
            $installation_error = false;
            $path = _PS_MODULE_DIR_ . $this->name . '/google_shopping_country.csv';
            if (!file_exists($path)) {
                $this->custom_errors[] = $this->l('Country file not found.');
                $installation_error = true;
            } elseif (!is_readable($path)) {
                $this->custom_errors[] = $this->l('Country file is not readable.');
                $installation_error = true;
            } elseif (!$sql = Tools::file_get_contents($path)) {
                $this->custom_errors[] = $this->l('Country file is empty.');
                $installation_error = true;
            }
            if (!$installation_error) {
                $csv_array = $this->readCSVtoArray($path, ',');
                if (is_array($csv_array) && !empty($csv_array)) {
                    $file_process = array();
                    foreach ($csv_array as $key => $array) {
                        if (is_array($array) && !empty($array)) {
                            if ($key == 0) {
                                continue;
                            }
                            if (!isset($array[0]) || !isset($array[1])) {
                                continue;
                            } else {
                                $file_process[] = $array;
                                $country_iso_code = trim($array[0]);
                                $country_name = trim($array[1]);
                                if (($country_iso_code == '') || ($country_name == '')) {
                                    continue;
                                } else {
                                    $id_country = Country::getByIso($country_iso_code);
                                    if (!empty($id_country)) {
					/**
					* To make the module multi-store compatible
					* @Author Ravi Kant Gupta
					* @date 06-11-2024
					* To save the data for all the stores available
					*/
                                        foreach (Shop::getCompleteListOfShopsID() as $shopId) {
                                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'kb_gs_countries VALUES(NULL,' . (int) $id_country . ',"' . pSQL($country_name) . '","' . pSQL($country_iso_code) . '", ' . (int)$shopId . ')');
                                    }
                                }
                                }
                            }
                        }
                    }
                }
            }

            //for language
            $path = _PS_MODULE_DIR_ . $this->name . '/google_shopping_language.csv';
            if (!file_exists($path)) {
                $this->custom_errors[] = $this->l('Language file not found.');
                $installation_error = true;
            } elseif (!is_readable($path)) {
                $this->custom_errors[] = $this->l('Language file is not readable.');
                $installation_error = true;
            } elseif (!$sql = Tools::file_get_contents($path)) {
                $this->custom_errors[] = $this->l('Language file is empty.');
                $installation_error = true;
            }
            if (!$installation_error) {
                $csv_array = $this->readCSVtoArray($path, ',');
                if (is_array($csv_array) && !empty($csv_array)) {
                    $file_process = array();
                    foreach ($csv_array as $key => $array) {
                        if (is_array($array) && !empty($array)) {
                            if ($key == 0) {
                                continue;
                            }
                            if (!isset($array[0]) || !isset($array[1])) {
                                continue;
                            } else {
                                $file_process[] = $array;
                                $country_iso_code = trim($array[0]);
                                $lang_iso_code = trim($array[1]);
                                $lang_name = trim($array[2]);
                                if (($country_iso_code == '') || ($lang_iso_code == '') || ($lang_name == '')) {
                                    continue;
                                } else {
                                    $id_country = Db::getInstance()->getValue('SELECT id_gs_countries FROM ' . _DB_PREFIX_ . 'kb_gs_countries WHERE iso_code="' . pSQL($country_iso_code) . '"');
                                    if (!empty($id_country)) {
				    	/**
					* To make the module multi-store compatible
					* @Author Ravi Kant Gupta
					* @date 06-11-2024
					* To save the data for all the stores available
					*/
                                        foreach (Shop::getCompleteListOfShopsID() as $shopId) {
                                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'kb_gs_languages VALUES(NULL,' . (int) $id_country . ',"' . pSQL($lang_name) . '","' . pSQL($lang_iso_code) . '", ' . (int)$shopId . ')');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //for currency
            $path = _PS_MODULE_DIR_ . $this->name . '/google_shopping_currency.csv';
            if (!file_exists($path)) {
                $this->custom_errors[] = $this->l('Currency file not found.');
                $installation_error = true;
            } elseif (!is_readable($path)) {
                $this->custom_errors[] = $this->l('Currency file is not readable.');
                $installation_error = true;
            } elseif (!$sql = Tools::file_get_contents($path)) {
                $this->custom_errors[] = $this->l('Currency file is empty.');
                $installation_error = true;
            }
            if (!$installation_error) {
                $csv_array = $this->readCSVtoArray($path, ',');
                if (is_array($csv_array) && !empty($csv_array)) {
                    $file_process = array();
                    foreach ($csv_array as $key => $array) {
                        if (is_array($array) && !empty($array)) {
                            if ($key == 0) {
                                continue;
                            }
                            if (!isset($array[0]) || !isset($array[1])) {
                                continue;
                            } else {
                                $file_process[] = $array;
                                $country_iso_code = trim($array[0]);
                                $currency_iso_code = trim($array[1]);
                                $currency_name = trim($array[2]);
                                if (($country_iso_code == '') || ($currency_iso_code == '') || ($currency_name == '')) {
                                    continue;
                                } else {
                                    $id_country = Db::getInstance()->getValue('SELECT id_gs_countries FROM ' . _DB_PREFIX_ . 'kb_gs_countries WHERE iso_code="' . pSQL($country_iso_code) . '"');
                                    if (!empty($id_country)) {
				    	/**
					* To make the module multi-store compatible
					* @Author Ravi Kant Gupta
					* @date 06-11-2024
					* To save the data for all the stores available
					*/
                                        foreach (Shop::getCompleteListOfShopsID() as $shopId) {
                                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'kb_gs_currencies VALUES(NULL,' . (int) $id_country . ',"' . pSQL($currency_name) . '","' . pSQL($currency_iso_code) . '", ' . $shopId . ')');
                                    }
                                }
                                }
                            }
                        }
                    }
                }
            }

            if ($installation_error) {
                return false;
            } else {
                return true;
            }
        }
        /**
         * This function definition to install module tabs for PS16
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         */
        public function installModuleTabs($tabClass = '', $tabName = '', $idTabParent = 0)
        {
            if (!empty($tabClass) && !empty($tabName)) {
                if (Tab::getIdFromClassName($tabClass)) {
                    return (true);
                }

                $tabNameLang = array();

                foreach (Language::getLanguages() as $language) {
                    $tabNameLang[$language['id_lang']] = $tabName;
                }

                $tab = new Tab();
                $tab->name = $tabNameLang;
                $tab->class_name = $tabClass;
                $tab->module = $this->name;
                $tab->id_parent = (int) $idTabParent;

                if ($tab->save()) {
                    return true;
                }
            }
        }

        
        /**
         * Function definition to get submenus list for PS16
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         */
        public function adminSubMenus16()
        {
            $subMenu = array(
                array(
                    'class' => 'AdminKbGSConnectionSettings',
                    'name' => $this->l('Connection Setting'),
                    'parent_id' => Tab::getIdFromClassName('AdminKbGSModule')
                ),
                array(
                    'class' => 'AdminKbGSGeneralSettings',
                    'name' => $this->l('General Settings'),
                    'parent_id' => Tab::getIdFromClassName('AdminKbGSModule')
                ),
                array(
                    'class' => 'AdminKbGSProfileManagement',
                    'name' => $this->l('Profile Management'),
                    'parent_id' => Tab::getIdFromClassName('AdminKbGSModule')
                ),
                array(
                    'class' => 'AdminKbGSProductsListing',
                    'name' => $this->l('Products Listing'),
                    'parent_id' => Tab::getIdFromClassName('AdminKbGSModule')
                ),
                array(
                    'class' => 'AdminKbGSFeedManagement',
                    'name' => $this->l('Feed Management'),
                    'parent_id' => Tab::getIdFromClassName('AdminKbGSModule')
                ),
                array(
                    'class' => 'AdminKbGSSynchronization',
                    'name' => $this->l('Synchronization'),
                    'parent_id' => Tab::getIdFromClassName('AdminKbGSModule')
                ),
                array(
                    'class' => 'AdminKbGSAuditLog',
                    'name' => $this->l('Audit Log'),
                    'parent_id' => Tab::getIdFromClassName('AdminKbGSModule')
                )
            );
            return $subMenu;
        }

        
        /**
         * Function definition to get submenus list
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         */
        public function adminSubMenus()
        {
            $subMenu = array(
                array(
                    'class_name' => 'AdminKbGSConnectionSettings',
                    'name' => $this->l('Connection Setting'),
                    'active' => 1,
                ),
                array(
                    'class_name' => 'AdminKbGSGeneralSettings',
                    'name' => $this->l('General Settings'),
                    'active' => 1,
                ),
                array(
                    'class_name' => 'AdminKbGSProfileManagement',
                    'name' => $this->l('Profile Management'),
                    'active' => 1,
                ),
                array(
                    'class_name' => 'AdminKbGSProductsListing',
                    'name' => $this->l('Products Listing'),
                    'active' => 1,
                ),
                array(
                    'class_name' => 'AdminKbGSFeedManagement',
                    'name' => $this->l('Feed Management'),
                    'active' => 1,
                ),
                array(
                    'class_name' => 'AdminKbGSSynchronization',
                    'name' => $this->l('Synchronization'),
                    'active' => 1,
                ),
                array(
                    'class_name' => 'AdminKbGSAuditLog',
                    'name' => $this->l('Audit Log'),
                    'active' => 1,
                )
            );

            return $subMenu;
        }

        
        /**
         * Function definition to uninstall the module
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         */
        public function uninstall()
        {
            if (!parent::uninstall() ||
                    !$this->unregisterHook('displayBackOfficeHeader') ||
                    !$this->unregisterHook('actionProductUpdate') ||
                    !$this->unregisterHook('actionProductDelete'))
                    
                    {
                    /**
                     * Commented below line because the corresponding method hookActionProductUpdate has not been defined in the Module.
                     * TGfeb2023 unused-hook
                     * @date 25-02-2023
                     * @author Tanisha Gupta
                     */
                    //!$this->unregisterHook('actionProductUpdate')) {
                return false;
            }

            $this->unInstallGSTabs();
    //        $tabClass = 'AdminKbGSModule';
    //        $idTab = Tab::getIdFromClassName($tabClass);
    //        if ($idTab != 0) {
    //            $tab = new Tab($idTab);
    //            if ($tab->delete()) {
    //                //Code to add submenus
    //                $subMenuList = $this->adminSubMenus();
    //
    //                if (isset($subMenuList)) {
    //                    foreach ($subMenuList as $subMenuList) {
    //                        $idTab = Tab::getIdFromClassName($subMenuList['class']);
    //                        if ($idTab != 0) {
    //                            $tab = new Tab($idTab);
    //                            $tab->delete();
    //                        }
    //                    }
    //                }
    //            }
    //        }

            return true;
        }
        /**
         * This function definition to install module tabs for PS16
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         */
    public function unInstallGSTabs()
    {
        $parentTab = new Tab(Tab::getIdFromClassName(self::PARENT_TAB_CLASS));
        $parentTab->delete();

        $admin_menus = $this->adminSubMenus();

        foreach ($admin_menus as $menu) {
            $sql = 'SELECT id_tab FROM `' . _DB_PREFIX_ . 'tab` Where class_name = "' . pSQL($menu['class_name']) . '" 
				AND module = "' . pSQL($this->name) . '"';
            $id_tab = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        return true;
    }
        

    
        /**
         * Hook to add content on Back Office Header
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         */
        public function hookDisplayBackOfficeHeader()
        {
            $this->context->controller->addCSS($this->_path . 'views/css/tab.css');
        }
        /**
        * @Author Ravi Kant Gupta
        * @date 25-09-2024
        * To mark the product for update when the product is updated in the prestashop store
        */ 
        public function hookActionProductUpdate($params)
        {
            $product = $params['product'];
            $productId = (int) $product->id;
	    	/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 07-11-2024
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
            $res = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_products_list WHERE id_product = ' . (int) $productId . ' AND id_shop = ' . (int)$shop_id);

            if (!empty($res)) {
                if ($res['listing_id'] != '' && $res['delete_flag'] ==  0 ) { 
                    $update_flag = '1'; 
                    $add_flag = '0';    
                } else {
                    $update_flag = '0'; 
                    $add_flag = '1';    
                }
                Db::getInstance()->execute('
                    UPDATE ' . _DB_PREFIX_ . 'kb_gs_products_list
                    SET update_flag = "' . pSQL($update_flag) . '",
                    add_flag = "' . pSQL($add_flag) . '"
                    WHERE id_product = ' . (int)$productId . ' AND id_shop = ' . (int)$shop_id
                );
            }
    }
        /**
        * @Author Ravi Kant Gupta
        * @date 25-09-2024
        * To mark the product as deleted when the product is deleted in the prestashop store
        */ 
        public function hookActionProductDelete($params)
        {
            $product = $params['product'];
          	/**
		* To make the module multi-store compatible
		* @Author Ravi Kant Gupta
		* @date 07-11-2024
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

            $productId = (int) $product->id;
            // Use getRow() to fetch a single row from the table
            $res = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'kb_gs_products_list WHERE id_product = ' . (int) $productId . ' AND id_shop = ' . (int)$shop_id);


            if (!empty($res)) {
                Db::getInstance()->execute('
                    UPDATE ' . _DB_PREFIX_ . 'kb_gs_products_list
                    SET delete_flag = "1",
                    add_flag = "0",
                    update_flag = "0"
                    WHERE id_product = ' . (int)$productId. ' AND id_shop = ' . (int)$shop_id
                );
            }
            
    }
        /**
         * Function used to convert CSV into Array
         * @date 25-02-2023
         * @author 
         * @commenter Tanisha Gupta
         */
        public function readCSVtoArray($csv_file, $delemietr)
        {
            $file_handle = fopen($csv_file, 'r');
            $line_of_text = array();
            while (!feof($file_handle)) {
                $line_of_text[] = fgetcsv($file_handle, '10000', $delemietr);
            }
            fclose($file_handle);
            return $line_of_text;
        }
    }
