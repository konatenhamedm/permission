<?php

namespace App\Form;

use App\Entity\Avis;
use App\Entity\AvisPresident;
use App\Entity\Demande;
use App\Entity\Entreprise;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Security\Core\Security;

class DemandeType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('type', ChoiceType::class,
            [   'placeholder'=>'Choissez un type de mande',
                'required'     => false,
                'expanded'     => false,
                'attr' => ['class' => 'has-select2 type'],
                'multiple' => false,
                'choices'  => array_flip([
                    'TYPE_JOURNEE' => 'Toute la journée',
                    'TYPE_DEMI_JOURNEE' => 'Demi journée'
                ]),
            ])
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
            ->add('nbreJour',TextType::class,[
                'label'=>'Nombre de jour(inclus)',
                'attr' => ['class' => 'nbre']
            ])
            ->add('heureDebut',TimeType::class,[
                'input'  => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('heureFin',TimeType::class,[
                'input'  => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('motifs', CollectionType::class, [
                'entry_type' => MotifType::class,
                'entry_options' => [
                    'label' => false,
                    'doc_options' => $options['doc_options'],
                    'doc_required' => $options['doc_required']
                ],

                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,
            ])
            ->add('avis', EntityType::class, [
                'class' => Avis::class,
                'choice_label' => 'libelle',
                'label' => 'Avis directeur',
                'attr' => ['class' => 'has-select2 form-select']
            ])
            ->add('avisPresident', EntityType::class, [
                'class' => AvisPresident::class,
                'choice_label' => 'libelle',
                'label' => 'Avis président',
                'attr' => ['class' => 'has-select2 form-select']
            ])

            ->add('annuler',SubmitType::class,['label' => 'Annuler', 'attr' => ['class' => 'btn btn-primary btn-sm' ,'data-bs-dismiss'=>'modal']])
            ->add('save',SubmitType::class,['label' => 'Enregister', 'attr' => ['class' => 'btn btn-main btn-ajax btn-sm']])
            ->add('passer',SubmitType::class,['label' => 'Valider etape', 'attr' => ['class' => 'btn btn-success btn-ajax btn-sm']])
            ->add('refuser',SubmitType::class,['label' => 'Réfuser la demande', 'attr' => ['class' => 'btn btn-danger btn-ajax btn-sm']])
            ->add('accepatation_president',SubmitType::class,['label' => 'Cloturer la demande', 'attr' => ['class' => 'btn btn-warning btn-ajax btn-sm']])
            ->add('accepatation_president_attente_document',SubmitType::class,['label' => 'Accepte et attente document', 'attr' => ['class' => 'btn btn-success btn-ajax btn-sm']])
            ->add('document_enregister',SubmitType::class,['label' => 'Soumettre le document', 'attr' => ['class' => 'btn btn-success btn-ajax btn-sm']])
            ->add('document_verification_accepte',SubmitType::class,['label' => 'Cloturer la demande', 'attr' => ['class' => 'btn btn-primary btn-ajax btn-sm']])
            ->add('document_verification_refuse',SubmitType::class,['label' => 'Rejeter le document', 'attr' => ['class' => 'btn btn-primary btn-ajax btn-sm']])

            /*->add('avis', CollectionType::class, [
                'entry_type' => AvisType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,
            ])*/
            /*->add('utilisateur')*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
            'doc_required' => false,
            'doc_options' => [],
        ]);
        $resolver->setRequired('doc_required');
        $resolver->setRequired('doc_options');
    }
}
