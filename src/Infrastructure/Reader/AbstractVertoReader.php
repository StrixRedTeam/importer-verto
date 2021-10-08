<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Reader;

use Ergonode\ImporterVerto\Infrastructure\Reader\Exception\ReaderFileProcessException;
use Exception;
use Iterator;
use League\Csv\Reader;

abstract class AbstractVertoReader
{
    protected array $headers;

    protected Iterator $records;

    protected string $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param string $file
     * @throws ReaderFileProcessException
     */
    public function open(string $file): void
    {
        $filepath = sprintf('%s%s%s', $this->directory, DIRECTORY_SEPARATOR, $file);

        try {
            $reader = Reader::createFromPath($filepath);
            $reader->setHeaderOffset(0);
            $reader->skipEmptyRecords();
            $reader->skipInputBOM();
            $this->headers = $reader->getHeader();
            $this->records = $reader->getRecords();
            $this->records->rewind();
        } catch (Exception $exception) {
            throw new ReaderFileProcessException($filepath, $file);
        }
    }
}
