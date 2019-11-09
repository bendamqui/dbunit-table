<?php

namespace Bendamqui\DbUnit;

use Closure;

class SmartArray implements \Countable
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
     * @return array
     */
    public function get(): array
    {
        return $this->array;
    }

    /**
     * @return mixed
     */
    public function first()
    {
        return array_values($this->array)[0] ?? null;
    }

    /**
     * @param string|array $columns
     * @return array
     */
    public function columnValues($columns)
    {
        $output = [];
        $columns = is_array($columns) ? $columns : [$columns];
        foreach ($columns as $column) {
            $output[$column] = array_column($this->get(), $column);
        }
        return $output;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->array);
    }
}
