<?php
defined('TYPO3_MODE') || die();

if (version_compare(TYPO3_version, '9.0', '<')) {
    $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cidaas']);
} else {
    $settings = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cidaas'] ?? [];
}

$tempColumns = [
    'tx_oidc' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:cidaas/Resources/Private/Language/locallang_db.xlf:fe_users.tx_oidc',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'readOnly' => (bool)$settings['frontendUserMustExistLocally'] ? 0 : 1,
        ]
    ],
        'access_token' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:cidaas/Resources/Private/Language/locallang_db.xlf:fe_users.access_token',
        'config' => [
            'type' => 'input',
            'size' => 1000,
        ]
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_oidc');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'access_token');
