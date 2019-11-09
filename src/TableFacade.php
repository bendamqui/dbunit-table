<?php

namespace Bendamqui\DbUnit;

use PHPUnit\DbUnit\DataSet\ITable;

/**
 * Class TableFacade.
 */
class TableFacade
{
    /**
     * @var ITable
     */
    private $table;

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
     * @param ITable $table
     */
    public function __construct(ITable $table)
    {
        $this->table = $table;
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
     * @param array $default
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
        $rows = $this->getAllRaw();
        foreach ($rows as &$row) {
            $row = $this->process($row, $override);
        }

        return $rows;
    }

    /**
     * Get all rows in raw format (skip post processing).
     *
     * @return array
     */
    public function getAllRaw()
    {
        $output = [];
        $row_count = $this->getRowCount();

        for ($i = 0; $i < $row_count; ++$i) {
            $output[] = $this->getRaw($i);
        }

        return $output;
    }

    /**
     * Get a row by its row number (skip post processing).
     *
     * @param int $row
     *
     * @return array
     */
    public function getRaw($row = 0)
    {
        return $this->table->getRow($row);
    }

    /**
     * Get one row.
     *
     * @param array $override
     * @param int   $row
     *
     * @return array
     */
    public function get($override = [], $row = 0)
    {
        $payload = $this->getRaw($row);

        return $this->process($payload, $override);
    }

    /**
     * Get one row by primary key.
     *
     * @param $id
     * @param $override
     *
     * @return array
     */
    public function getByPrimaryKey($id, $override = [])
    {
        $payload = $this->applyFilters([$this->primary_key => $id]);

        return $this->process($payload[0], $override);
    }

    /**
     * Get the value of a given row/column in the Table.
     *
     * @param $column
     * @param int $row
     *
     * @return mixed
     */
    public function getValue($column, $row = 0)
    {
        return $this->table->getValue($row, $column);
    }

    /**
     * Get all the values for given columns.
     *
     * @param array|string $columns
     * @param array        $filters
     *
     * @return array
     */
    public function getValues($columns, $filters = [])
    {
        $output = [];
        $columns = is_array($columns) ? $columns : [$columns];
        $payload = $this->getWhere($filters);
        foreach ($columns as $column) {
            $output[$column] = array_column($payload, $column);
        }

        return $output;
    }

    /**
     * Get the number of row in the table.
     *
     * @return int
     */
    public function getRowCount()
    {
        return $this->table->getRowCount();
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    public function applyFilters($filters)
    {
        $output = [];
        $fixtures = $this->getAllRaw();
        foreach ($fixtures as $fixture) {
            $found = true;
            foreach ($filters as $key => $value) {
                if ($fixture[$key] != $value) {
                    $found = false;
                }
            }
            if ($found) {
                $output[] = $fixture;
            }
        }

        return $output;
    }

    /**
     * Get many rows using filters in the form of key value. Perform AND only.
     *
     * @param array $filters
     *
     * @return array
     */
    public function getWhere($filters, $override = [])
    {
        $payload = $this->applyFilters($filters);
        foreach ($payload as &$row) {
            $row = $this->process($row, $override);
        }

        return $payload;
    }

    /**
     * Placeholder, can be override in order to process the raw data in the db to
     * the actual format of the payload. e.g. The db store a name while the web form
     * send first_name and last_name separately.
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function postProcess($payload)
    {
        return $payload;
    }

    /**
     * @param array $payload
     * @param array $override
     *
     * @return array
     */
    private function process($payload, $override)
    {
        $payload = $this->applyDefaultOverride($payload);
        $payload = $this->postProcess($payload);
        $payload = $this->hide($payload);
        foreach ($override as $key => $value) {
            $payload = $this->dotSetter($payload, $key, $value);
        }

        return $payload;
    }

    private function applyDefaultOverride($payload)
    {
        foreach ($this->default_override as $key => $value) {
            $payload = $this->dotSetter($payload, $key, $value);
        }

        return $payload;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    private function hide($rows)
    {
        foreach ($this->hidden as $hidden) {
            unset($rows[$hidden]);
        }

        return $rows;
    }

    /**
     * @param array  $payload
     * @param string $keys
     * @param mixed  $value
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
