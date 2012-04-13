<?php

/**
 * Marketplace banner view.
 *
 * @category   Apps
 * @package    Marketplace
 * @subpackage Views
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

if ($is_professional)
    $image = 'pro-marketplace.png';
else
    $image = 'community-marketplace.png';

$banner = "<h2 style='font-size: 1.8em; color: #909090; width: 687px;'>Getting Started</h2>";
$banner .= "<img style='float: right; padding-left: 20px;' src='" . clearos_app_htdocs('marketplace') . "/marketplace.png' alt=''>";
$banner .= "<p style='font-size: 1.2em; line-height: 20px;'>Congratulations!  You are now ready to install apps and integrated cloud services through the ClearCenter Marketplace.  You will find a large selection of both free and paid apps that can be installed in a few short steps.";
$banner .= "<p style='font-size: 1.2em; line-height: 20px;'>If this is your first time using ClearOS, the number of apps and services in the Marketplace can be overwhelming.  The Marketplace Wizard guides you through the process of selecting the right features for your system.</p>";

$banner .= "<p align='center'><img src='" . clearos_app_htdocs('marketplace') . "/$image' alt='Marketplace'></p>";

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('marketplace/wizard/intro', array('id' => 'marketplace_intro'));
echo form_header(lang('marketplace_welcome_to_marketplace'));

echo form_banner($banner);

echo form_footer();
echo form_close();
