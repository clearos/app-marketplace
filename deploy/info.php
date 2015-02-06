<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'marketplace';
$app['version'] = '1.6.8';
$app['release'] = '1';
$app['vendor'] = 'ClearCenter';
$app['packager'] = 'ClearCenter';
$app['license'] = 'Proprietary';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('marketplace_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('marketplace_app_name');
$app['category'] = lang('base_category_spotlight');
$app['subcategory'] = lang('base_subcategory_overview');

// Wizard extras
$app['controllers']['selection']['wizard_name'] = lang('marketplace_app_selection');
$app['controllers']['selection']['wizard_description'] = lang('base_loading...');
$app['controllers']['selection']['inline_help'] = array(
    lang('marketplace_more_info') => ''
);
$app['controllers']['install']['wizard_name'] = lang('marketplace_app_review');
$app['controllers']['install']['wizard_description'] = lang('marketplace_app_review_description');

// Yuck - TODO - Probably shouldn't be using help_box function to do this
$app['controllers']['select']['help_action'] = array(
    'url' => '/app/marketplace/all',
    'text' => lang('marketplace_select_all'),
    'priority' => 'high',
    'js' => array('id' => 'toggle_select')
);

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

// TODO: push software-updates and dashboard to comps
$app['requires'] = array(
    'app-network',
    'app-registration',
    'app-software-updates',
    'app-dashboard',
);

$app['core_requires'] = array(
    'app-clearcenter-core => 1:1.5.11',
    'app-registration-core => 1:1.2.4',
    'app-base-core => 1:1.4.38',
    'yum-marketplace-plugin >= 1.5',
    'theme-default >= 6.4.26',
    'clearos-framework >= 6.4.27',
    'clearos-release-jws >= 1.1'
);

$app['core_file_manifest'] = array(
   'marketplace.acl' => array( 'target' => '/var/clearos/base/access_control/authenticated/marketplace' ),
   'marketplace.conf' => array(
        'target' => '/etc/clearos/marketplace.conf',
        'mode' => '0644',
        'owner' => 'webconfig',
        'group' => 'webconfig',
        'config' => TRUE,
        'config_params' => 'noreplace',
    )
);

$app['core_directory_manifest'] = array(
   '/var/clearos/marketplace' => array('mode' => '755', 'owner' => 'webconfig', 'group' => 'webconfig')
);
