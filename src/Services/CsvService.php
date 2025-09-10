<?php

namespace Csv\Services;

use Csv\Exceptions\CsvException;
use Csv\Models\Stream;
use Csv\Models\StreamInterface;
use Fs\Enumerations\Mode;

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
        } catch (\Throwable $throwable) {
            if ($throwable instanceof CsvException) {
                throw $throwable;
            }

            throw new CsvException($throwable->getMessage(), 0, $throwable);
        } finally {
            if (isset($stream)) {
                $stream->close();
            }
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
        } catch (\Throwable $throwable) {
            if ($throwable instanceof CsvException) {
                throw $throwable;
            }

            throw new CsvException($throwable->getMessage(), 0, $throwable);
        } finally {
            if (isset($stream)) {
                $stream->close();
            }
        }
    }

    /**
     * @throws CsvException
     */
    protected function openStream(): StreamInterface
    {
        try {
            return new Stream('php://temp', Mode::Write);
        } catch (\Throwable $throwable) {
            throw new CsvException($throwable->getMessage(), 0, $throwable);
        }
    }

    /**
     * @return array<int,string>
     */
    protected function getDefaultHeader(int $numberOfColumns): array
    {
        $header = [];

        for ($i = 0; $i < $numberOfColumns; ++$i) {
            $header[] = $this->getColumnName($i);
        }

        return $header;
    }

    protected function getColumnName(int $index): string
    {
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
