<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

use Contao\ActivityBundle\Model\ActiveTimesModel;
use Contao\ActivityBundle\Model\LogModel;

$GLOBALS['TL_MODELS']['tl_active_times'] = ActiveTimesModel::class;
$GLOBALS['TL_MODELS']['tl_log'] = LogModel::class;
