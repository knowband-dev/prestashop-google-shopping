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

//Code to execute on window load


//Function to show listing error in FancyBox
function show_etsy_listing_error(c)
{
    $('.' + c).fancybox();
}



//Function definition to disconnect connection
function save()
{
    var url = $("#save_url").val();
    window.location.href = url;
}

//Function definition to execute job to sync prodcuts listing
function sync_products_listing()
{
    $("#sync_products_listing").attr('disabled', true);
    $(".sync_products_listing_loader").show();
    //Execute Job to sync products listing on Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_products_listing_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_products_listing_loader").hide();
                $("#sync_products_listing").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_products_listing_loader").hide();
                $("#sync_products_listing").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

//Function definition to execute job to sync feeds
function sync_feeds_listing()
{
    $("#sync_feeds_listing").attr('disabled', true);
    $(".sync_shipping_templates_loader").show();
    //Execute Job to sync products listing on Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_feed_listing_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_shipping_templates_loader").hide();
                $("#sync_feeds_listing").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_shipping_templates_loader").hide();
                $("#sync_feeds_listing").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

function sync_product_status()
{
    $("#sync_product_status").attr('disabled', true);
    $(".sync_countries_regions_loader").show();
    //Execute Job to sync products listing on Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_product_status_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_countries_regions_loader").hide();
                $("#sync_product_status").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_countries_regions_loader").hide();
                $("#sync_product_status").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

//Hide alert messages in Synchronization tab
function hide_notification()
{
    $(".synchronization").html('');
}

//Show alert messages in Synchronization tab
function show_notification(type)
{
    if (type == 'success') {
        $(".synchronization").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">x</button>' + sync_msg + '</div>');
    } else {
        $(".synchronization").html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">x</button>' + sync_fail_msg + '</div>');
    }
}

$(document).ready(function () {
	/**
	* @Author Ravi Kant Gupta
	* @date 22-09-2024
	* To enable disable the products specifcs field on change of the product override button
	*/ 
    var $override_product_info_on = $('#override_product_info_on');

    // Define the IDs of all the fields you want to toggle
    var product_listing_fields = [
        '#product_material', '#product_pattern', '#product_specifics_condition', '#product_specific_gender', '#product_specific_type',
        '#product_specific_age_group', '#product_specific_adult', '#product_specific_color', '#product_specific_size', '#product_specific_size_type',
        '#product_specific_size_system', '#product_specific_promotion_id', '#product_listing_type'
    ];
    
    function editProductFields(isEnabled) {
        product_listing_fields.forEach(function(fieldId) {
            if (isEnabled === 1) {
                // If enabled, remove disabled attribute to make the fields editable
                $(fieldId).removeAttr('disabled');
            } else {
                // If disabled, add disabled attribute to disable the fields
                $(fieldId).attr('disabled', 'disabled');
            }
        });
    }
    
    // Event listener for the 'override_product_info' switch
    $('input[name="override_product_info"]').on('change', function() {
        var isChecked = $override_product_info_on.is(':checked') ? 1 : 0; // Check if 'on' is checked
        editProductFields(isChecked); // Toggle fields based on the switch state
    });
    // Trigger the event on page load to set the initial state
    var isChecked = $override_product_info_on.is(':checked') ? 1 : 0; // Check if 'on' is checked
    editProductFields(isChecked); 
    // Function to get the value of a query parameter by name
    function getParameterByName(name) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    var idGsProductsList = getParameterByName('id_gs_products_list');

    if (idGsProductsList) {
        // Create a hidden input field dynamically using jQuery
        var input = $('<input>').attr({
            type: 'hidden',
            name: 'product_id',
            id: 'product_id',
            value: idGsProductsList
        });

        // Append the input field to the form with ID 'kb_gs_products_list_form'
        $('#kb_gs_products_list_form').append(input);
    }

    $('select[name="upload_type"]').change(function(e) {
        console.log($(this).val());
        if ($(this).val() == 'weekly') {
            $('select[name="weekday"]').closest('.form-group').show();
            $('select[name="day_of_month"]').closest('.form-group').hide();
        }else if ($(this).val() == 'monthly') {
            $('select[name="weekday"]').closest('.form-group').hide();
            $('select[name="day_of_month"]').closest('.form-group').show();
        } else {
           $('select[name="weekday"]').closest('.form-group').hide();
            $('select[name="day_of_month"]').closest('.form-group').hide();
        }
    }).change();
    
    $('input[name="gs_category"]').closest('.form-group').after($('select[name="gs_category_code"]').closest('.form-group'));
    $('select[name="gs_category_code"]').closest('.form-group').after($('.displayGSConfirm'));
    
    $body = $("body");

    $(document).on({
        ajaxStart: function() {
            $body.addClass("loading");
        },
        ajaxStop: function() {
            $body.removeClass("loading");
        }
    });
    
    $(document).on('change', '.velsof_number_field', function () {
        this.value = this.value.replace(/,/g, '.');
    });
    //for customized product title
    $('input[name="customize_product_title"]').parent().append($('#customize_product_title_block'));
    $('#customize_product_title_block').show();
    
    //for custom labels
    $('select[name="shipping[]"]').closest('.form-group').after($('.gs_profile_form_custom_fields').closest('.row'));
    $('.gs_profile_form_custom_fields').append($('input[name="custom_label_0"]').closest('.form-group'));
    $('.gs_profile_form_custom_fields').append($('input[name="custom_label_1"]').closest('.form-group'));
    $('.gs_profile_form_custom_fields').append($('input[name="custom_label_2"]').closest('.form-group'));
    $('.gs_profile_form_custom_fields').append($('input[name="custom_label_3"]').closest('.form-group'));
    $('.gs_profile_form_custom_fields').append($('input[name="custom_label_4"]').closest('.form-group'));
    $('.gs_profile_form_custom_fields_block').show();
    
    //for country field
    $('.kb_gs_profile_country_field').append($('select[name="id_country"]').closest('.form-group'));
    
//    $('select[name="gs_category_code"]').after($('#confim_cat'));
//    $('select[name="gs_category_code"]').after($('#cancel_cat'));
    
    $('select[name="id_country"]').on('change', function() {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: kb_gs_profile_controller,
            data: {
                ajaxGetCurrencyLang:true,
                ajax:true,
                id_country:$(this).val()
            },
            success: function (data) {
                if (data != '') {
                    $('#kb_gs_profiles_form').hide();
                    $('select[name="id_lang"] option').remove();
                    $('select[name="id_currency"] option').remove();
                    $('.kb-profile-form-error').hide();
                    $('.kb-profile-form-error li').remove();
                    if (!data.lang_exit || !data.currency_exit) {
                        if (!data.lang_exit) {
                            $('.kb-profile-form-error ul').append('<li>'+kb_language_not_exist+'</li>');
                        }
                        if (!data.currency_exit) {
                            $('.kb-profile-form-error ul').append('<li>'+kb_currency_not_exist+'</li>');
                        }
                        $('.kb-profile-form-error').show();
                    } else {
                        $(data.languages).each(function(key, val) {
                            $('select[name="id_lang"]').append('<option value='+val.id_lang+'>'+val.lang_name+'</option>');
                        });

                        $(data.currency).each(function(key1, val1) {
                            $('select[name="id_currency"]').append('<option value='+val1.id_currency+'>'+val1.currency_name+'</option>');
                        });
                        $('#kb_gs_profiles_form').show();
                    }
                }
            }
        });
    }).change();
    
    /**
    * Show the category dropdown at the time of the create profile.
    * GS001-09032024 gs-profile-category-selection
    * @date 09-03-2024
    * @author Ashish
    */
    if($("#id_gs_profiles").val() == undefined) {
        submitCancelGSCancel();
    }
    
    /*
    * Fixed deprecated .live() method with modern .on() method for Google Shopping Category dropdown functionality
    * @date 29-07-2025
    * @modifier Himanshu Vishwakarma
    */
    $(document).on('change', '.gs_root', function(){
        $('.displayGSConfirm').remove();
        $(this).closest('.gs_root').removeClass("gs_root subcat").addClass("gs_root");
        $(this).closest('.gs_root').next().remove();
        $(this).closest('.gs_root').next().remove();
        $(this).closest('.gs_root').next().remove();
        $(this).closest('.gs_root').next().remove();
        $(this).closest('.gs_root').next().remove();
        $(this).closest('.gs_root').next().remove();
        $(this).closest('.gs_root').next().remove();
        var cancelBtn = '<div class="form-group displayGSConfirm"><label class="col-lg-4"></label><div class="col-lg-5"><input type="button" id="confim_cat" class="btn btn-warning" onclick="submitFinalGSCat()" value="'+kb_confirm+'"><input type="button" id="cancel_cat" class="btn btn-warning" value="'+kb_cancel+'" onclick="submitCancelGSCancel()"></div></div>';
        if ($(this).val() != '') {
            $('#confim_cat').show();
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: kb_gs_profile_controller,
                data: {
                    ajaxGetGSCategory:true,
                    ajax:true,
                    gs_category_code:$(this).val()
                },
                success: function (data) {
                    if (data != '') {
                        if (data.has_child) {
                            var s1 = $('<select name="gs_subcat" class="gs_root subcat fixed-width-xl"/>');
                            $('<option value>Select</option>').appendTo(s1);
                            $(data.sub_category).each(function(key,val){
                                $('<option />', {value: val.category_code, text: val.name}).appendTo(s1);
                            });
                            /*
                            * Fixed selector to append new dropdowns to the correct parent container
                            * @date 15-01-2025
                            * @author Assistant
                            */
                            $('.kbgsCategoryField .col-lg-5').append(s1);
                        } 
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
        if ($(this).attr('name') == "gs_subcat") {
            $('.kbgsCategoryField').after(cancelBtn);
        } else if ($(this).attr('name') == "gs_category_code") {
            if ($(this).val() != '') {
                /*
                * Fixed cancel button placement for main category dropdown
                * @date 29-07-2025
                * @modifier Himanshu Vishwakarma
                */
                $('.kbgsCategoryField').after(cancelBtn);
            }
        }
    });
});

function submitFinalGSCat() {
    var str = [];
    var id_cat = [];
    
    $('.gs_root option:selected').each(function() {
        if ($(this).val() != '') {
            str.push($(this).text());
            id_cat.push($(this).val());
        }
    });
    /**
    * Show the category dropdown at the time of the create profile.
    * GS001-09032024 gs-profile-category-selection
    * @date 09-03-2024
    * @author Ashish
    */
    $("#gs_category_code_error").remove();
    if(id_cat.length == 0) {
        $('select[name="gs_category_code"]').after('<span id="gs_category_code_error" class="error_message">' + gs_category_mand + '</span>');
    } else {
        var cat_text = (str.join('>>'));
        $('#cat_text').remove();
        $('<p id="cat_text" style="margin:0px">' + cat_text + '</p>').appendTo($('select[name="gs_category_code"]').closest('.col-lg-5'));
        $('.gs_root').hide();
        
        $('input[name="gs_category"]').val(id_cat[id_cat.length-1]);
        $('#confim_cat').hide();
        $('#cancel_cat').show();
    }
}

function submitCancelGSCancel() {
    $('.gs_root').show();
    $('#confim_cat').show();
    $('#cancel_cat').hide();
    $('input[name="gs_category"]').val('');
    $('#cat_text').hide();
}