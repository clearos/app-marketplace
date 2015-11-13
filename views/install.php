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

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

$buttons = array(
    form_submit_custom('eval_checkout', lang('marketplace_eval_and_install'), 'high', array('id' => 'eval_checkout', 'hide' => TRUE)),
    form_submit_custom('buy_checkout', lang('marketplace_buy_and_install'), 'high', array('id' => 'buy_checkout'))
);

echo form_open('marketplace/install', array('id' => 'account-information-container'));
echo form_header(lang('marketplace_account_info'));
echo field_dropdown('username', '', array(), lang('marketplace_account'), TRUE);
echo field_input('billing_cycle', '', lang('marketplace_billing_cycle'), TRUE);
echo field_input('display_total', $display_total, lang('marketplace_total'), TRUE);
echo field_radio_set(
    lang('marketplace_payment_method'),
    array(
        field_radio_set_item('preauth', 'payment_method', lang('marketplace_credit_card') . "  (<span id='card_number'></span>)", FALSE, FALSE),
        field_radio_set_item('po', 'payment_method', lang('marketplace_purchase_order') . "  (<span id='po_available'></span>)", FALSE, FALSE),
        field_radio_set_item('debit', 'payment_method', lang('marketplace_debit') . "  (<span id='debit_available'></span>)", FALSE, FALSE)
    ),
    'payment_method',
    array('orientation' => 'vertical')
);
echo field_input('notes', $notes, lang('marketplace_notes'), TRUE);
echo field_button_set($buttons);
echo form_footer(array('loading' => TRUE));
echo form_close();

if ($itemnotfound)
    echo infobox_warning(lang('base_warning'), $itemnotfound);

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('base_description'),
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
            anchor_delete('/app/marketplace/install/delete/' . $item->get_id(), 'high')
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
        else if (is_int($item->get_evaluation()) && $item->get_evaluation() > 0)
            $unit_price = lang('marketplace_free_trial') . ' (' . $item->get_evaluation() . ' ' . lang('base_days') . ')';
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
        "</div>" : lang('base_none'))
    );
    $rows['apps'][] = $row;

    if (!$item->get_exempt() && !$item->get_evaluation())
        $total += $item->get_quantity() * $item->get_unit_price() * (1 - $item->get_discount()/100);
}

// Merge apps and packages
$rows = array_merge($rows['apps'], $rows['packages']);

///////////////////////////////////////////////////////////////////////////////
// Anchors
///////////////////////////////////////////////////////////////////////////////

if (count($rows) == 0) {
    if ($this->session->userdata('wizard'))
        $anchors = array(
            anchor_custom('/app/marketplace/wizard/stop', lang('marketplace_install_apps_later'))
        );
    else
        $anchors = array(
            form_submit_custom('free_checkout', lang('marketplace_download_and_install'), 'high', array('id' => 'free_checkout'))
        );
} else if ($total == 0) {
    $anchors = array(
        form_submit_custom('free_checkout', lang('marketplace_download_and_install'), 'high', array('id' => 'free_checkout')),
        anchor_custom('/app/marketplace/install/delete/all', lang('marketplace_delete_all'), 'low')
    );
} else {
    $anchors = array(anchor_custom('/app/marketplace/install/delete/all', lang('marketplace_delete_all')));
}

///////////////////////////////////////////////////////////////////////////////
// App Sumary table
///////////////////////////////////////////////////////////////////////////////

$options['default_rows'] = 100;
$options['id'] = 'install_apps';
$options['empty_table_message'] = lang('marketplace_no_apps_selected');

echo summary_table(
    lang('marketplace_app_install_list'),
    $anchors,
    $headers,
    $rows,
    $options
);

if (count($rows) == 0) {
    echo infobox_and_redirect(
        lang('marketplace_select_apps'),
        lang('marketplace_app_select_help'),
        '/app/marketplace',
        lang('marketplace_search_marketplace')
    );
}
    
// Need these value in JS
echo "<input type='hidden' name='total' id='total' value='$total' />";
echo "<input type='hidden' name='num_of_apps' id='num_of_apps' value='" . count($rows) . "' />";
echo "<input type='hidden' name='po_number' id='po_number' value='' />";
echo "<input type='hidden' name='has_prorated' id='has_prorated' value='" . ($has_prorated ? 1 : 1) . "' />";
echo modal_input(
    lang('marketplace_po_required'),
    lang('marketplace_po_enter'),
    array("id" => "po"),
    "po_number",
    "modal-input-po",
    array("callback" => "update_po();")
);
echo modal_confirm(
    lang('base_confirmation_required'),
    lang('marketplace_wizard_skip_install'),
    "/app/marketplace/wizard/stop",
    NULL,
    NULL,
    "wizard_next_showstopper"
);
