<?php

use PHPUnit\Framework\TestCase;
use Bendamqui\DbUnit\SmartArray;

class SmartArrayTest extends TestCase
{
    /**
     * @var SmartArray
     */
    private $simple;

    protected function setUp()
    {
        parent::setUp();
        $this->simple = new SmartArray([0, 5, 10]);
    }

    public function testMap()
    {
        $result = $this->simple->map($this->add(1));
        $this->assertEquals([1, 6, 11], $result->get());
    }

    public function testMultipleMaps()
    {
        $result = $this->simple->map($this->add(1))->map($this->add(2));
        $this->assertEquals([3, 8, 13], $result->get());
    }

    public function testFilters()
    {
        $result = $this->simple->filter($this->isGreaterThan(0))->filter($this->isGreaterThan(5));
        $this->assertEquals([10], array_values($result->get()));
    }

    public function testMapWithFilters()
    {
        $result = $this->simple->map($this->add(5))->filter($this->isGreaterThan(5));
        $this->assertEquals([10, 15], array_values($result->get()));
    }

    public function testWithFilterCount()
    {
        $result = $this->simple->filter($this->isGreaterThan(0));
        $this->assertEquals(2, $result->count());
    }

    /**
     * @param int $number
     * @return Closure
     */
    private function add($number)
    {
        return function ($row) use ($number) {
            return $row + $number;
        };
    }

    /**
     * @param int $number
     * @return Closure
     */
    private function isGreaterThan($number)
    {
        return function ($row) use ($number) {
            return $row > $number;
        };
    }
}
