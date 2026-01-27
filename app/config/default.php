<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

/*
 * Main configuration file.
 */
return [
    /*
    * ----------------------------------------------------------------------
    * Address Book
    * ----------------------------------------------------------------------
    */
    'address_book' => [
        'admin' => [
            'email' => env('MAIL_FROM_ADDRESS', 'noreply@userfrosting.com'),
            'name'  => env('MAIL_FROM_NAME', 'UserFrosting'),
        ],
    ],

    // TODO : Disable page cache by default in dev mode, but keep search cache enabled.

    /**
     * ----------------------------------------------------------------------
     * Learn Settings
     *
     * Settings for the documentation application.
     * - Cache : Enable/disable caching of documentation pages and menu.
     * - Key   : Cache key prefix for cached documentation pages and menu.
     * - TTL   : Time to live for cached documentation pages and menu, in seconds.
     * ----------------------------------------------------------------------
     */
    'learn' => [
        'cache' => [
            'key'     => 'learn.%1$s.%2$s',
            'ttl'     => 86400,
        ],
        'github' => [
            'url'    => 'https://github.com/userfrosting/learn',
            'path'   => '/app',
            'branch' => 'main',
        ],
        'versions' => [
            'available' => [
                '6.0' => '6.0 Beta'
            ],
            'latest' => '6.0',
        ],
        'search' => [
            'min_length'        => 3,   // Minimum length of search query
            'default_size'      => 25,  // Default number of results per page
            'snippet_length'    => 150, // Length of content snippets in results
            'max_results'       => 150, // Maximum number of results to consider for pagination
            'cache' => [
                'key' => 'learn.search.%1$s', // %1$s = keyword hash
                'ttl' => 86400 * 30,          // 30 days
            ],
            'index'             => [
                'key' => 'learn.index.%1$s', // %1$s = version
                'ttl' => 86400 * 30,         // 30 days

                // Metadata fields to include in the search index
                'metadata_fields' => ['description', 'tags', 'category', 'author'],
            ],
        ],
    ],

    /*
    * ----------------------------------------------------------------------
    * Site Settings
    * ----------------------------------------------------------------------
    */
    'site' => [
        'analytics' => [
            'google' => [
                'code'    => '',
                'enabled' => false,
            ],
        ],
        'author'    => 'UserFrosting Community',
        'locales'   => [
            'available' => [
                'en_US' => true,
                'fr_FR' => false,
            ],
            'default' => 'en_US',
        ],
        'title' => 'UserFrosting',
        'uri'   => [
            'author'    => 'https://www.userfrosting.com',
            'publisher' => '',
            'public'    => null,
        ],
    ],
];
