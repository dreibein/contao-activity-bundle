<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

namespace Dreibein\ActivityBundle\Resources\contao\dca;

$table = 'tl_log';

// Add the data-protection-page-field to the dca, so it can be added to the database
$GLOBALS['TL_DCA'][$table]['fields']['inStatistic'] = [
    'exclude' => true,
    'sql' => ['type' => 'boolean', 'length' => 1, 'notnull' => true, 'default' => 0],
];
