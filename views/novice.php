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

echo "<script type='text/javascript'>\n";
echo "  $(document).ready(function() {\n";
echo "    get_novice_set(0);\n";
echo "    $(function() {
            $('#radio').buttonset();
          });
";
echo "  });\n";
echo "</script>\n";
echo "<input id='number_of_apps_to_display' type='hidden' name='number_of_apps_to_display' value='$number_of_apps_to_display'>";
/*
echo "<div id='app-selector-header'>\n";
echo "  <div id='category-container'>";
echo "    <div id='novice-prev' class='marketplace-novice-nav novice-prev'></div>";
echo "    <div class='marketplace-novice novice-selected'>";
echo "      <div id='marketplace-novice-title'></div>";
echo "      <div id='marketplace-novice-description'></div>";
echo "    </div>";
echo "  <div id='novice-next' class='marketplace-novice-nav novice-next'></div>";
echo "  <div id='marketplace-novice-step'></div>";
echo "  </div>";
echo "</div>\n";
            <input type='radio' id='novice-0' name='radio' checked='checked' /><label for='novice-0'>" . lang('marketplace_directory_services') . "</label>
            <input type='radio' id='novice-1' name='radio' /><label for='novice-1'>" . lang('marketplace_perimeter_security') . "</label>
            <input type='radio' id='novice-2' name='radio' /><label for='novice-2'>" . lang('marketplace_content_filter') . "</label>
            <input type='radio' id='novice-3' name='radio' /><label for='novice-3'>" . lang('marketplace_groupware_and_email') . "</label>
            <input type='radio' id='novice-4' name='radio' /><label for='novice-4'>" . lang('marketplace_disaster_recovery') . "</label>
            <input type='radio' id='novice-5' name='radio' /><label for='novice-5'>" . lang('marketplace_home_networking') . "</label>
*/



echo "<div id='app-selector-header'>
        <div id='category-container'>
          <div class='marketplace-novice novice-selected'>
            <div id='marketplace-novice-title'></div>
            <div id='marketplace-novice-description'></div>
          </div>
          <hr style='width: 70%; border-style: dotted;'>
          <div id='radio' class='ui-buttonset' style='padding-top: 5px;'>
            <input type='radio' id='novice-0' name='radio' class='novice-select' checked='checked' /><label for='novice-0'>1</label>
            <input type='radio' id='novice-1' name='radio' class='novice-select' /><label for='novice-1'>2</label>
            <input type='radio' id='novice-2' name='radio' class='novice-select' /><label for='novice-2'>3</label>
            <input type='radio' id='novice-3' name='radio' class='novice-select' /><label for='novice-3'>4</label>
            <input type='radio' id='novice-4' name='radio' class='novice-select' /><label for='novice-4'>5</label>
            <input type='radio' id='novice-5' name='radio' class='novice-select' /><label for='novice-5'>6</label>
            <input type='radio' id='novice-6' name='radio' class='novice-select' /><label for='novice-6'>7</label>
          </div>
        </div>
      </div>
";



echo "<br clear='both'>\n";
echo "<div style='text-align: center;'>\n";
echo "<div id='marketplace-loading' style='padding: 10px 0px 0px 0px;'>" . loading('normal', lang('marketplace_searching_marketplace'), array('icon-below' => TRUE)) . "</div>\n";
echo "<div id='app_list_overview'></div>\n";
echo "</div>\n";

echo form_open('marketplace/settings', array('method' => 'GET', 'name' => 'form_app_list', 'id' => 'form_app_list'));
echo "<div id='marketplace-app-container'></div>\n";
echo form_close();
echo "<input id='display_format' type='hidden' value='$display_format'>\n";
echo "<input type='hidden' value='$mode' id='wizard_marketplace_mode' name='wizard_marketplace_mode' />\n";
