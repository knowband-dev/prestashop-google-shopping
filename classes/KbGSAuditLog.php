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
class KbGSAuditLog extends ObjectModel
{
    public $id_gs_audit_log;
    public $log_entry;
    public $log_user;
    public $log_class_method;
    public $log_time;
    public $id_shop;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'kb_gs_audit_log',
        'primary' => 'id_gs_audit_log',
        'fields' => array(
            'id_gs_audit_log' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'log_entry' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
            'log_user' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'log_class_method' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
            'log_time' => array(
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
}
