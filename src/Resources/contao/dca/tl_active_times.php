<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

$GLOBALS['TL_DCA']['tl_active_times'] = [
    'config' => [
        'dataContainer' => 'Table',
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'fields' => [
        'username' => [
            'exclude' => true,
        ],
        'length' => [
            'exclude' => true,
        ],
        'month' => [
            'exclude' => true,
        ],
        'year' => [
            'exclude' => true,
        ],
    ],
];
