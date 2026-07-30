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
* Admin List Action View Error tpl file
*}
<a href="#{$href|escape:'htmlall':'UTF-8'}" title="{$action|escape:'htmlall':'UTF-8'}" onclick="show_etsy_listing_error('{$href|escape:'htmlall':'UTF-8'}')" class="edit {$href|escape:'htmlall':'UTF-8'}">
	<i class="icon-{$icon|escape:'htmlall':'UTF-8'}"></i> {$action|escape:'htmlall':'UTF-8'}
</a>
<div style="display:none;" id="{$href|escape:'htmlall':'UTF-8'}">{$text|escape:'htmlall':'UTF-8'}</div>
