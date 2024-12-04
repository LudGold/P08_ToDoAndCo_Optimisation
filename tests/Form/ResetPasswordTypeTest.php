<?php

namespace App\Tests\Form;

use App\Form\ResetPasswordType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class ResetPasswordTypeTest extends KernelTestCase
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
            'plainPassword' => 'newpassword',
        ];

        $form = $this->formFactory->create(ResetPasswordType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('newpassword', $form->getData()['plainPassword']);
    }

    public function testFormFields()
    {
        $form = $this->formFactory->create(ResetPasswordType::class);

        $this->assertTrue($form->has('plainPassword'));

        // Vérification des options des champs
        $plainPasswordConfig = $form->get('plainPassword')->getConfig();

        $this->assertEquals('Nouveau mot de passe', $plainPasswordConfig->getOption('label'));
    }
}
