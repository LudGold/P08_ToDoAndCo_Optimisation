<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Task.
 *
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    /**
     * Identifiant unique de la tâche.
     *
     * @var int
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Date de création de la tâche.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Titre de la tâche.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * Contenu de la tâche.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * Statut de la tâche : terminée ou non.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isDone = false;

    /**
     * Auteur de la tâche.
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tasks")
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function isDone(): ?bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): self
    {
        $this->isDone = $isDone;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function toggle(bool $flag): self
    {
        $this->isDone = $flag;

        return $this;
    }
}
