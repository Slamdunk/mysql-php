<?php

declare(strict_types=1);

namespace SlamMysql;

interface MysqlInterface
{
    /**
     * @param resource $inputStream
     * @param resource $outputStream
     * @param resource $errorStream
     */
    public function run($inputStream, $outputStream, $errorStream): bool;
}
