<?php

/**
 * Marketplace settings view.
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

$this->load->helper('number');
$this->lang->load('base');
$this->lang->load('marketplace');

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('marketplace/settings');
echo form_header(lang('base_settings'));

///////////////////////////////////////////////////////////////////////////////
// Form fields and buttons
///////////////////////////////////////////////////////////////////////////////

$read_only = FALSE;
$buttons = array(
    form_submit_update('submit'),
    form_submit_custom('delete_cache', lang('marketplace_clear_cache')),
    anchor_cancel('/app/marketplace')
);

$apps_per_page_options = array(
    6 => 6,
    10 => 10,
    20 => 20,
    30 => 30,
    40 => 40,
    50 => 50,
    0 => lang('base_all'),
);
echo field_dropdown('number_of_apps_to_display', $apps_per_page_options, $number_of_apps_to_display, lang('marketplace_apps_per_page'), $read_only);
echo field_input('pseudonym', $pseudonym, lang('marketplace_pseudonym'), $read_only);
echo field_info(
    'clear_cache',
    lang('marketplace_cache_data'),
    byte_format($cache_size)
);
echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();
