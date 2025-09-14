<?php

declare(strict_types=1);

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
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
        'versions' => [
            'available' => [
                '6.0' => '6.0 Beta',
                '5.1' => '5.1',
                '5.0' => '5.0',
            ],
            'latest' => '6.0',
        ],
    ],
];
