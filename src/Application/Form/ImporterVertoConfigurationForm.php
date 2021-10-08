<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Application\Form;

use Ergonode\ImporterVerto\Application\Model\ImporterVertoConfigurationModel;
use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImporterVertoConfigurationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'name',
                ]
            )
            ->add(
                'import',
                ChoiceType::class,
                [
                    'label' => 'Include in the imports',
                    'choices' => [
                        'Products' => VertoCsvSource::PRODUCTS,
                    ],
                    'multiple' => true,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'importer',
            'data_class' => ImporterVertoConfigurationModel::class,
            'allow_extra_fields' => true,
            'label' => 'Import settings',
        ]);
    }

    public function getBlockPrefix(): ?string
    {
        return null;
    }
}
