<?php

namespace App\tests\Form;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class TaskTypeTest extends KernelTestCase
{
    private $task;

    private $user;

    private $entityManager;

    private $formFactory;

    protected function setUp(): void
    {
        // Démarrer le kernel
        self::bootKernel();

        // Récupérer l'entity manager
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        // Récupérer le form factory
        $this->formFactory = static::getContainer()->get(FormFactoryInterface::class);

        // Créer un utilisateur de test
        $this->user = new User();
        $this->user->setUsername('testUser');
        $this->user->setPassword('password123');
        $this->user->setEmail('test@test.com');

        // Persister l'utilisateur
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();

        // Créer une tâche de test
        $this->task = new Task();
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
            'title'   => 'Tâche de test',
            'content' => 'Contenu de la tâche de test',
            'author'  => $this->user->getId(), // Important : utiliser l'ID pour EntityType
        ];

        $form = $this->formFactory->create(TaskType::class, $this->task);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('Tâche de test', $this->task->getTitle());
        $this->assertEquals('Contenu de la tâche de test', $this->task->getContent());
        $this->assertEquals($this->user, $this->task->getAuthor());
    }

    public function testFormFields()
    {
        $form = $this->formFactory->create(TaskType::class, $this->task);

        $this->assertTrue($form->has('title'));
        $this->assertTrue($form->has('content'));
        $this->assertTrue($form->has('author'));

        // Vérification des options des champs
        $titleConfig   = $form->get('title')->getConfig();
        $contentConfig = $form->get('content')->getConfig();

        $this->assertEquals('wide-title, form-control', $titleConfig->getOption('attr')['class']);
        $this->assertEquals('wide-textarea, form-control', $contentConfig->getOption('attr')['class']);
    }

    public function testEditMode()
    {
        $form = $this->formFactory->create(TaskType::class, $this->task, [
            'is_edit' => true,
        ]);

        $this->assertFalse($form->has('author'));
        $this->assertTrue($form->has('title'));
        $this->assertTrue($form->has('content'));
    }

    public function testDefaultOptions()
    {
        $form = $this->formFactory->create(TaskType::class, $this->task);

        $options = $form->getConfig()->getOptions();

        $this->assertEquals(Task::class, $options['data_class']);
        $this->assertFalse($options['is_edit']);
    }
}
