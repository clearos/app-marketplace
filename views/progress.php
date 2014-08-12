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
//echo "<div id='info'></div>";

echo row_open();
echo column_open(4, array('class' => 'marketplace-progress-spash'));
echo "<h3>" . lang('marketplace_thankyou') . "</h3>\n";
echo "<p>" . lang('marketplace_progress_help') . "</p>\n";
echo column_close();
echo column_open(8, array('class' => 'summary-info'));
echo "<h3>" . lang('marketplace_overall_progress') . "</h3>\n";
echo progress_bar('overall', array('input' => 'overall'));
echo "<h3 style='clear: both;'>" . lang('marketplace_operation_progress') . "</h3>\n";
echo "<div>\n";
echo progress_bar('progress', array('input' => 'progress'));
echo "</div>\n";
echo "<h3 style='clear: both;'>" . lang('marketplace_details') . "</h3>\n";
echo "<div id='details'></div>\n";
echo "  <div id='reload_button' class='theme-hidden' style='margin-top: 10px; text-align: center;'>\n";
echo anchor_custom('/app/marketplace', lang('marketplace_reload_after_updates'), 'high');
echo "  </div>\n";
echo "</div>\n";
echo column_close();
echo row_close();
