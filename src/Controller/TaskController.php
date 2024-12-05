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
use Symfony\Component\Security\Core\Security;

class TaskController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/tasks", name="task_list", methods={"GET"})
     */
    public function listAction(): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();
        // Récupère l'utilisateur "anonyme" pour le rôle admin
        $anonymousUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'anonyme']);

        // Vérifie si l'utilisateur est administrateur
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        // Si l'utilisateur est admin, récupérer les tâches de l'utilisateur connecté + de l'utilisateur "anonyme"
        if ($isAdmin) {
            // Récupérer les tâches soit de l'utilisateur connecté soit de l'utilisateur anonyme
            $tasks = $this->entityManager->getRepository(Task::class)->createQueryBuilder('t')
                ->where('t.author = :user OR t.author = :anonymous')
                ->setParameter('user', $user)
                ->setParameter('anonymous', $anonymousUser)
                ->getQuery()
                ->getResult();
        } else {
            // Si l'utilisateur n'est pas admin, ne récupérer que ses propres tâches
            $tasks = $this->entityManager->getRepository(Task::class)->findBy(['author' => $user]);
        }

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'title' => 'Liste des tâches',
        ]);
    }

    /**
     * @Route("/tasks/create", name="task_create", methods={"GET", "POST"})
     */
    public function createAction(Request $request, Security $security): Response
    {
        $task = new Task();
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        $task->setAuthor($user);

        $form = $this->createForm(TaskType::class, $task, ['is_edit' => false]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$task->getCreatedAt()) {
                $task->setCreatedAt(new \DateTime());
            }
            $user = $security->getUser();
            $task->setAuthor($user);

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a été ajoutée avec succès.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit", methods={"GET", "POST"})
     */
    public function editAction(Task $task, Request $request): Response
    {
        $user = $this->getUser();
        if ($task->getAuthor() !== $user) {
            // Ajouter un message flash
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cette tâche.');

            // Rediriger vers la liste des tâches de l'utilisateur
            return $this->redirectToRoute('task_list');
        }
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

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle", methods={"POST"})
     */
    public function toggleTaskAction(Task $task): Response
    {
        $task->toggle(!$task->isDone());
        $this->entityManager->flush();

        // Vérification de l'état pour afficher le message approprié
        if ($task->isDone()) {
            $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));
        } else {
            $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme non terminée.', $task->getTitle()));
        }

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete", methods={"GET", "POST"})
     */
    public function deleteTaskAction(Task $task, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        // Récupérer l'utilisateur "anonyme" pour la comparaison
        $anonymousUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'anonyme']);

        // Vérification des autorisations de suppression
        if ($task->getAuthor() !== $user && (!$isAdmin || $task->getAuthor() !== $anonymousUser)) {
            // Si l'utilisateur n'est pas l'auteur et que ce n'est pas un admin qui supprime une tâche anonyme
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette tâche.');

            return $this->redirectToRoute('task_list');
        }

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            // Suppression de la tâche
            $entityManager->remove($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été supprimée.');
        } else {
            // Si le token CSRF est invalide
            $this->addFlash('error', 'Le token CSRF est invalide.');
        }

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/todo", name="task_list_todo", methods={"GET"})
     */
    public function listTodoAction(): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Récupère les tâches non terminées pour l'utilisateur connecté
        $tasks = $this->entityManager->getRepository(Task::class)->findBy([
            'author' => $user,
            'isDone' => false,
        ]);

        return $this->render('task/list.html.twig', ['tasks' => $tasks, 'title' => 'Tâches à faire']);
    }

    /**
     * @Route("/tasks/done", name="task_list_done", methods={"GET"})
     */
    public function listDoneAction(): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Récupère les tâches terminées pour l'utilisateur connecté
        $tasks = $this->entityManager->getRepository(Task::class)->findBy([
            'author' => $user,
            'isDone' => true,
        ]);

        return $this->render('task/list.html.twig', ['tasks' => $tasks, 'title' => 'Tâches terminées']);
    }
}
