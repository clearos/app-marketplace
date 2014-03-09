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

///////////////////////////////////////////////////////////////////////////////
// Content 
///////////////////////////////////////////////////////////////////////////////

// TODO: move style elements to theme

$banner = "<img style='float: right; padding-left: 20px;' src='" . clearos_app_htdocs('marketplace') . "/marketplace.png' alt=''>";
$banner .= "<h2 style='font-size: 1.8em; color: #909090; width: 687px;'>" . lang('marketplace_getting_started') . "</h2>";
$banner .= "<p style='font-size: 1.2em; line-height: 20px;'>" . lang('marketplace_wizard_congrats') . "</p>";

$banner .= "<div class='marketplace-wizard-option-container'>";
$banner .= "  <div class='mode marketplace-wizard-options' id='mode1'>";
$banner .= "    <div style='font-size: 1.4em; font-weight: bold;'>" . lang('marketplace_install_by_function') . "</div>";
$banner .= "<img style='float: right; padding: 10px 0px 5px 10px;' src='" . clearos_app_htdocs('marketplace') . "/marketplace_feature_50x50.png' alt=''>";
$banner .= "    <div style='text-align: left; padding-top: 10px;'>" . lang('marketplace_install_by_function_description') . "</div>";
$banner .= "  </div>";
$banner .= "  <div  class='mode marketplace-wizard-options' id='mode2'>";
$banner .= "    <div style='font-size: 1.4em; font-weight: bold;'>" . lang('marketplace_install_by_category') . "</div>";
$banner .= "<img style='float: right; padding: 10px 0px 5px 10px;' src='" . clearos_app_htdocs('marketplace') . "/marketplace_category_50x50.png' alt=''>";
$banner .= "    <div style='text-align: left; padding-top: 10px;'>" . lang('marketplace_install_by_category_description') . "</div>";
$banner .= "  </div>";
$banner .= "  <div class='mode marketplace-wizard-options' id='mode3'>";
$banner .= "    <div style='font-size: 1.4em; font-weight: bold;'>" . lang('marketplace_install_by_qsf') . "</div>";
$banner .= "<img style='float: right; padding: 10px 0px 5px 10px;' src='" . clearos_app_htdocs('marketplace') . "/marketplace_qsf_50x50.png' alt=''>";
$banner .= "    <div style='text-align: left; padding-top: 10px;'>" . lang('marketplace_install_by_qsf_description') . "</div>";
$banner .= "  </div>";
$banner .= "  <div class='mode marketplace-wizard-options' id='mode4'>";
$banner .= "    <div style='font-size: 1.4em; font-weight: bold;'>" . lang('marketplace_skip_wizard') . "</div>";
$banner .= "<img style='float: right; padding: 10px 0px 5px 10px;' src='" . clearos_app_htdocs('marketplace') . "/marketplace_exit_50x50.png' alt=''>";
$banner .= "    <div style='text-align: left; padding-top: 10px;'>" . lang('marketplace_skip_wizard_description') . "</div>";
$banner .= "  </div>";
$banner .= "</div>";

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('marketplace/wizard/intro', array('id' => 'marketplace_intro'));
echo form_header(lang('marketplace_welcome_to_marketplace'));

echo form_banner($banner);
echo "<input type='hidden' value='$mode' id='wizard_marketplace_mode' name='wizard_marketplace_mode' />";

echo form_footer();
echo form_close();
