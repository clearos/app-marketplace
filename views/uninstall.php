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

echo infobox_warning(
    lang('marketplace_uninstall') . ' - ' . $prefix . preg_replace("/_/", "-", $basename),
    lang('marketplace_delete_dependencies'), 
    array ('buttons' =>
        array (
            anchor_custom('#', lang('marketplace_confirm_uninstall'), 'high', array('id' => 'uninstall-app-confirm')),
            anchor_cancel('/app/marketplace/view/' . $basename)
        )
    )
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
    array(
        lang('marketplace_package_name'),
        lang('base_description'),
        lang('base_version'),
        lang('base_size')
    ),
    $items,
    array('no_action' => TRUE)
);

echo modal_confirm(
    lang('base_warning'),
    lang('marketplace_confirm_uninstall_last_chance'),
    '/app/marketplace/uninstall/' . $basename . '/' . $app_delete_key,
    array('id' => 'uninstall-app-confirm')
);
