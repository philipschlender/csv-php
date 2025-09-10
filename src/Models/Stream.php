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
