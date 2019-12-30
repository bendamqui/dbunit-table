<?php

namespace Bendamqui\DbUnit;

use Closure;

/**
 * Class TableFacade.
 */
class TableFacade
{
    /**
     * @var SmartArray
     */
    private $smart_array;

    /**
     * @var string
     */
    private $primary_key = 'id';

    /**
     * @var array
     */
    private $hidden = [];

    /**
     * @var array
     */
    private $default_override = [];

    /**
     * TableFacade constructor.
     *
     * @param array $table
     */
    public function __construct(array $table)
    {
        $this->smart_array = new SmartArray($table);
    }

    /**
     * Set the primary key of the table. Is set to 'id' by default.
     *
     * @param string $primary_key
     */
    public function setPrimaryKey($primary_key)
    {
        $this->primary_key = $primary_key;
    }

    /**
     * Set a list of columns that should not be returned when fetching row(s).
     *
     * @param array $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @param array $override
     */
    public function setDefaultOverride($override)
    {
        $this->default_override = $override;
    }

    /**
     * @param array $override
     *
     * @return array
     */
    public function getAll($override = [])
    {
        return $this->withDefaultTransformations($this->smart_array)
            ->map($this->applyOverride($override))
            ->get();
    }

    /**
     * Get all rows in raw format (skip post processing).
     *
     * @return array
     */
    public function getAllRaw()
    {
        return $this->smart_array->get();
    }

    /**
     * Get a row by its index
     *
     * @param int $search
     * @return mixed
     */
    public function getRaw($search = 0)
    {
        return $this->smart_array
            ->filter($this->filterByIndex($search))
            ->first();
    }

    /**
     * @param int $search
     * @return Closure
     */
    private function filterByIndex($search)
    {
        return function ($row, $index) use ($search) {
            return $index === $search;
        };
    }

    /**
     * @param array $override
     * @return Closure
     */
    private function applyOverride($override = [])
    {
        return function ($row) use ($override) {
            foreach ($override as $key => $value) {
                $row = $this->dotSetter($row, $key, $value);
            }
            return $row;
        };
    }

    /**
     * @param array $hidden
     * @return Closure
     */
    private function applyHidden($hidden)
    {
        return function ($row) use ($hidden) {
            foreach ($hidden as $key) {
                unset($row[$key]);
            }
            return $row;
        };
    }

    /**
     * @param array $filters
     * @return Closure
     */
    private function applyFilters($filters)
    {
        return function ($row) use ($filters) {
            foreach ($filters as $key => $value) {
                //@todo dotGetter
                if ($row[$key] !== $value) {
                    return false;
                }
            }
            return true;
        };
    }

    /**
     * @param SmartArray $smart_array
     * @return SmartArray
     */
    private function withDefaultTransformations(SmartArray $smart_array)
    {
        return $smart_array
            ->map($this->applyOverride($this->default_override))
            ->map($this->applyPostProcess())
            ->map($this->applyHidden($this->hidden));
    }

    /**
     * Get one row.
     *
     * @param array $override
     * @param int $row
     *
     * @return mixed
     */
    public function get($override = [], $row = 0)
    {
        return $this->withDefaultTransformations($this->smart_array->filter($this->filterByIndex($row)))
            ->map($this->applyOverride($override))
            ->first();
    }

    /**
     * Get one by primary key.
     *
     * @param $id
     * @param array $override
     *
     * @return mixed
     */
    public function getByPrimaryKey($id, $override = [])
    {
        return $this->withDefaultTransformations(
            $this->smart_array->filter($this->applyFilters([$this->primary_key => $id]))
        )
            ->map($this->applyOverride($override))
            ->first();

    }

    /**
     * Get row's column value.
     *
     * @param $column
     * @param int $row
     *
     * @return mixed
     */
    public function getValue($column, $row = 0)
    {
        return $this->smart_array
                ->filter($this->filterByIndex($row))
                ->first()[$column] ?? null;
    }

    /**
     * Get all the values for given columns.
     *
     * @param array|string $columns
     * @param array $filters
     *
     * @return array
     */
    public function getValues($columns, $filters = [])
    {
        return $this->smart_array
            ->filter($this->applyFilters($filters))
            ->columnValues($columns);
    }

    /**
     * Get the number of row in the table.
     *
     * @return int
     */
    public function getRowCount()
    {
        return $this->smart_array->count();
    }

    /**
     * Get many rows using filters in the form of key value. Perform AND only.
     *
     * @param array $filters
     * @param array $override
     *
     * @return array
     */
    public function getWhere($filters, $override = [])
    {
        return $this->withDefaultTransformations($this->smart_array->filter($this->applyFilters($filters)))
            ->map($this->applyOverride($override))
            ->get();
    }

    /**
     * Placeholder, can be override in order to process the raw data in the db to
     * the actual format of the payload. e.g. The db store a name while the web form
     * send first_name and last_name separately.
     *
     *
     * @return mixed
     */
    protected function applyPostProcess()
    {
        return function ($row) {
            return $row;
        };
    }

    /**
     * @param array $payload
     * @param string $keys
     * @param mixed $value
     *
     * @return array
     */
    private function dotSetter($payload, $keys, $value)
    {
        $copy = &$payload;
        $keys = explode('.', $keys);

        foreach ($keys as $key) {
            if (is_array($copy)) {
                $copy = &$copy[$key] ?? null;
            } else {
                $copy = [];
                $copy = &$copy[$key];
            }
        }

        $copy = $value;

        return $payload;
    }
}
