<?php

namespace App\Form;

use App\Entity\Civilite;
use App\Entity\Entreprise;
use App\Entity\Fonction;
use App\Entity\Employe;
use App\Entity\Service;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmployeType extends AbstractType
{
    private $user;
    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('civilite', EntityType::class, [
                'class' => Civilite::class,
                'choice_label' => 'code',
                'label' => 'Civilité',
                'attr' => ['class' => 'has-select2 form-select']
            ])
            ->add('entreprise', EntityType::class, [
                'class' => Entreprise::class,
                'choice_label' => 'denomination',
                'label' => 'Entreprise',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->innerJoin('e.employes', 'em')
                        ->innerJoin('em.utilisateur', 'u')
                        ->andWhere('u =:user')
                        ->setParameter('user', $this->user);
                },
                'attr' => ['class' => 'has-select2 form-select']
            ])
            ->add('matricule', null, ['label' => 'Matricule'])
            ->add(
                'contact',
                null,
                [
                    'label' => 'Contact(s)',
                    'constraints' => [
                        /*  new NotBlank([
                            'message' => 'Please enter a password',
                        ]), */
                        new Length([
                            //'max' => 10,
                            'min' => 10,
                            'minMessage' => 'Renseigner au minumum et au maximum {{ limit }} characteres le champs contact',

                            // max length allowed by Symfony for security reasons
                        ]),
                    ],
                ]

            )
            ->add('adresseMail', EmailType::class, ['label' => 'Adresse E-mail', 'required' => false, 'empty_data' => ''])
            ->add('nom', null, ['label' => 'Nom'])
            ->add('prenom', null, ['label' => 'Prénoms'])
            ->add('fonction', EntityType::class, [
                'class' => Fonction::class,
                'choice_label' => 'libelle',
                'label' => 'Fonction',
                'attr' => ['class' => 'has-select2 form-select']
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'libelle',
                'label' => 'Direction',
                'attr' => ['class' => 'has-select2 form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employe::class,
        ]);
    }
}
