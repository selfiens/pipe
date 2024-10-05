# Minimalistic Pipe for Method Call Chaining

In PHP, scalars and arrays aren't objects, and that makes it cumbersome to create a function pipeline.

This package provides a simple `pipe` function that focuses on:

- Simplicity: Simple idea, simple signature.
- Freedom to use PHP callables in a native PHP way.

## The Signature

```php
pipe(mixed $data, ...$callables): mixed
```

## Code Example

```php
// In class context
$count = pipe(
    $this->fetch(...),
    $this->filterBadEntries(...),
    $this->removeDupes(...),      
    $this->count(...),
);
```

Note that the above example uses PHP 8.1's [First Class Callable Syntax](https://www.php.net/manual/en/functions.first_class_callable_syntax.php).

You can use all the callable forms supported by your PHP version.

<details>
  <summary>The same logic without pipe</summary>

```php
// In class context
$count = $this->count(
    $this->removeDupes(
        $this->filterBadEntries(
            $this->fetch()
        )
    )
);
```

</details>

The first parameter is the data.
Calling `pipe` without transformer functions returns the input data unchanged.

```php
pipe('my data'); // = 'my data'
```

After the data, place your transformer functions.

```php
pipe(
    'x', 
    fn($s) => $s . 'y',
    fn($s) => $s . 'z',
    fn($s) => $s . '0',
    ...
); // = 'xyz0...'
```

You can use all the callable forms supported by your PHP version.

```php
pipe(
    'initial data',
    'trim',                             // global function
    function($s) { return trim($s) },   // anonymous function since PHP 5.3
    fn($s) => trim($s),                 // arrow function since PHP 7.4
    trim(...),                          // first-class callable since PHP 8.1
    [$myObject, 'trim'],                // instance method
    [MyClass::class, 'trim'],           // static method
    MyClass::trim(...),                 // static method, first-class callable since PHP 8.1
    $myObject->trim(...),               // instance method, first-class callable since PHP 8.1
    new Trim(),                         // when __invoke() is implemented 
);
```

The following code creates a function using first-class callable syntax in PHP 8.1.

```php
// create a function with pipe
$starizePhoneNumber = fn($phoneNumber) => pipe(
    $phoneNumber,
    PhoneNumberFormat::numbersOnly(...),
    PhoneNumberFormat::starize(...),
    PhoneNumberFormat::dashify(...),
);

$starizePhoneNumber("001-0000-0000");
```

### Using Companion Methods

The `Pipe` class contains several companion methods for handling arrays with ease.

```php
use Selfiens\Pipe as P;

P::pipe(
    [1,2,3,4,5],
    P::map(fn($i) => $i*2),
    P::filter(fn($i) => $i<10),
    P::take(3),
); // [2,4,5]
```

### Using Companion Functions

If you prefer, you can use companion functions.

```php
use Selfiens\Pipe\Pipe;

use function Selfiens\Pipe\pipe;
use function Selfiens\Pipe\map;
use function Selfiens\Pipe\filter;
use function Selfiens\Pipe\take;

Pipe::install(); // dummy method to load companion functions into memory

pipe(
    [1,2,3,4,5],
    map(fn($i) => $i*2),
    filter(fn($i) => $i<10),
    take(3),
); // [2,4,6]
```

## Installation

Use [Composer](https://getcomposer.org) to install this package.
This package requires PHP 8.0 or later.

```shell
composer require selfiens/pipe
```

After installing the package, load the `autoload.php` file.

```php
use Selfiens\Pipe\Pipe;

Pipe::pipe(...);
```

## Global `pipe` Function

You can installs the `pipe` function in the global namespace

```php
\Selfiens\Pipe\Pipe::installGlobal();
```

To include it automatically via `autoload.php`, you can add `"vendor/selfiens/pipe/src/pipe_global.php"` in the `autoload/files` section of your `composer.json`:

```json
{
  "autoload": {
    "files": [
      "vendor/selfiens/pipe/src/pipe_global.php"
    ]
  }
}
```

NOTE: You may need to run the following if you have created autoload cache.

```shell
composer dump
```

## More Examples

See examples folder for more examples.

## Tests

```shell
composer test
```