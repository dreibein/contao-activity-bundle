<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

namespace Contao\ActivityBundle\Model;

use Contao\Model;

/**
 * @property int    $tstamp
 * @property string $source
 * @property string $action
 * @property string $username
 * @property string $text
 * @property string $func
 * @property string $browser
 * @property int    $inStatistic
 */
class LogModel extends Model
{
    protected static $strTable = 'tl_log';
}
