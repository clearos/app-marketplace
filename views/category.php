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
$this->lang->load('base_category');
$this->lang->load('marketplace');

$radios = array(
    radio_set_item(
        'cloud',
        'radio',
        lang('base_category_cloud'),
        TRUE,
        array(
            'label_id' => 'category-cloud',
            'class' => 'category-select active',
            'orientation' => 'horizontal',
            'buttons' => TRUE
        )
    ),
    radio_set_item(
        'gateway',
        'radio',
        lang('base_category_gateway'),
        FALSE,
        array(
            'label_id' => 'category-gateway',
            'class' => 'category-select',
            'orientation' => 'horizontal',
            'buttons' => TRUE
        )
    ),
    radio_set_item(
        'network',
        'radio',
        lang('base_category_network'),
        FALSE,
        array(
            'label_id' => 'category-network',
            'class' => 'category-select',
            'orientation' => 'horizontal',
            'buttons' => TRUE
        )
    ),
    radio_set_item(
        'server',
        'radio',
        lang('base_category_server'),
        FALSE,
        array(
            'label_id' => 'category-server',
            'class' => 'category-select',
            'orientation' => 'horizontal',
            'buttons' => TRUE
        )
    ),
    radio_set_item(
        'system',
        'radio',
        lang('base_category_system'),
        FALSE,
        array(
            'label_id' => 'category-system',
            'class' => 'category-select',
            'orientation' => 'horizontal',
            'buttons' => TRUE
        )
    ),
    radio_set_item(
        'reports',
        'radio',
        lang('base_category_reports'),
        FALSE,
        array(
            'label_id' => 'category-reports',
            'class' => 'category-select',
            'orientation' => 'horizontal',
            'buttons' => TRUE
        )
    ),
);

$buttons = array(
    anchor_custom('#', lang('marketplace_select_all'), 'high', array ('id' => 'toggle_select', 'hide' => TRUE)),
);
echo box_open(lang('marketplace_app_selection'), array('id' => 'marketplace-category', 'anchors' => button_set($buttons)));
echo box_footer('marketplace-category-options', radio_set($radios, 'category', array('buttons' => TRUE)));
echo box_close();

echo form_open('marketplace/settings', array('method' => 'GET', 'name' => 'form_app_list', 'id' => 'form_app_list'));
echo "<div id='marketplace-app-container'></div>";
echo form_close();
echo "<script type='text/javascript'>\n";
echo "  $(document).ready(function() {\n";
echo "    get_apps(false, 0);\n";
echo "  });\n";
echo "</script>\n";

echo "<input id='number_of_apps_to_display' type='hidden' value='$number_of_apps_to_display'>\n";
echo "<input id='display_format' type='hidden' value='$display_format'>\n";
echo "<input type='hidden' value='$mode' id='wizard_marketplace_mode' name='wizard_marketplace_mode' />\n";
