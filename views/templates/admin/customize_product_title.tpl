{*
* DISCLAIMER
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
* Admin Customize Product Title Placeholders tpl file
*}
<div class="alert alert-info" id="customize_product_title_block" style="display:none;">
    <h4>{l s='You can use the following place-holders to customize the product title on Google Shopping' mod='kbgoogleshopping'}</h4>
    <ul>
        <li>{'{id_product}'}{*escape not required*} {l s=' for Product ID' mod='kbgoogleshopping'}</li>
        <li>{'{product_title}'}{*escape not required*} {l s=' for Product title' mod='kbgoogleshopping'}</li>
        <li>{'{manufacturer_name}'}{*escape not required*} {l s=' for Manufacturer name' mod='kbgoogleshopping'}</li>
        <li>{'{supplier_name}'}{*escape not required*} {l s=' for Supplier name' mod='kbgoogleshopping'}</li>
        <li>{'{reference}'}{*escape not required*} {l s=' for Product Reference' mod='kbgoogleshopping'}</li>
{*        <li>{'{supplier_reference}'} {l s=' for Supplier Reference'}</li>*}
        <li>{'{ean13}'}{*escape not required*} {l s=' for Product EAN13' mod='kbgoogleshopping'}</li>
        <li>{'{short_description}'}{*escape not required*} {l s=' for Product Short Description' mod='kbgoogleshopping'}</li>
        <li>{'{price}'}{*escape not required*} {l s=' for Product Price' mod='kbgoogleshopping'}</li>
    </ul>
</div>
