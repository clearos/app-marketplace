<?php

/**
 * App view.
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

echo loading('1.4em', lang('marketplace_loading'), array('icon-below' => TRUE, 'center' => TRUE, 'id' => 'app-loading', 'class' => 'marketplace-app-loading'));

echo "<div id='app-details-container' class='theme-hidden'>";
echo box_open($basename, array('id' => 'app_name', 'class' => 'marketplace-app-info-container'));
echo box_content_open();
echo row_open();
echo column_open(2, NULL, NULL, array('class' => 'marketplace-indiv-logo'));
echo app_logo($basename);
echo "<div id='app_rating' class='marketplace-indiv-rating'></div>";
echo column_close();
echo column_open(10, NULL, NULL, array('class' => 'marketplace-app-info'));
echo row_open();
echo column_open(12);
echo "<h4 class='marketplace-about-app'>" . lang('marketplace_about_this_app') . "</h4>";
echo column_close();
echo row_close();
echo row_open();
echo column_open(3);
echo 
    "      <div id='field_installed_version' class='marketplace-about'>" .
    "        <div class='marketplace-about-field'>" . lang('marketplace_installed_version') . ":</div>" .
    "        <div id='app_installed_version' class='marketplace-about-value'></div>" .
    "      </div>" .
    "      <div id='field_latest_version' class='marketplace-about'>" . 
    "        <div class='marketplace-about-field'>" . lang('marketplace_latest_version') . ":</div>" .
    "        <div id='app_latest_version' class='marketplace-about-value'></div>" .
    "      </div>"
;
echo column_close();
echo column_open(3);
echo 
    "      <div id='field_cost' class='marketplace-about'>" . 
    "        <div class='marketplace-about-field'>" . lang('marketplace_cost') . ":</div>" .
    "        <div id='app_cost' class='marketplace-about-value'></div>" .
    "      </div>" .
    "      <div id='field_introduced' class='marketplace-about'>" .
    "        <div class='marketplace-about-field'>" . lang('marketplace_released') . ":</div>" .
    "        <div id='app_introduced' class='marketplace-about-value'></div>" .
    "      </div>"
;
echo column_close();
echo column_open(3);
echo 
    "      <div id='field_category' class='marketplace-about'>" .
    "        <div class='marketplace-about-field'>" . lang('marketplace_category') . ":</div>" .
    "        <div id='app_category' class='marketplace-about-value'></div>" .
    "      </div>" .
    "      <div id='field_support_policy' class='marketplace-about'>" . 
    "        <div class='marketplace-about-field'>" . lang('marketplace_app_supported') . ":</div>" .
    "        <div id='app_support_policy' class='marketplace-about-value'></div>" .
    "      </div>"
;
echo column_close();
echo column_open(3);
echo 
    "      <div id='field_license' class='marketplace-about'>" .
    "        <div class='marketplace-about-field'>" . lang('marketplace_license') . ":</div>" .
    "        <div id='app_license' class='marketplace-about-value'></div>" .
    "      </div>" .
    "      <div id='field_license_library' class='marketplace-about'>" . 
    "        <div class='marketplace-about-field'>" . lang('marketplace_license_library') . ":</div>" .
    "        <div id='app_license_library' class='marketplace-about-value'></div>" .
    "      </div>"
;
echo column_close();
echo row_close();
echo column_close();
echo row_close();
echo box_content_close();
echo box_close();

echo "<div id='availability_warning_box' class='theme-hidden'>" .
        infobox_warning(lang('marketplace_not_available'), "<div id='availability_warning'></div>") .
     "</div>"
;

/* Overview */
echo row_open(array('id' => 'marketplace-overview'));
echo "<h3>" . lang('marketplace_overview') . "</h3>";
echo "<div id='app_description'></div>";
echo row_close();

$buttons = array(
    form_submit_custom('but_upgrade', lang('marketplace_install_upgrade'), 'high', array ('id' => 'indiv_upgrade', 'hide' => TRUE)),
    anchor_custom('/app/software_repository/index/detailed', lang('marketplace_enable_repo'), 'high', array('id' => 'indiv_repo', 'hide' => TRUE)),
    anchor_custom('/app/' . $basename, lang('base_configure'), 'high', array('id' => 'indiv_configure', 'hide' => TRUE)),
    form_submit_custom('but_buy', lang('marketplace_buy'), 'high', array ('id' => 'indiv_buy', 'hide' => TRUE)),
    form_submit_custom('but_install', lang('marketplace_download_and_install'), 'high', array ('id' => 'indiv_install', 'hide' => TRUE)),
    anchor_custom('/app/marketplace/uninstall/' . $basename, lang('marketplace_uninstall'), 'high', array('id' => 'indiv_uninstall', 'hide' => TRUE)),
    anchor_custom('#', lang('marketplace_documentation'), 'high', array('id' => 'documentation', 'target' => '_blank')),
    anchor_custom('#', lang('marketplace_learn_more'), 'high', array('id' => 'learn_more', 'target' => '_blank'))
);

echo "<div class='marketplace-indiv-button-set'>";
echo button_set($buttons);
echo "</div>";

/* Developer */
echo row_open(array('id' => 'marketplace-developer'));
echo "<h3>" . lang('marketplace_developer') . "</h3>";
echo marketplace_developer_field('app_devel_org', lang('marketplace_devel_org')); 
echo marketplace_developer_field('app_devel_contact', lang('marketplace_devel_contact')); 
echo marketplace_developer_field('app_devel_email', lang('marketplace_devel_email')); 
echo marketplace_developer_field('app_devel_website', lang('marketplace_devel_website')); 
echo row_close();

/* Screenshots */
echo row_open(array('id' => 'marketplace-screenshot'));
echo "<h3>" . lang('marketplace_screenshots') . "</h3>";
echo screenshot_set('app_screenshots');
echo row_close();

/* Localization */
echo row_open(array('id' => 'marketplace-localization'));
echo "<h3>" . lang('marketplace_localization') . "</h3>";
echo column_open(5);
echo "<div id='app_localization'></div>";
echo column_close();
echo column_open(7);
echo "<h4>" . lang('marketplace_translation_acknowledgements') . "</h4>";
echo "<ol id='app_localization_contributor'>";
echo "</ol>";
echo column_close();
echo row_close();

/* Reviews */
echo row_open(array('id' => 'marketplace-review'));
echo "<h3>" . lang('marketplace_reviews') . "</h3>";
echo form_submit_custom('review', lang('marketplace_submit_review'), 'high', array ('id' => ($is_installed ? 'add_review' : 'prevent_review')));
echo marketplace_review($basename, $pseudonym);
echo "<div id='app_ratings' class='marketplace-ratings clearfix'></div>";
echo row_close();

/* Complimentary */
echo row_open(array('id' => 'marketplace-complementary'));
echo "<h3>" . lang('marketplace_complementary_apps') . "</h3>";
echo "  <p class='complementary'>" . lang('marketplace_complementary_apps_info') . "</p>";
echo "<div id='app_complementary'></div>";
echo row_close();

/* Other Apps */
echo row_open(array('id' => 'marketplace-other'));
echo "<h3>" . lang('marketplace_other_apps_by_devel') . "</h3>";
echo "<div id='app_other_by_devel'></div>";
echo row_close();

echo "</div>";

echo "<script type='text/javascript'>";
echo "$(document).ready(function() {";
echo "    get_app_details('" . $basename . "'); $('.btn-group').button('refresh');";
echo "});";
echo "</script>";
echo "<input type='hidden' name='basename' id='basename' value='" . $basename . "' />";
