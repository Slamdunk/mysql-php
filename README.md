# MySQL bin in PHP

[![Latest Stable Version](https://img.shields.io/packagist/v/slam/mysql-php.svg)](https://packagist.org/packages/slam/mysql-php)
[![Downloads](https://img.shields.io/packagist/dt/slam/mysql-php.svg)](https://packagist.org/packages/slam/mysql-php)
[![Integrate](https://github.com/Slamdunk/mysql-php/workflows/Integrate/badge.svg?branch=master)](https://github.com/Slamdunk/mysql-php/actions)
[![Code Coverage](https://codecov.io/gh/Slamdunk/mysql-php/coverage.svg?branch=master)](https://codecov.io/gh/Slamdunk/mysql-php?branch=master)

PHP light version of mysql cli that comes with MySQL.

## Why

1. You are inside a PHP only environment, like a PHP Docker image
1. You need to import a large mysql dump
1. You don't have access to the native `mysql` client

## Performance

Speed is exactly the **same** of the original `mysql` binary thanks to streams usage.

## Supported formats

|Input type|Example|Supported?|
|---|---|:---:|
|`mysqldump` output|*as is*|:heavy_check_mark:|
|Single query on single line|`SELECT NOW();`|:heavy_check_mark:|
|Single query on multiple lines|`SELECT`<br />`NOW();`|:heavy_check_mark:|
|Multiple queries on separated single or multiple lines|`SELECT NOW();`<br />`SELECT`<br />`NOW();`|:heavy_check_mark:|
|Multiple queries on single line|`SELECT NOW();SELECT NOW();`|:x:|

## Usage

The library provides two usages, the binary and the `\SlamMysql\Mysql` class.

### From CLI

```
$ ./mysql -h
Usage: mysql [OPTIONS]
  --host       Connect to host     [Default: INI mysqli.default_host]
  --port       Port number         [Default: INI mysqli.default_port]
  --username   User for login      [Default: INI mysqli.default_user]
  --password   Password to use     [Default: INI mysqli.default_pw]
  --database   Database to use     [Default: empty]
  --socket     The socket file     [Default: INI mysqli.default_socket]

$ printf "CREATE DATABASE foobar;\nSHOW DATABASES;" | ./mysql
information_schema
foobar
mysql
performance_schema
sys

$ ./mysql --database foobar < foobar_huge_dump.sql
```

### From PHP

```php
$mysql = new \SlamMysql\Mysql('localhost', 'root', 'pwd', 'my_database', 3306, '/socket');
$return = $mysql->run(\STDIN, \STDOUT, \STDERR);
exit((int) (true !== $return));
```

`\SlamMysql\Mysql::run` accepts any type of resource consumable by `fgets/fwrite` functions.

## Related projects

1. [ifsnop/mysqldump-php](https://github.com/ifsnop/mysqldump-php): `mysqldump` binary port in pure PHP
