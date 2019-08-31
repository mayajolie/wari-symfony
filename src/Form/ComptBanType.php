<?php

namespace App\Form;


use App\Entity\ComptBancaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Partenaires;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ComptBanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
        ->add('partenaire' ,EntityType::class,[
            'class'=>Partenaires::class
        ])    
            
            
            ->add('save',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ComptBancaire::class,
        ]);
    }
}
