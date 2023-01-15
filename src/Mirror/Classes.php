<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror;

use Zenstruck\Mirror\Internal\MirrorIterator;
use Zenstruck\MirrorClass;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends MirrorIterator<MirrorClass>
 *
 * @method MirrorClass[] getIterator()
 */
final class Classes extends MirrorIterator
{
}
