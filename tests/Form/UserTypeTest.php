<?php

namespace App\tests\Form;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;

class UserTypeTest extends KernelTestCase
{
    private $user;

    private $entityManager;

    private $formFactory;

    private $security;

    protected function setUp(): void
    {
        // Démarrer le kernel
        self::bootKernel();

        // Récupérer l'entity manager
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        // Récupérer le form factory
        $this->formFactory = static::getContainer()->get(FormFactoryInterface::class);

        // Récupérer le service de sécurité
        $this->security = $this->createMock(Security::class);

        // Créer un utilisateur de test
        $this->user = new User();
        $this->user->setUsername('testUser');
        $this->user->setPassword('password123');
        $this->user->setEmail('test@test.com');

        // Persister l'utilisateur
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        // Nettoyer la base de données après chaque test
        if ($this->user) {
            $this->entityManager->remove($this->user);
            $this->entityManager->flush();
        }

        parent::tearDown();
    }

    public function testSubmitValidData()
    {
        $formData = [
            'username' => 'newUser',
            'email'    => 'newuser@example.com',
            'password' => [
                'first'  => 'newpassword',
                'second' => 'newpassword',
            ],
        ];

        $form = $this->formFactory->create(UserType::class, $this->user, [
            'is_edit'      => false,
            'is_admin'     => false,
            'is_self_edit' => false,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('newUser', $this->user->getUsername());
        $this->assertEquals('newuser@example.com', $this->user->getEmail());
        // Vous pouvez ajouter des assertions pour vérifier le mot de passe si nécessaire
    }

    public function testFormFields()
    {
        $form = $this->formFactory->create(UserType::class, $this->user, [
            'is_edit'      => false,
            'is_admin'     => false,
            'is_self_edit' => false,
        ]);

        $this->assertTrue($form->has('username'));
        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('password'));

        // Vérification des options des champs
        $usernameConfig = $form->get('username')->getConfig();
        $emailConfig    = $form->get('email')->getConfig();
        $passwordConfig = $form->get('password')->getConfig();

        $this->assertEquals('form-control', $usernameConfig->getOption('attr')['class']);
        $this->assertEquals('form-control', $emailConfig->getOption('attr')['class']);
        $this->assertEquals('form-control', $passwordConfig->getOption('first_options')['attr']['class']);
        $this->assertEquals('form-control', $passwordConfig->getOption('second_options')['attr']['class']);
    }

    public function testEditMode(): void
    {
        $form = $this->formFactory->create(UserType::class, $this->user, [
            'is_edit'      => true,
            'is_admin'     => false,
            'is_self_edit' => false,
        ]);

        // Vérifier les champs de base
        $this->assertTrue($form->has('username'));
        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('password'));

        // En mode édition non-admin, le champ roles ne devrait pas être présent
        $this->assertFalse($form->has('roles'), 'Le champ roles ne devrait pas être présent pour un utilisateur non admin');
    }

    public function testAdminMode(): void
    {
        $form = $this->formFactory->create(UserType::class, $this->user, [
            'is_edit'      => true,
            'is_admin'     => true,
            'is_self_edit' => false,
        ]);

        // Vérifier les champs de base
        $this->assertTrue($form->has('username'));
        $this->assertTrue($form->has('email'));
        $this->assertFalse($form->has('password'), 'Le champ password ne devrait pas être présent en mode admin');

        // Vérifier le champ roles
        $this->assertTrue($form->has('roles'), 'Le champ roles devrait être présent en mode admin');
        $rolesField = $form->get('roles');
        $this->assertFalse($rolesField->getConfig()->getDisabled(), 'Le champ roles devrait être actif pour un admin');
    }

    public function testDefaultOptions()
    {
        $form = $this->formFactory->create(UserType::class, $this->user);

        $options = $form->getConfig()->getOptions();

        $this->assertEquals(User::class, $options['data_class']);
        $this->assertFalse($options['is_edit']);
        $this->assertFalse($options['is_admin']);
        $this->assertFalse($options['is_self_edit']);
    }
}
