{* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2017 Knowband
* @license   see file: LICENSE.txt
*
* Description
*
* Custom Error tpl file
*}
<div class="bootstrap">
    <div class="alert alert-danger">
        {l s='Google categories are not imported.' mod='kbgoogleshopping'} <a href="{$sync_categories|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Click here' mod='kbgoogleshopping'}</a> {l s=' to import the google categories to continue.' mod='kbgoogleshopping'}
    </div>
</div>