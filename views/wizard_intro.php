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

// TODO: translate
// TODO: move style elements to theme

//if ($is_professional)
//    $image = 'pro-marketplace.png';
//else
 //   $image = 'community-marketplace.png';

$banner = "<img style='float: right; padding-left: 20px;' src='" . clearos_app_htdocs('marketplace') . "/marketplace.png' alt=''>";
$banner .= "<h2 style='font-size: 1.8em; color: #909090; width: 687px;'>Getting Started</h2>";
$banner .= "<p style='font-size: 1.2em; line-height: 20px;'>Congratulations!  You are now ready to install apps and integrated cloud services through the ClearCenter Marketplace.  You will find a large selection of both free and paid apps that can be installed in a few short steps.";

$banner .= "<div class='marketplace-wizard-option-container'>";
$banner .= "  <div class='mode marketplace-wizard-options' id='mode1'>";
$banner .= "    <div style='font-size: 1.4em; font-weight: bold;'>Option #1 - Install by Function</div>";
$banner .= "<img style='float: right; padding: 10px 0px 5px 10px;' src='" . clearos_app_htdocs('marketplace') . "/market_default.png' alt=''>";
$banner .= "    <div style='text-align: left; padding-top: 10px;'>If you are new to ClearOS or prefer to configure the system by specific tasks (eg. prevent users from accssing certain websites, share files between users etc.), select this option.</div>";
$banner .= "  </div>";
$banner .= "  <div  class='mode marketplace-wizard-options' id='mode2'>";
$banner .= "    <div style='font-size: 1.4em; font-weight: bold;'>Option #2 - Install by Category</div>";
$banner .= "<img style='float: right; padding: 10px 0px 5px 10px;' src='" . clearos_app_htdocs('marketplace') . "/market_default.png' alt=''>";
$banner .= "    <div style='text-align: left; padding-top: 10px;'>Displays groups of apps based on a category classification system used in the main menu.  If you are familiar with basic networking/server terms and familiar with the ClearOS Marketplace, select this option.</div>";
$banner .= "  </div>";
$banner .= "  <div class='mode marketplace-wizard-options' id='mode3'>";
$banner .= "    <div style='font-size: 1.4em; font-weight: bold;'>Option #3 - Quick Select File</div>";
$banner .= "<img style='float: right; padding: 10px 0px 5px 10px;' src='" . clearos_app_htdocs('marketplace') . "/market_default.png' alt=''>";
$banner .= "    <div style='text-align: left; padding-top: 10px;'>ClearCenter and the ClearFoundation community have a wide selection of pre-configured templates that will quickly get you up and running.</div>";
$banner .= "  </div>";
$banner .= "  <div class='mode marketplace-wizard-options' id='mode4'>";
$banner .= "    <div style='font-size: 1.4em; font-weight: bold;'>Option #4 - Skip Wizard</div>";
$banner .= "<img style='float: right; padding: 10px 0px 5px 10px;' src='" . clearos_app_htdocs('marketplace') . "/market_default.png' alt=''>";
$banner .= "    <div style='text-align: left; padding-top: 10px;'>Skip the Marketplace wizard.  You can install apps at any time by navigating to the Marketplace.</div>";
$banner .= "  </div>";
$banner .= "</div>";
//$banner .= "<p align='center'><img src='" . clearos_app_htdocs('marketplace') . "/$image' alt='Marketplace'></p>";

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('marketplace/wizard/intro', array('id' => 'marketplace_intro'));
echo form_header(lang('marketplace_welcome_to_marketplace'));

echo form_banner($banner);
echo "<input type='hidden' value='$mode' id='wizard_marketplace_mode' name='wizard_marketplace_mode' />";

echo form_footer();
echo form_close();
