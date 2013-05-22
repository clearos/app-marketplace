<?php

/**
 * Marketplace banner view.
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
// Marketplace Banner
///////////////////////////////////////////////////////////////////////////////

$buttons = array(
    anchor_custom('/app/marketplace/settings', lang('base_settings'), 'high'), 
    anchor_custom('/app/marketplace/all', lang('marketplace_select_all'), 'high', array('id' => 'toggle_select')), 
    anchor_custom('/app/marketplace/install', lang('marketplace_install_selected_apps'), 'high')
);
echo "
<div>
  <div style='clear: both; float: right; padding: 0px 5px 0px 0px;'>
    " . field_button_set($buttons) . "
  </div>
  <div id='pagination-top' style='font-size: .7em; padding: 5px 12px 0px 0px;'></div>
  <div class='ui-widget marketplace-footer'></div>
</div>
";
