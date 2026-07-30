/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store. 
 *
 * @category  PrestaShop Module
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 */


  /*
  * To show the modal box when the user clicks on the premium features
  * @date 06-06-2024
  * @author Ravi Kant Gupta
  */

$(document).ready(function () {
    // Code to handle the toggle and show the upgrade modal box
$('#desc-kb_gs_feeds-new, #page-header-desc-kb_gs_feeds-new_template, #desc-kb_gs_profiles-new, #page-header-desc-kb_gs_profiles-new_template').on('click', function(event) {
    event.preventDefault();
    showUpgradeModal();
});

// Define the function to showUpgradeModal 
window.showUpgradeModal = function() {
    $('#kbUpgradeModal').css('display', 'block');
}

// Function to close the modal when the close button is clicked
$('#kbUpgradeModal .close').click(function() {
    $('#kbUpgradeModal').css('display', 'none');
});

// Close the modal when clicking outside of it
$(window).click(function(event) {
    if ($(event.target).is('.kb_upgrade_modal')) {
    $('#kbUpgradeModal').css('display', 'none');
    }
});

});