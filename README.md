# MySQL bin in PHP

[![Build Status](https://travis-ci.org/Slamdunk/mysql-php.svg?branch=master)](https://travis-ci.org/Slamdunk/mysql-php)
[![Code Coverage](https://scrutinizer-ci.com/g/Slamdunk/mysql-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Slamdunk/mysql-php/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Slamdunk/mysql-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Slamdunk/mysql-php/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/slam/mysql-php/v/stable.svg)](https://packagist.org/packages/slam/mysql-php)
[![Total Downloads](https://poser.pugx.org/slam/mysql-php/downloads.png)](https://packagist.org/packages/slam/mysql-php)

PHP light version of mysql cli that comes with MySQL.

## Why

1. You are inside a PHP only environment, like a PHP Docker image
1. You need to import a large mysql dump
1. You don't have access to the native `mysql` client

## Requirements

1. PHP 7.3
1. `ext-mysqli`

## Performance

Speed is exactly the **same** of the original `mysql` binary thanks to streams usage.

## Supported formats

|Input type|Supported?|
|---|:---:|
|`mysqldump` output|:heavy_check_mark:|
|Single query on multiple lines|:heavy_check_mark:|
|Multiple query on single line|:x:|

## Usage

