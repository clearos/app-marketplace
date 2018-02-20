<?php

/////////////////////////////////////////////////////////////////////////////
// Marketplace Cart Rules
/////////////////////////////////////////////////////////////////////////////

$rules = array(
    'incompatible' => array(
        'app-active-directory' => array(
            'app-google-apps',
            'app-openldap-directory',
            'app-openldap-directory-core',
            'app-zarafa-community',
            'app-zarafa-small-business',
            'app-zarafa-professional',
            'app-zarafa-extension',
            'app-shell-extension',
            'app-password-policies',
            'app-owncloud',
            'app-owncloud-business',
            'app-owncloud-extension',
            'app-samba-directory',
            'app-wpad',
            'app-kopano-basic',
            'app-kopano-pro',
        ),
        'app-samba-directory' => array(
            'app-google-apps',
            'app-openldap-directory',
            'app-openldap-directory-core',
            'app-zarafa-community',
            'app-zarafa-small-business',
            'app-zarafa-professional',
            'app-zarafa-extension',
            'app-shell-extension',
            'app-owncloud',
            'app-owncloud-business',
            'app-owncloud-extension',
            'app-password-policies',
            'app-active-directory',
            'app-wpad',
            'app-kopano-basic',
            'app-kopano-pro',
        ),
        'app-zarafa-community' => array(
            'app-imap',
            'app-samba-directory',
            'app-zarafa-small-business',
            'app-zarafa-professional',
            'app-kopano-basic',
            'app-kopano-pro',
        ),
        'app-zarafa-small-business' => array(
            'app-imap',
            'app-samba-directory',
            'app-zarafa-community',
            'app-zarafa-professional',
            'app-kopano-basic',
            'app-kopano-pro',
        ),
        'app-zarafa-professional' => array(
            'app-imap',
            'app-samba-directory',
            'app-zarafa-community',
            'app-zarafa-small-business',
            'app-kopano-basic',
            'app-kopano-pro',
        ),
        'app-kopano-basic' => array(
            'app-imap',
            'app-samba-directory',
            'app-zarafa-community',
            'app-zarafa-small-business',
            'app-zarafa-professional',
            'app-kopano-pro',
        ),
        'app-kopano-pro' => array(
            'app-imap',
            'app-samba-directory',
            'app-zarafa-community',
            'app-zarafa-small-business',
            'app-zarafa-professional',
            'app-kopano-basic',
        ),
        'app-dnsthingy' => array(
            'app-web-proxy',
            'app-content-filter',
            'app-gateway-management'
        ),
        'app-gateway-management' => array(
            'app-web-proxy',
            'app-content-filter',
            'app-dnsthingy',
        ),
        'app-clearglass-community' => array(
            'app-clearglass-enterprise',
        ),
        'app-clearglass-enterprise' => array(
            'app-clearglass-community',
        ),
    ),
    'requires' => array(
        'app-remote-backup-1gb' => 'app-remote-backup',
        'app-remote-backup-5gb' => 'app-remote-backup',
        'app-remote-backup-10gb' => 'app-remote-backup',
        'app-remote-backup-25gb' => 'app-remote-backup',
        'app-remote-backup-50gb' => 'app-remote-backup',
        'app-remote-backup-100gb' => 'app-remote-backup',
        'app-zarafa-professional-5-users' => 'app-zarafa-professional',
        'app-zarafa-professional-10-users' => 'app-zarafa-professional',
        'app-zarafa-professional-20-users' => 'app-zarafa-professional',
        'app-zarafa-professional-50-users' => 'app-zarafa-professional',
        'app-zarafa-professional-100-users' => 'app-zarafa-professional',
        'app-zarafa-small-business-5-users' => 'app-zarafa-small-business',
        'app-zarafa-small-business-10-users' => 'app-zarafa-small-business',
        'app-zarafa-small-business-20-users' => 'app-zarafa-small-business',
        'app-zarafa-small-business-50-users' => 'app-zarafa-small-business',
        'app-zarafa-small-business-100-users' => 'app-zarafa-small-business',
        'app-kaspersky-gateway-50-users' => 'app-kaspersky-gateway',
        'app-kaspersky-gateway-100-users' => 'app-kaspersky-gateway',
        'app-kaspersky-gateway-250-users' => 'app-kaspersky-gateway',
        'app-kaspersky-mail-50-users' => 'app-kaspersky-mail',
        'app-kaspersky-mail-100-users' => 'app-kaspersky-mail',
        'app-kaspersky-mail-250-users' => 'app-kaspersky-mail',
        'app-owncloud-business-5-users' => 'app-owncloud-business',
        'app-owncloud-business-10-users' => 'app-owncloud-business',
        'app-owncloud-business-20-users' => 'app-owncloud-business',
        'app-owncloud-business-50-users' => 'app-owncloud-business',
        'app-owncloud-business-100-users' => 'app-owncloud-business',
        'app-kopano-basic-5-users' => 'app-zarafa-small-business',
        'app-kopano-basic-10-users' => 'app-zarafa-small-business',
        'app-kopano-basic-20-users' => 'app-zarafa-small-business',
        'app-kopano-basic-25-users' => 'app-zarafa-small-business',
        'app-kopano-basic-50-users' => 'app-zarafa-small-business',
        'app-kopano-basic-100-users' => 'app-zarafa-small-business',
        'app-kopano-basic-250-users' => 'app-zarafa-small-business',
        'app-kopano-basic-500-users' => 'app-zarafa-small-business',
        'app-kopano-webmeetings' => 'app-kopano-basic',
        'app-kopano-webmeetings-5-users' => 'app-kopano-basic',
        'app-kopano-webmeetings-10-users' => 'app-kopano-basic',
        'app-kopano-webmeetings-20-users' => 'app-kopano-basic',
        'app-kopano-webmeetings-25-users' => 'app-kopano-basic',
        'app-kopano-webmeetings-50-users' => 'app-kopano-basic',
        'app-kopano-webmeetings-100-users' => 'app-kopano-basic',
        'app-kopano-webmeetings-250-users' => 'app-kopano-basic',
        'app-kopano-webmeetings-500-users' => 'app-kopano-basic',
    )
);
