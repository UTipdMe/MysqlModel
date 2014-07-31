A MysqlModel component for UTipdMe.

[![Build Status](https://travis-ci.org/UTipdMe/MysqlModel.svg?branch=master)](https://travis-ci.org/UTipdMe/MysqlModel)


A simple ORM to map MySQL table rows to PHP models and back.


Usage Example:

```php

<?php 

// create a class
//   this maps to table user in MySQL (you must create this yourself)
class UserDirectory extends \Utipd\MysqlModel\BaseDocumentMysqlDirectory {

    protected $column_names = ['email'];

}

// pass in your PDO object
$user_directory = new UserDirectory(new \PDO('mysql:dbname=testdb;host=127.0.0.1'));

// find by email
$user = $user_directory->findOne(['email' => 'johny@appleseed.com']);

// access rows and properties
print $user['email']."\n";

// update in MySQL, adding arbitrary columns
$user_directory->update($user, ['firstName' => 'John', 'lastName' => 'Appleseed']);

// get the user again from the database
$user = $user_directory->reload($user);
print $user['firstName']." ".$user['lastName']."\n";


```
