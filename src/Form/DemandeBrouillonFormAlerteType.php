<?php

namespace App\Form;

use App\Entity\Avis;
use App\Entity\AvisPresident;
use App\Entity\Demande;
use App\Entity\DemandeBrouillon;
use App\Entity\ElementMotif;
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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Security\Core\Security;

class DemandeBrouillonFormAlerteType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        

        $builder
        ->add('motif_rejet_president', TextareaType::class,
        [   
            'label'=>false,
            'required'     => false,
        ])
        ->add('motif_rejet_directeur', TextareaType::class,
        [   
            'label'=>false,
            'required'     => false,
        ])
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
