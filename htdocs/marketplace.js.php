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
var UNIT = [];
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
UNIT[0] = '';
UNIT[9] = '';
UNIT[100] = '" . lang('marketplace_monthly') . "';
UNIT[1000] = '" . lang('marketplace_1_year') . "';
UNIT[2000] = '" . lang('marketplace_2_year') . "';
UNIT[3000] = '" . lang('marketplace_3_year') . "';

var realtime = false;

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
    if ($('#noapps').val() != undefined) {
        $('#r_fee_install').hide();
        $('#r_eval_install').hide();
        return;
    }
    if (data.code == 0) {
        if ($('#total').val() > 0) {
            $('#r_bill_cycle').show();
            $('#r_total').show();
            $('#display-total').html(data.currency + ' ' + (parseFloat($('#total').val())).toFixed(2).toLocaleString());
            if (data.evaluation) {
                $('#bill_cycle').html('" . lang('marketplace_not_applicable') . " - " . lang('marketplace_trial_in_progress') . "');
                $('#r_notes').show();
                $('#notes').html('<div>" . lang('marketplace_note_evaluation_and_payment') . "</div>');
            } else {
                $('#bill_cycle').html($.datepicker.formatDate('MM d, yy', new Date(data.billing_cycle)));
            }
            
            if ($('#has_prorated').val() > 0) {
                $('#r_notes').show();
                $('#notes').append('<div>" . lang('marketplace_prorated_discount_included') . "</div>');
            }
                
            $('#r_payment_method').show();
            // Check all payment types
            var has_valid_payment_method = false;
            if (data.preauth) {
                $('#option_preauth').show();
                $('#card_number').html(data.preauth_card);
                $('#preauth').attr('checked', true);
                has_valid_payment_method = true;
            } else {
                $('#option_preauth').hide();
            }
            if (data.po) {
                $('#option_po').show();
                if ($('#total').val() > data.po_available) {
                    $('#po').attr('disabled', true);
                } else {
                    has_valid_payment_method = true;
                    if (!data.preauth)
                        $('#po').attr('checked', true);
                }
                // TODO Should use Jquery number formatter plugin
                $('#po_available').html(data.po_currency + ' ' + data.po_available.toFixed(2).toLocaleString()
                    + ' " . lang('marketplace_limit') . "' + ($('#total').val() > data.po_available ? ' - " .
                    lang('marketplace_insufficient_funds') . "' : ''));
            } else {
                $('#option_po').hide();
            }
            if (data.debit) {
                $('#option_debit').show();
                if ($('#total').val() > data.debit_available) {
                    $('#debit').attr('disabled', true);
                } else {
                    has_valid_payment_method = true;
                    if (!data.preauth && !data.po)
                        $('#debit').attr('checked', true);
                }
                // TODO Should use Jquery number formatter plugin
                $('#debit_available').html(data.debit_currency + ' ' + data.debit_available.toFixed(2).toLocaleString()
                    + ($('#total').val() > data.debit_available ? ' - " .
                    lang('marketplace_insufficient_funds') . "' : ''));
            } else {
                $('#option_debit').hide();
            }
            
            // Show/hide PO input
            toggle_payment_display();
            if ((!has_valid_payment_method || !data.verify_contact) && !data.evaluation) {
                $('#payment_method').html('" . lang('marketplace_not_applicable') . "');
                clearos_sdn_account_setup(data.sdn_url_payment, data.sdn_username, data.sdn_device_id);
            } else {
                if (data.evaluation) {
                    $('#payment_method').html('" . lang('marketplace_not_applicable') . "');
                    $('#r_eval_install').show();
                } else {
                    $('#r_fee_install').show();
                }
            }
        }
    }
}

function get_image(type, id, domid) {
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/app/marketplace/ajax/get_image',
        data: 'type=' + type + '&id=' + id,
        success: function(data) {
            $('#' + domid).attr('src', data.location);
        },
        error: function(xhr, text, err) {
            // Don't display any errors if ajax request was aborted due to page redirect/reload
            if (xhr['abort'] == undefined)
                clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
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
                if (data.noeula != undefined) {
                    clearos_dialog_box('invalid_eula', '" . lang('base_warning') . "', '" . lang('marketplace_no_eula') . "');
                } else {
                    clearos_eula(basename, 'eula_display', data.en_US.eula);
                }
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
                    $('#' + app.id).removeClass('marketplace-selected');
					if (app.state == 1) {
                        $('#' + app.id).attr('checked', false);
                        $('#' + app.id).removeClass('marketplace-hover');
                    } else {
                        $('#' + app.id).attr('checked', true);
                        $('#' + app.id).addClass('marketplace-selected');
                    }
                });
                clearos_dialog_box('invalid_bulk_cart', '" . lang('base_warning') . "', data.errmsg);
            } else {
                $('.marketplace-category').removeClass('marketplace-hover');
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
                    if ($('#select-' + this.id).is(':checked')) {
                        category_class = '';
                    } else {
                        $('#select-' + this.id).attr('checked', true);
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
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&id=' + id + '&add=' + (individual || $('#select-' + id + ':checked').val() !== undefined ? '1' : '0'),
        success: function(data) {
            if (data.code == 0 && redirect)
                window.location = '/app/marketplace/install';
            if (data.code != 0) {
                $('#' + id).removeClass('marketplace-selected');
                if ($('#select-' + id).is(':checked')) {
                    $('#' + id).attr('checked', false);
                    $('#' + id).removeClass('marketplace-hover');
                } else {
                    $('#' + id).attr('checked', true);
                    $('#' + id).addClass('marketplace-selected');
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
    if ($('#install_list').length != 0 && $('#display_format').val() == 'table')
        table_install_list.fnClearTable();
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/get_apps',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&max=' + apps_to_display_per_page +
            '&offset=' + (apps_to_display_per_page * offset) + (realtime ? '&realtime=1' : '&realtime=0'),
        success: function(data) {
            if (data.code != undefined && data.code != 0) {
                // Code 3 is 'Device Not Registered'
                if (data.code == 3) {
                    $('#app_list_overview').remove();
                    var options = new Object();
                    options.redirect_on_close = '/app/registration/register';
                    clearos_dialog_box('error', '" . lang('base_warning') . "', data.errmsg, options);
                    return;
                } else {
                    $('#marketplace-loading').hide();
                    $('#app_list_overview').html(data.errmsg);
                    return;
                }
            }
            // Hide whirly
            $('#app_list_overview').remove();
            $('#marketplace-loading').hide();
            $('#search_and_install').show();
            $('#filter').show();
            display_apps(data);

            var previous = offset - 1;
            if (previous < 0)
                previous = 0;
            var next = offset + 1;
            if (data.total / apps_to_display_per_page < next)
                next = Math.round(data.total / apps_to_display_per_page + .49999) - 1;
            var paginate = '<a style=\'margin-right: 2px; display: inline;\' class=\'theme-anchor theme-anchor-add theme-anchor-important\' href=\'/app/marketplace/search/index/0\'><i class=\'fa fa-fast-backward\'></i></a>';
            paginate += '<a style=\'margin-right: 2px; display: inline;\' class=\'theme-anchor theme-anchor-add theme-anchor-important\' href=\'/app/marketplace/search/index/' + previous + '\'><i class=\'fa fa-backward\'></i></a>';
            var pages = 0;
            if (apps_to_display_per_page > 0)
                pages = Math.round(data.total / apps_to_display_per_page + .49999) - 1;
            paginate += '<a style=\'margin-right: 2px; display: inline;\' class=\'theme-anchor theme-anchor-add theme-anchor-important\' href=\'/app/marketplace/search/index/' + next + '\'><i class=\'fa fa-forward\'></i></a>';
            paginate += '<a style=\'display: inline;\' class=\'theme-anchor theme-anchor-add theme-anchor-important\' href=\'/app/marketplace/search/index/' + pages + '\'><i class=\'fa fa-fast-forward\'></i></a>';
            if (pages > 0) {
                $('#pagination-top').html(paginate + '<div style=\'padding: 5px 0px 0px 0px; font-size: 7pt;\'>" . lang('marketplace_displaying') . " ' + (apps_to_display_per_page * offset + 1) + ' - ' + (apps_to_display_per_page * offset + data.list.length) + ' " . lang('base_of') . " ' + data.total + '</div>');
                $('#pagination-bottom').html(paginate);
            }
            
        },
        error: function(xhr, text, err) {
            // Don't display any errors if ajax request was aborted due to page redirect/reload
            if (xhr['abort'] == undefined)
                clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

$(document).on('mouseover', '.marketplace-app-event', function(event) {
    if (!$(this).hasClass('marketplace-selected') && $('#select-' + this.id).val() != undefined) {
        $('.marketplace-app-event').css('cursor', 'pointer');
        $(this).addClass('marketplace-hover');
    } else {
        $('.marketplace-app-event').css('cursor', 'default');
    }
}).on('mouseout', '.marketplace-app-event', function(event) {
    $(this).removeClass('marketplace-hover');
}).on('click', '.marketplace-app-event', function(event) {
    if ($('#select-' + this.id).val() == undefined) {
        var id = this.id + '-na';
        var original = $('#' + this.id + '-na').css('color');
        $('#' + this.id + '-na').css('color', 'red');
        $('#' + this.id + '-na').hide();
        $('#' + this.id + '-na').fadeIn(2000, function() {
            $('#' + id).css('color', original);
        });
        return;
    } else if ($('#select-' + this.id).is(':checked')) {
        $('#select-' + this.id).removeAttr('checked');
        $(this).removeClass('marketplace-selected');
        $(this).addClass('marketplace-hover');
    } else {
        $('#select-' + this.id).attr('checked', true);
        $(this).removeClass('marketplace-hover');
        $(this).addClass('marketplace-selected');
    }
    clicked_app = this.id
    if ($('#wizard_marketplace_mode').val() == 'mode1' && novice_set[novice_index].exclusive && $('#' + this.id,'#optional-apps').length != 1) {
        // Need to unset all other apps before selecting this one
        var apps = Array();
        $.each($('#form_app_list input[type=\'checkbox\']'), function (index, value) {
            if (clicked_app == value.id.replace('select-', '') && $('#' + value.id).is(':checked')) {
                apps[index] = {state: '1', id: this.id.replace('select-', '')};
            } else {
                $('#' + value.id).removeAttr('checked');
                $('#' + value.id.replace('select-', '')).removeClass('marketplace-selected');
                apps[index] = {state: '0', id: this.id.replace('select-', '')};
            }
        });
        bulk_cart_update(JSON.stringify(apps), 'exclusive');
        if ($('#select-' + this.id).is(':checked'))
            add_optional_apps(clicked_app);
        else
            $('#optional-apps').remove();

    } else {
        update_cart(this.id, false, false);
    }
});

function add_optional_apps(app_focus) {
    // Add Novice Optional apps
    var content = '';
    $.each(novice_optional_apps, function(index, myapp) { 
        if (myapp.app_parent == app_focus)
            if ($('#display_format').val() == 'list')
                content += get_app_as_column(myapp.app_child);
            else
                content += get_app_as_tile(myapp.app_child);
    });
    $('#optional-apps').remove();
    if (content.length > 0)
        $('#marketplace-app-container').append('<div id=\'optional-apps\' style=\'margin-top: 15px; padding-top: 10px; border-top: 1px dotted grey;\'><h1>" . lang('marketplace_optional_apps') . "</h1>' + content + '</div>');
    $.each(novice_optional_apps, function(index, myapp) {
        get_image('app-logo', myapp.app_child.basename, 'app-logo-' + myapp.app_child.basename);
        // Tooltip is broken wrt to using .on() function
        $('#' + myapp.app_child.basename).tooltip({
            offset: [-102, -425],
            predelay: 2000,
            position: 'top center',
            opacity: 0.95
            });
    });
}

function display_apps(data) {
    var applist = [];
    var categorylist = [];
    var content = '';
    var category_class = '';
    var toggle_state = 'none';
    var exclusive_app_selected = null;
    novice_optional_apps = [];

    if ($('#wizard_marketplace_mode').val() == 'mode1')
        in_wizard_or_novice = true;
    else
        in_wizard_or_novice = false;
    if (data.list.length == 0) {
        $('#marketplace-app-container').append('<div style=\'padding: 70px 0px;\'>" . lang('marketplace_search_no_results') . "</div>');
        return;
    }
    jQuery.each(data.list, function(index, app) { 
        // Bitmask of 0 or 1 means allow to install or Pro only (which we display)
        if (app.display_mask > 1)
            return true;

        if ($('#install_list').length != 0 && $('#display_format').val() == 'table') {
            var new_row = table_install_list.fnAddData([
                app.category_en_US,
                app.name + (app.installed ? '' : '<input type=\'checkbox\' class=\'theme-hidden\' id=\'select-' + app.basename + '\' name=\'' + app.basename + '\' ' + (app.incart ? 'CHECKED ' : '') + '\'>'),
                '<p>' + app.description.replace(/\\n/g, '</p><p>') + '</p>',
                (app.pricing.unit_price > 0 ? app.pricing.currency + app.pricing.unit_price + ' ' + UNIT[app.pricing.unit] : '" . lang('marketplace_free') . "'),
                (app.installed ? '" . lang('base_yes') . "' : '" . lang('base_no') . "')
            ]);
            var nTr = table_install_list.fnSettings().aoData[new_row[0]].nTr;
            nTr.id = app.basename;
            var my_classes = 'marketplace-app-event';
            if (app.incart)
                my_classes += ' marketplace-selected';
            
            nTr.className = my_classes;
            return true;
        }
        if (!app.incart)
            toggle_state = 'all';
        applist.push(app.basename);
        var tags = app.tags.split(' ');
        var is_option = false;
        // Only look at tags in mode 1 (novice) of wizard or MP select
        if ($('#wizard_marketplace_mode').val() == 'mode1') {
            $.each(tags, function(tagindex, tag) {
                if ($.isNumeric(tag.substring(0, 2)) && parseInt(tag.substring(0, 2)) == 0) {
                    is_option = true;
                    novice_optional_apps.push({app_parent: tag.substring(3, tag.length).toLowerCase(), app_child:app});
                }
            });
        }
        if (!is_option) {
            if ($('#display_format').val() == 'list')
                content += get_app_as_column(app);
            else
                content += get_app_as_tile(app);
            if (novice_set[novice_index].exclusive && app.incart)
                exclusive_app_selected = app.basename;
        }
    });

    if (toggle_state == 'all') {
        $('#toggle_select').html('<span class=\'ui-button-text\'>" . lang('marketplace_select_all') . "</span>');
        $('#toggle_select').attr('href', '/app/marketplace/all');
    } else {
        $('#toggle_select').html('<span class=\'ui-button-text\'>" . lang('marketplace_select_none') . "</span>');
        $('#toggle_select').attr('href', '/app/marketplace/none');
    }

    $('#marketplace-app-container').append(content);
    for (var index = 0; index < applist.length; index++)
        get_image('app-logo', applist[index], 'app-logo-' + applist[index]);

    if ($('#wizard_marketplace_mode').val() == 'mode1' && exclusive_app_selected)
        add_optional_apps(exclusive_app_selected);

    if ($('#display_format').val() == 'tile') {
        $('.marketplace-app').tooltip({
            offset: [-48, -314],
            predelay: 1500,
            delay: 250,
            position: 'top left',
            opacity: 0.95
        });
    }
}

function get_app_as_column(app) {
    var content = '';
    content += '<div class=\'marketplace-app-event marketplace-app marketplace-list' + (app.incart ? ' marketplace-selected' : '') + '\' id=\'' + app.basename + '\'>';
    if (app.installed)
        content += '<span class=\'marketplace-installed-list\'>INSTALLED</span>';
    content += '  <div style=\'float:left; width:80px; text-align: center; padding: 0px 2px 5px 2px;\'>';
    // App logo
    content += '    <img src=\'" . clearos_app_htdocs('marketplace') . "/market_default.png\' '
        + 'id=\'app-logo-' + app.basename + '\' style=\'padding-bottom: 8px;\' ' + (app.repo_enabled && app.display_mask == 0 ? '' : 'class=\'marketplace-unavailable\'') + '>';
    // App rating
    content += '<div style=\'padding: 5px 0px;\'>' + get_rating(app.rating, app.rating_count, false, false) + '</div>';
    // If software is installed and latest version, don't show selector checkbox
    if (app.display_mask != 0)
        content += '<div id=\'' + app.basename + '-na\' style=\'padding-top: 5px;\'>" . lang('marketplace_not_available') . "</div>';
    else if (app.up2date)
        content += '<div style=\'padding-top: 5px;\'><div>" . lang('base_version') . "</div> ' + app.latest_version + '</div>';
    else if (!app.repo_enabled)
        content += '<div></div>';
    else
        content += '<input class=\'theme-hidden marketplace-select\' type=\'checkbox\' id=\'select-' + app.basename + '\' name=\'' + app.basename + '\' ' + (app.incart ? 'CHECKED ' : '') + '/>';
    if (app.up2date || app.display_mask != 0) {
        // Don't show pricing information if its installed
    } else if (app.pricing.unit_price > 0 && app.pricing.exempt) {
        content += '<div>';
        content += '  <div style=\'text-decoration: line-through;\'>';
        content += '' + app.pricing.currency + app.pricing.unit_price + ' ' + UNIT[app.pricing.unit];
        content += '  </div>" . lang('marketplace_credit_available') . "';
        content += '</div>';
    } else if (app.pricing.unit_price > 0) {
        content += '<div>' + app.pricing.currency + app.pricing.unit_price
            + ' ' + UNIT[app.pricing.unit] + '</div>';
    } else {
        content += ' <div>" . lang('marketplace_free') . "</div>';
    }
    content += '  </div>';
    content += '  <div style=\'margin-left: 80px; width: 75%; padding: 0px 2px 5px 2px;\'>';
    content += '    <h2 style=\'padding:0px 0px 5px 0px; margin: 0px 0px 0px 0px;\'>';
    if (in_wizard_or_novice)
        content += app.name;
    else
        content += '<a class=\'marketplace\' href=\'/app/marketplace/view/' + app.basename + '\'>' + app.name + '</a>';
    content += '</h2>';
    content += '    <div style=\'font-size: 8pt;\'>';
    content += '      <div style=\'padding: 3px 0px;\'>' + app.description.substr(0, 100).replace(/\\n/g, '</p><p>') + '...</div>';
    content += '      <div style=\'padding: 3px 0px;\'>' + app.vendor.toUpperCase() + '</div>';
    content += '      <div style=\'padding:5px 0px 0px 0px;\'>';
    content += get_configure(app);
    content += '      </div>';
    content += '    </div>';
    content += '  </div>';
    content += '</div>';

    return content;
}

function get_app_as_tile(app) {
    var content = '';
    content += '<div class=\'marketplace-app-event marketplace-app' + (app.incart ? ' marketplace-selected' : '') + '\' id=\'' + app.basename + '\'>';
    if (app.installed)
        content += '<span class=\'marketplace-installed\'>INSTALLED</span>';
    content += '<img src=\'" . clearos_app_htdocs('marketplace') . "/market_default.png\' '
        + 'id=\'app-logo-' + app.basename + '\' style=\'padding: 2px 2px 5px 2px; float: left;\'>';
    if (app.pricing.unit_price > 0 && app.pricing.exempt) {
        content += '<div style=\'text-decoration: line-through; height: 20px; padding-top: 18px\'>' + app.pricing.currency + app.pricing.unit_price + ' ' + UNIT[app.pricing.unit] + '</div>';
    } else if (app.pricing.unit_price > 0) {
        content += '<div style=\'padding-top: 18px;\'>' + app.pricing.currency + app.pricing.unit_price + ' ' + UNIT[app.pricing.unit] + '</div>';
    } else {
        content += '<div style=\'padding-top: 18px;\'>" . strtoupper(lang('marketplace_free')) . "</div>';
    }
    if ((app.display_mask & 1) == 1)
        content += '<div id=\'' + app.basename + '-na\'>" . lang('marketplace_pro_exclusive') . "</div>';
    content += '<div style=\'clear: both;\'>' + (app.name.length < 50 ? app.name : app.name.substr(0, 25) + '...' + app.name.substr(app.name.length-10, app.name.length)) + '</div>';
    if (app.display_mask == 0 && !app.up2date && app.repo_enabled)
        content += '<input class=\'theme-hidden marketplace-select\' type=\'checkbox\' id=\'select-' + app.basename + '\' name=\'' + app.basename + '\' ' + (app.incart ? 'CHECKED ' : '') + '/>';
    content += '</div>';
    content += '<div class=\'marketplace-description-tooltip\'>';
    content += '<div class=\'marketplace-description-tooltip-content\'>';
    content += '<div style=\'padding: 5px; float: right;\'><img src=\'/cache/app-logo-' + app.basename.replace('/_/g', '-') + '.png\' alt=\'\'></div>';
    content += '<h2>' + app.name + '</h2>';
    content += '<p>' + app.description.replace(/\\n/g, '</p><p>') + '</p>';
    if (in_wizard_or_novice)
        content += '<p style=\'text-align: right;\'><a href=\'http://www.clearcenter.com/marketplace/type/?basename=' + app.basename + '\' target=\'_blank\'>' + lang_marketplace_learn_more + '</a></p></div></div>';
    else
        content += '<p style=\'text-align: right;\'><a href=\'/app/marketplace/view/' + app.basename + '\'>' + lang_marketplace_learn_more + '</a></p></div></div>';
    return content;
}

function get_rating(rating, num_of_ratings, show_avg, show_total) {
    // Rating system (< 0 means no rating yet)
    var rounded_rating = Math.round(rating);
    if (rating < 0) {
        return '" . lang('marketplace_not_rated') . "';
    } else {
        var content = '<span style=\'padding-bottom: 5px;\'>';
        for (var index = 0 ; index < rounded_rating; index++)
            content += '<img style=\'padding-left: 1px;\' '
                + 'src=\'" . clearos_app_htdocs('marketplace') . "/star_on.png\' alt=\'*\'>';
        for (var index = 5 ; index > rounded_rating; index--)
            content += '<img style=\'padding-left: 1px;\' '
                + 'src=\'" . clearos_app_htdocs('marketplace') . "/star_off.png\' alt=\'-\'>';
        if (show_avg && rating > 0)
            content += '&#160;&#160;(' + rating.toFixed(1) + ')';
        if (show_total && num_of_ratings > 0) {
            content += '<div>' + num_of_ratings + ' " . lang('marketplace_reviews') . "'.toLowerCase() + '</div></span>';
        } else {
            content += '</span>';
        }
    }
    return content;
}

function get_app_details(id) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/get_app_details',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&id=' + id,
        success: function(data) {
            if (data.code != undefined && data.code == 3) {
                $('#app_overview').remove();
                var options = new Object();
                options.redirect_on_close = '/app/registration/register';
                clearos_dialog_box('error', '" . lang('base_warning') . "', data.errmsg, options);
                return;
            } else if (data.code != undefined && data.code != 0) {
                $('#app_overview').html(data.errmsg);
                return;
            }

            // Hide the loading page
            $('#app_overview').remove();
            $('#tabs').show();
            $('#app_name').html(data.name);
            $('#app_description').html('<p>' + data.description.replace(/\\n/g, '</p><p>') + '</p>');
            if (data.installed_version == '')
                $('#app_installed_version').html('" . lang('marketplace_not_installed') . "');
            else
                $('#app_installed_version').html(data.installed_version);
            $('#app_latest_version').html(data.latest_version);
            $('#app_latest_release_date').html($.datepicker.formatDate('MM d, yy', new Date(data.latest_release_date)));
  
            $('.actions').hide();

            if ((data.pricing.pid_bitmask & 256) != 256) {
                // 256 is the bit that indicates there is an RPM associated with this app
                // If it is not set, hide irrelevant info in rhs info bar
                $('#field_installed_version').hide();
                $('#field_latest_version').hide();
                $('#field_latest_release_date').hide();
                $('#field_license').hide();
                $('#field_license_library').hide();
            }
            if (data.display_mask != 0) {
                // A non-zero display_mask means the app is not available to install
            } else if (data.installed && data.up2date) {
                $('#a_configure').show();
                if (!data.no_uninstall)
                    $('#a_uninstall').show();
            } else if (!data.repo_enabled) {
                $('#a_repo').show();
                // tack on repo name to href for repo
                $('#indiv_repo').attr('href', '/app/software_repository/index/detailed/' + data.repo_name);
            } else if (data.installed) {
                $('#a_upgrade').show();
                $('#a_configure').show();
                if (!data.no_uninstall)
                    $('#a_uninstall').show();
            } else if (data.pricing.exempt && data.pricing.unit_price != 0) {
                $('#a_install').show();
            } else if (data.pricing.unit_price != 0) {
                $('#a_buy').show();
            } else {
                $('#a_install').show();
            }

            $('#app_support_policy').html(
                '<div id=\'theme-support-policy-trigger\'>' +
                '<div class=\'theme-support theme-support-' + (data.supported & 1) + '\'></div>' +
                '<div class=\'theme-support theme-support-' + (data.supported & 2) + '\'></div>' +
                '<div class=\'theme-support theme-support-' + (data.supported & 4) + '\'></div>' +
                '<div class=\'theme-support theme-support-' + (data.supported & 8) + '\'></div>' +
                '<div class=\'theme-support theme-support-' + (data.supported & 16) + '\'></div>' +
                '</div>' +
                '<div class=\'theme-rhs-tooltip\'>' +
                '<p class=\'theme-support-legend-title\'>' + lang_marketplace_support_legend + '</p>' +
                '<div class=\'theme-support theme-support-1\' style=\'margin-right: 5px;\'></div>' +
                '<div class=\'theme-support-type\'>' + lang_marketplace_support_1_title + '</div>' +
                lang_marketplace_support_1_description +
                '</p>' +
                '<p><div class=\'theme-support theme-support-2\' style=\'margin-right: 5px;\'></div>' +
                '<div class=\'theme-support-type\'>' + lang_marketplace_support_2_title + '</div>' +
                lang_marketplace_support_2_description +
                '</p>' +
                '<p><div class=\'theme-support theme-support-4\' style=\'margin-right: 5px;\'></div>' +
                '<div class=\'theme-support-type\'>' + lang_marketplace_support_4_title + '</div>' +
                lang_marketplace_support_4_description +
                '</p>' +
                '<p><div class=\'theme-support theme-support-8\' style=\'margin-right: 5px;\'></div>' +
                '<div class=\'theme-support-type\'>' + lang_marketplace_support_8_title + '</div>' +
                lang_marketplace_support_8_description +
                '</p>' +
                '<p><div class=\'theme-support theme-support-16\' style=\'margin-right: 5px;\'></div>' +
                '<div class=\'theme-support-type\'>' + lang_marketplace_support_16_title + '</div>' +
                lang_marketplace_support_16_description +
                '</p>' +
                '<div class=\'theme-support-learn-more\'>' +
                '<a href=\'http://www.clearcenter.com/clearcare/landing\' target=\'_blank\'>' + lang_marketplace_learn_more + '...</a>' +
                '</div>' +
                '</div>'
            );

            $('#theme-support-policy-trigger').tooltip({
                offset: [-240, -310],
                position: 'center left',
                effect: 'slide',
                direction: 'left',
                slideOffset: 110, 
                opacity: 0.95,
                delay: 500,
                predelay: 200
            });

            $('#indiv_configure').attr('href', '/' + data.url_config);

            if ((data.display_mask & 1) == 1) {
                $('#availability_warning').html('" . lang('marketplace_professional_only') . "');
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
                $('#learn_more').hide();
            else
                $('#learn_more').attr('href', data.url_learn_more);

            if (data.url_documentation == '')
                $('#documentation').hide();
            else
                $('#documentation').attr('href', data.url_documentation);

            $('#app_repo').html(data.repo_name);

            if (data.pricing.unit_price == 0)
                $('#app_cost').html('" . lang('marketplace_free') . "');
            else
                $('#app_cost').html(data.pricing.currency + ' '
                    + data.pricing.unit_price.toFixed(2) + ' ' + UNIT[data.pricing.unit]);

            get_image('app-logo', data.basename, 'detail_img');
            $('#app_rating').html(get_rating(data.rating, data.rating_count, true, true));
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
            $('#app_introduced').html($.datepicker.formatDate('MM d, yy', new Date(data.introduced)));
            $('#app_devel_org').html(data.devel_org);
            $('#app_devel_contact').html(data.devel_contact);
            $('#app_devel_email').html(data.devel_email);
            $('#app_devel_website').html('<a href=\'' + data.devel_website + '\' target=\'_blank\'>'
                + data.devel_website + '</a>');
            // Screenshots
            var screenshots = data.screenshots;
            if (screenshots.length == 0)
                $('#app_screenshots').append('<div>" . lang('marketplace_no_screenshots') . "</div>');
            for (index = 0 ; index < screenshots.length; index++) {
                $('#app_screenshots').append('<div style=\'position: relative; width: 33%;'
                    + 'float: left;\'><a href=\'/cache/screenshot-' + screenshots[index].id + '.png\' title=\''
                    + screenshots[index].caption + '\'><img id=\'screenshot-' + index + '\' '
                    + 'src=\'" . clearos_app_htdocs('marketplace') . "/placeholder.png\' '
                    + 'style=\'height:120; width: 120; padding-bottom: 10px;\' alt=\''
                    + screenshots[index].caption + '\'></a></div>');
                get_image('screenshot', screenshots[index].id, 'screenshot-' + index);
            }

            // Complementary apps
            var complementary_apps = data.complementary_apps;
            if (complementary_apps.length == 0) {
                $('.complementary').remove();
            }
            for (index = 0 ; index < complementary_apps.length; index++) {
                $('#app_complementary').append(
                    '<div style=\'padding-bottom: 60px;\'><div style=\'position: '
                    + 'relative; width: 60px; float: left;\'><a href=\'' + complementary_apps[index].basename + '\'>'
                    + '<img align=\'left\' id=\'app-logo-complementary-' + complementary_apps[index].basename
                    + '\' src=\'" . clearos_app_htdocs('marketplace') . "/market_default.png\' '
                    + 'style=\'padding-bottom: 10px;\' alt=\'' + complementary_apps[index].name
                    + '\'></a></div><div style=\'position: relative; width: 400px; float: left;\'>'
                    + '<a href=\'' + complementary_apps[index].basename + '\' style=\'font-weight: bold;\'>'
                    + complementary_apps[index].name + '</a>&#160;&#160;' + get_rating(complementary_apps[index].rating, -1, true, false)
                    + '<p>' + complementary_apps[index].description.replace(/\\n/g, '</p><p>')
                    + '</p></div></div><br clear=\'all\'>'
                );
                get_image('app-logo', complementary_apps[index].basename, 'app-logo-complementary-' + complementary_apps[index].basename);
            }

            // Other apps by developer
            var other_apps = data.other_by_devel;
            if (other_apps.length == 0)
                $('#app_other_by_devel').append('<div>" . lang('marketplace_no_other_apps') . "</div>');
            for (index = 0 ; index < other_apps.length; index++) {
                $('#app_other_by_devel').append(
                    '<div style=\'padding-bottom: 60px;\'><div style=\'position: '
                    + 'relative; width: 60px; float: left;\'><a href=\'' + other_apps[index].basename + '\'>'
                    + '<img align=\'left\' id=\'app-logo-' + other_apps[index].basename
                    + '\' src=\'" . clearos_app_htdocs('marketplace') . "/market_default.png\' '
                    + 'style=\'padding-bottom: 10px;\' alt=\'' + other_apps[index].name
                    + '\'></a></div><div style=\'position: relative; width: 400px; float: left;\'>'
                    + '<a href=\'' + other_apps[index].basename + '\' style=\'font-weight: bold;\'>'
                    + other_apps[index].name + '</a>&#160;&#160;' + get_rating(other_apps[index].rating, -1, true, false)
                    + '<p>' + other_apps[index].description.replace(/\\n/g, '</p><p>')
                    + '</p></div></div><br clear=\'all\'>'
                );
                get_image('app-logo', other_apps[index].basename, 'app-logo-' + other_apps[index].basename);
            }

            // Ratings
            var ratings = data.ratings;
            if (ratings.length == 0) {
                $('#app_ratings').append('<p>" . lang('marketplace_no_reviews') . "</p>');
            }
            for (index = 0 ; index < ratings.length; index++) {
                var rating_header = ratings[index].comment;
                var show_full_comment = false;
                if (rating_header.indexOf('\\n') > 0) {
                    show_full_comment = true;
                    position = rating_header.indexOf('\\n');
                    if (position < 25)
                        rating_header = rating_header.substring(0, position);
                    else
                        rating_header = rating_header.substring(0, 25) + '...';
                } else if (rating_header.length > 25) {
                    rating_header = rating_header.substring(0, 25) + '...';
                    show_full_comment = true;
                }
                $('#app_ratings').append(
					'<div class=\'reviews\'>' +
                    '<div style=\'padding: 5px 0px 5px 0px;\'><b style=\'font-size: 1.2em;\'>' +
                    rating_header + '</b><div style=\'float: right; font-weight: bold;\'>' +
                    '<span style=\'font-size: 1.2em;\' id=\'agree_' + ratings[index].id + '\'>' +
                    ratings[index].agree + '</span><a class=\'peer_review\' href=\'#-1-' + ratings[index].id + '\'>' +
                    '<span style=\'padding: 0px 15px 0px 5px;\'>' +
                    '<img src=\'" . clearos_app_htdocs('marketplace') . "/icon_thumb_up.gif\'>&#160;" .
                    lang('marketplace_agree') . "</span></a>' +
                    '<span style=\'font-size: 1.2em;\' id=\'disagree_' + ratings[index].id + '\'>' +
                    ratings[index].disagree + '</span>' +
                    '<a class=\'peer_review\' href=\'#-0-' + ratings[index].id + '\'>' +
                    '<span style=\'padding: 0px 0px 0px 5px;\'>' +
                    '<img src=\'" . clearos_app_htdocs('marketplace') . "/icon_thumb_down.gif\'>&#160;" .
                    lang('marketplace_disagree') . "</span></a></div></div>' +
                    '<div>' +
                    get_rating(ratings[index].rating, -1, false, false) +
                    ' " . lang('marketplace_by') . " ' + ratings[index].pseudonym + ' - ' +
                    $.datepicker.formatDate('MM d, yy', new Date(ratings[index].timestamp)) +
                    '</div>' + (show_full_comment ? '<p>' + ratings[index].comment.replace(/\\n/g, '</p><p>') + '</p></div>' : '')
                );
            }

            $('a.peer_review').click(function (e) {
                e.preventDefault();
                var parts = $(this).attr('href').split('-');
                clearos_is_authenticated();
                peer_review(id, parseInt(parts[1]), parseInt(parts[2]));
            });

            var locales = data.locales;
            var contributors = data.locales_contributors;
            var contributor_list = '';
            for (index = 0 ; index < contributors.length; index++) {
                contributor_list += '<li style=\'margin-left: 0;\'>' + contributors[index].contact +
                    '  (<a href=\'mailto:' + contributors[index].email + '\'>' + contributors[index].email + '</a>)</li>';
            }
            $('#app_locale').append('<table id=\'locale_table\' border=\'0\' width=\'100%\'></table>');
            for (index = 0 ; index < locales.length; index++) {
                $('#locale_table').append(
                '<tr>' +
                '<td width=\'5%\'>' + locales[index].locale + '</td>' +
                '<td width=\'35%\'><div id=\'lang-' + locales[index].locale + '\' style=\'width:80%; height: 10px;\'></div></td>' +
                (index == 0 ? '<td width=\'60%\' rowspan=\'5\' valign=\'top\'>' +
                '<p style=\'font-weight: bold; text-decoration: underline;\'>" .
                lang('marketplace_translation_acknowledgements') . "' +
                '</p><ol style=\'list-style-position: inside; margin: 0px 0px 0px 0px;' +
                ' padding-left: 0px;\'>' + contributor_list + '</ol></td>' : '') +
                '</tr>'
                );
                $('#lang-' + locales[index].locale).progressbar({
                  value: locales[index].completion
                });
                // Fix brain damage from progress bar within tab
                $('#lang-' + locales[index].locale + ' div').removeClass('ui-widget-header');
            }
            var versions = data.versions;
            for (index = 0 ; index < versions.length; index++) {
                var logs = '';
                versions[index].change_log.forEach(function(item) {
                    logs += '<li style=\'margin-left: 0;\'>' + item + '</li>';
                });
                $('#app_versions').append(
                    '<table width=\'100%\' border=\'0\'>' +
                    '  <tr>' +
                    '    <td width=\'30%\'>" . lang('marketplace_version') . "</td>' +
                    '    <td width=\'70%\'>' + versions[index].version + '-' + versions[index].release + '</td>' +
                    '  </tr>' +
                    '  <tr>' +
                    '    <td>" . lang('marketplace_released') . "</td>' +
                    '    <td>' + $.datepicker.formatDate('MM d, yy', new Date(versions[index].released)) + '</td>' +
                    '  </tr>' +
                    (versions[index].repo_name != undefined && versions[index].repo_name != '' ?
                    '  <tr>' +
                    '    <td>" . lang('marketplace_software_repo') . "</td>' +
                    '    <td>' + versions[index].repo_name + '</td>' +
                    '  </tr>'
                    : '') +
                    (versions[index].hash != undefined && versions[index].hash != '' ?
                    '  <tr>' +
                    '    <td>" . lang('marketplace_sha256') . "</td>' +
                    '    <td>' + versions[index].hash + '</td>' +
                    '  </tr>'
                    : '') +
                    '  <tr>' +
                    '    <td>" . lang('marketplace_copyright') . "</td>' +
                    '    <td>' + versions[index].copyright + '</td>' +
                    '  </tr>' +
                    '  <tr>' +
                    '    <td>" . lang('marketplace_packager') . "</td>' +
                    '    <td>' + versions[index].packager + '</td>' +
                    '  </tr>' +
                    '  <tr>' +
                    '    <td>" . lang('marketplace_license') . "</td>' +
                    '    <td>' + versions[index].license + '</td>' +
                    '  </tr>' +
                    '  <tr>' +
                    '    <td>" . lang('marketplace_license_library') . "</td>' +
                    '    <td>' + versions[index].license_library + '</td>' +
                    '  </tr>' +
                    '  <tr>' +
                    '    <td valign=\'top\'>" . lang('marketplace_change_log') . "</td>' +
                    '    <td><ol style=\'list-style-position: inside; margin: 0px 0px 0px 0px;' +
                    ' padding-left: 0px;\'>' + logs + '</ol></td>' +
                    '  </tr>' +
                    '  <tr>' +
                    '    <td>" . lang('marketplace_notes') . "</td>' +
                    '    <td>' + versions[index].notes + '</td>' +
                    '  </tr>' +
                    '</table>' +
                    (index < versions.length - 1 ? '<hr>' : '')
                );
            }
            // Hack because of some interference with JQuery UI and tabs
            $('#documentation').css('padding', '1px 5px 1px 5px');
            $('#learn_more').css('padding', '1px 5px 1px 5px');
            $('#indiv_install').css('padding', '1px 5px 1px 5px');
            $('#indiv_repo').css('padding', '1px 5px 1px 5px');
            $('#indiv_configure').css('padding', '1px 5px 1px 5px');
            $('#warranty_link').css('padding', '1px 5px 1px 5px');

            $(function() {
                // TODO - We need some PHP function to grab image path
                $('#app_screenshots a').lightBox({
                        imageLoading: '/themes/default/images/loading.gif',
                        imageBtnPrev: '" . clearos_app_htdocs('marketplace') . "/prev.png',
                        imageBtnNext: '" . clearos_app_htdocs('marketplace') . "/next.png',
                        imageBtnClose: '" . clearos_app_htdocs('marketplace') . "/close.png'
                    }
                );
            });
        },
        error: function(xhr, text, err) {
            clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

function checkout(type) {
    $('#free_checkout').hide();
    if ($('#po:checked').val() !== undefined && $('#po_number').val() == '') {
        clearos_dialog_box('invalid_po_err', '" . lang('base_warning') . "', '" . lang('marketplace_invalid_po') . "');
        return;
    } else if (type == 'paid' && $('input[name=payment_method]:checked').val() == undefined) {
        clearos_dialog_box('invalid_method_err', '" . lang('base_warning') . "', '" . lang('marketplace_select_payment_method') . "');
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
            if ($('#preauth:checked').val() !== undefined)
                whirlyText += ' " . strtolower(lang('marketplace_credit_card')) . "...';
            if ($('#po:checked').val() !== undefined)
                whirlyText += ' " . strtolower(lang('marketplace_purchase_order')) . "...';
            else if ($('#debit:checked').val() !== undefined)
                whirlyText += ' " . strtolower(lang('marketplace_debit')) . "...';
        }
        processingText += '<div style=\\'width:100%; text-align: center;\\'><div class=\\'theme-loading-normal\\' style=\\'margin: 0 auto;\\'>' + whirlyText + '</div></div>';
        clearos_dialog_box('processing_info', '" . lang('marketplace_processing_order') . "...', processingText);
        $('#notes').html('<div class=\\'theme-loading-normal\\'>' + whirlyText + '</div>');
        // Hide buttons and show loading...
        $('#r_fee_install').hide();
        $('#r_eval_install').hide();
    }
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/checkout',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&payment=' + $('input[name=payment_method]:checked').val() + '&po=' + $('#po_number').val(),
        success: function(data) {
            if (data.code != 0) {
                $('#processing_info').dialog('close');
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
    if ($('#po:checked').val() !== undefined)
        $('#po_number').show();
    else
        $('#po_number').hide();
}

function prevent_review() {
    clearos_dialog_box('review_error', '" . lang('base_warning') . "', '" . lang('marketplace_no_install_no_review') . "');
}

function add_review(id) {
    $('#review_form').show();
    // Sometimes browser autocompletes this field
    $('#comment').val('');
    clearos_is_authenticated();
}

function submit_review(update) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/add_review',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&basename=' + $('#basename').val() + '&comment=' + $('#comment').val()
            + '&rating=' + $('#rating').val() + '&pseudonym=' + $('#pseudonym').val() + (update ? '&update=1' : ''),
        success: function(data) {
            if (data.code != 0) {
                // Check to see if there's already a review
                if (data.code == 8) {
                    clearos_confirm_review_replace();
                    return;
                }
                clearos_dialog_box('submit_review_error', '" . lang('base_warning') . "', data.errmsg);
            } else {
                $('#review_form').hide(); 
                var options = new Object();
                options.reload_on_close = true;
                clearos_dialog_box('submit_info', '" . lang('base_information') . "', data.status, options);
            }
        },
        error: function(xhr, text, err) {
            clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

function update_rating(rating) {
    for (var starindex = 1; starindex <= 5; starindex++) {
        if (rating >= starindex)
            $('#star' + starindex).attr('src', '" . clearos_app_htdocs('marketplace') . "/star_on.png');
        else
            $('#star' + starindex).attr('src', '" . clearos_app_htdocs('marketplace') . "/star_off.png');
    }
    $('#rating').val(rating);
}

function peer_review(id, approve, dbid) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/peer_review',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&basename=' + $('#basename').val() + '&id=' + id + '&approve=' + approve + '&dbid=' + dbid,
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

function get_novice_set() {

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/set_search',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&search=' + novice_set[novice_index].search,
        success: function(data) {
            get_apps(false, 0);
            $('#marketplace-novice-step').html((novice_index + 1) + ' / ' + novice_set.length);
            $('.theme-help-box-breadcrumb').html(novice_set[novice_index].title);
            $('#marketplace-novice-description').html(novice_set[novice_index].description);
            $('#inline-help-title-0').html(novice_set[novice_index].helptitle);
            $('#inline-help-content-0').html(novice_set[novice_index].helpcontent);
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

$(document).ready(function() {
    if ($(location).attr('href').match('.*marketplace\/wizard\/selection\/.*$') != null)
        window.location = '/app/marketplace/wizard';

    $('#theme-left-menu a').css('min-width', '105px');
    $('#display_options a').css('min-width', '');
    $('#tabs-overview a').css('min-width', '85px');
    // Wizard previous/next button handling
    $('#wizard_nav_next').click(function() {
        if ($(location).attr('href').match('.*marketplace\/wizard$') != null) {
            // Hack...ajax is required to override session value on 'next hop'
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '/app/marketplace/wizard/set_mode',
                data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&mode=' + $('#wizard_marketplace_mode').val(),
                success: function(data) {
                    window.location = '/app/base/wizard/next_step';
                },
                error: function(xhr, text, err) {
                    window.location = '/app/base/wizard/next_step';
                }
            });
        } else {
            window.location = '/app/base/wizard/next_step';
        }
    });
    if ($(location).attr('href').match('.*marketplace\/wizard$') != null) {
        $('.mode').mouseover(function() {
            if (this.id != $('#wizard_marketplace_mode').val())
                $(this).addClass('marketplace-category-hover');
        }).mouseout(function() {
            if (this.id != $('#wizard_marketplace_mode').val())
                $(this).removeClass('marketplace-category-hover');
        }).click(function() {
            $('.mode').removeClass('marketplace-category-selected');
            $('.mode').removeClass('marketplace-category-hover');
            var mySelector = this;
            $('#wizard_marketplace_mode').val(mySelector.id);
            $('#' + mySelector.id).addClass('marketplace-category-selected');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '/app/marketplace/wizard/set_mode',
                data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&mode=' + $('#wizard_marketplace_mode').val()
            });
        });
        $('#' + $('#wizard_marketplace_mode').val()).addClass('marketplace-category-selected');
    }
    if ($('#wizard_marketplace_mode').val() == 'mode1') {
        $('div.theme-help-box-content').html($('#app-selector-header').html());
        $('#app-selector-header').remove();
        $('.novice-select').on({
            click: function() {
                $('#marketplace-loading').show();
                $('#marketplace-app-container').html('');
                novice_index = this.id.replace('novice-', '');
                get_novice_set();
            }
        });
    }
    if ($('#wizard_marketplace_mode').val() == 'mode2') {
        // Ugly...but we move the div contents up to the help box
        $('div.theme-help-box-content').html($('#app-selector-header').html());
        // Then delete div completely
        $('#app-selector-header').remove();

        // Add help content
        $('#inline-help-title-0').html('" . lang('marketplace_categories') . "');
        $('#inline-help-content-0').html(
            '<p>" . lang('marketplace_mode_category_help') . "</p>' +
            '<p>" . lang('marketplace_mode_category_best_practices_help') . "</p>'
        );
        $('.marketplace-category').on({
            mouseover: function() {
                if ($(this).hasClass('marketplace-category-selected')) {
                    category_class = 'marketplace-category-selected';
                } else {
                    $(this).addClass('marketplace-hover');
                    category_class = '';
                }
            },
            mouseout: function() {
                $(this).removeClass('marketplace-hover');
                if (category_class !== '')
                    $(this).addClass(category_class);
            },
            click: function() {
                $('.marketplace-category').removeClass('marketplace-category-selected');
                $('.marketplace-category').removeClass('marketplace-hover');
                if ($('#select-' + this.id).is(':checked')) {
                    $('#select-' + this.id).removeAttr('checked');
                    $(this).removeClass('marketplace-category-selected');
                    category_class = '';
                } else {
                    $('#select-' + this.id).attr('checked', true);
                    $(this).removeClass('marketplace-hover');
                    $(this).addClass('marketplace-category-selected');
                    category_class = 'marketplace-category-selected';
                }
                $('#marketplace-loading').show();
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

    if ($(location).attr('href').match('.*marketplace\/install') != null && $('#total').val() > 0) {
        $('#theme_wizard_nav_next').hide();
    } else if ($(location).attr('href').match('.*marketplace\/install') != null && $('#num_of_apps').val() > 0) {
        $('#theme_wizard_nav_next').hide();
    } else if ($(location).attr('href').match('.*marketplace\/install') != null && $('#num_of_apps').val() == 0) {
        $('#free_checkout').hide();
        table_install_apps.fnClearTable();
    }

    if ($(location).attr('href').match('.*marketplace\/progress') != null) {
        $('#theme_wizard_nav').hide();
        $('#theme_wizard_complete').show();
    }
    
    if ($('#number_of_apps_to_display').length != 0)
        apps_to_display_per_page = $('#number_of_apps_to_display').val();

    $('.filter_event').css('width', 160);

    if ($(location).attr('href').match('.*progress$|.*progress\/busy') != null) {
        get_progress();
    } else if ($(location).attr('href').match('.*install') != null || $(location).attr('href').match('.*install\/delete\/.*') != null) {
        if ($('#total').val() == 0) {
            allow_noauth_mods();
        } else {
            $('#infotable').show();
            auth_options.reload_after_auth = true;
            clearos_is_authenticated();
            if ($('#total').val() > 0)
                get_account_info(false);
            else
                $('#account_information').remove();
        }
        $('#install_apps tbody tr').each(function(){
            $(this).find('td:eq(1)').attr('nowrap', 'nowrap');
            $(this).find('td:eq(4)').attr('nowrap', 'nowrap');
        });
    }

    $('#toggle_select').click(function(e) {
        e.preventDefault();
        $('#toggle_select').html('<span class=\'theme-loading-small\'></span>');
        $('.theme-loading-small').css('margin', '1px 2px 1px 4px');
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

    $('#comment').keyup(function() {
        var charLength = $(this).val().length;
        $('#char-remaining').html(1000 - charLength + ' " . lang('marketplace_remaining') . "');
    });

    $('#marketplace-home').click(function() {
        window.location = '/app/marketplace';
    });

    $('#marketplace-home').mouseover(function() {
        $(this).css('cursor', 'pointer');
    });

    $('.filter_event').change(function(event) {
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
    $('input').click(function() {
        if (this.id == 'add_review')
            add_review(this.id);
        else if (this.id == 'prevent_review')
            prevent_review();
        else if (this.id == 'submit_review')
            submit_review(false);
        else if (this.id == 'cancel_review')
            $('#review_form').hide();
        else if (this.id == 'indiv_upgrade')
            update_cart($('#basename').val(), this.id, true, true);
        else if (this.id == 'indiv_buy')
            update_cart($('#basename').val(), this.id, true, true);
        else if (this.id == 'indiv_install')
            update_cart($('#basename').val(), this.id, true, true);
        else if (this.id == 'free_checkout')
            checkout('free');
        else if (this.id == 'buy_checkout')
            checkout('paid');
        else if (this.id == 'eval_checkout')
            checkout('eval');
        else if (this.id.match('^delete-'))
            remove_from_checkout(this.id.substr(7, this.id.length));
    });

});

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

            $('#progress').animate_progressbar(parseInt(json.progress));

            $('#overall').animate_progressbar(parseInt(json.overall));

            if (json.code === 0) {
                $('#details').html(json.details);
            } else if (json.code === -999) {
                // Do nothing...no data yet
            } else {
                // Uh oh...something bad happened
                $('#progress').progressbar({value: 0});
                $('#overall').progressbar({value: 0});
                $('#details').html(json.errmsg);
            }

            if ($(location).attr('href').match('.*progress\/busy$') != null) {
                // We're on the busy page...let's check again in 5 seconds.
                window.setTimeout(get_progress, 5000);
            } else if (json.overall == 100) {
                if ($('#theme_wizard_nav_next').length == 0) {
                    $('#reload_button').show();
                    $('#progress').progressbar({value: 100});
                    $('#overall').progressbar({value: 100});
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
            // TODO: This seems problematic on my slow network connection (PB).  More digging required.
            // clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
            window.setTimeout(get_progress, 1000);
        }
    });
}

function clearos_eula(basename, id, message) {
  $('#theme-page-container').append('<div id=\"' + id + '\" title=\"" . lang('marketplace_eula') . "\">' +
      '<div style=\"text-align: left\">' + message + '</div>' +
    '</div>'
  );
  $('#' + id).dialog({
    modal: true,
    width: 600,
    height: 400,
    resizeable: false,
    draggable: false,
    closeOnEscape: false,
    buttons: {
      '" . lang('marketplace_read_and_understand') . "': function() {
        $(this).dialog('close');
      },
      '" . lang('marketplace_do_not_agree') . "': function() {
        window.location = '/app/marketplace/install/delete/' + basename;
      }
    }
  });
  $('.ui-dialog-titlebar-close').hide();
}

function clearos_confirm_review_replace() {
  $('#theme-page-container').append('<div id=\"confirm_view_replace\" title=\"" . lang('base_warning') . "\">' +
      '<div class=\"dialog_alert_icon\"></div>' +
      '<div class=\"dialog_alert_text\">" . lang('marketplace_confirm_review_replace') . "</div>' +
    '</div>'
  );
  $('#confirm_view_replace').dialog({
    autoOpen: true,
    bgiframe: true,
    title: false,
    modal: true,
    resizable: false,
    draggable: false,
    closeOnEscape: false,
    height: 180,
    width: 350,
    buttons: {
      '" . lang('base_cancel') . "': function() {
        $(this).dialog('close');
      },
      '" . lang('base_confirm') . "': function() {
        $(this).dialog('close');
        submit_review(true);
      }
    }
  });
  $('.ui-dialog-titlebar-close').hide();
}

function clearos_sdn_account_setup(landing_url, username, device_id) {
  $('#theme-page-container').append('<div id=\"sdn_marketplace_setup_dialog\" title=\"" . lang('marketplace_sdn_account_setup') . "\">' +
      '<p style=\"text-align: left; width: 250px;\">' +
        '" . lang('marketplace_sdn_account_setup_help_1') . "' +
      '</p>' +
      '<p style=\"text-align: left; width: 250px;\">' +
        '" . lang('marketplace_sdn_account_setup_help_2') . "' +
      '</p>' +
    '</div>'
  );
  $('#sdn_marketplace_setup_dialog').dialog({
    autoOpen: true,
    bgiframe: true,
    title: false,
    modal: true,
    resizable: false,
    draggable: false,
    closeOnEscape: false,
    height: 250,
    width: 450,
    buttons: {
      '" . lang('marketplace_setup_payment_on_clear') . "': function() {
        $(this).dialog('close');
        window.open(landing_url + '?username=' + username + '&device_id=' + device_id);
      },
      '" . lang('base_cancel') . "': function() {
        $(this).dialog('close');
      }
    }
  });
  $('.ui-dialog-titlebar-close').hide();
}

";

// vim: syntax=javascript ts=4
