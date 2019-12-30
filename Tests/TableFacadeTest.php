<?php

use PHPUnit\Framework\TestCase;
use Bendamqui\DbUnit\FixtureUtil;

class TableFacadeTest extends TestCase
{
    const PRIMARY_KEY = 'user_id';

    /**
     * @var FixtureUtil
     */
    private $table;

    /**
     * Use a random number for the row to fetch for test where fetching one row or
     * another is irrelevant.
     *
     * @var int
     */
    private $row_number;

    /**
     * @var int
     */
    private $row_count;

    protected function setUp()
    {
        parent::setUp();
        $fixture = $this->getFixture();
        $this->row_count = count($fixture);
        $this->table = new FixtureUtil($fixture);
        $this->table->setPrimaryKey(self::PRIMARY_KEY);
        $this->row_number = rand(0, $this->row_count - 1);
    }

    private function getFixture()
    {
        return  require __DIR__.'/../Tests/fixture.php';
    }

    public function testGetAllRaw()
    {
        $result = $this->table->getAllRaw();
        $this->assertCount($this->row_count, $result);
    }

    public function testGetRaw()
    {
        $result = $this->table->getRaw();
        $this->assertEquals(1, $result[self::PRIMARY_KEY]);
    }

    public function testGetRawByRow()
    {
        $result = $this->table->getRaw($this->row_number);
        $this->assertEquals($this->row_number + 1, $result[self::PRIMARY_KEY]);
    }

    public function testGetRowCount()
    {
        $result = $this->table->getRowCount();
        $this->assertEquals($this->row_count, $result);
    }

    public function testGetValue()
    {
        $result = $this->table->getValue('first_name', 3);
        $this->assertEquals('Kianna', $result);
    }

    public function testGetFirst()
    {
        $result = $this->table->get();
        $this->assertEquals(1, $result[self::PRIMARY_KEY]);
    }

    public function testGetByRow()
    {
        $result = $this->table->get([], $this->row_number);
        $this->assertEquals($this->row_number + 1, $result[self::PRIMARY_KEY]);
    }

    public function testGetOverride()
    {
        $result = $this->table->get(['first_name' => 'Bob'], $this->row_number);
        $this->assertEquals('Bob', $result['first_name']);
    }

    public function testHide()
    {
        $this->table->setHidden(['first_name']);
        $result = $this->table->get();
        $this->assertTrue(!isset($result['first_name']));
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

    public function testGetWhereWithDefaultOverride()
    {
        $this->table->setDefaultOverride(['password' => 12345]);
        $results = $this->table->getWhere(['user_id' => 2]);
        foreach ($results as $result) {
            $this->assertEquals(12345, $result['password']);
        }

    }

    public function testGetWhereWithOverride()
    {
        $results = $this->table->getWhere(['user_id' => 2], ['password' => 12345]);
        foreach ($results as $result) {
            $this->assertEquals(12345, $result['password']);
        }
    }

    public function testDotSetter()
    {
        $result = $this->table->get(['a.b' => 1]);
        $this->assertEquals(1, $result['a']['b']);
    }

    public function testGetByPrimaryKey()
    {
        $result = $this->table->getByPrimaryKey(4);
        $this->assertEquals('Kianna', $result['first_name']);
    }

    public function testGetByPrimaryKeyWithOverride()
    {
        $result = $this->table->getByPrimaryKey(4, ['last_name' => 'Michaud', 'a.b' => 1]);
        $this->assertEquals('Michaud', $result['last_name']);
        $this->assertEquals(1, $result['a']['b']);
    }

    public function testDefaultOverride()
    {
        $this->table->setDefaultOverride([
            'default_override' => 'default_override',
            'email' => 'default_override',
        ]);
        $result = $this->table->get();
        $this->assertEquals('default_override', $result['default_override']);
    }

    public function testDefaultOverridePriority()
    {
        $this->table->setDefaultOverride([
            'default_override' => 'default_override',
            'email' => 'default_override',
        ]);
        $result = $this->table->get(['email' => 'override_email']);
        $this->assertEquals('override_email', $result['email']);
    }

    public function testGetValues()
    {
        $result = $this->table->getValues('user_id');
        $this->assertEquals([1, 2, 3, 4, 5], $result['user_id']);
    }

    public function testGetValuesWithMultipleColumns()
    {
        $result = $this->table->getValues(['user_id', 'role'], ['role' => 'admin']);
        $this->assertEquals([1, 2, 3], $result['user_id']);
        $this->assertEquals(array_fill(0, 3, 'admin'), $result['role']);
    }


    public function testGetAll()
    {
        $result = $this->table->getAll();
        $this->assertCount($this->row_count, $result);
    }

    public function testGetAllWithOverride()
    {
        $result = $this->table->getAll(['email' => 'override_email']);
        $this->assertCount($this->row_count, $result);
        $this->assertEquals(array_unique(array_column($result, 'email'))[0], 'override_email');
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
            [[self::PRIMARY_KEY => 1], 1],
            [[self::PRIMARY_KEY => 2, 'created_at' => '2019-04-06 03:56:44'], 1],
            [['created_at' => '2019-04-06 03:56:44'], 5],
            [['first_name' => 'Bob'], 0],
            [['first_name' => 'Bob', 'created_at' => '2019-04-06 03:56:44'], 0],
        ];
    }
}
