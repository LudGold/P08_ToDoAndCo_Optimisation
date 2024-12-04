<?php

namespace App\Tests\Form;

use App\Form\PasswordResetRequestType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class PasswordResetRequestTypeTest extends KernelTestCase
{
    private $formFactory;
    protected function setUp(): void
    {
        // Démarrer le kernel
        self::bootKernel();

        // Récupérer le form factory
        $this->formFactory = static::getContainer()->get(FormFactoryInterface::class);
    }

    public function testSubmitValidData()
    {
        $formData = [
            'username' => 'testUser',
        ];

        $form = $this->formFactory->create(PasswordResetRequestType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('testUser', $form->getData()['username']);
    }

    public function testFormFields()
    {
        $form = $this->formFactory->create(PasswordResetRequestType::class);

        $this->assertTrue($form->has('username'));

        // Vérification des options des champs
        $usernameConfig = $form->get('username')->getConfig();

        $this->assertEquals('Nom d\'utilisateur', $usernameConfig->getOption('label'));
    }
}
