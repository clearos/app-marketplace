<?php


/**
 * Marketplace view.
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
$this->lang->load('marketplace');
$this->load->view('marketplace/banner');

echo "<div id='app_list_overview' style='text-align: center; padding: 60px 10px 30px 10px;'>";
if ($search)
    echo "<div style='padding: 10px 0px 0px 0px;'>" . loading('normal', lang('marketplace_searching_marketplace'), array('icon-below' => TRUE)) . "</div>";
else
    echo "<div style='padding: 10px 0px 0px 0px;'>" . loading('normal', lang('marketplace_loading')) . "</div>";
echo "</div>";

echo form_open('marketplace/settings', array('method' => 'GET', 'name' => 'form_app_list', 'id' => 'form_app_list'));
echo "<div id='marketplace-app-container'></div>";
echo form_close();
echo "<div id='pagination-bottom' style='font-size: .7em; padding: 0px 10px 30px 0px;'></div>";
echo "<script type='text/javascript'>\n";
echo "  $(document).ready(function() {\n";
echo "    get_apps(\n";
echo "      false,\n";
echo "      " . isset($page) ? (int)$page : 0;
echo "    );\n";
echo "  });\n";
echo "</script>\n";
echo "<input id='number_of_apps_to_display' type='hidden' value='$number_of_apps_to_display'>";
echo "<input id='display_format' type='hidden' value='$display_format'>";
