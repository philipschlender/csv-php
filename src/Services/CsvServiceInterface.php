<?php

namespace Csv\Services;

use Csv\Exceptions\CsvException;

interface CsvServiceInterface
{
    /**
     * @param array<int,array<string,string>> $rows
     *
     * @throws CsvException
     */
    public function arrayToCsv(array $rows, bool $useHeader = true, string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n"): string;

    /**
     * @return array<int,array<string,string>>
     *
     * @throws CsvException
     */
    public function csvToArray(string $csv, bool $hasHeader = true, string $separator = ',', string $enclosure = '"', string $escape = '\\'): array;
}
