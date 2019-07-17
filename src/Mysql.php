<?php

declare(strict_types=1);

namespace SlamMysql;

use mysqli;

final class Mysql
{
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $database;
    /**
     * @var int
     */
    private $port;
    /**
     * @var string
     */
    private $socket;

    public function __construct(string $host, string $username, string $password, string $database, int $port, string $socket)
    {
        $this->host     = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port     = $port;
        $this->socket   = $socket;
    }

    /**
     * @param resource $inputStream
     * @param resource $outputStream
     * @param resource $errorStream
     */
    public function run($inputStream, $outputStream, $errorStream): bool
    {
        $read   = [$inputStream];
        $write  = [];
        $except = [];
        $result = \stream_select($read, $write, $except, 0);
        if (false === $result) {
            // @codeCoverageIgnoreStart
            \fwrite($errorStream, 'stream_select failed');

            return false;
            // @codeCoverageIgnoreEnd
        }
        if (0 === $result) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $mysqli = @new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->database,
            $this->port,
            $this->socket
        );

        if ($mysqli->connect_error) {
            \fwrite($errorStream, 'MySQLi Error (' . $mysqli->connect_errno . '):' . $mysqli->connect_error);

            return false;
        }

        $query = '';
        while (false !== ($line = \fgets($inputStream))) {
            if (0 === \strpos($line, '--')) {
                continue;
            }

            $query .= $line;
            if (1 !== \preg_match('/;\s*$/', $query)) {
                continue;
            }
            $query = \trim($query);
            $query = \rtrim($query, ';');

            if (true !== $this->executeQuery($query, $mysqli, $outputStream, $errorStream)) {
                return false;
            }

            $query = '';
        }

        if ('' !== \trim($query)) {
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
    private function executeQuery(string $query, mysqli $mysqli, $outputStream, $errorStream): bool
    {
        $mysqli->real_query($query);

        if ($mysqli->error) {
            \fwrite($errorStream, 'Query Error (' . $mysqli->errno . '):' . $mysqli->error . "\n\n" . 'Query: "' . $query . '"');

            return false;
        }
        if (false !== ($result = $mysqli->store_result())) {
            while ($row = $result->fetch_row()) {
                \fwrite($outputStream, \sprintf("%s\n", \implode("\t", $row)));
            }
            $result->free();
        }

        return true;
    }
}
