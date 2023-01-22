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

// methods
$class->methods(); // Methods|MirrorMethod[]
$class->hasMethod('methodName'); // bool
$class->method('methodName'); // ?MirrorMethod
$class->methodOrFail('methodName'); // MirrorMethod or throws NoSuchMethod exception

// properties
$class->properties(); // Properties|MirrorProperty[]
$class->hasProperty('propertyName'); // bool
$class->property('propertyName'); // ?MirrorProperty
$class->propertyOrFail('propertyName'); // MirrorProperty or throws NoSuchProperty exception

// constants
$class->constants(); // ClassConstants|MirrorClassConstant[]
$class->hasMethod('CONST_NAME'); // bool
$class->method('CONST_NAME'); // ?MirrorClassConstant
$class->methodOrFail('CONST_NAME'); // MirrorClassConstant or throws NoSuchConstant exception

// attributes
$class->attributes(); // Attributes|MirrorAttributes[]

// other methods
$class->constructor(); // ?MirrorMethod
$class->isA(Some::class); // bool
$class->isA($objectInstance); // bool
$class->uses(SomeTrait::class); // bool
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

// methods
$object->methods(); // Methods|MirrorMethod[]
$object->hasMethod('methodName'); // bool
$object->method('methodName'); // ?MirrorMethod
$object->methodOrFail('methodName'); // MirrorMethod or throws NoSuchMethod exception

// properties
$object->properties(); // Properties|MirrorProperty[]
$object->hasProperty('propertyName'); // bool
$object->property('propertyName'); // ?MirrorProperty
$object->propertyOrFail('propertyName'); // MirrorProperty or throws NoSuchProperty exception

// constants
$object->constants(); // ClassConstants|MirrorClassConstant[]
$object->hasMethod('CONST_NAME'); // bool
$object->method('CONST_NAME'); // ?MirrorClassConstant
$object->methodOrFail('CONST_NAME'); // MirrorClassConstant or throws NoSuchConstant exception

// attributes
$object->attributes(); // Attributes|MirrorAttributes[]

// other methods
$object->object(); // object (wrapped object)
$object->class(); // MirrorClass
$object->constructor(); // ?MirrorMethod
$object->isA(Some::class); // bool
$object->isA($objectInstance); // bool
$object->uses(SomeTrait::class); // bool
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
```

#### `Classes`

`Zenstruck\Mirror\Classes`, a collection of `MirrorClass`'s.

```php
use Zenstruck\MirrorClass;

/** @var MirrorClass $class */

$classes = $class->parents(); // Classes|MirrorClass[]
$classes = $class->interfaces(); // Classes|MirrorClass[]

foreach ($classes as $c) {
    /** @var MirrorClass $c */
}

\count($classes); // int
$classes->all(); // MirrorClass[]
$classes->first(); // ?MirrorClass (first in collection)
$classes->names(); // string[] (FQCNs of classes in collection)
$classes->has(Some::class); // bool

// include only classes with this attribute
$classes = $classes->withAttribute(SomeAttribute::class); // Classes|MirrorClass[]

// include only classes with an instance of this attribute
$classes = $classes->withAttribute(SomeAttribute::class, instanceOf: true); // Classes|MirrorClass[]

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

$traits = $class->traits(); // Traits|MirrorClass[] (includes traits of traits)

foreach ($traits as $t) {
    /** @var MirrorClass $t */
}

\count($traits); // int
$traits->all(); // MirrorClass[]
$traits->first(); // ?MirrorClass (first in collection)
$traits->names(); // string[] (FQCNs of traits in collection)

// include only traits with this attribute
$traits = $traits->withAttribute(SomeAttribute::class); // Traits|MirrorClass[]

// include only traits with an instance of this attribute
$traits = $traits->withAttribute(SomeAttribute::class, instanceOf: true); // Traits|MirrorClass[]

// recursively include traits from parents (duplicates excluded)
$traits = $traits->recursive(); // Traits|MirrorClass[]

// recursively include traits from parents (duplicates included)
$traits = $traits->recursive(includeDuplicates: true); // Traits|MirrorClass[]

// custom filter
$traits = $traits->filter(fn(MirrorClass $c) => bool); // Traits|MirrorClass[]

// custom map
$traits = $traits->map(fn(MirrorClass $c) => $c->something()); // array
```

#### `MirrorMethod`

Wraps a `\ReflectionMethod`.

```php
use Zenstruck\MirrorMethod;

$method = MirrorMethod::wrap($reflectionMethod);
$method = MirrorMethod::for($objectOrClass, 'methodName');

$method->invoke([$arg1, $arg2, ...]); // mixed

$method->parameters(); // Parameters|MirrorParameter[]
$method->class(); // MirrorClass
\count($methods); // int # of parameters
$method->attributes(); // Attributes|MirrorAttributes[]
$method->comment(); // string|null docblock
$method->isAbstract(); // bool
$method->isConcrete(); // bool
$method->isFinal(); // bool
$method->isExtendable(); // bool
$method->isInstance(); // bool
$method->isStatic(); // bool
$method->isPublic(); // bool
$method->isPrivate(); // bool
$method->isProtected(); // bool
$method->isConstructor(); // bool
$method->isDestructor(); // bool
$method->reflector(); // \ReflectionMethod
$method->name(); // string - name of method
$method->returnType(); // MirrorType
```

##### `Methods`

`Zenstruck\Mirror\Methods`, a collection of `MirrorMethod`'s.

```php
use Zenstruck\MirrorClass;
use Zenstruck\Mirror\Methods;
use Zenstruck\MirrorMethod;

/** @var MirrorClass $class */

$methods = $class->methods(); // Methods|MirrorMethod[]

foreach ($methods as $method) {
    /** @var MirrorMethod $method */
}

\count($methods); // int
$methods->all(); // MirrorMethod[]
$methods->first(); // ?MirrorMethod (first in collection)
$methods->names(); // string[] (method names in collection)

$methods = $methods->abstract(); // Methods|MirrorMethod[] include only abstract methods
$methods = $methods->concrete(); // Methods|MirrorMethod[] include only concrete methods
$methods = $methods->final(); // Methods|MirrorMethod[] include only final methods
$methods = $methods->extendable(); // Methods|MirrorMethod[] include only non-final methods
$methods = $methods->instance(); // Methods|MirrorMethod[] include only instance methods
$methods = $methods->static(); // Methods|MirrorMethod[] include only static methods
$methods = $methods->public(); // Methods|MirrorMethod[] include only public methods
$methods = $methods->protected(); // Methods|MirrorMethod[] include only protected methods
$methods = $methods->private(); // Methods|MirrorMethod[] include only private methods

// include only methods with this attribute
$methods = $methods->withAttribute(SomeAttribute::class); // Methods|MirrorMethod[]

// include only methods with an instance of this attribute
$methods = $methods->withAttribute(SomeAttribute::class, instanceOf: true); // Methods|MirrorMethod[]

// recursively include methods from parent classes (duplicates excluded)
$methods = $methods->recursive(); // Methods|MirrorMethod[]

// recursively include methods from parent classes (duplicates included)
$methods = $methods->recursive(includeDuplicates: true); // Methods|MirrorMethod[]

// custom filter
$methods = $methods->filter(fn(MirrorMethod $m) => bool); // Methods|MirrorMethod[]

// custom map
$methods->map(fn(MirrorMethod $m) => $m->something()); // array

// advanced filter
$methods = $methods
    ->public()
    ->protected()
    ->final() // Methods|MirrorMethod[] final/non-private methods
;
```

#### `MirrorProperty`

Wraps a `\ReflectionProperty`.

```php
use Zenstruck\MirrorProperty;

$property = MirrorProperty::wrap($reflectionProperty);
$property = MirrorProperty::for($objectOrClass, 'propertyName');

$property->get(); // mixed (get static property value)
$property->get($object); // mixed (get instance property value)
$property->set($someValue); // void (set static property value)
$property->set($someValue, $object); // void (set instance property value)

$property->class(); // MirrorClass
$property->attributes(); // Attributes|MirrorAttributes[]
$property->comment(); // string|null docblock
$property->isInstance(); // bool
$property->isReadOnly(); // bool
$property->isModifiable(); // bool
$property->isInitialized(); // bool
$property->isInitialized($object); // bool
$property->isPromoted(); // bool
$property->isStatic(); // bool
$property->isPublic(); // bool
$property->isPrivate(); // bool
$property->isProtected(); // bool
$property->reflector(); // \ReflectionProperty
$property->name(); // string - name of property
$property->hasDefault(); // string
$property->default(); // mixed
$property->type(); // MirrorType
$property->hasType(); // bool
$property->supports('string'); // bool
$property->supports(SomeClass::class); // bool
$property->accepts($someValue); // bool
```

##### `Properties`

`Zenstruck\Mirror\Properties`, a collection of `MirrorProperty`'s.

```php
use Zenstruck\MirrorClass;
use Zenstruck\Mirror\Properties;
use Zenstruck\MirrorProperty;

/** @var MirrorClass $class */

$properties = $class->properties(); // Properties|MirrorProperty[]

foreach ($properties as $property) {
    /** @var MirrorProperty $property */
}

\count($properties); // int
$properties->all(); // MirrorProperty[]
$properties->first(); // ?MirrorProperty (first in collection)
$properties->names(); // string[] (property names in collection)

$properties = $properties->instance(); // Properties|MirrorProperty[] include only instance properties
$properties = $properties->static(); // Properties|MirrorProperty[] include only static properties
$properties = $properties->public(); // Properties|MirrorProperty[] include only public properties
$properties = $properties->protected(); // Properties|MirrorProperty[] include only protected properties
$properties = $properties->private(); // Properties|MirrorProperty[] include only private properties
$properties = $properties->readOnly(); // Properties|MirrorProperty[] include only read-only properties
$properties = $properties->modifiable(); // Properties|MirrorProperty[] include only non-read-only properties

// include only properties with this attribute
$properties = $properties->withAttribute(SomeAttribute::class); // Properties|MirrorProperty[]

// include only properties with an instance of this attribute
$properties = $properties->withAttribute(SomeAttribute::class, instanceOf: true); // Properties|MirrorProperty[]

// recursively include properties from parent classes (duplicates excluded)
$properties = $properties->recursive(); // Properties|MirrorProperty[]

// recursively include properties from parent classes (duplicates included)
$properties = $properties->recursive(includeDuplicates: true); // Properties|MirrorProperty[]

// custom filter
$properties = $properties->filter(fn(MirrorProperty $p) => bool); // Properties|MirrorProperty[]

// custom map
$properties->map(fn(MirrorProperty $p) => $p->something()); // array

// advanced filter
$properties = $properties
    ->public()
    ->protected()
    ->readOnly() // Properties|MirrorProperty[] read-only/non-private properties
;
```

#### `MirrorClassConstant`

Wraps a `\ReflectionClassConstant`.

```php
use Zenstruck\MirrorClassConstant;

$constant = MirrorClassConstant::wrap($reflectionClassConstant);
$constant = MirrorClassConstant::for($objectOrClass, 'CONSTANT_NAME');

$constant->value(); // mixed
$constant->class(); // MirrorClass
$constant->attributes(); // Attributes|MirrorAttributes[]
$constant->comment(); // string|null docblock
$constant->isFinal(); // bool
$constant->isExtendable(); // bool
$constant->isPublic(); // bool
$constant->isPrivate(); // bool
$constant->isProtected(); // bool
$constant->reflector(); // \ReflectionClassConstant
$constant->name(); // string - name of constant
```

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

### `MirrorAttribute`

#### `Attributes`

`Zenstruck\Mirror\Attributes`, a collection of `MirrorAttribute`'s.

##### Create For

## Dynamic Invokables
