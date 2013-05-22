<?php

/**
 * Marketplace uninstall view.
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
$this->load->helper('number');
$this->load->library('marketplace/Marketplace');

echo infobox_highlight(
    lang('marketplace_uninstall') . ' - ' . $prefix . preg_replace("/_/", "-", $basename),
    lang('marketplace_delete_dependencies') . 
    '<div style=\'text-align: center;margin-top: 10px;\'>' .
    button_set(
        array (
            anchor_custom('/app/marketplace/uninstall/' . $basename . '/' . $app_delete_key, lang('marketplace_confirm_uninstall'), 'high'),
            anchor_cancel('/app/marketplace/view/' . $basename)
        )
    ) .
    '</div>'
);

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

$items = array();

foreach ($apps as $app => $info) {
    $item = array(
        'title' => $app,
        'action' => '',
        'anchors' => NULL,
        'details' => array(
            $app,
            $info['summary'],
            $info['version'] . '-' . $info['release'],
            byte_format($info['size'])
        )
    );

    $items[] = $item;
}

echo summary_table(
    lang('marketplace_uninstall_list'),
    NULL,
    array(lang('marketplace_package_name'), lang('base_description'), lang('marketplace_version'), lang('base_size')),
    $items,
    array('no_action' => TRUE)
);
