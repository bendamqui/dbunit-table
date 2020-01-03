<?php

use PHPUnit\Framework\TestCase;
use Bendamqui\DbUnit\SmartArray;

class SmartArrayTest extends TestCase
{
    /**
     * @var SmartArray
     */
    private $simple;

    /**
     * @var SmartArray
     */
    private $fixture;

    protected function setUp()
    {
        parent::setUp();
        $this->simple = new SmartArray([0, 5, 10]);
        $this->fixture = new SmartArray($this->getFixtureArray());
    }

    /**
     * @return array
     */
    private function getFixtureArray(): array
    {
        return require __DIR__.'/../Tests/fixture.php';
    }

    public function testInterfaces()
    {
        $this->assertInstanceOf(Countable::class, $this->fixture);
        $this->assertInstanceOf(ArrayAccess::class, $this->fixture);
        $this->assertInstanceOf(IteratorAggregate::class, $this->fixture);
    }

    public function testOffsetExists()
    {
        $this->assertEquals(false, $this->simple->offsetExists('a'));
        $this->assertEquals(true, $this->simple->offsetExists(0));
    }

    public function testOffsetGet()
    {
        $this->assertEquals(5, $this->simple->offsetGet(1));
        $this->assertNull($this->simple->offsetGet('a'));
    }

    public function testOffsetSet()
    {
        $this->simple->offsetSet(0, 'offsetSet');
        $this->assertEquals($this->simple->offsetGet(0), 'offsetSet');
    }

    public function testOffsetUnset()
    {
        $this->simple->offsetUnset(0);
        $this->assertNull($this->simple->offsetGet(0));
    }

    public function testGetIterator()
    {
        $this->assertInstanceOf(ArrayIterator::class, $this->simple->getIterator());
    }

    public function testMap()
    {
        $result = $this->simple->map($this->add(1));
        $this->assertEquals([1, 6, 11], $result->toArray());
    }

    public function testMultipleMaps()
    {
        $result = $this->simple->map($this->add(1))->map($this->add(2));
        $this->assertEquals([3, 8, 13], $result->toArray());
    }

    public function testFilters()
    {
        $result = $this->simple->filter($this->isGreaterThan(0))->filter($this->isGreaterThan(5));
        $this->assertEquals([10], array_values($result->toArray()));
    }

    public function testMapWithFilters()
    {
        $result = $this->simple->map($this->add(5))->filter($this->isGreaterThan(5));
        $this->assertEquals([10, 15], array_values($result->toArray()));
    }

    public function testWithFilterCount()
    {
        $result = $this->simple->filter($this->isGreaterThan(0));
        $this->assertEquals(2, $result->count());
    }

    public function testColumn()
    {
        $this->assertEquals(
            array_column($this->getFixtureArray(), 'email'),
            $this->fixture->column('email')->toArray()
        );
    }

    public function testMethodsCanBeChained()
    {
        $result = $this->fixture->map(function ($row) {
            return $row;
        })->filter(function () {
            return true;
        })->column('user_id')
        ->filter(function () {
            return true;
        });

        $this->assertInstanceOf(SmartArray::class, $result);
    }

    public function testIsImmutable()
    {
        $count = $this->simple->filter(function () {
            return false;
        })->count();
        $this->assertEquals(0, $count);
        $this->assertNotEquals($this->simple->count(), $count);
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
