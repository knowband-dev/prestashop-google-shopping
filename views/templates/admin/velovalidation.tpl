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
* Admin Velovalidation tpl file
*}

{if isset($is_redirect_url)} 
    <div class="alert alert-warning">
        <p><b>{l s='Your Authorized Redirect URI is:' mod='kbgoogleshopping'}</b><br>{$redirect_url|escape:'quotes':'UTF-8'}</p>
        <p>{l s='Set the above url in <b>Authorized redirect URIs</b> field of your google developer account before getting refresh token. Please see appendix in user manual.' js=1 mod='kbgoogleshopping'}</p>
        <p>
            
                {*{if $token_info_expired}
                    {l s='Your refresh token has been expired. click to reconnect' mod='kbgoogleshopping'} 
                {/if}*}
        </p>
    </div>
{/if}

<script>
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
    var kb_profile_mand = "{l s='Please select Profile' mod='kbgoogleshopping'}";
</script>