<?php

/**
 * Marketplace progress view.
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

echo "<div id='info'></div>";

echo "<div class='marketplace-progress-spash' style='width:300; float: left; border: padding: 0 0 0 0;'>\n";
echo "<p>" . lang('marketplace_thankyou') . "</p>\n";
echo "<p style='padding-top: 110;'>" . lang('marketplace_progress_help') . "</p>\n";
echo "<p>" . lang('marketplace_delete_help') . "</p>\n";
echo "</div>\n";
echo "<div id='summary-info' style='width:380; float: right;'>\n";
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
