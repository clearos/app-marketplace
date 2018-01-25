<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'marketplace';
$app['version'] = '2.4.1';
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
$app['category'] = lang('base_category_system');
$app['subcategory'] = lang('base_subcategory_base');

// Wizard extras
$app['controllers']['selection']['inline_help'] = array(
    lang('marketplace_install_by_function') => lang('marketplace_install_by_function_help')
);
$app['controllers']['wizard']['wizard_name'] = lang('marketplace_getting_started');
$app['controllers']['wizard']['wizard_description'] = lang('marketplace_wizard_congrats');

$app['controllers']['install']['wizard_name'] = lang('marketplace_app_review');
$app['controllers']['install']['wizard_description'] = lang('marketplace_app_review_description');

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

// KLUDGE: The following dependencies should be moved to a new app at some
// point (e.g. app-clearos). For now, these are here to make sure the packages
// are installed on ClearOS, but not a barebones ClearVM system.
// - syswatch
// - webconfig-php-* (to avoid webconfig restarts)

$app['core_requires'] = array(
    'app-clearcenter-core => 1:1.5.11',
    'app-registration-core => 1:1.2.4',
    'app-base-core => 1:1.4.38',
    'yum-marketplace-plugin >= 1.5',
    'clearos-framework >= 6.4.27',
    'clearos-release-jws >= 1.1',
    'syswatch',
    'webconfig-php-gd',
    'webconfig-php-ldap',
    'webconfig-php-mysql'
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
