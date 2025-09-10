<?php

namespace Csv\Models;

use Csv\Exceptions\CsvException;
use Fs\Models\StreamInterface as FsStreamInterface;

interface StreamInterface extends FsStreamInterface
{
    /**
     * @return array<int,string>
     *
     * @throws CsvException
     */
    public function readRow(string $separator = ',', string $enclosure = '"', string $escape = '\\'): array;

    /**
     * @param array<int|string,string> $row
     *
     * @throws CsvException
     */
    public function writeRow(array $row, string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n"): int;
}
