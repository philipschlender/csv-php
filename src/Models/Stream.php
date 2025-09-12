<?php

namespace Csv\Models;

use Csv\Exceptions\CsvException;
use Fs\Models\Stream as FsStream;

class Stream extends FsStream implements StreamInterface
{
    /**
     * @return array<int,string>
     *
     * @throws CsvException
     */
    public function readRow(string $separator = ',', string $enclosure = '"', string $escape = '\\'): array
    {
        if (1 !== strlen($separator)) {
            throw new CsvException('The separator must have a length of 1.');
        }

        if (1 !== strlen($enclosure)) {
            throw new CsvException('The enclosure must have a length of 1.');
        }

        if (1 !== strlen($escape)) {
            throw new CsvException('The escape must have a length of 1.');
        }

        if (!$this->isReadable()) {
            throw new CsvException('The stream must be readable.');
        }

        if ($this->eof()) {
            return [];
        }

        $row = @fgetcsv($this->stream, null, $separator, $enclosure, $escape);

        if (!is_array($row)) {
            throw new CsvException('Failed to read a row of the stream.');
        }

        if (1 === count($row) && is_null($row[0])) {
            throw new CsvException('A row must not be blank.');
        }

        return $row;
    }

    /**
     * @param array<int|string,string> $row
     *
     * @throws CsvException
     */
    public function writeRow(array $row, string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n"): int
    {
        if (empty($row)) {
            throw new CsvException('The row must contain at least one element.');
        }

        if (1 !== strlen($separator)) {
            throw new CsvException('The separator must have a length of 1.');
        }

        if (1 !== strlen($enclosure)) {
            throw new CsvException('The enclosure must have a length of 1.');
        }

        if (1 !== strlen($escape)) {
            throw new CsvException('The escape must have a length of 1.');
        }

        if (strlen($eol) > 1) {
            throw new CsvException('The eol must have a length of 0 or 1.');
        }

        if (!$this->isWritable()) {
            throw new CsvException('The stream must be writable.');
        }

        $numberOfBytes = @fputcsv($this->stream, $row, $separator, $enclosure, $escape, $eol);

        if (!is_int($numberOfBytes)) {
            throw new CsvException('Failed to write the row to the stream.');
        }

        return $numberOfBytes;
    }
}
