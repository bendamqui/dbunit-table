<?php

namespace Bendamqui\DbUnit;

use Closure;
use Countable;
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

class SmartArray implements Countable, ArrayAccess, IteratorAggregate
{
    /**
     * @var array
     */
    private $array;

    /**
     * SmartArray constructor.
     * @param array $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * @param Closure $closure
     * @return SmartArray
     */
    public function map(Closure $closure): SmartArray
    {
        return new self(array_map($closure, $this->array));
    }

    /**
     * @param Closure $closure
     * @return SmartArray
     */
    public function filter(Closure $closure): SmartArray
    {
        return new self(array_filter($this->array, $closure,ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @param string $column
     * @param string|null $index_key
     * @return SmartArray
     */
    public function column(string $column, ?string $index_key = null): SmartArray
    {
        return new self(array_column($this->array, $column, $index_key));
    }

    /**
     * @return mixed
     */
    public function first()
    {
        return array_values($this->array)[0] ?? null;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->array);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->array[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->array[$offset] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        is_null($offset) ? $this->array[] = $offset : $this->array[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->array);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->array;
    }
}
