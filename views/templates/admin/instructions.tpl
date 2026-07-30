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
* Admin instruction for adding redirection URL in tpl file
*}

<div class="row">
    <div class="col-lg-12">
        <div class="panel">
            <div class="panel-heading instructions-toggle" style="cursor: pointer;">
                {l s='Instructions for connecting the module to Google Merchant Center. Click here to read more.' mod='kbgoogleshopping'}
                                <span class="toggle-icon">&#9662;</span>

            </div>
            <div class="row instructions-content" style="display: none;">
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='1. Go to ' mod='kbgoogleshopping'} <a href="https://console.cloud.google.com/" target="_blank">{l s='Google Cloud console' mod='kbgoogleshopping'} </a></p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='2. Sign in to your Google account.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='3. In the top-left corner, click on the hamburger menu (three horizontal lines) to expand the sidebar.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='4. Click on "API & Services" to expand the submenu, then select "Dashboard".' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='5. Click on the "+ ENABLE APIS AND SERVICES" button at the top center of the page.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='6. In the API Library, search for "Content API for Shopping".' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='7. Click on "Content API for Shopping" from the search results.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='8. Click the "Enable" button to activate the API for your project.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='9. Once enabled, go back to the API & Services dashboard.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='10. Now, navigate to "Credentials" on the left sidebar.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='11. Click on "+ CREATE CREDENTIALS" and select "OAuth client ID".' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='12. Select the Application type as "Web application".' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='13. Now under "Authorized redirect URIs", click on the "+ ADD URI" button.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='14. Enter the below URL for redirection:' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>{$redirect_url|escape:'htmlall':'UTF-8'}</b></p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='15. Click on the Create Button.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='16. Now, use the Client ID and Client Secret for module connection.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='17. Next, to obtain your Merchant ID, go to the Google Merchant Center.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='18. Sign in or create an account if you don\'t have one.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='19. Once in the Merchant Center dashboard, click on "Settings" (gear icon) in the top right corner.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='20. Under "Business information", you will find your Merchant ID listed at the top of the page.' mod='kbgoogleshopping'}</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='21. Copy the Merchant ID for use in module configuration.' mod='kbgoogleshopping'}</p>
                <br>
            </div>
            <div class="panel-heading">
                <p>&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://drive.google.com/file/d/1l1H-EWmTeX2XMnBejs7QK6PKjtxZtqky/view?usp=sharing" target="_blank">{l s='Click Here' mod='kbgoogleshopping'} </a>{l s='to watch the connection steps video.' mod='kbgoogleshopping'} </p>
            </div>
        </div>
    </div>
    <div class="modal"></div>
</div>



