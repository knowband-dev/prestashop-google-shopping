{*for display error message*}
<style>
a#expand-all-prestashop_category, a#collapse-all-prestashop_category {
    display: none;
}
</style>
<div class="alert alert-danger kb-profile-form-error">
    <h4>{l s='There are some error' mod='kbgoogleshopping'}</h4>
    <ul></ul>
</div>
{*{if !empty($id_gs_profiles)}
    <div class="pull-right" style="margin-bottom: 5px; ">
        <a target="_blank" href="{$kb_gs_feed_controller|escape:'quotes':'UTF-8'}&id_gs_profiles={$id_gs_profiles|escape:'htmlall':'UTF-8'}" class="btn btn-primary"><i class="icon-rss"></i> {l s='Manage Feed' mod='kbgoogleshopping'}</a>
    </div>
{/if}*}

<div class="row">
    <div class="col-sm-12">

        <div class="panel">
            <div class="panel form-horizontal kb_gs_profile_country_field">
            </div>
            <div class="gs_profile_form">
            </div>

        </div>
    </div>
</div> 


<div class="form-group kbgsCategoryField">
    <label class="control-label col-lg-4 required">
        <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Select Google Shopping' mod='kbgoogleshopping'}">{l s='Google Shopping Category' mod='kbgoogleshopping'}</span>
    </label>
    <div class="col-lg-5">
        {if $id_gs_profiles != 0 && $id_gs_profiles != ""}
            {if isset($left_category9)}
                {if ($left_category9[0]['id_parent'] != 0)}
                    <select name="gs_subcat" class="fixed-width-xl 1">
                        <option value="">{l s='Select' mod='kbgoogleshopping'}</option>
                        {foreach $left_category9 as $leftCat9}
                            <option value="{$leftCat9['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category8) && ($left_category8['category_code'] == $leftCat9['category_code'])}selected{/if}>{$leftCat9['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {else}
                    <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                        <option value="">{l s='Select Category' mod='kbgoogleshopping'}</option>
                        {foreach $left_category9 as $leftCat9}
                            <option value="{$leftCat9['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category8) && ($left_category8['category_code'] == $leftCat9['category_code'])}selected{/if}>{$leftCat9['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {/if}

            {/if}
            {if isset($left_category7)}
                {if ($left_category7[0]['id_parent'] != 0)}
                    <select name="gs_subcat" class="fixed-width-xl gs_root 2">
                        <option value="">{l s='Select' mod='kbgoogleshopping'}}</option>
                        {foreach $left_category7 as $leftCat7}
                            <option value="{$leftCat7['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category6) && ($left_category6['category_code'] == $leftCat7['category_code'])}selected{/if}>{$leftCat7['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {else}
                    <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                        <option value="">{l s='Select Category' mod='kbgoogleshopping'}</option>
                        {foreach $left_category7 as $leftCat7}
                            <option value="{$leftCat7['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category6) && ($left_category6['category_code'] == $leftCat7['category_code'])}selected{/if}>{$leftCat7['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {/if}
            {/if}
            {if isset($left_category5)}
                {if ($left_category5[0]['id_parent'] != 0)}
                    <select name="gs_subcat" class="fixed-width-xl gs_root 3">
                        <option value="">{l s='Select' mod='kbgoogleshopping'}</option>
                        {foreach $left_category5 as $leftCat5}
                            <option value="{$leftCat5['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category4) && ($left_category4['category_code'] == $leftCat5['category_code'])}selected{/if}>{$leftCat5['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {else}
                    <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                        <option value="">{l s='Select Category' mod='kbgoogleshopping'}</option>
                        {foreach $left_category5 as $leftCat5}
                            <option value="{$leftCat5['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category4) && ($left_category4['category_code'] == $leftCat5['category_code'])}selected{/if}>{$leftCat5['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>

                {/if}
            {/if}
            {if isset($left_category3)}
                {if ($left_category3[0]['id_parent'] != 0)}
                    <select name="gs_subcat" class="fixed-width-xl gs_root 4">
                        <option value="">{l s='Select' mod='kbgoogleshopping'}</option>
                        {foreach $left_category3 as $leftCat3}
                            <option value="{$leftCat3['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category2) && ($left_category2['category_code'] == $leftCat3['category_code'])}selected{/if}>{$leftCat3['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {else}
                    <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                        <option value="">{l s='Select Category' mod='kbgoogleshopping'}</option>
                        {foreach $left_category3 as $leftCat3}
                            <option value="{$leftCat3['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category2) && ($left_category2['category_code'] == $leftCat3['category_code'])}selected{/if}>{$leftCat3['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>

                {/if}
            {/if}
            {if isset($left_category1)}
                {if ($left_category1[0]['id_parent'] != 0)}
                    <select name="gs_subcat" class="fixed-width-xl gs_root 5">
                        <option value="">{l s='Select' mod='kbgoogleshopping'}</option>
                        {foreach $left_category1 as $leftCat1}
                            <option value="{$leftCat1['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category0) && ($left_category0 == $leftCat1['category_code'])}selected{/if}>{$leftCat1['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {else}
                    <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                        <option value="">{l s='Select Category' mod='kbgoogleshopping'}</option>
                        {foreach $left_category1 as $leftCat1}
                            <option value="{$leftCat1['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category0) && ($left_category0 == $leftCat1['category_code'])}selected{/if}>{$leftCat1['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>

                {/if}
            {/if}
        {else}
            <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                <option value="">{l s='Select Category' mod='kbgoogleshopping'}</option>
                {foreach $kbgsCategory as $leftCat1}
                    <option value="{$leftCat1['category_code']|escape:'htmlall':'UTF-8'}">{$leftCat1['name']|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        {/if}
    </div>
</div>
{if $id_gs_profiles != 0}
    <div class="form-group displayGSConfirm">
        <label class="col-lg-4"></label>
        <div class="col-lg-6">
            <input type="button" id="confim_cat" class="btn btn-warning" onclick="submitFinalGSCat()" value="{l s='Confirm' mod='kbgoogleshopping'}" style="display: inline-block;">
            <input type="button" id="cancel_cat" class="btn btn-warning" value="{l s='Change' mod='kbgoogleshopping'}" onclick="submitCancelGSCancel()" style="display: none;">
        </div>
    </div>
    <script> 
    	//Commented by Ashish on 10th March 2024 as its duplicate call. Same is being called again in bottom of the page.
        //$('#confim_cat').trigger('click');
    </script>
{/if}

<div class="row gs_profile_form_custom_fields_block">
    <div class="col-sm-12">

        <div class="panel">
            <div class="panel-heading">
                {l s='Google Adsense' mod='kbgoogleshopping'}
            </div>
            {*            <div class="panel form-horizontal">*}
            {*            </div>*}
            <div class="gs_profile_form_custom_fields">
            </div>

        </div>
    </div>
</div> 
<script>
    var kb_gs_profile_controller = "{$kb_gs_profile_controller|escape:'quotes':'UTF-8'}";
    var kb_language_not_exist = "{l s='Supported language is not available for the selected country. Kindly add language' mod='kbgoogleshopping'}";
    var kb_currency_not_exist = "{l s='Supported currency is not available for the selected country. Kindly add currency' mod='kbgoogleshopping'}";
    var kb_cancel = "{l s='Change' mod='kbgoogleshopping'}";
    var kb_confirm = "{l s='Confirm' mod='kbgoogleshopping'}";
    var gs_category_mand = "{l s='Please select google shopping category & click on the confirm button to choose the category' mod='kbgoogleshopping'}";
    var store_category_mand = "{l s='Please select store category' mod='kbgoogleshopping'}";
    var kb_shipping_mand = "{l s='Please select atleast one shipping' mod='kbgoogleshopping'}";
    var kb_profile_mand = "{l s='Please select profile' mod='kbgoogleshopping'}";
/*
* Defer velovalidation.setErrorLanguage until velovalidation.js is loaded (footer)
* 01-08-2026
*/
(function () {
    function kbGsInitVelovalidation() {
        if (typeof velovalidation === 'undefined') {
            setTimeout(kbGsInitVelovalidation, 50);
            return;
        }
        velovalidation.setErrorLanguage({
        alphanumeric: "{l s='Field should be alphanumeric.' mod='kbgoogleshopping'}",
        digit_pass: "{l s='Password should contain atleast 1 digit.' mod='kbgoogleshopping'}",
        empty_field: "{l s='Field cannot be empty.' mod='kbgoogleshopping'}",
        number_field: "{l s='You can enter only numbers.' mod='kbgoogleshopping'}",            
        positive_number: "{l s='Number should be greater than 0.' mod='kbgoogleshopping'}",
        maxchar_field: "{l s='Field cannot be greater than # characters.' mod='kbgoogleshopping'}",
        minchar_field: "{l s='Field cannot be less than # character(s).' mod='kbgoogleshopping'}",
        invalid_date: "{l s='Invalid date format.' mod='kbgoogleshopping'}",
        valid_amount: "{l s='Field should be numeric.' mod='kbgoogleshopping'}",
        valid_decimal: "{l s='Field can have only upto two decimal values.' mod='kbgoogleshopping'}",
        maxchar_size: "{l s='Size cannot be greater than # characters.' mod='kbgoogleshopping'}",
        specialchar_size: "{l s='Size should not have special characters.' mod='kbgoogleshopping'}",
        maxchar_bar: "{l s='Barcode cannot be greater than # characters.' mod='kbgoogleshopping'}",
        positive_amount: "{l s='Field should be positive.' mod='kbgoogleshopping'}",
        maxchar_color: "{l s='Color could not be greater than # characters.' mod='kbgoogleshopping'}",
        invalid_color: "{l s='Color is not valid.' mod='kbgoogleshopping'}",
        specialchar: "{l s='Special characters are not allowed.' mod='kbgoogleshopping'}",
        script: "{l s='Script tags are not allowed.' mod='kbgoogleshopping'}",
        style: "{l s='Style tags are not allowed.' mod='kbgoogleshopping'}",
        iframe: "{l s='Iframe tags are not allowed.' mod='kbgoogleshopping'}",
        image_size: "{l s='Uploaded file size must be less than #.' mod='kbgoogleshopping'}",
        html_tags: "{l s='Field should not contain HTML tags.' mod='kbgoogleshopping'}",
        number_pos: "{l s='You can enter only positive numbers.' mod='kbgoogleshopping'}",
    });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', kbGsInitVelovalidation);
    } else {
        kbGsInitVelovalidation();
    }
})();
    var kb_profile_mand = "{l s='Please select Profile' mod='kbgoogleshopping'}";
    var lang_err = "{l s='You can not select default language in sync languages.' mod='kbgoogleshopping'}";
    var amount_err = "{l s='Please enter valid amount (e.g. 3.50).' mod='kbgoogleshopping'}";
    var range_err = "{l s='Please enter valid number between 1 - 10.' mod='kbgoogleshopping'}";
    var process_day_err = "{l s='Minimum Processing Days cannot be greater than or equal to Maximum Processing Days.' mod='kbgoogleshopping'}";
    var country_err = "{l s='Please choose Destination Country.' mod='kbgoogleshopping'}";
    var region_err = "{l s='Please choose Destination Region.' mod='kbgoogleshopping'}";
    var max_qty_err = "{l s='Maximum Quantity cannot be less than minimum quantity' mod='kbgoogleshopping'}";
    var min_qty_vald = "{l s='Minimum quantity cannot be greater than 999.' mod='kbgoogleshopping'}";
    var max_qty_vald = "{l s='Maximum quantity cannot be greater than 999.' mod='kbgoogleshopping'}";
    var min_qty_zero = "{l s='Quantity cannot be zero' mod='kbgoogleshopping'}";
    
    /*
    * Wrong category confirm click trigger on page load. It should be applied only in the case of edit profile becuase in the case of the edit profile, Category are prepopulated.
    * GS001-09032024 gs-profile-category-selection
    * @author Ashish
    */
    $(document).ready(function() {
        if($("#id_gs_profiles").val() != undefined) {
            $('#confim_cat').trigger('click');
        }
    });
    
</script>

<style>
    .modal {
    display:    none;
    position:   fixed;
    z-index:    1000;
    top:        0;
    left:       0;
    height:     100%;
    width:      100%;
    background: rgba( 255, 255, 255, .8 ) 
                url('{$loader|escape:'quotes':'UTF-8'}') 
                50% 50% 
                no-repeat;
}

/* When the body has the loading class, we turn
   the scrollbar off with overflow:hidden */
body.loading {
    overflow: hidden;   
}

/* Anytime the body has the loading class, our
   modal element will be visible */
body.loading .modal {
    display: block;
}
</style>
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
* Admin Synchronization tpl file
*}
