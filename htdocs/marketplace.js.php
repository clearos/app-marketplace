<?php

/**
 * Javascript helper for Marketplace.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage javascript
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('marketplace');
clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type: application/x-javascript');

echo "
var apps_to_display_per_page = 10;
var reg_info_ok = false;
var installation_complete = '" . lang('marketplace_installation_complete') . "';
var my_systems = new Array();
var my_subscriptions = new Array();
var novice_index = 0;
var novice_optional_apps = [];
var in_wizard_or_novice = false;
//TODO Translate
var novice_set = [
    {
        search:'99_directory', exclusive: true, title:'Directory Services',
        description:'A directory service stores, organizes and provides access to information about your users, groups, networked devices and more.', helptitle: 'Directory Services', helpcontent: '<p>The options listed under Directory Services are mutually exclusive - you can select one or the other...not both.</p><p>If you do not have existing Microsoft server infrastructure running Windows Active Directory&trade;, you will almost certainly want to select the OpenLDAP-based directory server.</p>'
    },
    {
        search:'99_networking', exclusive: false, title:'Firewall and Networking',
        description:'ClearOS can be used as a router or in standalone mode on your LAN to deliver firewall and/or networking infrastructure.  A firewall is the first (and very effective) line of defense in preventing outsiders unauthorized access to your server and any devices that may be connected to the network.', helptitle: 'Firewall and Networking', helpcontent: '<p>Firewall capabilities (port forwarding, DMZ etc.) are split into individual apps to keep thing simple and intuitive in the User Interface.</p><p>Virtual Private Networking (VPN) allows remote users to securely connect to your network in order to access resources as if they were on-site.'
    },
    {
        search:'99_security', exclusive: false, title:'Perimeter Security',
        description:'Is your ClearOS server acting as a gateway to the Internet for connected devices on your Local Area Network (LAN)?  If so, implementing effective perimeter security measures is highly recommended.', helptitle: 'Intrusion Protection', helpcontent: 'Intrusion protection consists of both an active (blocking) and passive (logging) components.  Attack vector identification and prevention is only as good as the signatures used to filter traffic.'
    },
    {
        search:'99_filter', exclusive: false, title:'Web Content Filter',
        description:'The ClearOS web proxy and content filter gives administrators clear visibility into web traffic on the network and allows organizations and business to restrict content to achieve compliance (eg. CIPA), block phishing and sites hosting malware and increase produtivity.', helptitle: 'Proxy/Filter for Web', helpcontent: '<p>Many of the core apps that combine to provide a web proxy filter solution complete with filtering, group policy support and malware detection are completely free to use.</p><p>Subscribing to the paid Content Filter Blacklist gives administrators over 100 categories, millions of domain classifications and a continually updating database to track new sites that come online everyday.</p><p>The filter engine has plugins for supporting apps from both ClearCenter (antimalware updates) and a leading commercial AV solution (Kaspersky Labs).</p>'
    },
    {
        search:'99_mail', exclusive: true, title:'Groupware / E-Mail',
        description:'Planning on running group collaboration and/or Email services or need to integrate with Google Apps?  ClearOS offers 4 variants for hosting your messaging services locally or in the cloud.', helptitle: 'Groupware/E-Mail', helpcontent: '<p>The ClearOS Marketplace currently supports four solutions for providing email and groupware services.</p><p>If you are already a Google Apps subscriber or wish to migrate email services to Google Apps, the Google Apps synchronization tool is an optional but useful app for synchronizing and provisioning accounts stored locally in OpenLDAP directory with Google Apps.</p><p>Cyrus provides a robust and lightweight IMAP(S)/POP(S) service for hosting a mail server without a web-based GUI (eg. use of mail client like Outlook&trade;, Thunderbird etc.).</p><p>Zarafa Community is a full groupware solution intended for home users, while Zarafa Small Business is positioned as the best-selling open-source drop-in Exchange replacement.</p>'
    },
    {
        search:'99_disaster', exclusive: false, title:'Disaster Prevention and Recovery',
        description:'In any environment, downtime - or worse, permanent loss of data - is simply not an option.  ClearOS has a wide range of apps to prevent data-loss events from occurring or to recover from one should it happen.  Whether your server instance is cloud-based or on-premise, planning and enforcing policies to protect your data is crucial.', helptitle: 'Disaster Recovery', helpcontent: '<p>Storing data in a centralized location and backing up off-site constitutes best-practises for ensuring you never lose data.  While RAID and monitoring are excellent preventative measures, it is important to realize they do not constitute a backup policy.</p><p>True backup requires multiple snapshots along with retention and recycling algorithms to prevent data loss under any condition - either natural or human error.</p>'
    },
    {
        search:'99_home', exclusive: false, title:'Home Networking',
        description:'ClearOS makes a perfect home networking gateway or server.  The apps below have been selected based on their suitability for the home environment.', helptitle: 'Home Environment - An Important Market', helpcontent: '<p>You\'d be surprised how many referrals we get from users who have installed ClearOS in their home and then advise friends or colleagues about running ClearOS in their place of business.</p><p>If ClearOS has found a place in your home networking environment, please help spread awareness and keep the development and community going strong by spreading the word.</p>'
    }
];

var realtime = false;

$(document).ready(function() {
    if ($(location).attr('href').match('.*marketplace\/wizard\/selection\/.*$') != null)
        window.location = '/app/marketplace/wizard';

    // Wizard next button handling
    //----------------------------

    $('#wizard_nav_next').on('click', function(e) {
        if ($('#mode1').length > 0 
            && (
                !$('#mode1').hasClass('btn-primary')
                || !$('#mode2').hasClass('btn-primary')
                || !$('#mode3').hasClass('btn-primary')
                || !$('#mode4').hasClass('btn-primary')
            )
        ) {
            // At least one mode button does not have primary meahave primary class means none selected
            $('#wizard_next_showstopper').remove();
        }
        if ($('#wizard_next_showstopper').length != 0) {
            e.preventDefault();
            clearos_modal_infobox_open('wizard_next_showstopper');
        }
    });

    $('.marketplace_wizard_mode').on({
        click: function(e) {
            e.preventDefault();
            var mySelector = this;
            $('#wizard_marketplace_mode').val(mySelector.id);
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '/app/marketplace/wizard/set_mode',
                data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&mode=' + $('#wizard_marketplace_mode').val()
            });
            for (index = 1; index <=4; index++) {
                if (this.id == 'mode' + index) {
                    $('#mode' + index).html('" . lang('marketplace_selected') . "');
                    $('#mode' + index).removeClass('btn-primary');
                    $('#mode' + index).addClass('btn-secondary');
                } else {
                    $('#mode' + index).html('" . lang('base_select') . "');
                    $('#mode' + index).addClass('btn-primary');
                    $('#mode' + index).removeClass('btn-secondary');
                }
            }
        }
    });
    if ($('#marketplace-novice').length > 0) {
        $('.novice-select').on({
            click: function() {
                $('#app-search-load').show();
                $('#marketplace-app-container').html('');
                novice_index = this.id.replace('novice-', '');
                get_novice_set();
            }
        });
    }
    if ($('#wizard_marketplace_mode').val() == 'mode2') {

        // Add help content
        $('#inline-help-title-0').html('" . lang('marketplace_categories') . "');
        $('#inline-help-content-0').html(
            '<p>" . lang('marketplace_mode_category_help') . "</p>' +
            '<p>" . lang('marketplace_mode_category_best_practices_help') . "</p>'
        );
        $('.category-select').on({
            click: function() {
                $('#app-search-load').show();
                $('#marketplace-app-container').html('');
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: '/app/marketplace/ajax/set_search',
                    data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&category=' + this.id.replace('category-', ''),
                    success: function(data) {
                        get_apps(false, 0);
                    },
                    error: function(xhr, text, err) {
                        // Don't display any errors if ajax request was aborted due to page redirect/reload
                        if (xhr['abort'] == undefined)
                            clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
                    }
                });
            }
        });
    }
    if ($('#wizard_marketplace_mode').val() == 'mode3') {
        $('#theme-help-box-container').remove();
        $('#inline-help-title-0').html('" . lang('marketplace_quick_select_file') . "');
        $('#inline-help-content-0').html(
            '<p>" . lang('marketplace_mode_qsf_help') . "</p>' +
            '<p>" . lang('marketplace_mode_qsf_best_practices_help') . "</p>'
        );
    }

    if ($('#search').val() != '' && $('#search').val() != '" . lang('marketplace_search_terms') . "') {
        // Change search icon to cancel and add hidden input
        $('.marketplace-search-bar').addClass('marketplace-search-bar-cancel');
        $('.marketplace-search-bar').append('<input type=\'hidden\' name=\'search_cancel\' value=\'cancel\'>');
    }

    if ($(location).attr('href').match(/.*\/install($|\/.*|\#$)/) != null && $('#total').val() > 0) {
        $('#theme_wizard_nav_next').hide();
    } else if ($(location).attr('href').match(/.*\/install($|\/.*|\#$)/) != null && $('#num_of_apps').val() == 0) {
        $('#free_checkout').hide();
    }

    if ($(location).attr('href').match('.*marketplace\/progress') != null) {
        $('#theme_wizard_nav').hide();
        $('#theme_wizard_complete').show();
    }
    
    if ($('#number_of_apps_to_display').length != 0)
        apps_to_display_per_page = $('#number_of_apps_to_display').val();

    if ($(location).attr('href').match(/.*\/progress($|\/.*|\#$)/) != null) {
        get_progress();
    } else if ($(location).attr('href').match(/.*\/install($|\/.*|\#$)/) != null) {
        if ($('#total').val() == 0) {
            allow_noauth_mods();
            $('#account-information-container').remove();
        } else {
            $('#infotable').show();
            auth_options.reload_after_auth = true;
            auth_options.use_full_path_on_redirect = true;
            clearos_is_authenticated();
            get_account_info(false);
        }
    }

    $('input[name=payment_method]').on('change', function(e) {
        toggle_payment_display();
    });

    $('#toggle_select').on('click', function(e) {
        e.preventDefault();
        var options = new Object();
        options.classes = 'theme-button-change';
        $('#toggle_select').html(clearos_loading(options));
        var toggle = 'all';
        if (!$('#toggle_select').attr('href').match('.*all$'))
            toggle = 'none';
        var apps = Array();
        $.each($('#form_app_list input[type=\'checkbox\']'), function (index, value) {
            if (toggle == 'all') {
                apps[index] = {state: '1', id: this.id.replace('select-', '')};
            } else {
                apps[index] = {state: '0', id: this.id.replace('select-', '')};
            }
        });
        bulk_cart_update(JSON.stringify(apps), toggle);
    });

    $('#novice-learn-more-action').on('click', function(event) {
        $('#novice-learn-more-modal').modal({show: true, backdrop: 'static'});
    });
    $('.filter-event').on('change', function(event) {
        this.form.submit();
    });

    $('.eula-link').click(function(e) {
        e.preventDefault();
        // chop off eula- (5 characters) to get EULA ID
        // chop off basename- (9 characters) to get basename
        get_eula($('#' + this.id).parent().attr('id').substr(9, $('#' + this.id).parent().attr('id').length), this.id.substr(5, this.id.length));
    });

    $('.marketplace-search-bar').click(function (e) {
        e.preventDefault();
        $('.marketplace-search-bar').closest('form').submit();
    });
//    $('#uninstall-app-confirm').click(function (e) {
//        e.preventDefault();
//        clearos_modal_infobox_open('confirm-app-uninstall');
//    });
    $('input').click(function (e) {
        if (this.id == 'add_review')
            add_review($('#app_name_title').html());
        else if (this.id == 'prevent_review')
            prevent_review();
        else if (this.id == 'cancel_review')
            $('#review-form').modal({show: true, backdrop: 'static'});
        else if (this.id == 'indiv_upgrade')
            update_cart($('#basename').val(), this.id, true, true);
        else if (this.id == 'indiv_buy')
            update_cart($('#basename').val(), this.id, true, true);
        else if (this.id == 'indiv_install')
            update_cart($('#basename').val(), this.id, true, true);
        else if (this.id == 'free_checkout')
            checkout(e, 'free');
        else if (this.id == 'buy_checkout')
            checkout(e, 'paid');
        else if (this.id == 'eval_checkout')
            checkout(e, 'eval');
        else if (this.id.match('^delete-'))
            remove_from_checkout(this.id.substr(7, this.id.length));
    });

});

function clear_entry() {
    if ($('#search').val() == '" . lang('marketplace_search_terms') . "')
        $('#search').val('');
}

function get_account_info(userinit) {

    // Hide any prior info
    $('#info').html('');

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/get_account_info',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token'),
        success: function(data) {
            update_install_form(data);
        },
        error: function(xhr, text, err) {
            // Don't display any errors if ajax request was aborted due to page redirect/reload
            if (xhr['abort'] == undefined)
                clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

function update_install_form(data) {

    // If no apps are selected, no need to continue
    if ($('#noapps').val() != undefined)
        return;
    if (data.code == 0) {
        if ($('#total').val() > 0) {
            clearos_loaded('account-information-container');
            // Use _text here since control is hidden
            $('#username_text').html(data.sdn_username);
            $('#billing_cycle_field').show();
            $('#display_total_field').show();
            $('#display_total_text').html(data.currency + ' ' + (parseFloat($('#total').val())).toFixed(2).toLocaleString());
            if (data.evaluation) {
                $('#billing_cycle_field').html('" . lang('marketplace_not_applicable') . " - " . lang('marketplace_trial_in_progress') . "');
                $('#notes_field').show();
                $('#notes_text').html('<div>" . lang('marketplace_note_evaluation_and_payment') . "</div>');
            } else {
                $('#billing_cycle_text').html(clearos_format_date(data.billing_cycle, 'MMMM d, yyyy'));
            }
            
            if ($('#has_prorated').val() > 0) {
                $('#notes_field').show();
                $('#notes_text').append('<div>" . lang('marketplace_prorated_discount_included') . "</div>');
            }

            if ($('.eula-link').length != 0) {
                $('#notes_field').show();
                $('#notes_text').append('<div>" . lang('marketplace_agree_to_eula') . "</div>');
            }
                
            $('#payment_method_field').show();
            // Check all payment types
            var has_valid_payment_method = false;
            if (data.preauth) {
                $('#preauth_field').show();
                $('#card_number').html(data.preauth_card);
                $('#preauth').prop('checked', true);
                has_valid_payment_method = true;
            } else {
                $('#preauth_field').hide();
            }
            if (data.po) {
                $('#po_field').show();
                if ($('#total').val() > data.po_available) {
                    $('#po').attr('disabled', true);
                } else {
                    has_valid_payment_method = true;
                    if (!data.preauth)
                        $('#po').prop('checked', true);
                }
                // TODO Should use Jquery number formatter plugin
                $('#po_available').html(data.po_currency + ' ' + data.po_available.toFixed(2).toLocaleString()
                    + ' " . lang('marketplace_limit') . "' + ($('#total').val() > data.po_available ? ' - " .
                    lang('marketplace_insufficient_funds') . "' : ''));
            } else {
                $('#po_field').hide();
            }
            if (data.debit) {
                $('#debit_field').show();
                if ($('#total').val() > data.debit_available) {
                    $('#debit').attr('disabled', true);
                } else {
                    has_valid_payment_method = true;
                    if (!data.preauth && !data.po)
                        $('#debit').prop('checked', true);
                }
                // TODO Should use Jquery number formatter plugin
                $('#debit_available').html(data.debit_currency + ' ' + data.debit_available.toFixed(2).toLocaleString()
                    + ($('#total').val() > data.debit_available ? ' - " .
                    lang('marketplace_insufficient_funds') . "' : ''));
            } else {
                $('#debit_field').hide();
            }
            
            // Show/hide PO input
            toggle_payment_display();
            if ((!has_valid_payment_method || !data.verify_contact) && !data.evaluation) {
                $('#payment_method').html('" . lang('marketplace_not_applicable') . "');
                clearos_sdn_account_setup(data.sdn_url_payment, data.sdn_username, data.sdn_device_id);
            } else {
                if (data.evaluation) {
                    $('#payment_method').html('" . lang('marketplace_not_applicable') . "');
                    $('#buy_checkout').remove();
                    $('#eval_checkout').show();
                } else {
                    $('#eval_checkout').remove();
                    $('#buy_checkout').show();
                }
            }
        }
    }
}

function get_eula(basename, id) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/get_eula',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&id=' + id,
        success: function(data) {
            if (data.code != 0) {
                clearos_dialog_box('eula_failure', '" . lang('base_warning') . "', data.errmsg);
            } else {
                if (data.noeula != undefined)
                    clearos_dialog_box('invalid_eula', '" . lang('base_warning') . "', '" . lang('marketplace_no_eula') . "');
                else
                    clearos_dialog_box('eula-' + basename,'" . lang('marketplace_eula') . "', data.en_US.eula);
            }
        },
        error: function(xhr, text, err) {
            clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

function allow_noauth_mods() {
    
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/allow_noauth_mods',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token'),
        success: function(data) {
            if (data.code == 0 && data.allow) {
                return;
            } else {
                $('#infotable').show();
                auth_options.reload_after_auth = true;
                clearos_is_authenticated();
            }
        },
        error: function(xhr, text, err) {
            clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

function bulk_cart_update(apps, toggle) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/bulk_cart_update',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&apps=' + apps + '&toggle=' + toggle,
        success: function(data) {
            if (data.code != 0) {
                if (toggle == 'all')
                    $('#toggle_select').html('<span class=\'ui-button-text\'>" . lang('marketplace_select_all') . "</span>');
                else if (toggle == 'none')
                    $('#toggle_select').html('<span class=\'ui-button-text\'>" . lang('marketplace_select_none') . "</span>');
                // Which apps to reset - this is coming back from our JSON data
                $.each(data.apps, function (id, app) {
					if (app.state == 1) {
                        $('#active-select-' + app.id).addClass('theme-hidden');
                        $('#select-' + app.id).prop('checked', false);
                        marketplace_unselect_app(app.id);
                    } else {
                        $('#active-select-' + app.id).removeClass('theme-hidden');
                        $('#select-' + app.id).prop('checked', true);
                        marketplace_select_app(app.id);
                    }
                });
                clearos_dialog_box('invalid_bulk_cart', '" . lang('base_warning') . "', data.errmsg);
            } else {
                if (toggle == 'all') {
                    $('#toggle_select').html('<span class=\'ui-button-text\'>" . lang('marketplace_select_none') . "</span>');
                    $('#toggle_select').attr('href', '/app/marketplace/none');
                    // We use the JSON string to update page
                    $.each(JSON.parse(apps), function (id, app) {
                        $('#' + app.id).addClass('marketplace-selected');
                    });
                } else if (toggle == 'none') {
                    $('#toggle_select').html('<span class=\'ui-button-text\'>" . lang('marketplace_select_all') . "</span>');
                    $('#toggle_select').attr('href', '/app/marketplace/all');
                    $('.marketplace-app').removeClass('marketplace-selected');
                    // We use the JSON string to update page
                    $.each(JSON.parse(apps), function (id, app) {
                        $('#' + id).removeClass('marketplace-selected');
                    });
                } else {
                    if ($('#select-' + this.id).prop('checked')) {
                        category_class = '';
                    } else {
                        $('#select-' + this.id).prop('checked', true);
                        $(this).removeClass('marketplace-hover');
                        $(this).addClass('marketplace-selected');
                        category_class = 'marketplace-selected';
                    }
                }
            }
        },
        error: function(xhr, text, err) {
            clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

function update_cart(id, individual, redirect) {
    
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/update_cart',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&id=' + id + '&add=' + (individual || $('#select-' + id).prop('checked') ? '1' : '0'),
        success: function(data) {
            if (data.code == 0 && redirect)
                window.location = '/app/marketplace/install';
            if (data.code != 0) {
                if ($('#select-' + id).prop('checked')) {
                    $('#active-select-' + id).addClass('theme-hidden');
                    $('#select-' + id).prop('checked', false);
                    marketplace_unselect_app(id);
                } else {
                    $('#active-select-' + id).removeClass('theme-hidden');
                    $('#select-' + id).prop('checked', true);
                    marketplace_select_app(id);
                }
                clearos_dialog_box('invalid_cart', '" . lang('base_warning') . "', data.errmsg);
            }
        },
        error: function(xhr, text, err) {
            if (xhr['abort'] == undefined)
                clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

function get_apps(realtime, offset) {

    var applist = [];
    var toggle_state = 'none';
    var exclusive_app_selected = null;
    novice_optional_apps = [];

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/get_apps',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&max=' + apps_to_display_per_page +
            '&offset=' + (apps_to_display_per_page * offset) + (realtime ? '&realtime=1' : '&realtime=0'),
        success: function(data) {
            if (data.code != undefined && data.code != 0) {
                // Code 3 is 'Device Not Registered'
                var options = new Object();
                options.type = 'warning';
                if (data.code == 3) {
                    $('#app_list_overview').remove();
                    options.redirect_on_close = '/app/registration/register';
                    clearos_dialog_box('error', '" . lang('base_warning') . "', data.errmsg, options);
                    return;
                } else {
                    $('#app-search-load').html(clearos_infobox_warning(lang_warning, data.errmsg));
                    return;
                }
            }
            // Hide whirly
            $('#app-search-load').hide();

            // Need to do some filtering of data before sending it to theme for display
            jQuery.each(data.list, function(index, app) {

                if (!app.incart)
                    toggle_state = 'all';
                var tags = app.tags.split(' ');
                var is_option = false;
                if ($('#marketplace-novice').length > 0) {
                    $.each(tags, function(tagindex, tag) {
                        // An optional 'novice' or feature app, has a tag starting with 00_
                        // After prefix, it contains the basename of the core app.
                        // Eg. 00_imap is a tagged app for the IMAP mail stack
                        if ($.isNumeric(tag.substring(0, 2)) && parseInt(tag.substring(0, 2)) == 0) {
                            is_option = true;
                            novice_optional_apps.push({app_parent: tag.substring(3, tag.length).toLowerCase(), app_child:app});
                        }
                    });
                }
                if (!is_option) {
                    applist.push(app);
                    if (novice_set[novice_index].exclusive && app.incart)
                        exclusive_app_selected = app.basename;
                }
            });

            var options = new Object();
            if ($('#wizard_marketplace_mode').length != 0) {
                options.wizard = true;
                options.columns = 2;
            }
            if ($('#marketplace-novice').length > 0)
                options.mode = 'feature';
            else if ($('#wizard_marketplace_mode').val() == 'mode3')
                options.mode = 'qsf';
            if (toggle_state == 'all') {
                $('#toggle_select').html('" . lang('marketplace_select_all') . "');
                $('#toggle_select').attr('href', '/app/marketplace/all');
            } else {
                $('#toggle_select').html('" . lang('marketplace_select_none') . "');
                $('#toggle_select').attr('href', '/app/marketplace/none');
            }

            // Display settings number of apps by default
            var to_display = $('#number_of_apps_to_display').val();
            if ($('#marketplace-novice').length > 0)
                to_display = 0;
            
            clearos_marketplace_app_list($('#display_format').val(), applist, to_display, data.total, options);

            if ($('#marketplace-novice').length > 0 && exclusive_app_selected)
                add_optional_apps(exclusive_app_selected);

            logo_list = [];
            $('.theme-placeholder').each(function( index ) {
                logo_list.push($(this).data('basename'));
            });
            get_app_logos(logo_list);
        },
        error: function(xhr, text, err) {
            // Don't display any errors if ajax request was aborted due to page redirect/reload
            if (xhr['abort'] == undefined)
                clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

$(document).on('click', '.marketplace-app-event', function(e) {
    e.preventDefault();
    // Undefined select means app is for Pro only
    if ($('#select-' + this.id).val() == undefined) {
        var id = this.id + '-na';
        var original = $('#' + this.id + '-na').css('color');
        $('#' + this.id + '-na').css('color', 'red');
        $('#' + this.id + '-na').hide();
        $('#' + this.id + '-na').fadeIn(2000, function() {
            $('#' + id).css('color', original);
        });
        return;
    } else if ($('#select-' + this.id).prop('checked')) {
        $('#select-' + this.id).prop('checked', false);
        marketplace_unselect_app(this.id);
    } else {
        $('#select-' + this.id).prop('checked', true);
        marketplace_select_app(this.id);
    }
    var clicked_app = new Object();
    clicked_app.id = this.id;
    clicked_app.name = $('#' + this.id).attr('data-appname'); 
    // Mode one hidden field is novice mode/select by feature
    if ($('#marketplace-novice').length > 0 && novice_set[novice_index].exclusive && $('#' + this.id,'#optional-apps').length != 1) {
        // Need to unset all other apps before selecting this one
        var apps = Array();
        $.each($('#form_app_list input[type=\'checkbox\']'), function (index, value) {
            if (clicked_app.id == value.id.replace('select-', '') && $('#' + value.id).prop('checked')) {
                apps[index] = {state: '1', id: this.id.replace('select-', '')};
            } else {
                $('#active-' + value.id).addClass('theme-hidden');
                $('#' + value.id).prop('checked', false);
                marketplace_unselect_app(value.id.replace('select-', ''));
                apps[index] = {state: '0', id: this.id.replace('select-', '')};
            }
        });
        bulk_cart_update(JSON.stringify(apps), 'exclusive');
        if ($('#select-' + this.id).prop('checked'))
            add_optional_apps(clicked_app);
        else
            $('#optional-apps').remove();

    } else {
        update_cart(this.id, false, false);
    }
});

function add_optional_apps(app) {
    // Add Novice Optional apps
    var content = '';
    var options = new Object();
    options.optional_apps = true;
    var applist = [];
    $.each(novice_optional_apps, function(index, myapp) { 
        if (myapp.app_parent == app.id)
            applist.push(myapp.app_child);
    });
    $('#optional-apps').remove();
    $('#marketplace-app-container').append('<div id=\'optional-apps\'><h2>" . lang('marketplace_optional_apps') . " - ' + app.name + ' </h2></div>');
    clearos_marketplace_app_list($('#display_format').val(), applist, 0, applist.length, options);
}

function get_app_details(basename) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/get_app_details',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&basename=' + basename,
        success: function(data) {
            if (data.code != undefined && data.code == 3) {
                $('#app_overview').remove();
                var options = new Object();
                options.redirect_on_close = '/app/registration/register';
                clearos_dialog_box('error', '" . lang('base_warning') . "', data.errmsg, options);
                return;
            } else if (data.code != undefined && data.code != 0) {
                $('#app-loading').html(clearos_infobox_warning(lang_warning, data.errmsg));
                return;
            } else if (data.rnf != undefined) {
                $('#app-loading').html(clearos_infobox_warning(lang_warning, '" . lang('marketplace_app_does_not_exist') . "'));
                return;
            }
            // Hide the loading page
            $('#app-loading').remove();

            // Add title to review form
            $('#review-app-name').html(data.name);

            $('#app-details-container').show(600);
            $('#app_name_title').html(data.name);
            $('#app_description').html('<p>' + data.description.replace(/\\n/g, '</p><p>') + '</p>');
            if (data.installed_version == '')
                $('#app_installed_version').html('" . lang('marketplace_not_installed') . "');
            else
                $('#app_installed_version').html(data.installed_version);
            $('#app_latest_version').html(data.latest_version);
            $('#app_latest_release_date').html(clearos_format_date(data.latest_release_date, 'MMMM d, yyyy'));
  
            $('.actions').hide();

            if ((data.pricing.pid_bitmask & 256) != 256) {
                // 256 is the bit that indicates there is an RPM associated with this app
                // If it is not set, hide irrelevant info in rhs info bar
                $('#app_installed_version').html('" . lang('marketplace_not_applicable') . "');
                $('#app_latest_version').html('" . lang('marketplace_not_applicable') . "');
                $('#app_latest_release_date').html('---');
                $('#app_license').html('" . lang('marketplace_not_applicable') . "');
                $('#app_license_library').html('" . lang('marketplace_not_applicable') . "');
            }

            if (data.installed) {
                $('#indiv_configure').show();

                if (!data.no_uninstall)
                    $('#indiv_uninstall').show();
                else
                    $('#indiv_uninstall').remove();

                if (data.up2date)
                    $('#indiv_upgrade').remove();
                else
                    $('#indiv_upgrade').show();
            } else {
                $('#indiv_configure').remove();
                $('#indiv_uninstall').remove();
                $('#indiv_upgrade').remove();
                $('#indiv_uninstall').remove();
                if (data.pricing.unit_price != 0) {
                    if (data.pricing.exempt) {
                        $('#indiv_install').show();
                        $('#indiv_buy').remove();
                    } else {
                        $('#indiv_buy').show();
                        $('#indiv_install').remove();
                    }
                } else {
                    $('#indiv_install').show();
                    $('#indiv_buy').remove();
                }
            }
                
            if (!data.repo_enabled) {
                $('#indiv_repo').show();
                // tack on repo name to href for repo
                $('#indiv_repo').attr('href', '/app/software_repository/index/detailed/' + data.repo_name);
            } else {
                $('#indiv_repo').remove();
            }

            // A non-zero display_mask means the app is not available to install
            if (data.display_mask != 0) {
                $('#indiv_buy').remove();
                $('#indiv_install').remove();
            }
            $('#app_support_policy').html(get_support_policy(data));

            $('#indiv_configure').attr('href', '/' + data.url_config);

            if ((data.display_mask & 1) == 1) {
                var learn_more_options = {
                    buttons: false,
                    external: true
                };
                $('#availability_warning').html(
                    '" . lang('marketplace_not_available_in_edition') . "&nbsp;&nbsp;' +
                    clearos_anchor(data.edition_landing_page, lang_marketplace_learn_more, learn_more_options)
                );
                $('#availability_warning_box').show();
            } else if ((data.display_mask & 2) == 2) {
                $('#availability_warning').html('" . lang('marketplace_mode_slave_invalid') . "');
                $('#availability_warning_box').show();
            } else if ((data.display_mask & 4) == 4) {
                $('#availability_warning').html('" . lang('marketplace_google_apps_not_compatible_with_ad') . "');
                $('#availability_warning_box').show();
            } else if ((data.display_mask & 8) == 8) {
                $('#availability_warning').html('" . lang('marketplace_extensions_not_compatible_with_ad') . "');
                $('#availability_warning_box').show();
            } else if ((data.display_mask & 16) == 16) {
                $('#availability_warning').html('" . lang('marketplace_not_available_during_eval') . "');
                $('#availability_warning_box').show();
            } else if ((data.display_mask & 32) == 32) {
                $('#availability_warning').html('" . lang('marketplace_not_available_repo_settings') . "');
                $('#availability_warning_box').show();
            } else if (data.display_mask > 0) {
                $('#availability_warning').html('" . lang('marketplace_not_available') . "');
                $('#availability_warning_box').show();
            }
                
            if (data.url_learn_more == '')
                $('#learn_more').remove();
            else
                $('#learn_more').attr('href', data.url_learn_more);

            if (data.url_documentation == '')
                $('#documentation').remove();
            else
                $('#documentation').attr('href', data.url_documentation);

            $('#app_repo').html(data.repo_name);

            if (data.pricing.unit_price == 0)
                $('#app_cost').html('" . lang('marketplace_free') . "');
            else
                $('#app_cost').html(data.pricing.currency + ' '
                    + data.pricing.unit_price.toFixed(2) + ' ' + UNIT[data.pricing.unit]);

            get_app_logo(data.basename, 'app-logo-' + basename);
            $('#app_rating').html(clearos_star_rating(data.rating));
            $('#app_category').html(data.category);
            var tags = data.tags.split(' ');
            var my_tags = '';
            $.each(tags, function(index, tag) {
                if (!$.isNumeric(tag.substring(0, 2)))
                    my_tags += tag + ' ';
            });
            $('#app_tags').html(my_tags);
            $('#app_license').html(data.license);
            $('#app_license_library').html(data.license_library);
            $('#app_introduced').html(clearos_format_date(data.introduced, 'MMMM d, yyyy'));
            $('#app_devel_org').html(data.devel_org);
            $('#app_devel_contact').html(data.devel_contact);
            $('#app_devel_email').html(data.devel_email);
            $('#app_devel_website').html('<a href=\'' + data.devel_website + '\' target=\'_blank\'>'
                + data.devel_website + '</a>');
            // Screenshots
            var screenshots = data.screenshots;
            if (screenshots.length == 0) {
                $('#app_screenshots').append('<div>" . lang('marketplace_no_screenshots') . "</div>');
            } else {
                $('#app_screenshots').append(clearos_screenshots(basename, screenshots));
                // Kick off Ajax to fetch screenshots
                $('.theme-screenshot-img').each(function() {
                    clearos_get_app_screenshot(basename, $(this).attr('data-index'));
                });
            }

            // Complementary apps
            if (data.complementary_apps.length == 0)
                $('#marketplace-complementary').remove();

            clearos_related_apps('complementary', data.complementary_apps);

            // Other apps by developer
            if (data.other_by_devel.length == 0)
                $('#app_other_by_devel').append('<div>" . lang('marketplace_no_other_apps') . "</div>');
            else
                clearos_related_apps('other_by_devel', data.other_by_devel);

            // Ratings
            var ratings = data.ratings;
            if (ratings.length == 0)
                $('#app_ratings').append('<div style=\'margin-top: 10px;\'>" . lang('marketplace_no_reviews') . "</div>');
            else
                $('#app_ratings').append(app_rating(basename, ratings));

            var locales = data.locales;
            var contributors = data.locales_contributors;
            var contributor_list = '';
            for (index = 0 ; index < contributors.length; index++) {
                $('#app_localization_contributor').append(
                    '<li style=\'margin-left: 0;\'>' + contributors[index].contact +
                    '  (<a href=\'mailto:' + contributors[index].email + '\'>' + contributors[index].email + '</a>)</li>'
                );
            }
            for (index = 0 ; index < locales.length; index++) {
                $('#app_localization').append(
                    '<div>' + locales[index].locale + '</div>' + clearos_progress_bar(locales[index].completion, null)
                );
            }

            logo_list = [];
            $('.theme-placeholder').each(function( index ) {
                logo_list.push($(this).data('basename'));
            });
            get_app_logos(logo_list);

        },
        error: function(xhr, text, err) {
            clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

function checkout(event, type) {
    event.preventDefault();
    $('#free_checkout').hide();
    var modal_feedback = null;
    if ($('#po').prop('checked') && $('#mi-po_number').val() == '') {
        //modal_feedback = clearos_dialog_box('invalid_po_err', '" . lang('base_warning') . "', '" . lang('marketplace_invalid_po') . "');
        // FIXME - Direct reference to theme framework
        $('#modal-input-po').modal({backdrop: 'static'});
        return;
    } else if (type == 'paid' && !$('input[name=payment_method]:checked').val()) {
        modal_feedback = clearos_dialog_box('invalid_method_err', '" . lang('base_warning') . "', '" . lang('marketplace_select_payment_method') . "');
        return;
    } else {
        // Display 'processing' indication
        $('.payment_option').attr('disabled', true);
        $('#payment_processing').show();
        var processingText = '<p>" . lang('marketplace_patience') . "</p>';
        var whirlyText = '" . lang('marketplace_processing') . "';
        if (type == 'free' || type == 'eval') {
            whirlyText += '...';
        } else {
            if ($('#preauth').prop('checked'))
                whirlyText += ' " . strtolower(lang('marketplace_credit_card')) . "...';
            if ($('#po').prop('checked'))
                whirlyText += ' " . strtolower(lang('marketplace_purchase_order')) . "...';
            else if ($('#debit').prop('checked'))
                whirlyText += ' " . strtolower(lang('marketplace_debit')) . "...';
        }
        var w_options = new Object();
        w_options.text = whirlyText;
        w_options.center = true;
        processingText += clearos_loading(w_options);
        modal_feedback = clearos_dialog_box('processing_info', '" . lang('marketplace_processing_order') . "...', processingText);
        $('#notes').html(clearos_loading(w_options));
        // Hide buttons and show loading...
        $('#buy_checkout').hide();
        $('#eval_checkout').remove();
    }
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/checkout',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&payment=' + $('input[name=payment_method]:checked').val() + '&po=' + $('#mi-po_number').val(),
        success: function(data) {
            if (data.code != 0) {
                clearos_dialog_close(modal_feedback);
                var options = new Object();
                options.reload_on_close = true;
                clearos_dialog_box('checkout_err', '" . lang('base_warning') . "', data.errmsg, options);
            } else {
                if (data.no_rpms_to_install != undefined)
                    window.location = '/app/marketplace/no_rpms';
                else
                    window.location = '/app/marketplace/progress';
            }
        },
        error: function(xhr, text, err) {
            clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

function toggle_payment_display() {
    if ($('#po').prop('checked')) {
        $('#po_number').show();
    } else {
        $('#po_number').hide();
        $('#display_po').html('');
    }
}

function get_novice_set() {

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/set_search',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&search=' + novice_set[novice_index].search,
        success: function(data) {
            get_apps(false, 0);
            $('#marketplace-novice-step').html((novice_index + 1) + ' / ' + novice_set.length);
            $('#marketplace-novice_title').html(novice_set[novice_index].title);
            $('#marketplace-novice-description').html(novice_set[novice_index].description);
            $('#novice-learn-more-modal-title').html(novice_set[novice_index].helptitle);
            $('#novice-learn-more-modal-message').html(novice_set[novice_index].helpcontent);
            if (novice_set[novice_index].exclusive)
                $('#toggle_select').hide();
            else
                $('#toggle_select').show();
        },
        error: function(xhr, text, err) {
            // Don't display any errors if ajax request was aborted due to page redirect/reload
            if (xhr['abort'] == undefined)
                clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

function get_configure(app) {
    var button_html = '<div class=\'theme-button-set\'>';
    if (app.installed && app.display_mask == 0) {
        if (!app.up2date)
            button_html += get_button_anchor('/app/marketplace/view/' + app.basename, '" . lang('marketplace_upgrade') . "', null);
        else
            button_html += get_button_anchor('/' + app.url_config, '" . lang('marketplace_configure') . "', null);
    } else {
        if (in_wizard_or_novice)
            button_html += get_button_anchor('http://www.clearcenter.com/marketplace/type/?basename=' + app.basename, '" . lang('marketplace_learn_more') . "', 'blank');
        else
            button_html += get_button_anchor('/app/marketplace/view/' + app.basename, '" . lang('marketplace_details') . "', null);
    }
    button_html += '</div>';
    return button_html;
}

function get_button_anchor(url, text, target) {
    return '<a href=\'' + url + '\' class=\'theme-anchor theme-anchor-add theme-anchor-important\' ' + (target == 'blank' ? 'target=\'_blank\'' : '') + '\'>' +
        text + '</a>';
}

function get_progress() {
    $.ajax({
        url: '/app/marketplace/ajax/progress',
        method: 'GET',
        dataType: 'json',
        success : function(json) {

            // If no wc-yum process is running, some other user or service is running yum which we can't latch on to output
            // Jump to busy page
            if (json.busy && !json.wc_busy && $(location).attr('href').match('.*progress\/busy$') == null)
                window.location = '/app/marketplace/progress/busy';

            clearos_set_progress_bar('progress', parseInt(json.progress), null);

            clearos_set_progress_bar('overall', parseInt(json.overall), null);

            if (!json.busy && !json.wc_busy && json.overall != 100)
                window.location = '/app/marketplace';

            if (json.code === 0) {
                $('#details').html(json.details);
            } else if (json.code === -999) {
                // Do nothing...no data yet
            } else {
                // Uh oh...something bad happened
                clearos_set_progress_bar('progress', 0, null);
                clearos_set_progress_bar('overall', 0, null);
                $('#details').html(json.errmsg);
                return;
            }

            if ($(location).attr('href').match('.*progress\/busy$') != null) {
                // We're on the busy page...let's check again in a few seconds.
                window.setTimeout(get_progress, 2000);
            } else if (json.overall == 100) {
                if ($('#theme_wizard_nav_next').length == 0) {
                    $('#reload_button').show();
                    clearos_set_progress_bar('progress', 100, null);
                    clearos_set_progress_bar('overall', 100, null);
                    $('#details').html(installation_complete);
                }
                return;
            } else {
                window.setTimeout(get_progress, 1000);
            }

            if (!json.busy) {
                // Check to see if in Wizard, if so, exit wizard
                // If no yum process is running, go back to Marketplace
                if ($('#theme_wizard_complete').length != 0)
                    window.location = '/app/marketplace/wizard/stop';
                else
                    window.location = '/app/marketplace';
            }
        },
        error: function(xhr, text, err) {
            window.setTimeout(get_progress, 1000);
        }
    });
}

function clearos_sdn_account_setup(landing_url, username, device_id) {
    $('#payment_method_field').append(theme_sdn_account_setup(landing_url, username, device_id));
    clearos_modal_infobox_open('sdn-account-setup-dialog');
}

function update_po() {
    $('#po').prop('checked', true);
    $('#po_available').html($('#po_number').val());
}

/**
 * Peer review.
 */

function peer_review(basename, dbid, approve) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/peer_review',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&basename=' + basename + '&approve=' + approve + '&dbid=' + dbid,
        success: function(data) {
            if (data.code == 1) {
                clearos_is_authenticated();
            } else if (data.code != 0) {
                clearos_dialog_box('peer_review_error', '" . lang('base_warning') . "', data.errmsg);
            } else {
                if (approve > 0) {
                    // Already rated
                    if (data.updated_review != undefined) {
                        $('#agree_' + dbid).html(parseInt($('#agree_' + dbid).text()) + 1);
                        if (parseInt($('#disagree_' + dbid).text()) > 0)
                            $('#disagree_' + dbid).html(parseInt($('#disagree_' + dbid).text()) - 1);
                    } else if (data.new_review != undefined) {
                        $('#agree_' + dbid).html(parseInt($('#agree_' + dbid).text()) + 1);
                    }
                } else {
                    // New rating
                    if (data.updated_review != undefined) {
                        $('#disagree_' + dbid).html(parseInt($('#disagree_' + dbid).text()) + 1);
                        if (parseInt($('#agree_' + dbid).text()) > 0)
                            $('#agree_' + dbid).html(parseInt($('#agree_' + dbid).text()) - 1);
                    } else if (data.new_review != undefined) {
                        $('#disagree_' + dbid).html(parseInt($('#disagree_' + dbid).text()) + 1);
                    }
                }
            }
        },
        error: function(xhr, text, err) {
            clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

/**
 * App rating.
 */

function app_rating(basename, ratings) {
    var html = '';
    for (index = 0 ; index < ratings.length; index++) {
        ar = ratings[index];
        var title = ar.comment;
        if (title.indexOf('.') > 0) {
            title = title.substring(0, title.indexOf('.'));
        } else if (title.indexOf('\\n') > 0) {
            title = title.substring(0, title.indexOf('\\n'));
        }

        if (title == ar.comment)
            html += theme_rating_review(basename, ar.id, title, null, ar.rating, ar.pseudonym, ar.timestamp, ar.agree, ar.disagree);
        else
            html += theme_rating_review(basename, ar.id, title, ar.comment, ar.rating, ar.pseudonym, ar.timestamp, ar.agree, ar.disagree);
    }
    html += '<script type=\'text/javascript\'>' +
            '  $(\'a.review-action\').on(\'click\', function (e) {' +
            '    e.preventDefault();' +
            '    var parts = this.id.split(\'-\');' +
            '    clearos_is_authenticated();' +
            '    peer_review(parts[0], parts[1], (parts[2].match(/up/) ? 1 : 0));' +
            '  });' +
            '</script>'
    ;
    return html;
}

/**
 * Returns app screenshot via ajax.
 *
 * @param string $basename basename of app
 * @param string $index    screenshot number
 */

function clearos_get_app_screenshot(basename, index) {
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/app/marketplace/ajax/get_app_screenshot/' + basename + '/' + index,
        success: function(data) {
            // Success..pass data to theme to update HTML.
            if (data.code == 0)
                $('#ss-' + basename + '_' + index).attr('src', data.location);
        },
        error: function(xhr, text, err) {
            console.log(xhr.responseText.toString());
        }
    });
}

/**
 * Returns app logo via ajax.
 *
 * @param string $basename basename of app
 * @param string $domid    DOM ID
 */

function get_app_logo(basename, domid) {
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/app/marketplace/ajax/get_app_logo/' + basename,
        success: function(data) {
            // Success..pass data to theme to update HTML.
            if (data.code == 0)
                $('#' + domid).html($.base64.decode(data.base64));
        },
        error: function(xhr, text, err) {
            console.log(xhr.responseText.toString());
        }
    });
}

/**
 * Select app action.
 */

function marketplace_select_app(id) {
    $('#' + id).val(lang_marketplace_remove);
    $('#active-select-' + id).removeClass('theme-hidden');
    $('figure[data-basename=\"' + id + '\"]').addClass('theme-app-selected');
}

/**
 * Unselect app action.
 */

function marketplace_unselect_app(id) {
    $('#' + id).val(lang_marketplace_select_for_install);
    $('#active-select-' + id).addClass('theme-hidden');
    $('figure[data-basename=\"' + id + '\"]').removeClass('theme-app-selected');
}

";

// vim: syntax=javascript ts=4
