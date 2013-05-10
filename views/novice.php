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

echo "<script type='text/javascript'>\n";
echo "  $(document).ready(function() {\n";
echo "    get_novice_set(0);\n";
echo "  });\n";
echo "</script>\n";
echo "<input id='number_of_apps_to_display' type='hidden' name='number_of_apps_to_display' value='$number_of_apps_to_display'>";
echo "<div id='app-selector-header'>";
echo "  <div id='category-container'>";
echo "    <div id='novice-prev' class='marketplace-novice-nav novice-prev'></div>";
echo "    <div class='marketplace-novice novice-selected'>";
echo "      <div id='marketplace-novice-title'></div>";
echo "      <div id='marketplace-novice-description'></div>";
echo "    </div>";
echo "  <div id='novice-next' class='marketplace-novice-nav novice-next'></div>";
echo "  <div id='marketplace-novice-step'></div>";
echo "  </div>";
echo "</div>";
echo "<br clear='both'>";
echo "<div style='text-align: center;'>";
echo "<div id='marketplace-loading' style='padding: 10px 0px 0px 0px;'>" . loading('normal', lang('marketplace_searching_marketplace'), array('icon-below' => TRUE)) . "</div>";
echo "<div id='app_list_overview'></div>";
echo "</div>";

echo form_open('marketplace/settings', array('method' => 'GET', 'name' => 'form_app_list', 'id' => 'form_app_list'));
echo "<div id='marketplace-app-container'></div>";
echo form_close();
echo "<input id='display_format' type='hidden' value='$display_format'>";
