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

//echo "<div class='ui-widget marketplace-footer'></div>";
//echo "<div id='app_overview' style='text-align: center; padding: 60px 10px 30px 10px;'>";
//echo lang('marketplace_loading');
//echo "<div style='padding: 10px 0px 10px 0px;'>" . loading() . "</div>";
//echo "</div>";

echo box_open($basename, array('id' => 'app_name', 'class' => 'marketplace-app-info-container'));
echo row_open();
echo column_open(2);
echo app_logo($basename);
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
echo box_close();

/* Overview */
echo row_open(array('id' => 'marketplace-overview'));
echo "<h3>" . lang('marketplace_overview') . "</h3>";
echo "<div id='app_description'></div>";
echo row_close();

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

/* Reviews */
echo row_open(array('id' => 'marketplace-review'));
echo "<h3>" . lang('marketplace_reviews') . "</h3>";
echo form_submit_custom('review', lang('marketplace_submit_review'), 'high', array ('id' => ($is_installed ? 'add_review' : 'prevent_review')));
echo marketplace_review($basename, $pseudonym);
echo "<div id='app_ratings'></div>";
echo row_close();


// TODO
//    "<div id='availability_warning_box' style='width: 95%; display: none'>" .
//    infobox_warning(lang('marketplace_not_available'), "<div id='availability_warning'></div>") .
//    "</div>" .


$DELETEME = 
    "<table width='100%' style='margin-top: 10px;' border='0'>" .
    "  <tr>" .
//    "    <td valign='top' width='70%'><h2 class='app-name'></h2>" .
    "      <div id='app_description'></div>" .
    "<div id='availability_warning_box' style='width: 95%; display: none'>" .
    infobox_warning(lang('marketplace_not_available'), "<div id='availability_warning'></div>") .
    "</div>" .
    "      <div>" .
    "      <span style='margin-right: 5px;'>" .
    anchor_custom('/app/software_repository/index/detailed', lang('marketplace_documentation'), 'high', array('id' => 'documentation', 'target' => '_blank')) .
    "      </span>" .
    "      <span>" .
    anchor_custom('/app/software_repository/index/detailed', lang('marketplace_learn_more'), 'high', array('id' => 'learn_more', 'target' => '_blank')) .
    "      </span>" .
    "      </div>" .
    "      <h2 class='complementary'>" . lang('marketplace_complementary_apps') . "</h2>" .
    "      <p class='complementary'>" . lang('marketplace_complementary_apps_info') . "</p>" .
    "      <div style='position: relative; width: 100%; clear: both;' id='app_complementary' class='complementary'></div>" .
    "      <h2>" . lang('marketplace_other_apps_by_devel') . "</h2>" .
    "      <div style='position: relative; width: 100%; clear: both;' id='app_other_by_devel'></div>" .
    "    </td>" .
    "    <td valign='top' width='30%'>" . app_logo() .
    "      <div style='padding: 15px 0px 15px 0px;' id='app_action'>" .
    "        <div class='app_actions' id='a_upgrade' style='display: none; padding-top: 5px;'>" .
    form_submit_custom('but_upgrade', lang('marketplace_install_upgrade'), 'high', array ('id' => 'indiv_upgrade')) .
    "        </div>" .
    "        <div class='app_actions' id='a_repo' style='display: none; padding-top: 5px; margin-left: 2px;'>" .
    anchor_custom('/app/software_repository/index/detailed', lang('marketplace_enable_repo'), 'high', array('id' => 'indiv_repo')) .
    "        </div>" .
    "        <div class='app_actions' id='a_configure' style='display: none; padding-top: 5px; margin-left: 2px;'>" .
    anchor_custom('/app/' . $basename, lang('base_configure'), 'high', array('id' => 'indiv_configure')) .
    "        </div>" .
    "        <div class='app_actions' id='a_buy' style='display: none; padding-top: 5px;'>" .
    form_submit_custom('but_buy', lang('marketplace_buy'), 'high', array ('id' => 'indiv_buy')) .
    "        </div>" .
    "        <div class='app_actions' id='a_install' style='display: none; padding-top: 5px;'>" .
    form_submit_custom('but_install', lang('marketplace_download_and_install'), 'high', array ('id' => 'indiv_install')) .
    "        </div>" .
    "        <div class='app_actions' id='a_uninstall' style='display: none; padding-top: 5px; margin-left: 2px;'>" .
    anchor_custom('/app/marketplace/uninstall/' . $basename, lang('marketplace_uninstall'), 'high', array('id' => 'undiv_uninstall')) .
    "        </div>" .
    "      </div>" .
    "      <div style='padding: 5px 0px 15px 0px;'>" . strtoupper(lang('marketplace_about_this_app')) . "</div>" .
    "      <div id='field_rating' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_rating')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_rating'></div>" .
    "      </div>" .
    "    </td>" .
    "    </td>" .
    "    </td>" .
    "  </tr>" . 
    "</table>"
;
$tabinfo['reviews']['content'] = "<h2 style='position: relative; float: left'>" . lang('marketplace_user_reviews') . "</h2>" .
    "<div id='review_form' style='display: none;'>" .
    "  <h2>" . lang('marketplace_write_a_review') . "</h2>" .
    "  <div>" .
    "    <table width='100%' border='0'>" .
    "      <tr class='rating'><td onclick='update_rating(0)'>" . lang('marketplace_rating') . "</td>" .
    "        <td>" .
    "          <img src='" . clearos_app_htdocs('marketplace') . "/star_off.png' alt='-' id='star1' onclick='update_rating(1)' />" .
    "          <img src='" . clearos_app_htdocs('marketplace') . "/star_off.png' alt='-' id='star2' onclick='update_rating(2)' />" .
    "          <img src='" . clearos_app_htdocs('marketplace') . "/star_off.png' alt='-' id='star3' onclick='update_rating(3)' />" .
    "          <img src='" . clearos_app_htdocs('marketplace') . "/star_off.png' alt='-' id='star4' onclick='update_rating(4)' />" .
    "          <img src='" . clearos_app_htdocs('marketplace') . "/star_off.png' alt='-' id='star5' onclick='update_rating(5)' />" .
    "          <input type='hidden' name='rating' id='rating' value='0' />" .
    "        </td>" .
    "      </tr>" .
    "      <tr class='rating'><td valign='top'>" . lang('marketplace_comment') . "</td><td><textarea id='comment' style='font-size: 9pt; width: 400px; height: 80px;'></textarea><div id='char-remaining'>1000 " . lang('marketplace_remaining') . "</div></td></tr>" .
    "      <tr class='rating'><td>" . lang('marketplace_submitted_by') . "</td>" .
    "        <td><input type='text' id='pseudonym' name='pseudonym' value='$pseudonym' /></td>" .
    "      </tr>" .
    "    </table>" .
    "    <div class='rating'><td>&#160;</td><td>" . field_button_set(array(form_submit_custom('submit_review', lang('marketplace_submit_review'), 'high', array ('id' => 'submit_review')), form_submit_custom('cancel_review', lang('base_cancel'), 'high', array ('id' => 'cancel_review')))) . "</div>" .
    "  </div>" .
    "</div>" .
    "<div id='app_ratings'></div>"
;
echo "<script type='text/javascript'>";
echo "$(document).ready(function() {";
echo "    get_app_details('" . $basename . "');";
echo "});";
echo "</script>";
echo "<input type='hidden' name='basename' id='basename' value='" . $basename . "' />";
