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
// TODO: convert image to text
// TODO: move style elements to theme

if ($is_professional)
    $image = 'pro-marketplace.png';
else
    $image = 'community-marketplace.png';

$banner = "<div style='background: url(" . clearos_app_htdocs('marketplace') . "/$image) no-repeat; height:370px; width:682; margin-left: 15px; margin-top: 15px;'></div>";

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('marketplace/wizard/intro', array('id' => 'marketplace_intro'));
echo form_header(lang('marketplace_welcome_to_marketplace'));

echo form_banner($banner);

echo form_footer();
echo form_close();
