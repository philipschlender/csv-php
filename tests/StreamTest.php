<?php

namespace Tests;

use Csv\Exceptions\CsvException;
use Csv\Models\Stream;
use Fs\Enumerations\Mode;
use PHPUnit\Framework\Attributes\DataProvider;

class StreamTest extends TestCase
{
    /**
     * @param array<int,array<int,string>> $expectedRows
     */
    #[DataProvider('dataProviderReadRow')]
    public function testReadRow(string $csv, array $expectedRows): void
    {
        $stream = new Stream('php://temp', Mode::Write);

        $stream->write($csv);

        $stream->rewind();

        $rows = [];

        while (true) {
            $row = $stream->readRow();

            if (empty($row)) {
                break;
            }

            $rows[] = $row;
        }

        $stream->close();

        $this->assertEquals($expectedRows, $rows);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function dataProviderReadRow(): array
    {
        return [
            [
                'csv' => "a,b,c\nd,e,f\n",
                'expectedRows' => [
                    [
                        'a',
                        'b',
                        'c',
                    ],
                    [
                        'd',
                        'e',
                        'f',
                    ],
                ],
            ],
            [
                'csv' => "\"a,b\",\"c,d\",\"e,f\"\n",
                'expectedRows' => [
                    [
                        'a,b',
                        'c,d',
                        'e,f',
                    ],
                ],
            ],
            [
                'csv' => "\"a\"\"b\",\"c\"\"d\",\"e\"\"f\"\n",
                'expectedRows' => [
                    [
                        'a"b',
                        'c"d',
                        'e"f',
                    ],
                ],
            ],
            [
                'csv' => "\"a\nb\",\"c\nd\",\"e\nf\"\n",
                'expectedRows' => [
                    [
                        "a\nb",
                        "c\nd",
                        "e\nf",
                    ],
                ],
            ],
        ];
    }

    public function testReadRowStreamNotReadable(): void
    {
        $this->expectException(CsvException::class);
        $this->expectExceptionMessage('The stream must be readable.');

        $stream = new Stream('php://temp', Mode::Write);

        $stream->close();

        $stream->readRow();
    }

    public function testReadRowStreamEof(): void
    {
        $stream = new Stream('php://temp', Mode::Write);

        $stream->read();

        $row = $stream->readRow();

        $stream->close();

        $this->assertEmpty($row);
    }

    public function testReadRowFailedToReadRow(): void
    {
        $this->expectException(CsvException::class);
        $this->expectExceptionMessage('Failed to read a row of the stream.');

        $stream = new Stream('php://temp', Mode::Write);

        $stream->write('');

        $stream->rewind();

        $stream->readRow();
    }

    public function testReadRowBlankRow(): void
    {
        $this->expectException(CsvException::class);
        $this->expectExceptionMessage('A row must not be blank.');

        $stream = new Stream('php://temp', Mode::Write);

        $stream->write("\n");

        $stream->rewind();

        $stream->readRow();
    }

    /**
     * @param array<int,array<int,string>> $rows
     */
    #[DataProvider('dataProviderWriteRow')]
    public function testWriteRow(array $rows, int $expectedNumberOfBytes, string $expectedCsv): void
    {
        $stream = new Stream('php://temp', Mode::Write);

        $numberOfBytes = 0;

        foreach ($rows as $row) {
            $numberOfBytes += $stream->writeRow($row);
        }

        $stream->rewind();

        $csv = $stream->read();

        $stream->close();

        $this->assertEquals($expectedNumberOfBytes, $numberOfBytes);
        $this->assertEquals($expectedCsv, $csv);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function dataProviderWriteRow(): array
    {
        return [
            [
                'rows' => [
                    [
                        'a',
                        'b',
                        'c',
                    ],
                    [
                        'd',
                        'e',
                        'f',
                    ],
                ],
                'expectedNumberOfBytes' => 12,
                'expectedCsv' => "a,b,c\nd,e,f\n",
            ],
            [
                'rows' => [
                    [
                        'a,b',
                        'c,d',
                        'e,f',
                    ],
                ],
                'expectedNumberOfBytes' => 18,
                'expectedCsv' => "\"a,b\",\"c,d\",\"e,f\"\n",
            ],
            [
                'rows' => [
                    [
                        'a"b',
                        'c"d',
                        'e"f',
                    ],
                ],
                'expectedNumberOfBytes' => 21,
                'expectedCsv' => "\"a\"\"b\",\"c\"\"d\",\"e\"\"f\"\n",
            ],
            [
                'rows' => [
                    [
                        "a\nb",
                        "c\nd",
                        "e\nf",
                    ],
                ],
                'expectedNumberOfBytes' => 18,
                'expectedCsv' => "\"a\nb\",\"c\nd\",\"e\nf\"\n",
            ],
        ];
    }

    public function testWriteRowStreamNotWritable(): void
    {
        $this->expectException(CsvException::class);
        $this->expectExceptionMessage('The stream must be writable.');

        $stream = new Stream('php://temp', Mode::Write);

        $stream->close();

        $stream->writeRow([]);
    }
}
