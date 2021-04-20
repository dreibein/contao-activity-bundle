<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

use Contao\ActivityBundle\Model\LogModel;

$GLOBALS['TL_MODELS']['tl_log'] = LogModel::class;
