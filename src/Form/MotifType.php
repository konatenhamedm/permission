<?php

namespace App\Form;

use App\Entity\CategorieAvis;
use App\Entity\ElementMotif;
use App\Entity\Motif;
use App\Repository\ElementMotifRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class MotifType extends AbstractType
{
    private $security;
    private $repository;

    public function __construct(Security $security, ElementMotifRepository $repository)
    {
        $this->security = $security;
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                //$departement = $event->getData()['departement'] ?? null;

                $departement = $this->repository->createQueryBuilder('d')
                    /*->andWhere('d.id =:id')*/
                    /*->setParameter('id',1)*/
                    ->orderBy('d.libelle', 'ASC')
                    ->getQuery()
                    ->getResult();
                $event->getForm()->add('element', EntityType::class, [
                    'class' => ElementMotif::class,
                    'choice_attr' => function (ElementMotif $element) {
                        return ['data-value' => $element->getCode()];
                    },
                    //'label'=>false,
                    'required' => true,
                    'choice_label' => 'libelle',
                    'choices' => $departement,
                    'disabled' => false,
                    'label' => false,
                    //'placeholder'=>'Selectionnez un motif',
                    'attr' => ['class' => 'has-select2 form-select element']
                    /*'constraints'=>new NotBlank(['message'=>'Selectionnez un departement']),*/
                ]);
            })
            ->add('nomEnfant', TextType::class, [
                'label' => false,

            ])

            ->add('observation', TextType::class, [
                'label' => false,
            ])
            ->add('precisez', TextareaType::class, [
                'label' => false,
                'attr' => ['class' => 'precisez']
            ])
            ->add('element', EntityType::class, [
                'label' => false,
                'class' => ElementMotif::class,
                'choice_label' => 'libelle',
                'attr' => ['class' => 'has-select2 form-select']
            ])
            ->add(
                'fichier',
                FichierType::class,
                [
                    'label' => 'Fichier',
                    'label' => false,
                    'doc_options' => $options['doc_options'],
                    'required' => $options['doc_required'] ?? true
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Motif::class,
            'doc_required' => true
        ]);
        $resolver->setRequired('doc_options');
        $resolver->setRequired('doc_required');
    }
}
