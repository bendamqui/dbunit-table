[![Build Status](https://travis-ci.org/bendamqui/dbunit-table.svg?branch=master)](https://travis-ci.org/bendamqui/dbunit-table)
[![Coverage Status](https://coveralls.io/repos/github/bendamqui/dbunit-table/badge.svg?branch=master)](https://coveralls.io/github/bendamqui/dbunit-table?branch=master)
[![Latest Stable Version](https://poser.pugx.org/bendamqui/dbunit-table/v/stable.png)](https://packagist.org/packages/bendamqui/dbunit-table)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)


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


## Basic Setup example


```php
use PHPUnit\Framework\TestCase;
use Bendamqui\DbUnit\FixtureUtil;

class DbUnitUsersTest extends TestCase
{
    /**
     * @var FixtureUtil
     */
    private $users_table;
    
    /**
        * @var array 
    */
    private $data = [];
    
    /**
     * @var YourApp
     */
    private $app;    

    public function setUp()
    {
        parent::setUp();                
        $this->users_table = new FixtureUtil($this->data);
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
	$this->assertEquals(422, $response->getCode(), 'User update with invalid email should receive a bad request response');
}	
```

## API

| Visibility | Function |
|:-----------|:---------|
| public | <strong>get(</strong><em>array</em> <strong>$override=array()</strong>, <em>int</em> <strong>$row=0</strong>)</strong> : <em>array</em><br /><em>Get one row.</em> |
| public | <strong>getAll(</strong><em>array</em> <strong>$override=array()</strong>)</strong> : <em>array</em><br /><em>Get all rows.</em> |
| public | <strong>getAllRaw()</strong> : <em>array</em><br /><em>Get all rows in raw format (skip post processing).</em> |
| public | <strong>getByPrimaryKey(</strong><em>mixed</em> <strong>$id</strong>, <em>array/mixed</em> <strong>$override=array()</strong>)</strong> : <em>array</em><br /><em>Get one row by primary key</em> |
| public | <strong>getRaw(</strong><em>int</em> <strong>$row=0</strong>)</strong> : <em>array</em><br /><em>Get a row by its row number (skip post processing).</em> |
| public | <strong>getRowCount()</strong> : <em>int</em><br /><em>Get the number of row in the table</em> |
| public | <strong>getValue(</strong><em>mixed</em> <strong>$column</strong>, <em>int</em> <strong>$row=0</strong>)</strong> : <em>mixed</em><br /><em>Get the value of a given row/column in the Table.</em> |
| public | <strong>getWhere(</strong><em>array</em> <strong>$filters=array()</strong>)</strong> : <em>array</em><br /><em>Get many rows using filters in the form of key value. Perform AND only.</em> |
| public | <strong>setHidden(</strong><em>array</em> <strong>$hidden</strong>)</strong> : <em>void</em><br /><em>Set a list of columns that should not be returned when fetching row(s)</em> |
| public | <strong>setPrimaryKey(</strong><em>string</em> <strong>$primary_key</strong>)</strong> : <em>void</em><br /><em>Set the primary key of the table. Is set to 'id' by default.</em> |



