<?php

declare(strict_types=1);

namespace SlamMysql\Tests;

use PHPUnit\Framework\TestCase;
use SlamMysql\Mysql;

/**
 * @covers \SlamMysql\Mysql
 */
final class MysqlTest extends TestCase
{
    /**
     * @var Mysql
     */
    private $mysql;

    protected function setUp(): void
    {
        $this->mysql = new Mysql(
            '127.0.0.1',
            'root',
            '',
            '',
            3306,
            ''
        );
    }

    public function testSkipOnEmptyStream(): void
    {
        static::assertFalse($this->mysql->run(\STDIN, \STDOUT, \STDERR));
    }

    public function testErroneousConnectionParameters(): void
    {
        $user  = \uniqid('root_');
        $mysql = new Mysql(
            '127.0.0.1',
            $user,
            \uniqid(),
            '',
            3306,
            ''
        );

        list($inputFile, $outputFile, $errorFile) = $this->createStreams('SHOW DATABASES');
        static::assertFalse($mysql->run($inputFile, $outputFile, $errorFile));

        \rewind($errorFile);
        static::assertStringContainsString($user, (string) \stream_get_contents($errorFile));
    }

    public function testReadDataFromInputAndReturnOutput(): void
    {
        $databaseName = \uniqid('db_');

        list($inputFile, $outputFile, $errorFile) = $this->createStreams(\sprintf('CREATE DATABASE %s', $databaseName));
        static::assertTrue($this->mysql->run($inputFile, $outputFile, $errorFile));

        \rewind($outputFile);
        static::assertEmpty(\stream_get_contents($outputFile));

        list($inputFile, $outputFile, $errorFile) = $this->createStreams('SHOW DATABASES');
        static::assertTrue($this->mysql->run($inputFile, $outputFile, $errorFile));

        \rewind($outputFile);
        static::assertStringContainsString($databaseName, (string) \stream_get_contents($outputFile));
    }

    public function testHandleMultipleQueries(): void
    {
        list($inputFile, $outputFile, $errorFile) = $this->createStreams(\implode(\PHP_EOL, [
            'SHOW VARIABLES;',
            'SHOW VARIABLES;',
        ]));
        static::assertTrue($this->mysql->run($inputFile, $outputFile, $errorFile));
    }

    public function testSkipCommentLines(): void
    {
        list($inputFile, $outputFile, $errorFile) = $this->createStreams(\implode(\PHP_EOL, [
            'SHOW VARIABLES;',
            '-- foo ' . \uniqid(),
            'SHOW VARIABLES;',
        ]));
        static::assertTrue($this->mysql->run($inputFile, $outputFile, $errorFile));
    }

    public function testReportSpecificQueryOnError(): void
    {
        $wrongQuery                               = \sprintf('SLEECT foooo_%s', \uniqid());
        list($inputFile, $outputFile, $errorFile) = $this->createStreams(\implode(\PHP_EOL, [
            'SHOW VARIABLES;',
            $wrongQuery . ';',
        ]));
        static::assertFalse($this->mysql->run($inputFile, $outputFile, $errorFile));

        \rewind($errorFile);
        $output = (string) \stream_get_contents($errorFile);
        static::assertStringContainsString($wrongQuery, $output);
        static::assertStringNotContainsString('SHOW VARIABLES', $output);
    }

    public function testReportSpecificQueryOnErrorInEndingFile(): void
    {
        $wrongQuery                               = \sprintf('SLEECT foooo_%s', \uniqid());
        list($inputFile, $outputFile, $errorFile) = $this->createStreams(\implode(\PHP_EOL, [
            'SHOW VARIABLES;',
            $wrongQuery,
        ]));
        static::assertFalse($this->mysql->run($inputFile, $outputFile, $errorFile));

        \rewind($errorFile);
        $output = (string) \stream_get_contents($errorFile);
        static::assertStringContainsString($wrongQuery, $output);
        static::assertStringNotContainsString('SHOW VARIABLES', $output);
    }

    /**
     * @return resource[]
     */
    private function createStreams(string $input): array
    {
        $inputFile = \tmpfile();
        static::assertIsResource($inputFile);
        \fwrite($inputFile, $input);
        \rewind($inputFile);

        $outputFile = \tmpfile();
        static::assertIsResource($outputFile);

        $errorFile = \tmpfile();
        static::assertIsResource($errorFile);

        return [$inputFile, $outputFile, $errorFile];
    }
}
