<?php


declare(strict_types=1);

/*
 * This file is part of the Dreibein-Activity-Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace Dreibein\ActivityBundle\Resources\contao\dca;

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$table = 'tl_log';

// Check if the entry must be added.
if (!$GLOBALS['TL_DCA'][$table]['fields']['inStatistic']) {
    // Add the data-protection-page-field to the palette
    PaletteManipulator::create()
        ->addField('inStatistic', 'global_legend', PaletteManipulator::POSITION_APPEND);

    // Add the data-protection-page-field to the dca, so it can be added to the database
    $GLOBALS['TL_DCA'][$table]['fields']['inStatistic'] = [
        'label' => &$GLOBALS['TL_LANG'][$table]['inStatistic'],
        'exclude' => true,
        'eval' => ['fieldType' => 'checkbox', 'tl_class' => 'clr', 'multiple' => false],
        'sql' => ['type' => 'integer', 'length' => 1, 'unsigned' => true, 'notnull' => true, 'default' => 0],
    ];
}
