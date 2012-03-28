<?php

/////////////////////////////////////////////////////////////////////////////
// Marketplace Cart Rules
/////////////////////////////////////////////////////////////////////////////

$rules['incompatible'] = array(
    'app-active-directory' => array(
        'app-google-apps',
        'app-openldap-directory',
        'app-zarafa',
        'app-zarafa-community',
        'app-zarafa-extension',
        'app-account-synchronization',
        'app-password-policies'
    )
);
