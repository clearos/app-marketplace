<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'marketplace';
$app['version'] = '1.0.16';
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
$app['controllers']['server']['wizard_name'] = lang('marketplace_server_apps');
$app['controllers']['server']['wizard_description'] = lang('marketplace_server_apps_description');
$app['controllers']['gateway']['wizard_name'] = lang('marketplace_gateway_apps');
$app['controllers']['gateway']['wizard_description'] = lang('marketplace_gateway_apps_description');
$app['controllers']['network']['wizard_name'] = lang('marketplace_network_apps');
$app['controllers']['network']['wizard_description'] = lang('marketplace_network_apps_description');
$app['controllers']['system']['wizard_name'] = lang('marketplace_system_apps');
$app['controllers']['system']['wizard_description'] = lang('marketplace_system_apps_description');
$app['controllers']['install']['wizard_name'] = lang('marketplace_app_review');
$app['controllers']['install']['wizard_description'] = lang('marketplace_app_review_description');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['requires'] = array(
    'app-network',
    'app-registration'
);

$app['core_requires'] = array(
    'app-clearcenter-core',
    'app-registration-core',
    'app-base >= 1:1.0.10'
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
