<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Application\Model;

use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;
use Symfony\Component\Validator\Constraints as Assert;

class ImporterVertoConfigurationModel
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=2)
     */
    public ?string $name = null;
    public array $import = [];

    public function __construct(?VertoCsvSource $source = null)
    {
        if ($source) {
            $this->name = $source->getName();

            foreach (VertoCsvSource::STEPS as $step) {
                if ($source->import($step)) {
                    $this->import[] = $step;
                }
            }
        }
    }
}
