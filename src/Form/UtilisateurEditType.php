<?php

namespace App\Form;

use App\Entity\Groupe;
use App\Entity\Employe;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UtilisateurEditType extends AbstractType
{
    private $userGroupe;
    public function __construct(Security $security)
    {
        $this->userGroupe = $security->getUser()->getGroupe()->getName();
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->userGroupe == 'Présidents') {
            $builder->add('groupe', EntityType::class, [
                'label'        => 'Groupe',
                'choice_label' => 'name',
                'multiple'     => false,
                'expanded'     => false,
                'placeholder' => 'Choisir un groupe',
                'attr' => ['class' => 'has-select2 element'],
                'class'        => Groupe::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e');
                }
            ]);
        } else {
            $builder->add('groupe', EntityType::class, [
                'label'        => 'Groupe',
                'choice_label' => 'name',
                'multiple'     => false,
                'expanded'     => false,
                'placeholder' => 'Choisir un groupe',
                'attr' => ['class' => 'has-select2 element'],
                'class'        => Groupe::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->andWhere('g.name in (:groupe)')
                        ->setParameter('groupe', ['Collaborateurs', 'Directeurs']);
                }
            ]);
        }
        $builder
            ->add('username', TextType::class, ['label' => 'Pseudo'])
            /*  ->add('roles', ChoiceType::class,
            [
                'placeholder' => 'Choisir un role',
                'label' => 'Privilèges Supplémentaires',
                'required'     => false,
                'expanded'     => false,
                'attr' => ['class' => 'has-select2'],
                'multiple' => true,
                'choices'  => array_flip([
                    'ROLE_SUPER_ADMIN' => 'Super Administrateur',
                    'ROLE_ADMIN' => 'Administrateur'
                ]),
            ]) */
            /*   ->add('groupe', EntityType::class, [
                'label'        => 'Groupes',
                'choice_label' => 'name',
                'multiple'     => true,
                'expanded'     => false,
                'placeholder' => 'Choisir au moins groupe',
                'attr' => ['class' => 'has-select2'],
                'class'        => Groupe::class,
            ]) */
            ->add(
                'password',
                RepeatedType::class,
                [
                    'type'            => PasswordType::class,
                    'invalid_message' => 'Les mots de passe doivent être identiques.',
                    'required'        => true,
                    'error_bubbling' => true,
                    'first_options'   => ['label' => 'Mot de passe'],
                    'second_options'  => ['label' => 'Répétez le mot de passe'],
                    'invalid_message' => 'The passwords do not match!',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'renseigner au minimum {{ limit }} characteres',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ]
            )
            /*  ->add(
                'employe',
                EntityType::class,
                [
                    'class' => Employe::class,
                    'choice_label' => 'nomComplet',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->withoutAccount();
                    }
                ]
            ) */;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'passwordRequired' => false
        ]);

        $resolver->setRequired('passwordRequired');
    }
}
