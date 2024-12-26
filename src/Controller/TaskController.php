<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TaskController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/tasks', name: 'task_list', methods: ['GET'])]
    public function listTasks(): Response
    {
        $user = $this->getUser();
        $tasks = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $anonymousUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'anonyme']);
            $tasks = $this->entityManager->getRepository(Task::class)->createQueryBuilder('t')
                ->where('t.author = :user OR t.author = :anonymous')
                ->setParameter('user', $user)
                ->setParameter('anonymous', $anonymousUser)
                ->getQuery()
                ->getResult();
        } else {
            $tasks = $this->entityManager->getRepository(Task::class)->findBy(['author' => $user]);
        }

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'title' => 'Liste des tâches',
        ]);
    }

    #[Route('/tasks/create', name: 'task_create', methods: ['GET', 'POST'])]
    public function createTask(Request $request): Response
    {
        $task = new Task();
        $task->setAuthor($this->getUser());
        $task->setCreatedAt(new \DateTime());

        $form = $this->createForm(TaskType::class, $task, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a été ajoutée avec succès.');
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function editTask(Task $task, Request $request): Response
    {
        $this->denyAccessUnlessGranted('TASK_EDIT', $task);

        $form = $this->createForm(TaskType::class, $task, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'La tâche a bien été modifiée.');
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle', methods: ['POST'])]
    public function toggleTask(Task $task): Response
    {
        $this->denyAccessUnlessGranted('TASK_EDIT', $task);

        $task->toggle(!$task->isDone());
        $this->entityManager->flush();

        $message = $task->isDone()
            ? sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle())
            : sprintf('La tâche %s a bien été marquée comme non terminée.', $task->getTitle());

        $this->addFlash('success', $message);
        return $this->redirectToRoute('task_list');
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete', methods: ['DELETE', 'POST'])]
    public function deleteTask(Task $task, Request $request): Response
    {
        // $this->denyAccessUnlessGranted('TASK_DELETE', $task);

        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($task);
            $this->entityManager->flush();
            $this->addFlash('success', 'La tâche a bien été supprimée.');
        } else {
            $this->addFlash('error', 'Le token CSRF est invalide.');
        }

        return $this->redirectToRoute('task_list');
    }

    #[Route('/tasks/todo', name: 'task_list_todo', methods: ['GET'])]
    public function listTodo(): Response
    {
        $tasks = $this->entityManager->getRepository(Task::class)->findBy([
            'author' => $this->getUser(),
            'isDone' => false,
        ]);

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'title' => 'Tâches à faire'
        ]);
    }

    #[Route('/tasks/done', name: 'task_list_done', methods: ['GET'])]
    public function listDone(): Response
    {
        $tasks = $this->entityManager->getRepository(Task::class)->findBy([
            'author' => $this->getUser(),
            'isDone' => true,
        ]);

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'title' => 'Tâches terminées'
        ]);
    }
}
