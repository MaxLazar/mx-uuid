<?php

$addonJson = json_decode(file_get_contents(__DIR__ . '/addon.json'));

if (!defined('MX_UUID_NAME')) {
    define('MX_UUID_NAME', $addonJson->name);
    define('MX_UUID_VERSION', $addonJson->version);
    define('MX_UUID_DOCS', '');
    define('MX_UUID_DESCRIPTION', $addonJson->description);
    define('MX_UUID_DEBUG', false);
}

return [
    'name'           => $addonJson->name,
    'description'    => $addonJson->description,
    'version'        => $addonJson->version,
    'namespace'      => $addonJson->namespace,
    'author'         => 'Max Lazar',
    'author_url'     => 'https://eecms.dev',
    'settings_exist' => false,
    // Advanced settings
    'fieldtypes'     => array(
        'UuidField'     => array(
        'name'           => 'MX UUID'
        )
    )
];
