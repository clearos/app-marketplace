<?php

/**
 * Marketplace progress view.
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

if ($alt_in_use) {
    echo infobox_highlight(lang('marketplace_unavailable'), lang('marketplace_package_manager_in_use'));
    return;
}
echo "<div id='info'></div>";

echo "<div class='marketplace-progress-spash' style='width:300px; float: left; border: padding: 0px;'>\n";
echo "<p>" . lang('marketplace_thankyou') . "</p>\n";
echo "<p style='padding-top: 110px;'>" . lang('marketplace_progress_help') . "</p>\n";
echo "<p>" . lang('marketplace_delete_help') . "</p>\n";
echo "</div>\n";
echo "<div id='summary-info' style='width:380px; float: right;'>\n";
echo "<h2>" . lang('marketplace_overall_progress') . "</h2>\n";
echo progress_bar('overall', array('input' => 'overall'));
echo "<h2 style='clear: both;'>" . lang('marketplace_operation_progress') . "</h2>\n";
echo "<div>\n";
echo progress_bar('progress', array('input' => 'progress'));
echo "</div>\n";
echo "<h2 style='clear: both;'>" . lang('marketplace_details') . "</h2>\n";
echo "<div id='details'></div>\n";
echo "  <div id='reload_button' class='theme-hidden' style='margin-top: 10px; text-align: center;'>\n";
echo anchor_custom('progress', lang('marketplace_reload_after_updates'), 'high');
echo "  </div>\n";
echo "</div>\n";
