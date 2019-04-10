[![Build Status](https://travis-ci.org/bendamqui/dbunit-table.svg?branch=master)](https://travis-ci.org/bendamqui/dbunit-table)
[![Coverage Status](https://coveralls.io/repos/github/bendamqui/dbunit-table/badge.svg?branch=master)](https://coveralls.io/github/bendamqui/dbunit-table?branch=master)
## Description
 
One of the challenge when using DbUnit is to write tests that will stay valid even when the data fixtures get
modified. As the application grows new tests that will require modifications to the fixtures are meant to happen.
Having a direct access to the data fixtures from the tests is often a good way to solve this problem.  

This facade allow to do just that without hitting the database to avoid slowing down the tests. It simply 
receive an instance of ``PHPUnit\DbUnit\DataSet\ITable`` which is where DbUnit store the data fixtures that 
are used to seed the database before each test. It then provides methods that allow a more precise access 
to the data then the original ITable interface in order to keep the test code cleaner. 


## Installation

```sh
composer require bendamqui/dbunit-table
```


## Basic Setup 

Assuming your class implement getDataSet and getConnection methods which are
required by DbUnit.

```php
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait as DbUnit;
use Bendamqui\DbUnit\TableFacade;

class DbUnitUsersTest extends TestCase
{
    /**
     * @var TableFacade
     */
    private $users_table;
    
    /**
     * @var YourApp
     */
    private $app;

    /**
     * Avoid conflicts between PHPUnit and DbUnit
     * setUp methods.
     */
    use DbUnit {
        setUp as protected DbUnitSetUp;
    }

    public function setUp()
    {
        parent::setUp();

        // Make sure to set DbUnit up
        $this->DbUnitSetUp();

        // Get a table from the data set.
        $table = $this->getDatabaseTester->getDataSet()->getTable('users');

        // Pass the table to a new instance of TableFacade and you're good to go.
        $this->users_table = new TableFacade($table);
    }   
}
```

## Examples

Below are a few basic examples of how to take advantage of the facade to write tests that will still be valid if the fixture changes while keeping
your code clean and avoiding unnecessary call to the database.

```php		
public function testGetAllUsers()
{
	$expected = $this->users_table->getRowCount();
	$response = $this->app->get('users');
	$this->assertCount($expected, $response);
}
```

```php	
public function testPendingUserCannotLogIn()
{
	$pending_users = $this->users_table->getWhere(['status' => 'pending']);
	foreach ($pending_users as $user) {
		$response = $this->app->post('login', ['email' => $user['email'], 'pass' => $user['pass']]);
		$this->assertEquals(401, $response->getCode(), 'Pending user should not be able to log in.');
	}
	$this->assertGreaterThan(0, count($pending_users));
}
``` 

```php
public function testCannotUpdateUserWithAnInvalidEmail()
{
	// Get a valid payload to update a user and override the email field. 
	$payload = $this->users_table->get(['email' => 'invalid_email']);
	$response = $this->app->put('user', $payload);
	$this->assertEquals(400, $response->getCode(), 'User update with invalid email should receive a bad reques response');
}	
```

## API

- [\Bendamqui\DbUnit\TableFacade](#class-bendamquidbunittablefacade)

<hr /><a id="class-bendamquidbunittablefacade"></a>
### Class: \Bendamqui\DbUnit\TableFacade

> Class TableFacade

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>\PHPUnit\DbUnit\DataSet\ITable</em> <strong>$table</strong>)</strong> : <em>void</em><br /><em>TableFacade constructor.</em> |
| public | <strong>get(</strong><em>array</em> <strong>$override=array()</strong>, <em>int</em> <strong>$row</strong>)</strong> : <em>array</em><br /><em>Get one row.</em> |
| public | <strong>getAllRaw()</strong> : <em>array</em><br /><em>Get all rows in raw format (skip post processing).</em> |
| public | <strong>getByPrimaryKey(</strong><em>mixed</em> <strong>$id</strong>, <em>array/mixed</em> <strong>$override=array()</strong>)</strong> : <em>array</em><br /><em>Get one row by primary key</em> |
| public | <strong>getRaw(</strong><em>int</em> <strong>$row</strong>)</strong> : <em>array</em><br /><em>Get a row by its row number (skip post processing).</em> |
| public | <strong>getRowCount()</strong> : <em>int</em><br /><em>Get the number of row in the table</em> |
| public | <strong>getValue(</strong><em>mixed</em> <strong>$column</strong>, <em>int</em> <strong>$row</strong>)</strong> : <em>mixed</em><br /><em>Get the value of a given row/column in the Table.</em> |
| public | <strong>getWhere(</strong><em>array</em> <strong>$filters=array()</strong>)</strong> : <em>array</em><br /><em>Get many rows using filters in the form of key value. Perform AND only.</em> |
| public | <strong>setHidden(</strong><em>array</em> <strong>$hidden</strong>)</strong> : <em>void</em><br /><em>Set a list of columns that should not be returned when fetching row(s)</em> |
| public | <strong>setPrimaryKey(</strong><em>string</em> <strong>$primary_key</strong>)</strong> : <em>void</em><br /><em>Set the primary key of the table. Is set to 'id' by default.</em> |
| protected | <strong>postProcess(</strong><em>mixed</em> <strong>$payload</strong>)</strong> : <em>mixed</em><br /><em>Placeholder, can be override in order to process the raw data in the db to the actual format of the payload. e.g. The db store a name while the web form send first_name and last_name separately.</em> |



