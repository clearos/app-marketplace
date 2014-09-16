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

$radios = array(
    radio_set_item('1', 'radio', "1", TRUE, array('label_id' => 'novice-0', 'class' => 'novice-select active', 'orientation' => 'horizontal', 'buttons' => TRUE)),
    radio_set_item('2', 'radio', "2", FALSE, array('label_id' => 'novice-1', 'class' => 'novice-select', 'orientation' => 'horizontal', 'buttons' => TRUE)),
    radio_set_item('3', 'radio', "3", FALSE, array('label_id' => 'novice-2', 'class' => 'novice-select', 'orientation' => 'horizontal', 'buttons' => TRUE)),
    radio_set_item('4', 'radio', "4", FALSE, array('label_id' => 'novice-3', 'class' => 'novice-select', 'orientation' => 'horizontal', 'buttons' => TRUE)),
    radio_set_item('5', 'radio', "5", FALSE, array('label_id' => 'novice-4', 'class' => 'novice-select', 'orientation' => 'horizontal', 'buttons' => TRUE)),
    radio_set_item('6', 'radio', "6", FALSE, array('label_id' => 'novice-5', 'class' => 'novice-select', 'orientation' => 'horizontal', 'buttons' => TRUE))
);
// Home/media type apps not suitable for display in Professional Edition
if (!preg_match('/Professional/', $os_name))
    $radios[] = radio_set_item('7', 'radio', "7", FALSE, array('label_id' => 'novice-6', 'class' => 'novice-select', 'orientation' => 'horizontal', 'buttons' => TRUE));

$buttons = array(
    anchor_custom('#', lang('marketplace_select_all'), 'high', array ('id' => 'toggle_select', 'hide' => TRUE)),
);
echo box_open("---", array('id' => 'marketplace-novice', 'anchors' => button_set($buttons)));
echo row_open();
echo column_open(12, NULL, NULL, array('id' => 'marketplace-novice-description'));
echo column_close();
echo column_open(12, NULL, NULL, array('id' => 'marketplace-novice-description-more'));
echo anchor_custom('#', lang('marketplace_learn_more'), 'high', array('id' => 'novice-learn-more-action'));
echo column_close();
echo row_close();

echo box_footer('marketplace-novice-options', radio_set($radios, 'feature', array('buttons' => TRUE)));

echo box_close();
echo modal_info('novice-learn-more-modal', 'Title', 'Help'); 

echo loading('1.5em', lang('marketplace_searching_marketplace'), array('icon-below' => TRUE, 'center' => TRUE, 'id' => 'app-search-load', 'class' => 'marketplace-app-loading'));

echo form_open('marketplace', array('method' => 'GET', 'name' => 'form_app_list', 'id' => 'form_app_list'));
echo marketplace_layout();
echo form_close();
echo "<script type='text/javascript'>
        $(document).ready(function() {
          get_novice_set(0);
        });
      </script>
";
echo "<input id='display_format' type='hidden' value='$display_format'>";
echo "<input id='number_of_apps_to_display' type='hidden' name='number_of_apps_to_display' value='$number_of_apps_to_display'>";
echo "<input type='hidden' value='mode1' id='wizard_marketplace_mode' name='wizard_marketplace_mode' />";
