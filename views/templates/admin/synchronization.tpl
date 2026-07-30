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

<script>
    var sync_msg = "{l s='Synchronization completed.' mod='kbgoogleshopping'}";
    var sync_fail_msg = "{l s='Synchronization failed.' mod='kbgoogleshopping'}";
</script>
<div class="bootstrap synchronization">
    
</div>


<div class="clearfix"></div>
{if $sync_type == 'api'}
<div class="row">
    <div class="col-lg-12">
        <div class="panel">                      
            <div class='panel-heading'>
                {l s='Products Synchronization' mod='kbgoogleshopping'}
            </div>
            <div class='row'>
                <div class="profileTabs col-lg-12">
                    <div class="form-group">
                        <label class="control-label col-lg-6">
                            <span class="label-tooltip">
                                {l s='Sync Products on Google Shopping' mod='kbgoogleshopping'}
                            </span>
                        </label>
                        <div class="col-lg-6">
                            <input type="hidden" name="sync_products_listing_url" id="sync_products_listing_url" value="{$sync_products_listing_url|escape:'htmlall':'UTF-8'}"/>
                            <a href="javascript://" id="sync_products_listing" class="btn btn-info" onclick="sync_products_listing()" role="button">{l s='Sync Products Listing' mod='kbgoogleshopping'}</a>
                            <span class="sync_products_listing_loader" style="    float: left;margin-right: 10px;"></span>
                            <div class="clearfix"></div>
                            <p class='help-block'>{l s='Sync Products on Google Shopping' mod='kbgoogleshopping'}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}      
{if $sync_type == 'feed'}
    <div class="row">
        
        <div class="col-md-4 col-lg-4" id="">
            <section class="panel widget loading">
                <div class="panel-heading label-tooltip" data-toggle="tooltip" data-placement="top" data-original-title="{l s='This action will prepare the PrestaShop products to sync on Google.' mod='kbgoogleshopping'}">
                    {l s='Local Sync' mod='kbgoogleshopping'}
                </div>
                <div align="center">
                    <a href="{$sync_local_url|escape:'htmlall':'UTF-8'}" class="btn btn-info" target="_blank" role="button">{l s='Local Sync' mod='kbgoogleshopping'}</a>
                </div>
            </section>
        </div>
        
        <div class="col-md-4 col-lg-4" id="">
            <section class="panel widget loading">
                <div class="panel-heading label-tooltip" data-toggle="tooltip" data-placement="top" data-original-title="{l s='This action will sync the products on Google Shopping.' mod='kbgoogleshopping'}">
                    {l s='Sync Feeds' mod='kbgoogleshopping'}
                </div>
                <div align="center">
                    <a href="{$sync_feed_listing_url|escape:'htmlall':'UTF-8'}" class="btn btn-info" target="_blank" role="button">{l s='Product Sync' mod='kbgoogleshopping'}</a>
                </div>
            </section>
        </div>
        
        <div class="col-md-4 col-lg-4" id="">
            <section class="panel widget loading">
                <div class="panel-heading label-tooltip" data-toggle="tooltip" data-placement="top" data-original-title="{l s='This action will sync the listing status of products on Google.' mod='kbgoogleshopping'}">
                    {l s='Sync Product Status' mod='kbgoogleshopping'}
                </div>
                <div align="center">
                    <a href="{$sync_product_status_url|escape:'htmlall':'UTF-8'}" class="btn btn-info" target="_blank" role="button">{l s='Sync Product Status' mod='kbgoogleshopping'}</a>
                </div>
            </section>
        </div>
        
        
    </div>
{/if}

<div class="clearfix"></div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel">                      
            <div class='panel-heading'>
                {l s='Cron Instructions' mod='kbgoogleshopping'}
            </div>
            <div class='row'>
                <!--
                <p>
                    {l s='Add the cron to your store via control panel/putty to synchronize data between your store and Google Shopping. Please find the instructions to setup crons below -' mod='kbgoogleshopping'}
                </p>
                <br>
                <p>
                    <b>{l s='URLs to Add to Cron via Control Panel' mod='kbgoogleshopping'}</b>
                </p>
                {if $sync_type == 'api'}
                    <p> <b>{l s='Products Listing Synchronization' mod='kbgoogleshopping'}</b> - {$sync_products_listing_url|escape:'htmlall':'UTF-8'}</p>
                {else}
                    <p> <b>{l s='Feed Synchronization' mod='kbgoogleshopping'}</b> - {$sync_feed_listing_url|escape:'htmlall':'UTF-8'}</p>
                {/if}
                -->
                <p> <b>{l s='Products Listing Synchronization' mod='kbgoogleshopping'}</b> - {$sync_feed_listing_url|escape:'htmlall':'UTF-8'}</p>
                <p> <b>{l s='Product Status Synchronization' mod='kbgoogleshopping'}</b> - {$sync_product_status_url|escape:'htmlall':'UTF-8'}</p>
                <br>
                
                <p>
                    <b>{l s='Cron setup via SSH' mod='kbgoogleshopping'}</b>
                </p>
                <!--
                {if $sync_type == 'api'}
                <p><b>{l s='Products Listing Synchronization' mod='kbgoogleshopping'}</b> - 0 */8 * * * curl -O /dev/null {$sync_products_listing_url|escape:'htmlall':'UTF-8'}</p>
                {else}
                    <p> <b>{l s='Feed Synchronization' mod='kbgoogleshopping'}</b> - 0 */12 * * * curl -O /dev/null {$sync_feed_listing_url|escape:'htmlall':'UTF-8'}</p>
                {/if}
                -->
                <p> <b>{l s='Products Listing Synchronization' mod='kbgoogleshopping'}</b> - 0 * * * * curl -O /dev/null {$sync_feed_listing_url|escape:'htmlall':'UTF-8'}</p>                
                <p> <b>{l s='Product Status Synchronization' mod='kbgoogleshopping'}</b> - 0 */12 * * * curl -O /dev/null {$sync_product_status_url|escape:'htmlall':'UTF-8'}</p>
                 
            </div>
    </div>
</div>