<?php

namespace Selfiens\Pipe;

use Closure;
use Exception;

use function array_filter;
use function array_map;
use function array_merge;
use function array_push;
use function array_reduce;
use function array_slice;
use function array_values;
use function count;
use function explode;
use function implode;
use function is_array;
use function is_callable;
use function sort;
use function usort;

// @formatter:off
// function pipe(mixed $data, mixed ...$fns): mixed  { return Pipe::pipe($data, ...$fns); }
// function tap(callable $fn): Closure { return Pipe::tap($fn); }
// function map(callable $fn): Closure { return Pipe::map($fn);}
// function filter(callable $fn): Closure { return Pipe::filter($fn);}
// function reduce(callable $fn, mixed $initial = null): Closure { return Pipe::reduce($fn, $initial);}
// function flat(): Closure { return Pipe::flat();}
// function merge(array ...$arrays): Closure { return Pipe::merge(...$arrays);}
// function take(int $n, bool $preserveKeys = false): Closure { return Pipe::take($n, $preserveKeys);}
// function skip(int $n, bool $preserveKeys = false): Closure { return Pipe::skip($n, $preserveKeys);}
// function explode(string $separator, int $limit = PHP_INT_MAX): Closure { return Pipe::explode($separator, $limit);}
// function implode(string $separator = ''): Closure { return Pipe::implode($separator);}
// function join(string $separator = ''): Closure { return Pipe::implode($separator);}
// function sort(?callable $fn): Closure { return Pipe::sort();}
// function push(mixed ...$values): Closure { return Pipe::push(...$values);}
// function isAll(callable $fn): Closure { return Pipe::isAll($fn);}
// function isAny(callable $fn): Closure { return Pipe::isAny($fn);}
// @formatter:on

/**
 *
 */
class Pipe
{
    /** @var array<string, callable> */
    protected static array $userDefined = [];

    public static function install(): void
    {
        // Expose companion functions
    }

    /**
     * Add companion functions to global(root) namespace
     */
    public static function installGlobal(): void
    {
        require_once __DIR__.'/pipe_global.php';
    }

    /**
     * Invokable
     */
    public function __invoke(mixed $data, callable ...$transformers): mixed
    {
        return static::pipe($data, ...$transformers);
    }

    /**
     * Runs data through transformer functions and return the final result
     */
    public static function pipe(mixed $data, mixed ...$fns): mixed
    {
        return array_reduce(
            $fns,
            fn($carry, $fn) => (is_callable($fn))
                ? $fn($carry)
                : $fn,
            $data
        );
    }

    public static function define(string $name, callable $fn): void
    {
        static::$userDefined[$name] = $fn;
    }

    public static function undefine(string $name): void
    {
        // $name must not be empty
        if (!strlen($name)) {
            throw new Exception('The name must not be empty');
        }
        unset(static::$userDefined[$name]);
    }

    /**
     * @throws Exception
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (isset(static::$userDefined[$name]) && is_callable(static::$userDefined[$name])) {
            return static::$userDefined[$name](...$arguments);
        }
        throw new Exception("Call to undefined method Pipe::$name");
    }

    /**
     * Top: peek pipe content
     */
    public static function tap(callable $fn): Closure
    {
        return static function (mixed $data) use ($fn): mixed {
            $fn($data);
            return $data;
        };
    }

    public static function map(callable $fn): Closure
    {
        return static function (mixed $data) use ($fn): array {
            return array_map($fn, self::arrayWrap($data));
        };
    }

    public static function filter(callable $fn): Closure
    {
        return static function (mixed $data) use ($fn): array {
            return array_filter(self::arrayWrap($data), $fn);
        };
    }

    public static function filterNot(?callable $fn = null): Closure
    {
        return static fn(mixed $data): array => \array_filter(self::arrayWrap($data), fn($d) => !$fn($d));
    }

    public static function column(string|int|null $column, int|null|string $index_key = null): Closure
    {
        return static fn(mixed $data): array => \array_column(self::arrayWrap($data), $column, $index_key);
    }

    /**
     * @param  callable  $fn  fn(mixed $carry, mixed $item)
     */
    public static function reduce(callable $fn, mixed $initial = null): Closure
    {
        return static function (mixed $data) use ($fn, $initial): mixed {
            return array_reduce(self::arrayWrap($data), $fn, $initial);
        };
    }

    /**
     * Flatten array into 1D
     */
    public static function flat(): Closure
    {
        return static function (mixed $value): array {
            return self::arrayFlat(self::arrayWrap($value));
        };
    }

    /**
     * Equivalent of \array_values
     */
    public static function values(): Closure
    {
        return static function (mixed $value): array {
            return array_values(self::arrayWrap($value));
        };
    }

    protected static function arrayFlat(mixed $data): array
    {
        return array_reduce(
            $data,
            fn($carry, $item) => is_array($item)
                ? array_merge($carry, self::arrayFlat($item))
                : array_merge($carry, [$item]),
            []
        );
    }

    public static function merge(array ...$arrays): Closure
    {
        return static function (mixed $data) use ($arrays): array {
            return array_merge($data, ...$arrays);
        };
    }

    /**
     * Returns first N element, if N is negative, returns from last
     */
    public static function take(int $n, bool $preserveKeys = false): Closure
    {
        return static function (mixed $data) use ($n, $preserveKeys): array {
            if ($n < 0) {
                return array_slice(self::arrayWrap($data), $n, -$n, $preserveKeys);
            }
            return array_slice(self::arrayWrap($data), 0, $n, $preserveKeys);
        };
    }

    /**
     * Discards first N element, if N is negative, discards last N elements
     */
    public static function skip(int $n, bool $preserveKeys = false): Closure
    {
        return static function (mixed $data) use ($n, $preserveKeys): array {
            if ($n < 0) {
                return array_slice(self::arrayWrap($data), 0, $n, $preserveKeys);
            }
            return array_slice(self::arrayWrap($data), $n, null, $preserveKeys);
        };
    }

    public static function explode(string $separator, int $limit = PHP_INT_MAX): Closure
    {
        return static function (string $data) use ($separator, $limit): array {
            return explode($separator, $data, $limit);
        };
    }

    public static function implode(string $separator = ''): Closure
    {
        return static function (array $data) use ($separator): string {
            return implode($separator, $data);
        };
    }

    /**
     * Alias of Pipe::implode
     */
    public static function join(string $separator = ''): Closure
    {
        return self::implode($separator);
    }

    public static function sort(?callable $fn = null): Closure
    {
        return static function (array $data) use ($fn): array {
            if (is_callable($fn)) {
                usort($data, $fn);
            } else {
                sort($data);
            }
            return $data;
        };
    }

    public static function push(mixed ...$values): Closure
    {
        return static function (mixed $data) use ($values): array {
            array_push($data, ...$values);
            return $data;
        };
    }

    /**
     * Returns true if all elements satisfy $fn
     */
    public static function all(callable $fn): Closure
    {
        return static function (array $data) use ($fn): bool {
            return !empty($data) && count($data) === count(array_filter($data, $fn));
        };
    }

    /**
     * Returns true if any elements satisfy $fn
     */
    public static function any(callable $fn): Closure
    {
        return static function (array $data) use ($fn): bool {
            return !empty($data) && count(array_filter($data, $fn)) > 0;
        };
    }

    /**
     * Alias of Pipe::any
     */
    public static function some(callable $fn): Closure
    {
        return self::any($fn);
    }

    /**
     * Returns true if no elements satisfy $fn
     */
    public static function none(callable $fn): Closure
    {
        return static function ($data) use ($fn) {
            return !self::any($fn)($data);
        };
    }

    protected static function arrayWrap(mixed $data): array
    {
        return is_array($data) ? $data : [$data];
    }
}