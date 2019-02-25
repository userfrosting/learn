<?php
/**
 * Multisite setup for sub-directories or path based
 * URLs for subsites.
 *
 * DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING!
 */

use Grav\Common\Filesystem\Folder;

// Get relative path from Grav root.
$path = isset($_SERVER['PATH_INFO'])
   ? $_SERVER['PATH_INFO']
   : Folder::getRelativePath($_SERVER['REQUEST_URI'], ROOT_DIR);

// Extract name of subsite from path
$name = Folder::shift($path);
$folder = "sites/{$name}";
$prefix = "/{$name}";

// If no sites is selected, default to master
if (!$name || !is_dir(ROOT_DIR . "user/{$folder}")) {
    return ['environment' => '4.1'];
}

// Prefix all pages with the name of the subsite
$container['pages']->base($prefix);

return [
    'environment' => $name,
    'streams' => [
        'schemes' => [
            'user' => [
               'type' => 'ReadOnlyStream',
               'prefixes' => [
                   '' => ["user/{$folder}"],
               ]
            ],
            'config' => [
                'type' => 'ReadOnlyStream',
                'prefixes' => [
                    '' => ['environment://config', 'user://config', 'user/config', 'system/config'],
                ]
            ],
            'themes' => [
                'type' => 'ReadOnlyStream',
                'prefixes' => [
                    '' => ['user://themes', 'user/themes'],
                ]
            ],
            'plugins' => [
                'type' => 'ReadOnlyStream',
                'prefixes' => [
                    '' => ['user://plugins', "user/plugins"],
                ]
            ],
            'plugin' => [
                'type' => 'ReadOnlyStream',
                'prefixes' => [
                    '' => ['user://plugins', "user/plugins"],
                ]
            ]
        ]
    ]
];
