<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Domain\Command;

use Ergonode\Importer\Domain\Command\UpdateSourceCommandInterface;
use Ergonode\SharedKernel\Domain\Aggregate\SourceId;

class UpdateVertoCsvSourceCommand implements UpdateSourceCommandInterface
{
    private SourceId $id;

    private string $name;

    private array $import;

    public function __construct(
        SourceId $id,
        string $name,
        array $import = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->import = $import;
    }

    public function getId(): SourceId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getImport(): array
    {
        return $this->import;
    }
}
