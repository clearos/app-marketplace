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

echo "<div style='text-align: center; padding: 10px 10px 0px 10px;'>";
echo "<div id='marketplace-loading' style='padding: 10px 0px 0px 0px;'>" . loading('normal', lang('marketplace_searching_marketplace'), array('icon-below' => TRUE)) . "</div>";
echo "<div id='app_list_overview'></div>";
echo "</div>";

$headers = array(
    'Name',
    lang('marketplace_description')
);

echo form_open('marketplace/settings', array('method' => 'GET', 'name' => 'form_app_list', 'id' => 'form_app_list'));
echo "<div id='marketplace-app-container'></div>";
echo form_close();
echo "<script type='text/javascript'>\n";
echo "  $(document).ready(function() {\n";
echo "    get_apps(false, 0);\n";
echo "    $(function() {
            $('#radio').buttonset();
          });
";
echo "  });\n";
echo "</script>\n";
echo "<div id='app-selector-header'>
        <div id='category-container'>
          <div id='radio' class='ui-buttonset'>
            <input type='radio' id='category-network' name='radio' class='marketplace-category' checked='checked' /><label for='category-network'>" . lang('base_network') . "</label>
            <input type='radio' id='category-gateway' name='radio' class='marketplace-category' /><label for='category-gateway'>" . lang('base_gateway') . "</label>
            <input type='radio' id='category-server' name='radio' class='marketplace-category' /><label for='category-server'>" . lang('base_server') . "</label>
            <input type='radio' id='category-system' name='radio' class='marketplace-category' /><label for='category-system'>" . lang('base_system') . "</label>
            <input type='radio' id='category-reports' name='radio' class='marketplace-category' /><label for='category-reports'>" . lang('base_reports') . "</label>
          </div>
        </div>
      </div>
";
echo "<input id='number_of_apps_to_display' type='hidden' value='$number_of_apps_to_display'>";
echo "<input id='display_format' type='hidden' value='$display_format'>";
