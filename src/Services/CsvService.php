<?php

namespace Csv\Services;

use Csv\Exceptions\CsvException;
use Csv\Models\Stream;
use Csv\Models\StreamInterface;
use Io\Enumerations\Mode;
use Io\Exceptions\IoException;

class CsvService implements CsvServiceInterface
{
    /**
     * @param array<int,array<string,string>> $rows
     *
     * @throws CsvException
     */
    public function arrayToCsv(array $rows, bool $useHeader = true, string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n"): string
    {
        if (empty($rows)) {
            return '';
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

        try {
            $stream = $this->openStream();

            if ($useHeader) {
                $header = array_keys($rows[0]);

                $stream->writeRow($header, $separator, $enclosure, $escape, $eol);
            }

            $numberOfColumns = count($rows[0]);

            foreach ($rows as $row) {
                $numberOfRowColumns = count($row);

                if ($numberOfRowColumns !== $numberOfColumns) {
                    throw new CsvException('All rows must have the same number of columns.');
                }

                $stream->writeRow($row, $separator, $enclosure, $escape, $eol);
            }

            $stream->rewind();

            return $stream->read();
        } catch (IoException $exception) {
            throw new CsvException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @return array<int,array<string,string>>
     *
     * @throws CsvException
     */
    public function csvToArray(string $csv, bool $hasHeader = true, string $separator = ',', string $enclosure = '"', string $escape = '\\'): array
    {
        if (empty($csv)) {
            return [];
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

        try {
            $stream = $this->openStream();

            $stream->write($csv);

            $stream->rewind();

            if ($hasHeader) {
                $header = $stream->readRow($separator, $enclosure, $escape);
            } else {
                $row = $stream->readRow($separator, $enclosure, $escape);

                $header = $this->getDefaultHeader(count($row));

                $stream->rewind();
            }

            $numberOfColumns = count($header);

            $rows = [];

            while (true) {
                $row = $stream->readRow($separator, $enclosure, $escape);

                if (empty($row)) {
                    break;
                }

                $numberOfRowColumns = count($row);

                if ($numberOfRowColumns !== $numberOfColumns) {
                    throw new CsvException('All rows must have the same number of columns.');
                }

                $rows[] = array_combine($header, $row);
            }

            return $rows;
        } catch (IoException $exception) {
            throw new CsvException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @throws CsvException
     */
    protected function openStream(): StreamInterface
    {
        try {
            return new Stream('php://temp', Mode::Write);
        } catch (IoException $exception) {
            throw new CsvException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @return array<int,string>
     *
     * @throws CsvException
     */
    protected function getDefaultHeader(int $numberOfColumns): array
    {
        if ($numberOfColumns < 0) {
            throw new CsvException('The number of columns must be greater than or equal to 0.');
        }

        $header = [];

        for ($i = 0; $i < $numberOfColumns; ++$i) {
            $header[] = $this->getColumnName($i);
        }

        return $header;
    }

    /**
     * @throws CsvException
     */
    protected function getColumnName(int $index): string
    {
        if ($index < 0) {
            throw new CsvException('The index must be greater than or equal to 0.');
        }

        $columnName = '';

        do {
            $character = chr(65 + ($index % 26));
            $columnName = sprintf('%s%s', $character, $columnName);

            $index = ((int) floor($index / 26)) - 1;

            if (0 === $index) {
                $character = chr(65);
                $columnName = sprintf('%s%s', $character, $columnName);
            }
        } while ($index > 0);

        return $columnName;
    }
}
