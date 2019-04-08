<?php

use PHPUnit\Framework\TestCase;
use Bendamqui\DbUnit\TableFacade;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;

class TableFacadeTest extends TestCase
{
    const ROW_COUNT = 5;

    /**
     * @var TableFacade
     */
    private $table;

    /**
     * Use a random number for the row to fetch when fetching one row or
     * another is irrelevant.
     *
     * @var int
     */
    private $row_number;

    public function setUp()
    {
        parent::setUp();
        $this->table = new TableFacade($this->getDataSet()->getTable('users'));
        $this->row_number = rand(0, self::ROW_COUNT - 1);
    }

    public function getDataSet()
    {
        $data = file_get_contents(__DIR__.'/../Tests/fixture.json');
        $data = json_decode($data, true);

        return new ArrayDataSet($data);
    }

    public function testGetAllRaw()
    {
        $result = $this->table->getAllRaw();
        $this->assertCount(self::ROW_COUNT, $result);
    }

    public function testGetRaw()
    {
        $result = $this->table->getRaw();
        $this->assertEquals(1, $result['id']);
    }

    public function testGetRawByRow()
    {
        $result = $this->table->getRaw($this->row_number);
        $this->assertEquals($this->row_number + 1, $result['id']);
    }

    public function testGetRowCount()
    {
        $result = $this->table->getRowCount();
        $this->assertEquals(self::ROW_COUNT, $result);
    }

    public function testGetValue()
    {
        $result = $this->table->getValue('first_name', 3);
        $this->assertEquals('Kianna', $result);
    }

    public function testGetFirst()
    {
        $result = $this->table->get();
        $this->assertEquals(1, $result['id']);
    }

    public function testGetByRow()
    {
        $result = $this->table->get([], $this->row_number);
        $this->assertEquals($this->row_number + 1, $result['id']);
    }

    public function testGetOverride()
    {
        $result = $this->table->get(['first_name' => 'Bob'], $this->row_number);
        $this->assertEquals('Bob', $result['first_name']);
    }

    /**
     * @param array $filters
     * @param int   $expected_row_count
     * @dataProvider filterProvider
     */
    public function testGetWhere($filters, $expected_row_count)
    {
        $result = $this->table->getWhere($filters);
        $this->assertIsArray($result);
        $this->assertCount($expected_row_count, $result);
        foreach ($filters as $key => $value) {
            foreach ($result as $row) {
                $this->assertEquals($value, $row[$key]);
            }
        }
    }

    public function testDotSetter()
    {
        $result = $this->table->get(['a.b' => 1]);
        $this->assertEquals(1, $result['a']['b']);
    }

    /**
     * - Filters
     * - Expected row count.
     *
     * @return array
     */
    public function filterProvider()
    {
        return [
            [['id' => 1], 1],
            [['id' => 2, 'created_at' => '2019-04-06 03:56:44'], 1],
            [['created_at' => '2019-04-06 03:56:44'], 5],
            [['first_name' => 'Bob'], 0],
            [['first_name' => 'Bob', 'created_at' => '2019-04-06 03:56:44'], 0],
        ];
    }
}
