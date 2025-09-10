<?php

namespace Tests;

use Csv\Exceptions\CsvException;
use Csv\Services\CsvService;
use PHPUnit\Framework\Attributes\DataProvider;

class CsvServiceTest extends TestCase
{
    protected CsvService $csvService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->csvService = new CsvService();
    }

    /**
     * @param array<int,array<string,string>> $rows
     */
    #[DataProvider('dataProviderArrayToCsv')]
    public function testArrayToCsv(array $rows, bool $useHeader, string $expectedCsv): void
    {
        $csv = $this->csvService->arrayToCsv($rows, $useHeader);

        $this->assertEquals($expectedCsv, $csv);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function dataProviderArrayToCsv(): array
    {
        return [
            [
                'rows' => [
                    [
                        'A' => '1',
                        'B' => '2',
                        'C' => '3',
                    ],
                    [
                        'A' => '4',
                        'B' => '5',
                        'C' => '6',
                    ],
                ],
                'useHeader' => true,
                'expectedCsv' => "A,B,C\n1,2,3\n4,5,6\n",
            ],
            [
                'rows' => [
                    [
                        'A' => '1,2',
                        'B' => '3,4',
                        'C' => '5,6',
                    ],
                    [
                        'A' => '7,8',
                        'B' => '9,10',
                        'C' => '11,12',
                    ],
                ],
                'useHeader' => true,
                'expectedCsv' => "A,B,C\n\"1,2\",\"3,4\",\"5,6\"\n\"7,8\",\"9,10\",\"11,12\"\n",
            ],
            [
                'rows' => [
                    [
                        'A' => '1"2',
                        'B' => '3"4',
                        'C' => '5"6',
                    ],
                    [
                        'A' => '7"8',
                        'B' => '9"10',
                        'C' => '11"12',
                    ],
                ],
                'useHeader' => true,
                'expectedCsv' => "A,B,C\n\"1\"\"2\",\"3\"\"4\",\"5\"\"6\"\n\"7\"\"8\",\"9\"\"10\",\"11\"\"12\"\n",
            ],
            [
                'rows' => [
                    [
                        'A' => "1\n2",
                        'B' => "3\n4",
                        'C' => "5\n6",
                    ],
                    [
                        'A' => "7\n8",
                        'B' => "9\n10",
                        'C' => "11\n12",
                    ],
                ],
                'useHeader' => true,
                'expectedCsv' => "A,B,C\n\"1\n2\",\"3\n4\",\"5\n6\"\n\"7\n8\",\"9\n10\",\"11\n12\"\n",
            ],
            [
                'rows' => [],
                'useHeader' => true,
                'expectedCsv' => '',
            ],
            [
                'rows' => [
                    [
                        'A' => '1',
                        'B' => '2',
                        'C' => '3',
                    ],
                    [
                        'A' => '4',
                        'B' => '5',
                        'C' => '6',
                    ],
                ],
                'useHeader' => false,
                'expectedCsv' => "1,2,3\n4,5,6\n",
            ],
            [
                'rows' => [
                    [
                        'A' => '1,2',
                        'B' => '3,4',
                        'C' => '5,6',
                    ],
                    [
                        'A' => '7,8',
                        'B' => '9,10',
                        'C' => '11,12',
                    ],
                ],
                'useHeader' => false,
                'expectedCsv' => "\"1,2\",\"3,4\",\"5,6\"\n\"7,8\",\"9,10\",\"11,12\"\n",
            ],
            [
                'rows' => [
                    [
                        'A' => '1"2',
                        'B' => '3"4',
                        'C' => '5"6',
                    ],
                    [
                        'A' => '7"8',
                        'B' => '9"10',
                        'C' => '11"12',
                    ],
                ],
                'useHeader' => false,
                'expectedCsv' => "\"1\"\"2\",\"3\"\"4\",\"5\"\"6\"\n\"7\"\"8\",\"9\"\"10\",\"11\"\"12\"\n",
            ],
            [
                'rows' => [
                    [
                        'A' => "1\n2",
                        'B' => "3\n4",
                        'C' => "5\n6",
                    ],
                    [
                        'A' => "7\n8",
                        'B' => "9\n10",
                        'C' => "11\n12",
                    ],
                ],
                'useHeader' => false,
                'expectedCsv' => "\"1\n2\",\"3\n4\",\"5\n6\"\n\"7\n8\",\"9\n10\",\"11\n12\"\n",
            ],
            [
                'rows' => [],
                'useHeader' => false,
                'expectedCsv' => '',
            ],
        ];
    }

    public function testArrayToCsvRowsNotSameNumberOfColumns(): void
    {
        $this->expectException(CsvException::class);
        $this->expectExceptionMessage('All rows must have the same number of columns.');

        $rows = [
            [
                'A' => '1',
                'B' => '2',
                'C' => '3',
            ],
            [
                'A' => '4',
                'B' => '5',
            ],
        ];

        $this->csvService->arrayToCsv($rows);
    }

    /**
     * @param array<int,array<string,string>> $expectedRows
     */
    #[DataProvider('dataProviderCsvToArray')]
    public function testCsvToArray(string $csv, bool $hasHeader, array $expectedRows): void
    {
        $rows = $this->csvService->csvToArray($csv, $hasHeader);

        $this->assertEquals($expectedRows, $rows);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function dataProviderCsvToArray(): array
    {
        return [
            [
                'csv' => "A,B,C\n1,2,3\n4,5,6\n",
                'hasHeader' => true,
                'expectedRows' => [
                    [
                        'A' => '1',
                        'B' => '2',
                        'C' => '3',
                    ],
                    [
                        'A' => '4',
                        'B' => '5',
                        'C' => '6',
                    ],
                ],
            ],
            [
                'csv' => "A,B,C\n\"1,2\",\"3,4\",\"5,6\"\n\"7,8\",\"9,10\",\"11,12\"\n",
                'hasHeader' => true,
                'expectedRows' => [
                    [
                        'A' => '1,2',
                        'B' => '3,4',
                        'C' => '5,6',
                    ],
                    [
                        'A' => '7,8',
                        'B' => '9,10',
                        'C' => '11,12',
                    ],
                ],
            ],
            [
                'csv' => "A,B,C\n\"1\"\"2\",\"3\"\"4\",\"5\"\"6\"\n\"7\"\"8\",\"9\"\"10\",\"11\"\"12\"\n",
                'hasHeader' => true,
                'expectedRows' => [
                    [
                        'A' => '1"2',
                        'B' => '3"4',
                        'C' => '5"6',
                    ],
                    [
                        'A' => '7"8',
                        'B' => '9"10',
                        'C' => '11"12',
                    ],
                ],
            ],
            [
                'csv' => "A,B,C\n\"1\n2\",\"3\n4\",\"5\n6\"\n\"7\n8\",\"9\n10\",\"11\n12\"\n",
                'hasHeader' => true,
                'expectedRows' => [
                    [
                        'A' => "1\n2",
                        'B' => "3\n4",
                        'C' => "5\n6",
                    ],
                    [
                        'A' => "7\n8",
                        'B' => "9\n10",
                        'C' => "11\n12",
                    ],
                ],
            ],
            [
                'csv' => '',
                'hasHeader' => true,
                'expectedRows' => [],
            ],
            [
                'csv' => "1,2,3\n4,5,6\n",
                'hasHeader' => false,
                'expectedRows' => [
                    [
                        'A' => '1',
                        'B' => '2',
                        'C' => '3',
                    ],
                    [
                        'A' => '4',
                        'B' => '5',
                        'C' => '6',
                    ],
                ],
            ],
            [
                'csv' => "\"1,2\",\"3,4\",\"5,6\"\n\"7,8\",\"9,10\",\"11,12\"\n",
                'hasHeader' => false,
                'expectedRows' => [
                    [
                        'A' => '1,2',
                        'B' => '3,4',
                        'C' => '5,6',
                    ],
                    [
                        'A' => '7,8',
                        'B' => '9,10',
                        'C' => '11,12',
                    ],
                ],
            ],
            [
                'csv' => "\"1\"\"2\",\"3\"\"4\",\"5\"\"6\"\n\"7\"\"8\",\"9\"\"10\",\"11\"\"12\"\n",
                'hasHeader' => false,
                'expectedRows' => [
                    [
                        'A' => '1"2',
                        'B' => '3"4',
                        'C' => '5"6',
                    ],
                    [
                        'A' => '7"8',
                        'B' => '9"10',
                        'C' => '11"12',
                    ],
                ],
            ],
            [
                'csv' => "\"1\n2\",\"3\n4\",\"5\n6\"\n\"7\n8\",\"9\n10\",\"11\n12\"\n",
                'hasHeader' => false,
                'expectedRows' => [
                    [
                        'A' => "1\n2",
                        'B' => "3\n4",
                        'C' => "5\n6",
                    ],
                    [
                        'A' => "7\n8",
                        'B' => "9\n10",
                        'C' => "11\n12",
                    ],
                ],
            ],
            [
                'csv' => '',
                'hasHeader' => false,
                'expectedRows' => [],
            ],
        ];
    }

    public function testCsvToArrayRowsNotSameNumberOfColumns(): void
    {
        $this->expectException(CsvException::class);
        $this->expectExceptionMessage('All rows must have the same number of columns.');

        $csv = "A,B,C\n1,2,3\n4,5\n";

        $this->csvService->csvToArray($csv);
    }

    public function testCsvToArrayDefaultHeader(): void
    {
        $csv = "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32\n";

        $expectedRows = [
            [
                'A' => '1',
                'B' => '2',
                'C' => '3',
                'D' => '4',
                'E' => '5',
                'F' => '6',
                'G' => '7',
                'H' => '8',
                'I' => '9',
                'J' => '10',
                'K' => '11',
                'L' => '12',
                'M' => '13',
                'N' => '14',
                'O' => '15',
                'P' => '16',
                'Q' => '17',
                'R' => '18',
                'S' => '19',
                'T' => '20',
                'U' => '21',
                'V' => '22',
                'W' => '23',
                'X' => '24',
                'Y' => '25',
                'Z' => '26',
                'AA' => '27',
                'AB' => '28',
                'AC' => '29',
                'AD' => '30',
                'AE' => '31',
                'AF' => '32',
            ],
        ];

        $rows = $this->csvService->csvToArray($csv, false);

        $this->assertEquals($expectedRows, $rows);
    }
}
