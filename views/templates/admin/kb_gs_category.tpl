<div class="form-group kbgsCategoryField">
    <label class="control-label col-lg-3 required">
        <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Select Google Shopping' mod='kbgoogleshopping'}">{l s='Google Shopping Category' mod='kbgoogleshopping'}</span>
    </label>
    <div class="col-lg-9">
        {if $id_profile_category != 0}
            {if isset($left_category9)}
                {if ($left_category9[0]['id_parent'] != 0)}
                    <select name="gs_subcat" class="fixed-width-xl 1">
                        <option value>{l s='Select' mod='kbgoogleshopping'}}</option>
                        {foreach $left_category9 as $leftCat9}
                            <option value="{$leftCat9['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category8) && ($left_category8['category_code'] == $leftCat9['category_code'])}selected{/if}>{$leftCat9['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {else}
                    <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                        <option>{l s='Select Category' mod='kbgoogleshopping'}}</option>
                        {foreach $left_category9 as $leftCat9}
                            <option value="{$leftCat9['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category8) && ($left_category8['category_code'] == $leftCat9['category_code'])}selected{/if}>{$leftCat9['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {/if}

            {/if}
            {if isset($left_category7)}
                {if ($left_category7[0]['id_parent'] != 0)}
                    <select name="gs_subcat" class="fixed-width-xl gs_root 2">
                        <option value>{l s='Select' mod='kbgoogleshopping'}}</option>
                        {foreach $left_category7 as $leftCat7}
                            <option value="{$leftCat7['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category6) && ($left_category6['category_code'] == $leftCat7['category_code'])}selected{/if}>{$leftCat7['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {else}
                    <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                        <option>{l s='Select Category' mod='kbgoogleshopping'}}</option>
                        {foreach $left_category7 as $leftCat7}
                            <option value="{$leftCat7['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category6) && ($left_category6['category_code'] == $leftCat7['category_code'])}selected{/if}>{$leftCat7['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {/if}
            {/if}
            {if isset($left_category5)}
                {if ($left_category5[0]['id_parent'] != 0)}
                    <select name="gs_subcat" class="fixed-width-xl gs_root 3">
                        <option value>{l s='Select' mod='kbgoogleshopping'}}</option>
                        {foreach $left_category5 as $leftCat5}
                            <option value="{$leftCat5['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category4) && ($left_category4['category_code'] == $leftCat5['category_code'])}selected{/if}>{$leftCat5['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {else}
                    <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                        <option>{l s='Select Category' mod='kbgoogleshopping'}}</option>
                        {foreach $left_category5 as $leftCat5}
                            <option value="{$leftCat5['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category4) && ($left_category4['category_code'] == $leftCat5['category_code'])}selected{/if}>{$leftCat5['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>

                {/if}
            {/if}
            {if isset($left_category3)}
                {if ($left_category3[0]['id_parent'] != 0)}
                    <select name="gs_subcat" class="fixed-width-xl gs_root 4">
                        <option value>{l s='Select' mod='kbgoogleshopping'}}</option>
                        {foreach $left_category3 as $leftCat3}
                            <option value="{$leftCat3['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category2) && ($left_category2['category_code'] == $leftCat3['category_code'])}selected{/if}>{$leftCat3['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {else}
                    <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                        <option>{l s='Select Category' mod='kbgoogleshopping'}}</option>
                        {foreach $left_category3 as $leftCat3}
                            <option value="{$leftCat3['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category2) && ($left_category2['category_code'] == $leftCat3['category_code'])}selected{/if}>{$leftCat3['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>

                {/if}
            {/if}
            {if isset($left_category1)}
                {if ($left_category1[0]['id_parent'] != 0)}
                    <select name="gs_subcat" class="fixed-width-xl gs_root 5">
                        <option value>{l s='Select' mod='kbgoogleshopping'}}</option>
                        {foreach $left_category1 as $leftCat1}
                            <option value="{$leftCat1['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category0) && ($left_category0 == $leftCat1['category_code'])}selected{/if}>{$leftCat1['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {else}
                    <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                        <option>{l s='Select Category' mod='kbgoogleshopping'}}</option>
                        {foreach $left_category1 as $leftCat1}
                            <option value="{$leftCat1['category_code']|escape:'htmlall':'UTF-8'}" {if isset($left_category0) && ($left_category0 == $leftCat1['category_code'])}selected{/if}>{$leftCat1['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>

                {/if}
            {/if}
        {else}
            <select name="gs_category_code" class="fixed-width-xl gs_root" id="gs_category_code">
                <option value>{l s='Select Category' mod='kbgoogleshopping'}</option>
                {foreach $kbgsCategory as $leftCat1}
                    <option value="{$leftCat1['category_code']|escape:'htmlall':'UTF-8'}">{$leftCat1['name']|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        {/if}
    </div>
</div>
    
    {if $id_profile_category != 0}
    <div class="form-group displayGSConfirm">
        <label class="col-lg-3"></label>
        <div class="col-lg-9">
            <input type="button" id="confim_cat" class="btn btn-warning" onclick="submitFinalGSCat()" value="{l s='Confirm' mod='kbgoogleshopping'}" style="display: inline-block;">
            <input type="button" id="cancel_cat" class="btn btn-warning" value="{l s='Change' mod='kbgoogleshopping'}" onclick="submitCancelGSCancel()" style="display: none;">
        </div>
    </div>
             <script> 
                $('#confim_cat').trigger('click');
            </script>
{/if}

<script>
    {*$('#confim_cat').trigger('click');*}
    var kb_gs_profile_controller = "{$kb_gs_profile_controller|escape:'quotes':'UTF-8'}";
    var kb_cancel = "{l s='Change' mod='kbgoogleshopping'}";
    var kb_confirm = "{l s='Confirm' mod='kbgoogleshopping'}";
    var gs_category_mand = "{l s='Please select Google Shopping category' mod='kbgoogleshopping'}";
    var store_category_mand = "{l s='Please select store category' mod='kbgoogleshopping'}";
</script>
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
