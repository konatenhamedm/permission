<?php

namespace App\Form;

use App\Entity\DemandeBrouillon;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeBrouillonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('dateDebut', DateType::class, [
            "required" => false,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => new \DateTime(),
            'attr' => ['class' => 'dateDebut']
        ])
        ->add('dateFin', DateType::class, [
            "required" => false,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => ['class' => 'dateFin']
        ])
        ->add('utilisateur', EntityType::class, [
            'class' => Utilisateur::class,
            'choice_label' => 'username',
            'label' => 'utilisteur',
            'attr' => ['class' => 'has-select2 form-select']
        ])
            ->add('motif')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeBrouillon::class,
        ]);
    }
}
