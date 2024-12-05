<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isUserConnected = null !== $this->security->getUser();
        // Si l'utilisateur connecté est admin, désactiver certains champs
        $isAdmin = $options['is_admin'];
        // si l'admin édite son propre profil
        $isSelfEdit = $options['is_self_edit'];

        $builder
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
                'attr' => ['class' => 'form-control'],
                'disabled' => !$isSelfEdit && $isAdmin, // Désactive le champ si admin
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => ['class' => 'form-control'],
                'disabled' => !$isSelfEdit && $isAdmin, // Désactive le champ si admin
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                'required' => !$options['is_edit'], // Pas obligatoire si en édition
                'disabled' => !$isSelfEdit && $isAdmin, // Désactive le champ si admin
                'first_options' => ['label' => 'Mot de passe', 'attr' => ['class' => 'form-control']],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 6]),
                ],
                'second_options' => ['label' => 'Répéter le mot de passe', 'attr' => ['class' => 'form-control']],
                'constraints' => [new Assert\NotBlank()],
            ]);

        // Si l'utilisateur connecté est admin, il peut modifier le rôle
        if ($isAdmin && !$isSelfEdit) {
            $builder->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'label' => 'Rôles',
                'attr' => ['class' => 'form-check'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
            'is_admin' => false,
            'is_self_edit' => false,
        ]);
    }
}
