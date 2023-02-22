<?php

declare(strict_types=1);

namespace SlamMysql\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SlamMysql\Mysql;

/**
 * @internal
 */
#[CoversClass(Mysql::class)]
/**
 * @internal
 *
 * @coversNothing
 */
final class MysqlTest extends TestCase
{
    private Mysql $mysql;

    protected function setUp(): void
    {
        $this->mysql = new Mysql(
            '127.0.0.1',
            'root',
            'root_password',
            '',
            3306,
            ''
        );
    }

    public function testErroneousConnectionParameters(): void
    {
        $user = uniqid('root_');
        $mysql = new Mysql(
            '127.0.0.1',
            $user,
            uniqid(),
            '',
            3306,
            ''
        );

        [$inputFile, $outputFile, $errorFile] = $this->createStreams('SHOW DATABASES');
        self::assertFalse($mysql->run($inputFile, $outputFile, $errorFile));

        rewind($errorFile);
        self::assertStringContainsString($user, (string) stream_get_contents($errorFile));
    }

    public function testReadDataFromInputAndReturnOutput(): void
    {
        $databaseName = uniqid('db_');

        [$inputFile, $outputFile, $errorFile] = $this->createStreams(sprintf('CREATE DATABASE %s', $databaseName));
        self::assertTrue($this->mysql->run($inputFile, $outputFile, $errorFile));

        rewind($outputFile);
        self::assertEmpty(stream_get_contents($outputFile));

        [$inputFile, $outputFile, $errorFile] = $this->createStreams('SHOW DATABASES');
        self::assertTrue($this->mysql->run($inputFile, $outputFile, $errorFile));

        rewind($outputFile);
        self::assertStringContainsString($databaseName, (string) stream_get_contents($outputFile));
    }

    public function testHandleMultipleQueries(): void
    {
        [$inputFile, $outputFile, $errorFile] = $this->createStreams(implode(PHP_EOL, [
            'SHOW VARIABLES;',
            'SHOW VARIABLES;',
        ]));
        self::assertTrue($this->mysql->run($inputFile, $outputFile, $errorFile));
    }

    public function testSkipCommentLines(): void
    {
        [$inputFile, $outputFile, $errorFile] = $this->createStreams(implode(PHP_EOL, [
            'SHOW VARIABLES;',
            '-- foo '.uniqid(),
            'SHOW VARIABLES;',
        ]));
        self::assertTrue($this->mysql->run($inputFile, $outputFile, $errorFile));
    }

    public function testReportSpecificQueryOnError(): void
    {
        $wrongQuery = sprintf('SLEECT foooo_%s', uniqid());
        [$inputFile, $outputFile, $errorFile] = $this->createStreams(implode(PHP_EOL, [
            'SHOW VARIABLES;',
            $wrongQuery.';',
        ]));
        self::assertFalse($this->mysql->run($inputFile, $outputFile, $errorFile));

        rewind($errorFile);
        $output = (string) stream_get_contents($errorFile);
        self::assertStringContainsString($wrongQuery, $output);
        self::assertStringNotContainsString('SHOW VARIABLES', $output);
    }

    public function testReportSpecificQueryOnErrorInEndingFile(): void
    {
        $wrongQuery = sprintf('SLEECT foooo_%s', uniqid());
        [$inputFile, $outputFile, $errorFile] = $this->createStreams(implode(PHP_EOL, [
            'SHOW VARIABLES;',
            $wrongQuery,
        ]));
        self::assertFalse($this->mysql->run($inputFile, $outputFile, $errorFile));

        rewind($errorFile);
        $output = (string) stream_get_contents($errorFile);
        self::assertStringContainsString($wrongQuery, $output);
        self::assertStringNotContainsString('SHOW VARIABLES', $output);
    }

    /**
     * @return resource[]
     */
    private function createStreams(string $input): array
    {
        $inputFile = tmpfile();
        self::assertIsResource($inputFile);
        fwrite($inputFile, $input);
        rewind($inputFile);

        $outputFile = tmpfile();
        self::assertIsResource($outputFile);

        $errorFile = tmpfile();
        self::assertIsResource($errorFile);

        return [$inputFile, $outputFile, $errorFile];
    }
}
