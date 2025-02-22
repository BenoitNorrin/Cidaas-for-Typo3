<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    // Configuration of authentication service
    $typo3Branch = class_exists(\TYPO3\CMS\Core\Information\Typo3Version::class)
            ? (new \TYPO3\CMS\Core\Information\Typo3Version())->getBranch()
            : TYPO3_branch;
    if (version_compare($typo3Branch, '9.0', '<')) {
        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
    } else {
        $settings = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][$_EXTKEY] ?? [];
    }

    // Service configuration
    $subTypesArr = [];
    $subTypes = '';
    if ((bool)$settings['enableFrontendAuthentication']) {
        $subTypesArr[] = 'getUserFE';
        $subTypesArr[] = 'authUserFE';
        $subTypesArr[] = 'getGroupsFE';
    }
    if (is_array($subTypesArr)) {
        $subTypesArr = array_unique($subTypesArr);
        $subTypes = implode(',', $subTypesArr);
    }

    $authenticationClassName = \Widas\Cidaas\Service\AuthenticationService::class;
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
        $_EXTKEY,
        'auth' /* sv type */,
        $authenticationClassName /* sv key */,
        [
            'title' => 'Authentication service',
            'description' => 'Authentication service for OpenID Connect.',
            'subtype' => $subTypes,
            'available' => true,
            'priority' => 82, /* will be called before default TYPO3 authentication service */
            'quality' => 80,
            'os' => '',
            'exec' => '',
            'className' => $authenticationClassName,
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Widas.' . $_EXTKEY,
        'Pi1',
        [
            \Widas\Cidaas\Controller\AuthenticationController::class => 'connect',
        ],
        // non-cacheable actions
        [
            \Widas\Cidaas\Controller\AuthenticationController::class => 'connect'
        ]
    );
    


    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('felogin')) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['postProcContent'][$_EXTKEY] = \Widas\Cidaas\Hooks\FeloginHook::class . '->postProcContent';
    }

    // Add typoscript for custom login plugin
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43('oidc', null, '_login');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43('oidc', null, '_logout');

    // Require 3rd-party libraries, in case TYPO3 does not run in composer mode
    if (!defined('TYPO3_COMPOSER_MODE')) {
    $pharFileName = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Libraries/league-oauth2-client.phar';
    if (is_file($pharFileName)) {
        @include 'phar://' . $pharFileName . '/vendor/autoload.php';
        }
    }
};

$boot('cidaas');
unset($boot);
