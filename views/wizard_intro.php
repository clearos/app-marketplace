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

echo row_open();

$link = anchor_select('#', 'high', array('id' => 'mode1', 'class' => 'marketplace_wizard_mode'));
echo column_open(6);
echo box_open(lang('marketplace_install_by_function'), array('class' => 'marketplace-wizard-select theme-clear'));
echo box_content("<div>" . lang('marketplace_install_by_function_description') . "</div>");
echo box_footer('footer-mode1', $link, array('class' => 'pull-right'));
echo box_close();
echo column_close();

$link = anchor_select('#', 'high', array('id' => 'mode2', 'class' => 'marketplace_wizard_mode'));
echo column_open(6);
echo box_open(lang('marketplace_install_by_category'), array('class' => 'marketplace-wizard-select theme-clear'));
echo box_content("<div>" . lang('marketplace_install_by_category_description') . "</div>");
echo box_footer('footer-mode2', $link, array('class' => 'pull-right'));
echo box_close();
echo column_close();

$link = anchor_select('#', 'high', array('id' => 'mode3', 'class' => 'marketplace_wizard_mode'));
echo column_open(6);
echo box_open(lang('marketplace_install_by_qsf'), array('class' => 'marketplace-wizard-select theme-clear'));
echo box_content("<div>" . lang('marketplace_install_by_qsf_description') . "</div>");
echo box_footer('footer-mode3', $link, array('class' => 'pull-right'));
echo box_close();
echo column_close();

$link = anchor_select('#', 'high', array('id' => 'mode4', 'class' => 'marketplace_wizard_mode'));
echo column_open(6);
echo box_open(lang('marketplace_skip_wizard'), array('class' => 'marketplace-wizard-select theme-clear'));
echo box_content("<div>" . lang('marketplace_skip_wizard_description') . "</div>");
echo box_footer('footer-mode4', $link, array('class' => 'pull-right'));
echo box_close();
echo column_close();

echo row_close();
echo "<input type='hidden' value='$mode' id='wizard_marketplace_mode' name='wizard_marketplace_mode' />";
echo modal_info("wizard_next_showstopper", lang('base_error'), lang('marketplace_no_mode_selected'), array('type' => 'warning'));
