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

use Zenstruck\Mirror;
use Zenstruck\MirrorAttribute;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface AttributesMirror extends Mirror
{
    /**
     * @return Attributes<MirrorAttribute<object,self>,self>
     */
    public function attributes(): Attributes;

    /**
     * @return \ReflectionClass<object>|\ReflectionClassConstant|\ReflectionParameter|\ReflectionProperty|\ReflectionFunctionAbstract
     */
    public function reflector(): \Reflector;
}
