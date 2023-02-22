<?php

declare(strict_types=1);

namespace SlamMysql;

final class Mysql implements MysqlInterface
{
    public function __construct(
        private readonly string $host,
        private readonly string $username,
        private readonly string $password,
        private readonly string $database,
        private readonly int $port,
        private readonly string $socket
    ) {
    }

    /**
     * @param resource $inputStream
     * @param resource $outputStream
     * @param resource $errorStream
     */
    public function run($inputStream, $outputStream, $errorStream): bool
    {
        $read = [$inputStream];
        $write = [];
        $except = [];
        $result = stream_select($read, $write, $except, 0);
        if (false === $result) {
            // @codeCoverageIgnoreStart
            fwrite($errorStream, 'stream_select failed'.PHP_EOL);

            return false;
            // @codeCoverageIgnoreEnd
        }
        if (0 === $result) {
            // @codeCoverageIgnoreStart
            fwrite($errorStream, 'Input stream is empty'.PHP_EOL);

            return false;
            // @codeCoverageIgnoreEnd
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $mysqli = new \mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database,
                $this->port,
                $this->socket
            );
        } catch (\mysqli_sql_exception $mysqli_sql_exception) {
            fwrite($errorStream, 'MySQLi Error ('.$mysqli_sql_exception->getCode().'):'.$mysqli_sql_exception->getMessage().PHP_EOL);

            return false;
        }

        $query = '';
        while (false !== ($line = fgets($inputStream))) {
            if (str_starts_with($line, '--')) {
                continue;
            }

            $query .= $line;
            if (1 !== preg_match('/;\s*$/', $query)) {
                continue;
            }
            $query = trim($query);
            $query = rtrim($query, ';');

            if (true !== $this->executeQuery($query, $mysqli, $outputStream, $errorStream)) {
                return false;
            }

            $query = '';
        }

        if ('' !== trim($query)) {
            if (true !== $this->executeQuery($query, $mysqli, $outputStream, $errorStream)) {
                return false;
            }
        }

        $mysqli->close();

        return true;
    }

    /**
     * @param resource $outputStream
     * @param resource $errorStream
     */
    private function executeQuery(string $query, \mysqli $mysqli, $outputStream, $errorStream): bool
    {
        try {
            $mysqli->real_query($query);
        } catch (\mysqli_sql_exception $mysqli_sql_exception) {
            fwrite($errorStream, 'Query Error ('.$mysqli_sql_exception->getCode().'):'.$mysqli_sql_exception->getMessage().PHP_EOL.PHP_EOL.'Query: "'.$query.'"'.PHP_EOL);

            return false;
        }

        if (false !== ($result = $mysqli->store_result())) {
            while ($row = $result->fetch_row()) {
                fwrite($outputStream, sprintf("%s\n", implode("\t", $row)));
            }
            $result->free();
        }

        return true;
    }
}
