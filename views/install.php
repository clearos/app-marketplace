<?php

/**
 * Marketplace install view.
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

echo "<div id='installer' style='text-align: center;'></div>";

echo "<div id='info'></div>";

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

// Hack...too difficult to use a table widget here.  Should we have another widget?
echo "<div class='theme-form-container'>";
echo "<table id='infotable' class='theme-hidden theme-form-wrapper' style='width:100%;' cellpadding='0' cellspacing='0'>";

echo "<tr class='theme-form-header'>" .
    "  <td colspan='2'><p class='theme-form-header-heading'>" . lang('marketplace_account_info') . "</p></td>" .
    "</tr>";
echo "<tr id='r_account' class='theme-fieldview'>" .
    "  <td class='theme-field-left'>" . lang('marketplace_account') . "</td>" .
    "  <td class='theme-field-right'><span id='display_sdn_username'>" . loading() . "</span></td>" .
    "</tr>" .
    "<tr id='r_bill_cycle' class='theme-fieldview' style='display: none;'>" .
    "  <td class='theme-field-left'>" . lang('marketplace_billing_cycle') . "</td>" .
    "  <td class='theme-field-right'><span id='bill_cycle'></span></td>" .
    "</tr>" .
    "<tr id='r_total' class='theme-hidden theme-fieldview billing-field'>" .
    "  <td valign='top' class='theme-field-left'>" . lang('marketplace_total') . "</td>" .
    "  <td class='theme-field-right'><span id='display-total'/></div></td>" .
    "</tr>" .
    "<tr id='r_payment_method' class='theme-fieldview' style='display: none;'>" .
    "  <td valign='top' class='theme-field-left'>" . lang('marketplace_payment_method') . "</td>" .
    "  <td class='theme-field-right'><div id='payment_method'>" .
    "    <div id='payment_processing' style='display: none;'></div>" .
    "    <div id='payment_options'>" .
    "      <div id='option_preauth' style='height: 22px;'>" .
    "        <input style='margin: 0px; float: left;' type='radio' class='payment_option' name='payment_method' value='preauth' id='preauth' onclick='toggle_payment_display()'>" .
    "        <div style='float:left; padding-top: 1px;'>" .
    "          <label for='preauth' style='padding-left: 5px;'>" . lang('marketplace_credit_card') . " (<span id='card_number'></span>)</label>" .
    "        </div>" .
    "      </div>" .
    "      <div id='option_po' style='clear: left; height: 22px;'>" .
    "        <input style='float: left; margin: 0px;' type='radio' class='payment_option' name='payment_method' value='po' id='po' onclick='toggle_payment_display()'>" .
    "        <div style='float:left; padding-top: 1px;'><label for='po' style='padding-left: 5px;'>" . lang('marketplace_purchase_order') . " (<span id='po_available'></span>)</label></div> " .
    "        <input type='text' id='po_number' value='' style='width:120px; margin: -1px 0px 0px 5px;' name='po_number' />" .
    "      </div>" .
    "      <div id='option_debit' style='clear:left; height: 22px;'>" .
    "        <input style='margin: 0px; float: left;' class='payment_option' type='radio' name='payment_method' value='debit' id='debit' onclick='toggle_payment_display()'>" .
    "        <div style='float:left; padding-top: 1px;'>" .
    "          <label for='debit' style='padding-left: 5px;'>" . lang('marketplace_debit') . " (<span id='debit_available'></span>)</label>" .
    "        </div>" .
    "      </div>" .
    "    </div>" .
    "  </div></td>" .
    "</tr>" .
    "<tr id='r_notes' class='theme-fieldview' style='display: none;'>" .
    "  <td class='theme-field-left'>" . lang('marketplace_notes') . "</td>" .
    "  <td class='theme-field-right'><span id='notes'></span></td>" .
    "</tr>" .
    "<tr id='r_eval_install' class='theme-fieldview' style='display: none;'>" .
    "  <td class='theme-field-left'>&nbsp;</td>" .
    "  <td class='theme-field-right'><span id='eval_install_cell'>" . form_submit_custom('eval_checkout', lang('marketplace_eval_and_install'), 'high', array('id' => 'eval_checkout')) . "</span></td>" . 
    "</tr>" .
    "<tr id='r_fee_install' class='theme-fieldview' style='display: none;'>" .
    "  <td class='theme-field-left'>&nbsp;</td>" .
    "  <td class='theme-field-right'><span id='fee_install_cell'>" . form_submit_custom('buy_checkout', lang('marketplace_buy_and_install'), 'high', array('id' => 'buy_checkout')) . "</span></td>" . 
    "</tr>"
;
echo "</table>";
echo "</div>";

if ($itemnotfound)
    echo infobox_warning(lang('base_warning'), $itemnotfound) . "<br />";

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('marketplace_description'),
//    lang('marketplace_quantity'),
    lang('marketplace_price'),
    lang('marketplace_unit'),
    lang('marketplace_discount'),
    lang('marketplace_extended'),
    lang('marketplace_eula')
);

if (!empty($items)) {
    echo "<div id='sdn-checkout' title='" . lang('marketplace_checkout') . "'>";
    echo "<div id='sdn-checkout-content'></div>";
    echo "</div>";
}

$total = 0;
$has_prorated = FALSE;
$rows = array(
    'apps' => array(),
    'packages' => array()
);
foreach ($items as $item) {
    $detail_buttons = button_set(
        array(
            anchor_delete('/app/marketplace/install/delete/' . $item->get_id())
        )
    );

    // Skip RPM's like 'vim-enhanced' - only show Marketplace apps.
    if (!$item->get_description()) {
        $rows['packages'][] = array(
            'title' => $item->get_id(),
            'action' => NULL,
            'anchors' => $detail_buttons,
            'details' => array(
                '{' . $item->get_id() . '}',  // By putting {} here, we force non-app packages to the bottom of the table
                '---', '---', '---', '---', FALSE
            )
        );
        continue;
    }

    $row['title'] = $item->get_description();
    $row['action'] = '/app/marketplace/edit/';
    $row['anchors'] = $detail_buttons;

    $discount = number_format($item->get_discount(), 1) . '%';
    $prorated = $item->get_prorated();
    $extended = $item->get_currency() . ' ' . money_format('%!i', $item->get_quantity() * $item->get_unit_price() * (1 - $item->get_discount()/100));
    $unit_price = $item->get_currency() . ' ' . money_format('%!i', $item->get_unit_price());
    $unit = $item->get_display_unit();
    if ($item->get_exempt() && $item->get_unit_price() > 0 || $item->get_unit_price() == 0) {
        $discount = '---';
        $prorated = FALSE;
        $extended = $item->get_currency() . ' ' . money_format('%!i', 0);
        if ($item->get_unit_price() == 0)
            $unit_price = lang('marketplace_free');
        else
            $unit_price = '---';
    } else if ($item->get_exempt() && $item->get_unit_price()> 0) {
        $unit = '---';
        $prorated = FALSE;
    } else if ($item->get_evaluation() && $item->get_unit_price() > 0) {
        $discount = lang('marketplace_free_trial');
        $extended = $item->get_currency() . ' ' . money_format('%!i', 0);
        $prorated = FALSE;
    }
        
    if ($prorated)
        $has_prorated = TRUE;
    $unit = ($item->get_exempt() && $item->get_unit_price() > 0 ? '' : ($item->get_unit() < 100 ? '' : ' ' . preg_replace('/^\/\s*/', '', $item->get_display_unit())));
    $row['details'] = array(
        $item->get_description() . ($item->get_note() ? "<div>" . lang('marketplace_note') . ":  " . $item->get_note() . "</div>": ""), 
        $unit_price,
        $unit,
        $discount,
        $extended,
        ($item->get_eula() > 0 ? 
        "<div id='basename-" . $item->get_id() . "'>" .
        "<a class='eula-link highlight-link' href='/app/marketplace/install' id='eula-" . $item->get_eula() . "'>" . lang('marketplace_eula') . "</a>" .
        "</div>" : lang('marketplace_none'))
    );
    $rows['apps'][] = $row;

    if (!$item->get_exempt())
        $total += $item->get_quantity() * $item->get_unit_price() * (1 - $item->get_discount()/100);
}

///////////////////////////////////////////////////////////////////////////////
// Anchors
///////////////////////////////////////////////////////////////////////////////

if (count($rows) === 0) {
    if ($this->session->userdata('wizard'))
        $anchors = array(
            anchor_custom('/app/marketplace/install/delete/all', lang('marketplace_delete_all')),
            anchor_custom('/app/marketplace/wizard/stop', lang('marketplace_install_apps_later'))
        );
    else
        $anchors = array(
            anchor_custom('/app/marketplace/install/delete/all', lang('marketplace_delete_all')),
            form_submit_custom('free_checkout', lang('marketplace_download_and_install'), 'high', array('id' => 'free_checkout'))
        );
} else if ($total == 0) {
    $anchors = array(
        anchor_custom('/app/marketplace/install/delete/all', lang('marketplace_delete_all')),
        form_submit_custom('free_checkout', lang('marketplace_download_and_install'), 'high', array('id' => 'free_checkout'))
    );
} else {
    $anchors = array(anchor_custom('/app/marketplace/install/delete/all', lang('marketplace_delete_all')));
}

///////////////////////////////////////////////////////////////////////////////
// App Sumary table
///////////////////////////////////////////////////////////////////////////////

$options['default_rows'] = 100;
$options['id'] = 'install_apps';
$options['empty_table_message'] = 'No apps selected.';
$rows = array_merge($rows['apps'], $rows['packages']);

echo summary_table(
    lang('marketplace_app_install_list'),
    $anchors,
    $headers,
    $rows,
    $options
);

// Need this value in JS
echo "<input type='hidden' name='total' id='total' value='$total' />";
echo "<input type='hidden' name='num_of_apps' id='num_of_apps' value='" . count($rows) . "' />";
echo "<input type='hidden' name='has_prorated' id='has_prorated' value='" . ($has_prorated ? 1 : 1) . "' />";
