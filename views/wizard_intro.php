<?php

/**
 * Marketplace banner view.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage views
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2012 ClearCenter
 * @license    http://www.clearcenter.com/Company/terms.html ClearSDN license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('marketplace');

$radios = array(
    radio_set_item(
        'function',
        'radio',
        lang('marketplace_install_by_function'),
        TRUE,
        array(
            'label_id' => 'mode1',
            'class' => 'marketplace_wizard_mode active',
            'orientation' => 'horizontal',
            'buttons' => TRUE
        )
    ),
    radio_set_item(
        'category',
        'radio',
        lang('marketplace_install_by_category'),
        FALSE,
        array(
            'label_id' => 'mode2',
            'class' => 'marketplace_wizard_mode',
            'orientation' => 'horizontal',
            'buttons' => TRUE
        )
    ),
    radio_set_item(
        'qsf',
        'radio',
        lang('marketplace_install_by_qsf'),
        FALSE,
        array(
            'label_id' => 'mode3',
            'class' => 'marketplace_wizard_mode',
            'orientation' => 'horizontal',
            'buttons' => TRUE
        )
    ),
    radio_set_item(
        'skip',
        'radio',
        lang('marketplace_skip_wizard'),
        FALSE,
        array(
            'label_id' => 'mode4',
            'class' => 'marketplace_wizard_mode',
            'orientation' => 'horizontal',
            'buttons' => TRUE
        )
    ),
);

echo box_open(lang('marketplace_getting_started'));
echo box_content_open();
echo row_open();
echo column_open(2);
echo app_logo('marketplace');
echo column_close();
echo column_open(10);
echo lang('marketplace_wizard_congrats');
echo column_close();
echo row_close();
echo box_content_close();
echo box_footer('marketplace-feature-select', radio_set($radios, 'feature', array('buttons' => TRUE)));
echo box_close();
echo "<input type='hidden' value='$mode' id='wizard_marketplace_mode' name='wizard_marketplace_mode' />";
