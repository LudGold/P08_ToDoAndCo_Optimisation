<?php

namespace App\tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends KernelTestCase
{
    private ValidatorInterface $validator;
    
    protected function setUp(): void
    {   
        self::bootKernel();
        $container = static::getContainer();
        $this->validator = $container->get('validator');
    }

    // ... autres tests ...

    public function testSetEmailWithInvalidEmailThrowsException()
    {
        $user = new User();
        $user->setEmail('invalid-email');
        
        $violations = $this->validator->validate($user);
        
        $this->assertGreaterThan(
            0, 
            count($violations), 
            'Un email invalide devrait générer des erreurs de validation'
        );
        
        // Vérifie spécifiquement l'erreur d'email
        $hasEmailError = false;
        foreach ($violations as $violation) {
            if (strpos($violation->getPropertyPath(), 'email') !== false) {
                $hasEmailError = true;
                break;
            }
        }
        
        $this->assertTrue($hasEmailError, 'Une violation de contrainte devrait exister pour l\'email');
    }
}