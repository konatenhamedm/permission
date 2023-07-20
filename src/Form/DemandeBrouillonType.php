<?php

namespace App\Form;

use App\Entity\DemandeBrouillon;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeBrouillonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('motif_valider_president')
        ->add('dateDebut', DateType::class,  [
            'attr' => ['class' => 'datepicker no-auto skip-init']
            , 'widget' => 'single_text'
            , 'format' => 'dd/MM/yyyy',
            'label'=>false
            , 'empty_data' => date('d/m/Y')
            , 'required' => false
            , 'html5' => false
        ])
        ->add('dateFin', DateType::class,  [
            'attr' => ['class' => 'datepicker no-auto skip-init']
            , 'widget' => 'single_text'
            , 'format' => 'dd/MM/yyyy',
            'label'=>false
            , 'empty_data' => date('d/m/Y')
            , 'required' => false
            , 'html5' => false
        ])
        ->add('utilisateur', EntityType::class, [
            'class' => Utilisateur::class,
            'choice_label' => 'username',
            'label' => 'utilisteur',
            'attr' => ['class' => 'form-select']
        ])
            ->add('motif')
            ->add('annuler',SubmitType::class,['label' => 'Annuler', 'attr' => ['class' => 'btn btn-default btn-sm' ,'data-bs-dismiss'=>'modal']])
            ->add('save',SubmitType::class,['label' => 'Valider', 'attr' => ['class' => 'btn btn-main  btn-sm btn-ajax']])
            ->add('valider',SubmitType::class,['label' => 'Valider le brouillon demande', 'attr' => ['class' => 'btn btn-success btn-sm btn-ajax']])
            ->add('valider_president',SubmitType::class,['label' => 'Valider le brouillon demande', 'attr' => ['class' => 'btn btn-success btn-sm btn-ajax']])
            ->add('review_president',SubmitType::class,['label' => 'Passer en revue par le président', 'attr' => ['class' => 'btn btn-primary btn-sm btn-ajax']])
            ->add('rejeter',SubmitType::class,['label' => 'Annuler le brouillon', 'attr' => ['class' => 'btn btn-primary btn-sm btn-ajax']])
            ->add('rejeter_president',SubmitType::class,['label' => 'Annuler le brouillon', 'attr' => ['class' => 'btn btn-primary btn-sm btn-ajax']])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeBrouillon::class,
        ]);
    }
}
