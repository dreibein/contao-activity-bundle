<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

\Contao\System::loadLanguageFile('tl_application_form');

$table = 'tl_active_times';
$currentYear = date('Y');

$GLOBALS['TL_DCA'][$table] = [
    'config' => [
        'dataContainer' => 'Table',
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'length' => 10, 'unsigned' => true, 'notnull' => true, 'autoincrement' => true],
        ],
        'username' => [
            'exclude' => true,
            'sql' => ['type' => 'string', 'length' => 255, 'notnull' => true, 'default' => ''],
        ],
        'length' => [
            'exclude' => true,
            'sql' => ['type' => 'integer', 'notnull' => true, 'unsigned' => true, 'default' => 0],
        ],
        'month' => [
            'exclude' => true,
            'sql' => ['type' => 'integer', 'notnull' => true, 'unsigned' => true, 'default' => 0],
        ],
        'year' => [
            'exclude' => true,
            'sql' => ['type' => 'integer', 'notnull' => true, 'unsigned' => true, 'default' => 0],
        ],
    ],
];
