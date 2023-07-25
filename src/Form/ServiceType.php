<?php

namespace App\Form;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', null, [
                'label' => 'Libellé',
                'constraints' => [
                    new NotBlank([
                        'message' => "S'il vous renseigner le libelle",
                    ]),

                ],
            ])
            ->add('code', null, [
                'label' => 'Code',
                'constraints' => [
                    new NotBlank([
                        'message' => "S'il vous renseigner le code",
                    ]),

                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
