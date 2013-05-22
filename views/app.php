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

echo "<div class='ui-widget marketplace-footer'></div>";
echo "<div id='app_overview' style='text-align: center; padding: 60px 10px 30px 10px;'>";
echo lang('marketplace_loading');
echo "<div style='padding: 10px 0px 10px 0px;'>" . loading() . "</div>";
echo "</div>";


$tabinfo = Array();

$tabinfo['overview']['title'] = lang('marketplace_overview');
$tabinfo['reviews']['title'] = lang('marketplace_reviews');
$tabinfo['versions']['title'] = lang('marketplace_version_information');

$tabinfo['overview']['content'] = 
    "<table width='100%' style='margin-top: 10px;' border='0'>" .
    "  <tr>" .
    "    <td valign='top' width='70%'><h2 id='app_name'></h2>" .
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
    "      <h2>" . lang('marketplace_developer') . "</h2>" .
    "      <table width='100%' border='0'>" .
    "        <tr><td width='40%'>" . lang('marketplace_devel_org') . "</td><td width='60%' id='app_devel_org'></td></tr>" .
    "        <tr><td>" . lang('marketplace_devel_contact') . "</td><td id='app_devel_contact'></td></tr>" .
    "        <tr><td>" . lang('marketplace_devel_email') . "</td><td id='app_devel_email'></td></tr>" .
    "        <tr><td>" . lang('marketplace_devel_website') . "</td><td id='app_devel_website'></td></tr>" .
    "      </table>" .
    "      <h2>" . lang('marketplace_screenshots') . "</h2>" .
    "      <div style='position: relative; width: 100%; clear: both;' id='app_screenshots'></div><br clear='all'>" .
    "      <h2 class='complementary'>" . lang('marketplace_complementary_apps') . "</h2>" .
    "      <p class='complementary'>" . lang('marketplace_complementary_apps_info') . "</p>" .
    "      <div style='position: relative; width: 100%; clear: both;' id='app_complementary' class='complementary'></div>" .
    "      <h2>" . lang('marketplace_other_apps_by_devel') . "</h2>" .
    "      <div style='position: relative; width: 100%; clear: both;' id='app_other_by_devel'></div>" .
    "    </td>" .
    "    <td valign='top' width='30%'><img id='detail_img' src='" . clearos_app_htdocs('marketplace') . "/market_default.png' alt=''>" .
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
    "      <div id='field_installed_version' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_installed_version')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_installed_version'></div>" .
    "      </div>" .
    "      <div id='field_latest_version' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_latest_version')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_latest_version'></div>" .
    "      </div>" .
    "      <div id='field_releast_date' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_latest_release_date')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_latest_release_date'></div>" .
    "      </div>" .
    "      <div id='field_support_policy' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_app_supported')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_support_policy'></div>" .
    "      </div>" .
    "      <div id='field_repo' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_software_repo')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_repo'></div>" .
    "      </div>" .
    "      <div id='field_cost' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_cost')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_cost'></div>" .
    "      </div>" .
    "      <div id='field_category' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_category')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_category'></div>" .
    "      </div>" .
    "      <div id='field_tags' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_tags')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_tags'></div>" .
    "      </div>" .
    "      <div id='field_license' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_license')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_license'></div>" .
    "      </div>" .
    "      <div id='field_license_library' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_license_library')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_license_library'></div>" .
    "      </div>" .
    "      <div id='field_introduced' style='padding: 0px 0px 5px 0px;'>" . strtoupper(lang('marketplace_app_introduced')) . ":" .
    "        <div style='padding: 0px 0px 10px 0px;' id='app_introduced'></div>" .
    "      </div>" .
    "    </td>" .
    "    </td>" .
    "    </td>" .
    "  </tr>" . 
    "</table>"
;
$tabinfo['reviews']['content'] = "<h2 style='position: relative; float: left'>" . lang('marketplace_user_reviews') . "</h2>" .
    "<div style='position: relative; float: right; padding-top: 10px;'>" . form_submit_custom('review', lang('marketplace_submit_review'), 'high', array ('id' => ($is_installed ? 'add_review' : 'prevent_review'))) .
    "</div><br clear='all'>" .
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
$tabinfo['versions']['content'] = "<h2>" . lang('marketplace_localization') . "</h2>" .
    "<div id='app_locale'></div>" .
    "<h2>" . lang('marketplace_version_history') . "</h2>" .
    "<div id='app_versions'></div>"
;

echo tab($tabinfo);

echo "<script type='text/javascript'>$('#tabs').hide(); get_app_details('" . $basename . "');</script>\n";
echo "<input type='hidden' name='basename' id='basename' value='" . $basename . "' />";
