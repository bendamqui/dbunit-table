<?php

namespace Bendamqui\DbUnit;

use PHPUnit\DbUnit\DataSet\ITable;

/**
 * Use the PHPUnit\DbUnit\DataSet\ITable to get mock payload based on the data
 * fixture set in DbUnit without querying the database.
 *
 * Class BaseFixtureAdaptor
 */
class TableFacade
{
    /**
     * @var ITable
     */
    private $table;

    /**
     * BaseFixtureAdaptor constructor.
     *
     * @param ITable $table
     */
    public function __construct(ITable $table)
    {
        $this->table = $table;
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
     * @param array $payload
     * @param array $override
     *
     * @return array
     */
    private function process($payload, $override)
    {
        $payload = $this->postProcess($payload);
        foreach ($override as $key => $value) {
            $payload = $this->dotSetter($payload, $key, $value);
        }

        return $payload;
    }

    /**
     * Takes a raw fixture from the table and override the properties
     * passed in $override.
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
     * Perform a count on the array that populated the DB in DbUnit set up
     * so we don't need to query the DB to get an initial row count.
     *
     * @return int
     */
    public function getRowCount()
    {
        return $this->table->getRowCount();
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
            } elseif (is_object($copy)) {
                $copy = &$copy->$key ?? null;
            } else {
                $copy = [];
                $copy = &$copy[$key];
            }
        }

        $copy = $value;

        return $payload;
    }

    /**
     * Get many rows using filters in the form of key value. Perform AND only.
     *
     * @param array $filters
     *
     * @return array
     */
    public function getWhere($filters = [])
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
}
