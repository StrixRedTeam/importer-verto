<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Domain\Builder;

use Ergonode\Importer\Application\Provider\CreateSourceCommandBuilderInterface;
use Ergonode\Importer\Domain\Command\CreateSourceCommandInterface;
use Ergonode\ImporterVerto\Application\Model\ImporterVertoConfigurationModel;
use Ergonode\ImporterVerto\Domain\Command\CreateVertoCsvSourceCommand;
use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;
use Ergonode\SharedKernel\Domain\Aggregate\SourceId;
use Symfony\Component\Form\FormInterface;

class VertoCsvCreateSourceCommandBuilder implements CreateSourceCommandBuilderInterface
{
    public function supported(string $type): bool
    {
        return $type === VertoCsvSource::TYPE;
    }

    public function build(FormInterface $form): CreateSourceCommandInterface
    {
        /** @var ImporterVertoConfigurationModel $data */
        $data = $form->getData();
        $name = $data->name;
        $import = (array) $data->import;

        return new CreateVertoCsvSourceCommand(SourceId::generate(), $name, $import);
    }
}
