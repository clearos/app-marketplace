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

$search_string = '';
if (is_array($filter)) {
    $first = current($filter);
    // Populate LHS widget
    if ($first['active']) {
        $search_string = $first['search'];
        $category_select[$first['category']] = " SELECTED";
        $price_select[$first['price']] = " SELECTED";
        $status_select[$first['status']] = " SELECTED";
        $intro_select[$first['intro']] = " SELECTED";
    }
}
echo "
<div style='display: $display;'>
    <div id='theme-left-menu-top'></div>
    <div id='theme-left-menu'>
        <h3 class='theme-left-menu-category'><a href='#'>" . lang('marketplace_marketplace_options') . "</a></h3>
        <div>
";

echo "
<style>
    .ui-autocomplete {
        max-height: 100px;
        overflow-y: auto;
        /* prevent horizontal scrollbar */
        overflow-x: hidden;
        /* add padding to account for vertical scrollbar */
        padding-right: 20px;
        background: #EEE;
        border: 1px solid #cccccc;
        text-align: left;
        width: 135px;
    }
    .ui-menu .ui-menu-item a.ui-state-hover, .ui-menu .ui-menu-item a.ui-state-active {
        margin: 0px;
    }
</style>
<script>
    $(function() {
        var availableTags = [
";
foreach ($filter as $entry )
    echo "'" . addslashes($entry['search']) . "',\n";


echo "
        ];
        $( '#search' ).autocomplete({
            source: availableTags
        });
    });
</script>

" . form_open('/marketplace/search') . "
    <h3>" . lang('marketplace_search') . "</h3>
    <div style='margin: 6px 10px 10px 0px; border: solid 1px #D1D3D4;'>
      <input type='text' name='search' id='search' value='$search_string' class='marketplace-search-no-focus' onfocus='clear_entry();' style='height: 20px; border: none; float: left; width: 140px; font-size: 8pt; padding-left:3px;' /><div class='marketplace-search-bar'></div>
    </div>
    <h3 style='clear: both;'>" . lang('marketplace_filters') . "</h3>
    <div id='filter' style='margin: 10px 0px 0px 0px;'>
        <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_category' id='filter_category'>
            <option value='all'" . $category_select['all'] . ">" . lang('marketplace_filter_by_category') . "</option>
            <option value='server'" . $category_select['server'] . ">" . lang('base_server') . "</option>
            <option value='network'" . $category_select['network'] . ">" . lang('base_network') . "</option>
            <option value='gateway'" . $category_select['gateway'] . ">" . lang('base_gateway') . "</option>
            <option value='system'" . $category_select['system'] . ">" . lang('base_system') . "</option>
        </select>
        <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_price' id='filter_price'>
            <option value='all'" . $price_select['all'] . ">" . lang('marketplace_filter_by_price') . "</option>
            <option value='free'" . $price_select['free'] . ">" . lang('marketplace_free') . "</option>
            <option value='paid'" . $price_select['paid'] . ">" . lang('marketplace_paid') . "</option>
        </select>
        <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_status' id='filter_status'>
            <option value='all'" . $status_select['all'] . ">" . lang('marketplace_filter_by_install') . "</option>
            <option value='installed'" . $status_select['installed'] . ">" . lang('marketplace_installed_apps') . "</option>
            <option value='upgrade_available'" . $status_select['upgrade_available'] . ">" . lang('marketplace_upgrade_apps') . "</option>
            <option value='new'" . $status_select['new'] . ">" . lang('marketplace_new_apps') . "</option>
        </select>
        <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_intro' id='filter_intro'>
            <option value='all'" . $intro_select['all'] . ">" . lang('marketplace_filter_by_intro') . "</option>
            <option value='7'" . $intro_select['7'] . ">" . lang('marketplace_added_7_day') . "</option>
            <option value='30'" . $intro_select['30'] . ">" . lang('marketplace_added_30_day') . "</option>
            <option value='180'" . $intro_select['180'] . ">" . lang('marketplace_added_6_month') . "</option>
            <option value='365'" . $intro_select['365'] . ">" . lang('marketplace_added_1_year') . "</option>
        </select>
    </div>
    <input type='hidden' name='" . $this->security->csrf_token_name . "' value='" . $this->security->csrf_hash . "' />  
";
echo form_close();

echo form_open('/marketplace/search');
echo "<div style='text-align: center'>";
echo form_submit_custom('reset_filter', lang('marketplace_reset_filters'), 'high');
echo "</div>";
echo form_close();
echo "
        </div>
    </div>
</div>
";
