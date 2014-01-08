<?php

/**
 * Marketplace table list view.
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

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('marketplace_category'),
    lang('marketplace_app'),
    lang('base_description'),
    lang('marketplace_unit_price'),
    lang('marketplace_installed'),
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

// Done in Ajax

///////////////////////////////////////////////////////////////////////////////
// List table
///////////////////////////////////////////////////////////////////////////////


$options['id'] = 'install_list';
$options['no_action'] = TRUE;
if ($search)
    $options['empty_table_message'] = "<div class='theme-loading-small'>" . lang('marketplace_searching_marketplace') . "</div>";
else
    $options['empty_table_message'] = "<div class='theme-loading-small'>" . lang('marketplace_loading') . "</div>";
$options['col-widths'] = array ('0%', '20%', '55%', '15%', '10%');
$options['grouping'] = TRUE;
$options['paginate'] = TRUE;

echo "<br clear='both'>";

echo summary_table(
    lang('marketplace_app_summary'),
    array(anchor_custom('/app/marketplace/install', lang('marketplace_install_selected_apps'), 'high')),
    $headers,
    NULL,
    $options
);
echo "<input id='display_format' type='hidden' value='table'>\n";
echo "<script type='text/javascript'>\n";
echo "  $(document).ready(function() {\n";
echo "    get_apps(false, 0);\n";
echo "  });\n";
echo "</script>\n";
echo "<input id='number_of_apps_to_display' type='hidden' value='0'>";
