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

$(document).ready(function () {
    // $('#disconnect_btn').hide();

 /**
 * To show and hide the connection input fields on change of the automatic upload feed switch
 * @date 17-09-2024
 * @author Ravi Kant Gupta
 */
        // Get the switch elements by their IDs
        var $automaticUploadOn = $('#automatic_upload_on');

        // Fields to hide/show
        var connection_settings_fields = ['#application_name', '#client_id', '#client_secret', '#merchant_id'];
    
        // Function to toggle visibility based on switch state
        function connectionsFields(isEnabled) {
            connection_settings_fields.forEach(function(fieldId) {
                if (isEnabled === 1) {
                                     // If enabled , show all fields
                                     $(fieldId).closest('.form-group').show();
                                     $('#submit_gs_connect_btn').show();
                                     $('#submit_gs_renewconnect_btn').show();
                } else {

                       // If disabled , hide the fields
                       $(fieldId).closest('.form-group').hide();
                       $('#submit_gs_connect_btn').hide();
                       $('#submit_gs_renewconnect_btn').hide();
                }
            });
        }

    
        // Listen for changes in the switch
        $('input[name="automatic_upload"]').on('change', function() {
            var isChecked = $automaticUploadOn.is(':checked') ? 1 : 0; // Check if 'on' is checked
            connectionsFields(isChecked); // Toggle fields based on the switch state
        });

    
        // Initial toggle based on the current switch state (on page load)
        var initialState = $automaticUploadOn.is(':checked') ? 1 : 0; // Check if 'on' is checked initially
        connectionsFields(initialState); // Apply the toggle initially

        isChecked = $('#automatic_upload_on').is(':checked');
    if (isChecked) {
        $('#submit_gs_connect_btn').show();
        $('#submit_gs_renewconnect_btn').show();
    } else {
        $('#submit_gs_connect_btn').hide();
        $('#submit_gs_renewconnect_btn').hide();
    }
    
    $('#submit_gs_connect_btn112').hide();
    
    $('#submit_gs_connectionform').click(function () {
 // If the automatic sync is off then skip the input fields validation
        isChecked = $('#automatic_upload_on').is(':checked');
        if (!isChecked) {
            $('#submit_gs_connect_btn').hide();
            $('#submit_gs_renewconnect_btn').hide();
            $('#savebtn').val('saveConfiguration');
            $('#submit_gs_connectionform').attr('disabled', 'disabled');
            $('#submit_gs_connect_btn').attr('disabled', 'disabled');
            $('#configuration_form').submit();
        } else {
        if (validate_connectionForm() == true) {
            return false;
        } else {
            $('#savebtn').val('saveConfiguration');
            $('#submit_gs_connectionform').attr('disabled', 'disabled');
            $('#submit_gs_connect_btn').attr('disabled', 'disabled');
            $('#configuration_form').submit();
        }
        }
    });
    $('#submit_gs_connect_btn').click(function() {
        if (validate_connectionForm() == true) {
            return false;
        } else {
            $('#configuration_form').attr('target','_blank');
            $('#savebtn').val('saveConnect');
            $('#submit_gs_connect_btn').attr('disabled', 'disabled');
            $('#submit_gs_connectionform').attr('disabled', 'disabled');
            $('#configuration_form').submit();
        }
    });
    $('#submit_gs_renewconnect_btn').click(function() {
        if (validate_connectionForm() == true) {
            return false;
        } else {
//            $('#configuration_form').attr('target','_blank');
            $('#savebtn').val('saveRenew');
            $('#submit_gs_renewconnect_btn').attr('disabled', 'disabled');
            $('#submit_gs_connectionform').attr('disabled', 'disabled');
            $('#configuration_form').submit();
        }
    });
    
    $('button[name="submitAddkb_gs_profiles"]').click(function() {
        var error = false;
        $('.error_message').remove();
        $('input[name="profile_title"]').removeClass('error_field');
        $('input[name="customize_product_title"]').removeClass('error_field');
        $('select[name="gs_category_code"]').removeClass('error_field');
        $('select[name="shipping[]"]').removeClass('error_field');
        $('input[name="prestashop_category[]"]').closest('.panel').removeClass('error_field');
        
        var title_mand = velovalidation.checkMandatory($('input[name="profile_title"]'));
        if (title_mand != true) {
            error = true;
            $('input[name="profile_title"]').addClass('error_field');
            $('input[name="profile_title"]').after('<span class="error_message">' + title_mand + '</span>');
        }
        
        if ($('input[name="gs_category"]').val() == '') {
            error = true;
            $('select[name="gs_category_code"]').addClass('error_field');
            $('select[name="gs_category_code"]').after('<span class="error_message">' + gs_category_mand + '</span>');
        }
//        console.log($('input[name="prestashop_category[]"]:checked').val());
        if ((typeof $('input[name="prestashop_category[]"]:checked').val()) == 'undefined') {
            error = true;
           $('input[name="prestashop_category[]"]').closest('.panel').addClass('error_field');
            $('input[name="prestashop_category[]"]').closest('.col-lg-9').append('<span class="error_message">' + store_category_mand + '</span>');
        }
        
        var customize_product_title_mand = velovalidation.checkMandatory($('input[name="customize_product_title"]'));
        if (customize_product_title_mand != true) {
            error = true;
            $('input[name="customize_product_title"]').addClass('error_field');
            $('input[name="customize_product_title"]').after('<span class="error_message">' + customize_product_title_mand + '</span>');
        }
        
//        var shipping_mand = velovalidation.checkMandatory($('select[name="shipping[]"]'));
        if ($('select[name="shipping[]"]').val() == null) {
            error = true;
            $('select[name="shipping[]"]').addClass('error_field');
            $('select[name="shipping[]"]').after('<span class="error_message">' + kb_shipping_mand + '</span>');
        }
        
        
        if (error) {
            return false;
        } else {
            $('#kb_gs_profiles_form').append('<input type="hidden" name="id_country" value="'+$('select[name="id_country"]').val()+'">');
            $('button[name="submitAddkb_gs_profiles"]').attr('disabled', 'disabled');
            $('#kb_gs_profiles_form').submit();
        }
    });
    
    $('button[name="submitAddconfiguration"]').click(function() {
        var error = false;
        $('.error_message').remove();
        $('input[name="exclude_product_less"]').closest('.input-group').removeClass('error_field');
        $('input[name="utm_campaign"]').removeClass('error_field');
        $('input[name="utm_medium"]').removeClass('error_field');
        $('input[name="utm_source"]').removeClass('error_field');
        
        var exclude_product_less_amt = velovalidation.checkAmount($('input[name="exclude_product_less"]'));
        if (exclude_product_less_amt != true) {
            error = true;
            $('input[name="exclude_product_less"]').closest('.input-group').addClass('error_field');
            $('input[name="exclude_product_less"]').closest('.input-group').after('<span class="error_message">' + exclude_product_less_amt + '</span>');
        }
        
        
        var utm_campaign_mand = velovalidation.checkHtmlTags($('input[name="utm_campaign"]'));
        if (utm_campaign_mand != true) {
            error = true;
            $('input[name="utm_campaign"]').addClass('error_field');
            $('input[name="utm_campaign"]').after('<span class="error_message">' + utm_campaign_mand + '</span>');
        }
        
        var utm_source_mand = velovalidation.checkHtmlTags($('input[name="utm_source"]'));
        if (utm_source_mand != true) {
            error = true;
            $('input[name="utm_source"]').addClass('error_field');
            $('input[name="utm_source"]').after('<span class="error_message">' + utm_source_mand + '</span>');
        }
        
        var utm_medium_mand = velovalidation.checkHtmlTags($('input[name="utm_medium"]'));
        if (utm_medium_mand != true) {
            error = true;
            $('input[name="utm_medium"]').addClass('error_field');
            $('input[name="utm_medium"]').after('<span class="error_message">' + utm_medium_mand + '</span>');
        }
        
        
        
        if (error) {
            return false;
        } else {
//            $('#kb_gs_profiles_form').append('<input type="hidden" name="id_country" value="'+$('select[name="id_country"]').val()+'">');
            $('button[name="submitAddconfiguration"]').attr('disabled', 'disabled');
            $('#configuration_form').submit();
        }
    });
    
    $('button[name="submitAddkb_gs_feeds"]').click(function(){
        var error = false;
        $('.error_message').remove();
        $('input[name="feed_label"]').removeClass('error_field');
        $('select[name="id_gs_profiles"]').removeClass('error_field');
        
        if ($('select[name="id_gs_profiles"]').val() == '') {
            error = true;
            $('select[name="id_gs_profiles"]').addClass('error_field');
            $('select[name="id_gs_profiles"]').after('<span class="error_message">' + kb_profile_mand + '</span>');
        }
        
        var feed_label_mand = velovalidation.checkMandatory($('input[name="feed_label"]'));
        
        if (feed_label_mand != true) {
            error = true;
            $('input[name="feed_label"]').addClass('error_field');
            $('input[name="feed_label"]').after('<span class="error_message">' + feed_label_mand + '</span>');
        }
        
        
        if (error) {
            return false;
        } else {
//            $('#kb_gs_profiles_form').append('<input type="hidden" name="id_country" value="'+$('select[name="id_country"]').val()+'">');
            $('button[name="submitAddkb_gs_feeds"]').attr('disabled', 'disabled');
            $('#kb_gs_feeds_form').submit();
        }
    });
    
    $('button[name="submitAddkb_gs_category_mapping"]').click(function() {
        var error = false;
        $('.error_message').remove();
        $('select[name="gs_category_code"]').removeClass('error_field');
        $('select[name="shipping[]"]').removeClass('error_field');
        $('input[name="prestashop_category[]"]').closest('.panel').removeClass('error_field');

        
        if ($('input[name="gs_category"]').val() == '') {
            error = true;
            $('select[name="gs_category_code"]').addClass('error_field');
            $('select[name="gs_category_code"]').after('<span class="error_message">' + gs_category_mand + '</span>');
        }
//        console.log($('input[name="prestashop_category[]"]:checked').val());
        if ((typeof $('input[name="prestashop_category[]"]:checked').val()) == 'undefined') {
            error = true;
           $('input[name="prestashop_category[]"]').closest('.panel').addClass('error_field');
            $('input[name="prestashop_category[]"]').closest('.col-lg-9').append('<span class="error_message">' + store_category_mand + '</span>');
        }
        
        
        if (error) {
            return false;
        } else {
//            $('#kb_gs_category_mapping_form').append('<input type="hidden" name="id_country" value="'+$('select[name="id_country"]').val()+'">');
            $('button[name="submitAddkb_gs_category_mapping"]').attr('disabled', 'disabled');
            $('#kb_gs_category_mapping_form').submit();
        }
    });

    /**
     *  To show and hide the instructions on the connection page
     * @date 27-11-2024
     * @author Ravi Kant Gupta
     */
         const toggleButton = document.querySelector(".instructions-toggle");
         const content = document.querySelector(".instructions-content");
         const icon = toggleButton.querySelector(".toggle-icon");
 
         toggleButton.addEventListener("click", function () {
             if (content.style.display === "none" || !content.style.display) {
                 content.style.display = "block";
                 icon.innerHTML = "&#9652;"; // Up arrow
             } else {
                 content.style.display = "none";
                 icon.innerHTML = "&#9662;"; // Down arrow
             }
         });
    
});

//Function definition to disconnect connection
function disconnect()
{
     if (validate_connectionForm() == true) {
        return false;
    } else {
        var url = $("#disconnect_url").val();
        window.location.href = url;
    }
}

function validate_connectionForm()
{
    var error = false;
    $('.error_message').remove();
    $('input[name="application_name"]').removeClass('error_field');
    $('input[name="client_id"]').removeClass('error_field');
    $('input[name="client_secret"]').removeClass('error_field');
    $('input[name="merchant_id"]').removeClass('error_field');

    var application_name_error = velovalidation.checkMandatory($('input[name="application_name"]'));
    var client_id_error = velovalidation.checkMandatory($('input[name="client_id"]'));
    var client_secret_error = velovalidation.checkMandatory($('input[name="client_secret"]'));
    var merchant_id_error = velovalidation.checkMandatory($('input[name="merchant_id"]'));
    var merchant_id_numeric = velovalidation.isNumeric($('input[name="merchant_id"]'));

    if (application_name_error != true) {
        error = true;
        $('input[name="application_name"]').addClass('error_field');
        $('input[name="application_name"]').after('<span class="error_message">' + application_name_error + '</span>');
    }
    if (client_id_error != true) {
        error = true;
        $('input[name="client_id"]').addClass('error_field');
        $('input[name="client_id"]').after('<span class="error_message">' + client_id_error + '</span>');
    }
    if (client_secret_error != true) {
        error = true;
        $('input[name="client_secret"]').addClass('error_field');
        $('input[name="client_secret"]').after('<span class="error_message">' + client_secret_error + '</span>');
    }
    if (merchant_id_error != true) {
        error = true;
        $('input[name="merchant_id"]').addClass('error_field');
        $('input[name="merchant_id"]').after('<span class="error_message">' + merchant_id_error + '</span>');
    } else {        
        if (merchant_id_numeric != true) {
            error = true;
            $('input[name="merchant_id"]').addClass('error_field');
            $('input[name="merchant_id"]').after('<span class="error_message">' + merchant_id_numeric + '</span>');
        }
    }
    
    return error;
}

$(document).ready(function () {
        // Code to handle the toggle and show the upgrade modal box
    $('#exclude_out_of_stock_off, #exclude_out_of_stock_on').change(function(event) {
        event.preventDefault();
        showUpgradeModal();
    });
    $('#exclude_product_less, #utm_medium, #utm_source, #utm_campaign').on('input', function(event) {
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