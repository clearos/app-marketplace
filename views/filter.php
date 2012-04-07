<?php

/**
 * Marketplace banner view.
 *
 * @category   Apps
 * @package    Marketplace
 * @subpackage Views
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/Company/terms.html ClearSDN license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('marketplace');

///////////////////////////////////////////////////////////////////////////////
// Marketplace filter
///////////////////////////////////////////////////////////////////////////////

// TODO: cleanup the "menu" theme stuff - for later.
// TODO: general cleanup

if (preg_match('/\/wizard\//', $_SERVER["PHP_SELF"]))
    $display = 'none';
else
    $display = 'inline';

echo "
<div style='display: $display;'>
    <div id='theme-left-menu-top'></div>
    <div id='theme-left-menu'>
        <h3 class='theme-left-menu-category'><a href='#'>" . lang('marketplace_marketplace_options') . "</a></h3>
        <div>
";

echo "
<form action='/app/marketplace/search' method='post' accept-charset='utf-8'>
    <h3>Search</h3>
    <div style='padding: 6 10 10 0;'>
      <input type='text' name='search' id='search' value='$search' onfocus='clear_entry();' style='width: 160px; font-size: 8pt;' class='marketplace-search-bar'>
    </div>
    <h3>Filter</h3>
    <div id='filter' style='margin: 10 0 10 0;'>
        <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_category' id='filter_category'>
            <option value='category_all'" . $category['select']['all'] . ">" . lang('marketplace_filter_by_category') . "</option>
            <option value='server'" . $category['select']['server'] . ">" . lang('base_server') . "</option>
            <option value='network'" . $category['select']['network'] . ">" . lang('base_network') . "</option>
            <option value='gateway'" . $category['select']['gateway'] . ">" . lang('base_gateway') . "</option>
            <option value='system'" . $category['select']['system'] . ">" . lang('base_system') . "</option>
        </select>
        <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_price' id='filter_price'>
            <option value='price_all'" . $price['select']['all'] . ">" . lang('marketplace_filter_by_price') . "</option>
            <option value='free'" . $price['select']['free'] . ">" . lang('marketplace_free') . "</option>
            <option value='paid'" . $price['select']['paid'] . ">" . lang('marketplace_paid') . "</option>
        </select>
        <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_install' id='filter_install'>
            <option value='install_all'" . $install['select']['all'] . ">" . lang('marketplace_filter_by_install') . "</option>
            <option value='installed'" . $install['select']['installed'] . ">" . lang('marketplace_installed_apps') . "</option>
            <option value='upgrades'" . $install['select']['upgrades'] . ">" . lang('marketplace_upgrade_apps') . "</option>
            <option value='new'" . $install['select']['new'] . ">" . lang('marketplace_new_apps') . "</option>
        </select>
        <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_intro' id='filter_intro'>
            <option value='intro_all'" . $intro['select']['all'] . ">" . lang('marketplace_filter_by_intro') . "</option>
            <option value='7'" . $intro['select']['7'] . ">" . lang('marketplace_added_7_day') . "</option>
            <option value='30'" . $intro['select']['30'] . ">" . lang('marketplace_added_30_day') . "</option>
            <option value='180'" . $intro['select']['180'] . ">" . lang('marketplace_added_6_month') . "</option>
            <option value='365'" . $intro['select']['365'] . ">" . lang('marketplace_added_1_year') . "</option>
        </select>
    </div>
    <input type='hidden' name='" . $this->security->csrf_token_name . "' value='" . $this->security->csrf_hash . "' />  
</form>
";

echo "
        </div>
    </div>
</div>
";
