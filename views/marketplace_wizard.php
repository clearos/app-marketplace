<?php

/**
 * Marketplace view.
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
$this->load->view('marketplace/filter');

echo "<div id='app_list_overview' style='text-align: center; padding: 60 10 30 10;'>";
if (!isset($search))
    echo "<div style='padding: 10 0 0 0;'>" . loading('normal', lang('marketplace_loading')) . "</div>";
else
    echo "<div style='padding: 10 0 0 0;'>" . loading('normal', lang('marketplace_searching_marketplace'), array('icon-below' => TRUE)) . "</div>";
echo "</div>";

if (!isset($search))
    $search = lang('marketplace_search_terms');

$headers = array(
    'Name',
    lang('marketplace_description')
);

echo "<div class='theme-summary-table-container ui-widget'>";
echo "<table width='100%' border='0' id='marketplace' cellpadding='5' cellspacing='0'></table>";
echo "</div>";
echo "<div id='pagination-bottom' style='font-size: .7em; padding: 0 0 30 0;'></div>";
echo "<script type='text/javascript'>\n";
echo "  $(document).ready(function() {\n";
echo "    get_apps(\n";
echo "      false,\n";
echo "      '" . (isset($search) && $search != lang('marketplace_search_terms') ? $search : '') . "',\n";
echo "      " . isset($page) ? (int)$page : 0;
echo "    );\n";
echo "  });\n";
echo "</script>\n";
echo "<input id='number_of_apps_to_display' type='hidden' value='$number_of_apps_to_display'>";