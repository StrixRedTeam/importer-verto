<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Application\Factory;

use Ergonode\ImporterVerto\Application\Form\ImporterVertoConfigurationForm;
use Ergonode\ImporterVerto\Application\Model\ImporterVertoConfigurationModel;
use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;
use Ergonode\Importer\Application\Provider\SourceFormFactoryInterface;
use Ergonode\Importer\Domain\Entity\Source\AbstractSource;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ImporterVertoSourceFormFactory implements SourceFormFactoryInterface
{
    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function supported(string $type): bool
    {
        return VertoCsvSource::TYPE === $type;
    }

    public function create(?AbstractSource $source = null): FormInterface
    {
        $model = new ImporterVertoConfigurationModel($source);
        if (null === $source) {
            return $this->formFactory->create(ImporterVertoConfigurationForm::class, $model);
        }

        return $this->formFactory->create(
            ImporterVertoConfigurationForm::class,
            $model,
            ['method' => Request::METHOD_PUT]
        );
    }
}
