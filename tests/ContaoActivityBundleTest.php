<?php

/*
 * This file is part of [package name].
 *
 * (c) John Doe
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\ActivityBundle\Tests;

use Contao\ActivityBundle\ContaoActivityBundle;
use PHPUnit\Framework\TestCase;

class ContaoActivityBundleTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new ContaoActivityBundle();

        $this->assertInstanceOf('Contao\ActivityBundle\ContaoActivityBundle', $bundle);
    }
}
