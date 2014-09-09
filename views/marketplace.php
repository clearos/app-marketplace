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

echo form_open('marketplace/search');
echo box_open();
echo row_open(array('class' => 'marketplace-search-filter'));
echo column_open(8, NULL, NULL, array('id' => 'marketplace_filter_container'));
foreach ($filters as $name => $options)
    echo marketplace_filter($name, $options, $selected[$name]);
echo column_close();
echo column_open(4, NULL, NULL, array('id' => 'marketplace_search_container', 'class' => 'search-form'));
echo marketplace_search($search);
echo column_close();
echo row_close();
echo row_open();
echo column_open(8, NULL, NULL, array('id' => 'marketplace_tools_container'));
$buttons = array(
    anchor_custom('/app/marketplace/install', lang('marketplace_install_selected_apps'), 'high'),
    anchor_custom('/app/marketplace/all', lang('marketplace_select_all'), 'low', array('id' => 'toggle_select')),
    anchor_custom('/app/marketplace/search/reset_filter', lang('marketplace_reset_filters'), 'low') 
);
echo button_set($buttons);
echo column_close();
echo column_open(4, NULL, NULL, array('id' => 'marketplace_paginate_container', 'class' => 'theme-hidden'));
echo paginate('/app/marketplace/search/index');
echo column_close();
echo row_close();
echo box_close();
echo form_close();


if ($search)
    echo loading('1.5em', lang('marketplace_searching_marketplace'), array('icon-below' => TRUE, 'center' => TRUE, 'id' => 'app-search-load', 'class' => 'marketplace-app-loading'));
else
    echo loading('2em', lang('marketplace_loading'), array('icon-below' => TRUE, 'center' => TRUE, 'id' => 'app-search-load', 'class' => 'marketplace-app-loading'));

echo form_open('marketplace', array('method' => 'GET', 'name' => 'form_app_list', 'id' => 'form_app_list'));
echo marketplace_layout();
echo form_close();
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
