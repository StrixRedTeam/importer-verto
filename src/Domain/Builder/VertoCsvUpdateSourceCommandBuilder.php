<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ImporterVerto\Domain\Builder;

use Ergonode\ImporterVerto\Domain\Command\UpdateVertoCsvSourceCommand;
use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;
use Ergonode\Importer\Application\Provider\UpdateSourceCommandBuilderInterface;
use Ergonode\Importer\Domain\Command\UpdateSourceCommandInterface;
use Ergonode\ImporterVerto\Application\Model\ImporterVertoConfigurationModel;
use Ergonode\SharedKernel\Domain\Aggregate\SourceId;
use Symfony\Component\Form\FormInterface;

class VertoCsvUpdateSourceCommandBuilder implements UpdateSourceCommandBuilderInterface
{
    public function supported(string $type): bool
    {
        return $type === VertoCsvSource::TYPE;
    }

    public function build(SourceId $id, FormInterface $form): UpdateSourceCommandInterface
    {
        /** @var ImporterVertoConfigurationModel $data */
        $data = $form->getData();
        $name = $data->name;
        $import = (array) $data->import;

        return new UpdateVertoCsvSourceCommand($id, $name, $import);
    }
}
