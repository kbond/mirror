# zenstruck/mirror

Alternate Reflection API and related helpers.

## Installation

```bash
composer require zenstruck/mirror
```

## API

### Class-Related Mirrors

#### `MirrorClass`

Wraps a `\ReflectionClass`.

```php
use Zenstruck\MirrorClass;

// create
$class = MirrorClass::wrap($reflectionClass);
$class = MirrorClass::for($objectOrClass);

$object = $class->instantiateWithoutConstructor();
$object = $class->instantiate('constructor arg1', 'constructor arg2', ...);

// matches array keys to constructor parameter names (order does not matter)
$object = $class->instantiate(['param1' => 'constructor arg1', ...]); // order

$object = $class->instantiateWith($factoryCallback, ['arg1', 'arg2']);

// matches array keys to callback parameter names (order does not matter)
$object = $class->instantiateWith($factoryCallback, ['param2' => 'arg2', 'param1' => 'arg1']);

// use named constructor
$object = $class->instantiateWith('staticNamedConstructor');
$object = $class->instantiateWith('staticNamedConstructor', ['arg1', 'arg2']);

// access static properties
$class->get('someStaticProperty'); // mixed
$class->set('someStaticProperty', 'value'); // void

// call a static method (can be any visibility)
$class->call('someStaticMethod'); // mixed
$class->call('someStaticMethod', ['arg1', 'arg2']); // mixed

// matches array keys to method parameter names (order does not matter)
$class->call('someStaticMethod', ['param2' => 'arg2', 'param1' => 'arg1']);

// other methods
$class->constructor(); // ?MirrorMethod
$class->isA(Some::class); // bool
$class->isA($objectInstance); // bool
$class->isAbstract(); // bool
$class->isInterface(); // bool
$class->isInstantiable(); // bool
$class->isTrait(); // bool
$class->isCloneable(); // bool
$class->isAnonymous(); // bool
$class->isReadonly(); // bool
$class->isUserDefined(); // bool
$class->isInternal(); // bool
$class->comment(); // ?string
$class->reflector(); // \ReflectionClass
$class->file(); // ?string
$class->name(); // string (FQCN)
$class->shortName(); // string (class name w/o namespace)
$class->namespace(); // string
$class->parent(); // ?MirrorClass
$class->interfaces(); // Classes|MirrorClass[]
$class->traits(); // Traits|MirrorClass[]
$class->methods(); // Methods|MirrorMethod[]
$class->properties(); // Properties|MirrorProperty[]
$class->constants(); // ClassConstants|MirrorClassConstant[]
```

#### `MirrorObject`

Wraps a `\ReflectionObject`.

```php
use Zenstruck\MirrorObject;

$object = MirrorObject::for($someObject);

$object->get('someProperty'); // mixed (can be static or instance property)
$object->set('someProperty', 'value'); // void (can be static or instance property)

// call a method (can be any visibility)
$object->call('someMethod'); // mixed
$object->call('someMethod', ['arg1', 'arg2']); // mixed

// matches array keys to method parameter names (order does not matter)
$object->call('someMethod', ['param2' => 'arg2', 'param1' => 'arg1']);

// other methods
$object->object(); // object (wrapped object)
$object->class(); // MirrorClass
$object->constructor(); // ?MirrorMethod
$object->isA(Some::class); // bool
$object->isA($objectInstance); // bool
$object->isCloneable(); // bool
$object->isAnonymous(); // bool
$object->isReadonly(); // bool
$object->isUserDefined(); // bool
$object->isInternal(); // bool
$object->comment(); // ?string
$object->reflector(); // \ReflectionObject
$object->file(); // ?string
$object->name(); // string (FQCN)
$object->shortName(); // string (class name w/o namespace)
$object->namespace(); // string
$object->parent(); // ?MirrorClass
$object->interfaces(); // Classes|MirrorClass[]
$object->traits(); // Traits|MirrorClass[]
$object->methods(); // Methods|MirrorMethod[]
$object->properties(); // Properties|MirrorProperty[]
$object->constants(); // ClassConstants|MirrorClassConstant[]
```

#### `Classes`

`Zenstruck\Mirror\Classes`, a collection of `MirrorClass`'s.

```php
use Zenstruck\MirrorClass;

/** @var MirrorClass $class */

$classes = $class->parents();
$classes = $class->interfaces();

foreach ($classes as $c) {
    /** @var MirrorClass $c */
}

\count($classes); // int
$classes->all(); // MirrorClass[]
$classes->first(); // ?MirrorClass (first in collection)
$classes->names(); // string[] (FQCNs of classes in collection)

// custom filter
$classes = $classes->filter(fn(MirrorClass $c) => bool); // Classes|MirrorClass[]

// custom map
$namespaces = $classes->map(fn(MirrorClass $c) => $c->something()); // array
```

#### `Traits`

`Zenstruck\Mirror\Traits`, a collection of `MirrorClass`'s (wrapping traits).

```php
use Zenstruck\MirrorClass;

/** @var MirrorClass $class */

$traits = $class->traits();

foreach ($traits as $t) {
    /** @var MirrorClass $c */
}

\count($traits); // int
$traits->all(); // MirrorClass[]
$traits->first(); // ?MirrorClass (first in collection)
$traits->names(); // string[] (FQCNs of traits in collection)

// recursively include traits or parents other traits (duplicates excluded)
$traits = $traits->recursive(); // Traits|MirrorClass[]

// recursively include traits or parents other traits (duplicates included)
$traits = $traits->recursive(includeDuplicates: true); // Traits|MirrorClass[]

// custom filter
$traits = $traits->filter(fn(MirrorClass $c) => bool); // Traits|MirrorClass[]

// custom map
$traits = $traits->map(fn(MirrorClass $c) => $c->something()); // array
```

#### `MirrorMethod`

Wraps a `\ReflectionMethod`.

```php
```

##### `Methods`

`Zenstruck\Mirror\Methods`, a collection of `MirrorMethod`'s.

#### `MirrorProperty`

Wraps a `\ReflectionProperty`.

##### `Properties`

`Zenstruck\Mirror\Properties`, a collection of `MirrorProperty`'s.

#### `MirrorClassConstant`

Wraps a `\ReflectionClassConstant`.

##### `ClassConstants`

`Zenstruck\Mirror\ClassConstants`, a collection of `MirrorClassConstant`'s.

### `MirrorFunction`

Wraps a `\ReflectionFunction`.

### `MirrorParameter`

Wraps a `\ReflectionParameter`.

#### `Parameters`

`Zenstruck\Mirror\Parameters`, a collection of `MirrorParameter`'s.

### `MirrorType`

Wraps a `\ReflectionType`.

### `Attributes`

`Zenstruck\Mirror\Attributes`, a collection of `\ReflectionAttribute`'s.
