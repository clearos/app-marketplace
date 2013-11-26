<?php

/**
 * Marketplace filter view.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage views
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/Company/terms.html ClearSDN license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('base_category');
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

$buttons = array(
    anchor_custom('/app/marketplace/install', lang('marketplace_install'), 'high'),
    anchor_cancel('/app/marketplace/select/cancel')
);

if ($mode == 'qsf-tool') {

    echo "
    <div style='display: $display;'>
      <div id='theme-left-menu-top'></div>
      <div id='marketplace-left-menu'>
        <h3 class='theme-menu-item-active' style='font-size: 11pt; font-weight: normal; margin-top: 0px;'>" . lang('base_help') . "</h3>
        <h3 style='color: #666666; font-size: 13px; font-weight: bold; margin-top: 15px;'>" . lang('marketplace_qsf') . "</h3>
        <p style='font-size: 13px;'>" . lang('marketplace_mode_qsf_help') . "</p>
        <h3 style='color: #666666; font-size: 13px; font-weight: bold; margin-top: 15px;'>" . lang('marketplace_best_practices') . "</h3>
        <p style='font-size: 13px;'>" . lang('marketplace_mode_qsf_best_practices_help') . "</p>
        <div style='text-align: center; margin: 10px 0px;'>" .
        field_button_set($buttons) . "
        </div>
      </div>
    </div>
    ";
    return;
} else if ($mode == 'feature-wizard') {
    echo "
    <div style='display: $display;'>
        <div id='theme-left-menu-top'></div>
        <div id='marketplace-left-menu'>
            <h3 class='theme-menu-item-active' style='font-size: 11pt; font-weight: normal; margin-top: 0px;'>" . lang('base_help') . "</h3>
            <h3 style='color: #666666; font-size: 13px; font-weight: bold; margin-top: 15px;' id='inline-help-title-0'></h3>
            <p style='font-size: 13px;' id='inline-help-content-0'></p>
        <div style='text-align: center; margin: 10px 0px;'>" .
        field_button_set($buttons) . "
        </div>
        </div>
    </div>
    ";
    return;
}

$search_string = '';
if (isset($filter) && is_array($filter)) {
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
if (isset($filter) && is_array($filter)) {
    foreach ($filter as $entry ) {
        if (preg_match('/\d\d_.*/', $entry['search']))
            continue;
        echo "'" . addslashes($entry['search']) . "',\n";
    }
}

echo "
        ];
        $( '#search' ).autocomplete({
            source: availableTags
        });
    });
</script>";

$display_options = button_set(
    array(
        anchor_custom('/app/marketplace/display/index/tile', "<i class='fa fa-th-large'></i>", 'high', array('no_escape_html' => TRUE)),
        anchor_custom('/app/marketplace/display/index/list', "<i class='fa fa-th-list'></i>", 'high', array('no_escape_html' => TRUE)),
        anchor_custom('/app/marketplace/display/index/table', "<i class='fa fa-table'></i>", 'high', array('no_escape_html' => TRUE))
    )
);
echo "
<div style='display: $display;'>
  <div id='theme-left-menu-top'></div>
  <div id='theme-left-menu'>
    <h3 class='theme-left-menu-category'><a href='#'>" . lang('marketplace_marketplace_options') . "</a></h3>
    <div>" .
      form_open('/marketplace/search') . "
      <div style='padding-top: 15px; text-align: center;' id='display_options'>" . $display_options . "</div>
      <h3>" . lang('marketplace_search') . "</h3>
      <div style='margin: 6px 24px 10px 0px; border: solid 1px #D1D3D4;'>
        <input type='text' name='search' id='search' value='$search_string' class='marketplace-search-no-focus' onfocus='clear_entry();' style='height: 20px; border: none; float: left; width: 140px; font-size: 8pt; padding-left:3px;' />
        <div class='marketplace-search-bar'></div>
    </div>
    <h3 style='clear: both;'>" . lang('marketplace_filters') . "</h3>
    <div id='filter' style='margin: 10px 0px 0px 0px;'>
      <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_category' id='filter_category'>
        <option value='all'" . (isset($category_select['all']) ? $category_select['all'] : '') . ">" . lang('marketplace_filter_by_category') . "</option>
        <option value='cloud'" . (isset($category_select['cloud']) ? $category_select['cloud'] : '') . ">" . lang('base_category_cloud') . "</option>
        <option value='server'" . (isset($category_select['server']) ? $category_select['server'] : '') . ">" . lang('base_category_server') . "</option>
        <option value='network'" . (isset($category_select['network']) ? $category_select['network'] : '') . ">" . lang('base_category_network') . "</option>
        <option value='gateway'" . (isset($category_select['gateway']) ? $category_select['gateway'] : '') . ">" . lang('base_category_gateway') . "</option>
        <option value='system'" . (isset($category_select['system']) ? $category_select['system'] : '') . ">" . lang('base_category_system') . "</option>
        <option value='reports'" . (isset($category_select['reports']) ? $category_select['reports'] : '') . ">" . lang('base_category_reports') . "</option>
      </select>
      <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_price' id='filter_price'>
        <option value='all'" . (isset($price_select['all']) ? $price_select['all'] : '') . ">" . lang('marketplace_filter_by_price') . "</option>
        <option value='free'" . (isset($price_select['free']) ? $price_select['free'] : '') . ">" . lang('marketplace_free') . "</option>
        <option value='paid'" . (isset($price_select['paid']) ? $price_select['paid'] : '') . ">" . lang('marketplace_paid') . "</option>
      </select>
      <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_status' id='filter_status'>
        <option value='all'" . (isset($status_select['all']) ? $status_select['all'] : '') . ">" . lang('marketplace_filter_by_install') . "</option>
        <option value='installed'" . (isset($status_select['installed']) ? $status_select['installed'] : '') . ">" . lang('marketplace_installed_apps') . "</option>
        <option value='upgrade_available'" . (isset($status_select['upgrade_available']) ? $status_select['upgrade_available'] : '') . ">" . lang('marketplace_upgrade_apps') . "</option>
        <option value='new'" . (isset($status_select['new']) ? $status_select['new'] : '') . ">" . lang('marketplace_new_apps') . "</option>
      </select>
      <select style='font-size: 8pt; margin-bottom: 2px;' class='filter_event' name='filter_intro' id='filter_intro'>
        <option value='all'" . (isset($intro_select['all']) ? $intro_select['all'] : '') . ">" . lang('marketplace_filter_by_intro') . "</option>
        <option value='7'" . (isset($intro_select['7']) ? $intro_select['7'] : '') . ">" . lang('marketplace_added_7_day') . "</option>
        <option value='30'" . (isset($intro_select['30']) ? $intro_select['30'] : '') . ">" . lang('marketplace_added_30_day') . "</option>
        <option value='180'" . (isset($intro_select['180']) ? $intro_select['180'] : '') . ">" . lang('marketplace_added_6_month') . "</option>
        <option value='365'" . (isset($intro_select['365']) ? $intro_select['365'] : '') . ">" . lang('marketplace_added_1_year') . "</option>
      </select>
    </div>
    <input type='hidden' name='" . $this->security->csrf_token_name . "' value='" . $this->security->csrf_hash . "' />  
";
echo "<div style='text-align: center; width: 160px; margin-top: 5px;'>";
echo anchor_custom('/app/marketplace/search/reset_filter', lang('marketplace_reset_filters'), 'high');
echo "</div>";
echo "<h3 style='clear: both;'>" . lang('marketplace_tools') . "</h3>";
echo "<div style='text-align: center; width: 160px;'>";
echo anchor_custom('/app/marketplace/select', lang('marketplace_feature_wizard'), 'high');
echo "<div style='margin-top: 5px;'>";
echo anchor_custom('/app/marketplace/qsf', lang('marketplace_quick_select_file'), 'high');
echo "</div>";
echo "</div>";
echo form_close();
echo "
        </div>
    </div>
</div>
";
