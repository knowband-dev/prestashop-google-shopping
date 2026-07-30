CREATE TABLE IF NOT EXISTS `_PREFIX_kb_gs_countries` (
    `id_gs_countries` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `id_country` INT(11) UNSIGNED NOT  NULL DEFAULT '0',
    `country_name` VARCHAR(50) NULL,
    `iso_code` VARCHAR(3) NULL,
    `id_shop` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_gs_countries`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_PREFIX_kb_gs_languages` (
    `id_gs_languages` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `id_gs_countries` INT(11) UNSIGNED NOT  NULL DEFAULT '0',
    `lang_name` VARCHAR(50) NULL,
    `iso_code` VARCHAR(3) NULL,
    `id_shop` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_gs_languages`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_PREFIX_kb_gs_currencies` (
    `id_gs_currencies` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `id_gs_countries` INT(11) UNSIGNED NOT  NULL DEFAULT '0',
    `currency_name` VARCHAR(50) NULL,
    `iso_code` VARCHAR(4) NULL,
    `id_shop` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_gs_currencies`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_PREFIX_kb_gs_categories` (
    `id_gs_category` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `category_code` INT(11) UNSIGNED NULL DEFAULT '0',
    `name` text null,
    `id_parent` INT(11) UNSIGNED NOT NULL DEFAULT '0',
--     `id_parent` VARCHAR(50) NULL,
    `date_add` DATETIME NOT NULL ,
    `date_upd` DATETIME NULL,
    `id_shop` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_gs_category`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_PREFIX_kb_gs_profiles` (
    `id_gs_profiles` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `profile_title` varchar(255) NOT NULL,
    `id_country`  INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `id_lang`  INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `id_currency`  INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `customize_product_title`  text NULL,
    `gtin` varchar(255) NULL,
    `promotion_id` varchar(255) NULL,
    `product_condition` varchar(255),
    `product_type` varchar(255) NULL,
    `listing_type` varchar(255) NULL DEFAULT 'feed',
    `shipping` TEXT NULL,
    `custom_label_0` TEXT NULL,
    `custom_label_1` TEXT NULL,
    `custom_label_2` TEXT NULL,
    `custom_label_3` TEXT NULL,
    `custom_label_4` TEXT NULL,
    `active` enum('1','0') NOT NULL DEFAULT '1',
    `feed_generated` TEXT NULL,
    `date_add` DATETIME NOT NULL ,
    `date_upd` DATETIME NULL,
   `id_shop` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_gs_profiles`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_PREFIX_kb_gs_category_mapping` (
    `id_profile_category` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `id_gs_profiles` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `gs_category_code` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `prestashop_category` TEXT NULL,
    `material`  INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `pattern`  INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `gender`  varchar(255) NULL,
    `age_group`  varchar(255) NULL,
    `adult`  varchar(255) NOT NULL,
    `color`  INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `size`  INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `size_type`  varchar(255) NOT NULL,
    `size_system`  varchar(255) NOT NULL,
    `is_global` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `date_add` DATETIME NOT NULL ,
    `date_upd` DATETIME NULL,
    `id_shop` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_profile_category`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_PREFIX_kb_gs_feeds` (
    `id_gs_feed` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `id_gs_profiles` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `feed_label` varchar(255) NOT NULL,
    `upload_type` varchar(255) NOT NULL,
    `upload_hour` varchar(255) NOT NULL,
    `weekday` varchar(255) NOT NULL,
    `day_of_month` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `gs_feed_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `feed_url` text null,
    `feed_path` text null,
    `feed_error` text null,
    `active` enum('1','0') NOT NULL DEFAULT '1',
    `date_add` DATETIME NOT NULL ,
    `date_upd` DATETIME NULL,
    `id_shop` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_gs_feed`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_PREFIX_kb_gs_audit_log` (
    `id_gs_audit_log` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `log_entry` text NOT NULL,
    `log_user` text NULL,
    `log_class_method` varchar(255) NOT NULL,
    `log_time` datetime NOT NULL,
   `id_shop` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_gs_audit_log`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_PREFIX_kb_gs_products_list` (
    `id_gs_products_list` int(10) NOT NULL AUTO_INCREMENT,
    `id_gs_profiles` int(10) NOT NULL,
    `id_product` int(10) NOT NULL,
    `reference` varchar(32) NOT NULL,
    `id_product_attribute` int(10) NOT NULL,
    `listing_status` enum('Pending','Listed','Inactive','Expired','Draft') NOT NULL DEFAULT 'Pending',
    `listing_id` varchar(255) NULL,
    `listing_image_id` varchar(255) DEFAULT NULL,
    `material` varchar(255),
    `pattern` varchar(255),
    `product_condition` varchar(255),
    `gender` varchar(255),
    `product_type` varchar(255),
    `age_group` varchar(255),
    `product_listing_method` varchar(255),
    `adult_content` varchar(255),
    `color` varchar(255),
    `size` varchar(255),
    `size_type` varchar(255),
    `size_system` varchar(255),
    `promotion_id` varchar(255),
    `is_product_info_overide` varchar(5) DEFAULT '0',
    `renew_flag` enum('0','1') NOT NULL DEFAULT '0',
    `add_flag` enum('0','1','2') NOT NULL DEFAULT '1',
    `update_flag` enum('0','1','2') NOT NULL DEFAULT '0',
    `delete_flag` enum('0','1','2') NOT NULL DEFAULT '0',
    `date_add` datetime NOT NULL,
    `date_listed` datetime NOT NULL,
    `date_last_renewed` datetime NOT NULL,
    `listing_error` text NOT NULL,
    `id_shop` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_gs_products_list`),
    KEY `listing_status` (`listing_status`,`renew_flag`,`delete_flag`),
    KEY `id_product_attribute` (`id_product_attribute`),
    KEY `id_gs_profiles` (`id_gs_profiles`)
) ENGINE=ENGINE_TYPE  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `_PREFIX_kb_gs_queue` (
    `id` int(10) UNSIGNED NOT NULL auto_increment,
    `filename` varchar(255) NULL,
    `process_date` datetime NULL,
    `current_file` varchar(255) NULL,
    `queue_status` enum("Pending", "Processing", "Completed") default "Pending",
    `queue_date` datetime NULL,
    `flag` tinyint(1) UNSIGNED NOT NULL default 0,
    `total_record` int(11) NULL,
    `processed_record` int(11) NULL,
    `type` text null,
    `position` text null,
    `id_shop` INT(11) UNSIGNED NOT NULL,
     PRIMARY KEY (`id`)
) ENGINE=ENGINE_TYPE  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
