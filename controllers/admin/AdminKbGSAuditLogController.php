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

//Include Google Module Class to inherit some common functions and callbacks
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSModule.php');
require_once(_PS_MODULE_DIR_ . 'kbgoogleshopping/classes/KbGSAuditLog.php');

class AdminKbGSAuditLogController extends ModuleAdminController
{
    //Class Constructor
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'kb_gs_audit_log';
        $this->className = 'KbGSAuditLog';
        $this->identifier = 'id_gs_audit_log';
        parent::__construct();
        $this->toolbar_title = $this->module->l('Audit Log', 'AdminKbGSAuditLogController');
        $this->fields_list = array(
            'id_gs_audit_log' => array(
                'title' => $this->module->l('Log ID', 'AdminKbGSAuditLogController'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'log_entry' => array(
                'title' => $this->module->l('Action Description', 'AdminKbGSAuditLogController'),
                'float' => true,
                
            ),
            'log_user' => array(
                'title' => $this->module->l('Action User', 'AdminKbGSAuditLogController'),
                'align' => 'center'
            ),
            'log_class_method' => array(
                'title' => $this->module->l('Action Called', 'AdminKbGSAuditLogController')
            ),
            'log_time' => array(
                'title' => $this->module->l('Time of Action', 'AdminKbGSAuditLogController'),
                'type' => 'datetime'
            )
        );
        
        $this->_orderBy = 'id_gs_audit_log';
        $this->_orderWay = 'DESC';

        // Disable click action on audit log listing for read-only view. 08-06-2024
        $this->list_no_link = true;
        parent::__construct();
    }

    public function initToolbar()
    {
        /*
        * Added the message to show on the top of the page
        * @date 22-07-2026
        * @author Amit Singh
        */
        $msg1 = $this->module->l('This is a free version for demo purpose only. Kindly purchase the ');
        $link1 = $this->module->l('paid version ');
        $msg2 = $this->module->l('to use all features. Click');
        $link2 = $this->module->l('here');
        $msg3= $this->module->l(' to connect with us');
        $this->context->smarty->assign('msg1', $msg1);
        $this->context->smarty->assign('link1', $link1);
        $this->context->smarty->assign('msg2', $msg2);
        $this->context->smarty->assign('link2', $link2);
        $this->context->smarty->assign('msg3', $msg3);
       Context::getContext()->controller->warnings[] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbgoogleshopping/views/templates/admin/warning.tpl');
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }
}
