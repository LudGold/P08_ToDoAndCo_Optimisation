<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'wide-title, form-control'],
            ])
            ->add('content', TextareaType::class, [
                'attr' => ['class' => 'wide-textarea, form-control'],
            ]);
        // Si c'est en mode édition, on n'ajoute pas le champ 'author'
        if (!$options['is_edit']) {
            $builder->add('author', EntityType::class, [
                'class'        => User::class,
                'choice_label' => 'username',
                'label'        => 'Auteur',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'is_edit'    => false,  // Par défaut, on considère que ce n'est pas une édition
        ]);
    }
}
