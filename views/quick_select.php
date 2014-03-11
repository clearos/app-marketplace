<?php

/**
 * Marketplace quick select view.
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
// Form open
///////////////////////////////////////////////////////////////////////////////

if ($wizard)
    echo form_open_multipart('marketplace/wizard/selection');
else
    echo form_open_multipart('marketplace/qsf');
echo form_header(lang('marketplace_quick_select'));

///////////////////////////////////////////////////////////////////////////////
// Form fields and buttons
///////////////////////////////////////////////////////////////////////////////

if ($qsf_ready)
    $buttons = array(
        form_submit_custom('reset', lang('base_reset'), 'high')
    );
else
    $buttons = array(
        form_submit_custom('upload', lang('marketplace_upload_qsf'), 'high')
    );

if (!$qsf_ready) {
    echo field_file('qsf', $filename, lang('marketplace_qsf_file'), $qsf_ready);
} else {
    echo field_input('size', $size, lang('base_file_size'), $qsf_ready);
    echo field_input('apps', $qsf['apps'], lang('marketplace_number_of_apps'), $qsf_ready);
    echo field_input('packages', $qsf['packages'], lang('marketplace_number_of_packages'), $qsf_ready);
    echo field_input(
        'number_of_apps_to_display',
        $number_of_apps_to_display,
        lang('marketplace_number_of_packages'),
        $qsf_ready, array('hide_field' => TRUE,
        'id' => 'number_of_apps_to_display')
    );
}

echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();
echo "<script type='text/javascript'>\n";
echo "  $(document).ready(function() {\n";
echo "    get_apps(false, 0);\n";
echo "  });\n";
echo "</script>\n";
if ($qsf_ready)
    echo "<div id='marketplace-app-container'></div>";
echo "<div style='text-align: center; padding: 10px 10px 0px 10px;'>";
echo "<div id='marketplace-loading' style='padding: 10px 0px 0px 0px;'>" . loading('normal', lang('marketplace_searching_marketplace'), array('icon-below' => TRUE)) . "</div>";
echo "<div id='app_list_overview'></div>";
echo "</div>";
echo "<input id='display_format' type='hidden' value='$display_format'>";
echo "<input type='hidden' value='mode3' id='wizard_marketplace_mode' name='wizard_marketplace_mode' />\n";
