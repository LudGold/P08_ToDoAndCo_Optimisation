<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 *
 * @UniqueEntity(fields={"email"}, message="Cet email est déjà utilisé.")
 */
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25)
     *
     * @Assert\NotBlank(message="Le nom d'utilisateur ne peut pas être vide.")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     *
     * @Assert\NotBlank(message="Le mot de passe ne doit pas être vide.")
     *
     * @Assert\Length(
     *     min=8,
     *     minMessage="Le mot de passe doit comporter au moins {{ limit }} caractères"
     * )
     *
     * @Assert\Regex(
     *     pattern="/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/",
     *     message="Le mot de passe doit comporter au moins une majuscule, une minuscule, un chiffre et un caractère spécial."
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @Assert\NotBlank(message="L'email ne doit pas être vide.")
     *
     * @Assert\Email(message="L'email '{{ value }}' n'est pas valide.")
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="author", orphanRemoval=true)
     */
    private $tasks;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tokenExpiryDate;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Implémentation de UserInterface : retourne le nom d'utilisateur (identifiant).
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): ?array
    {
        $roles = $this->roles;
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * Efface les informations sensibles après l'authentification (si nécessaire).
     */
    public function eraseCredentials()
    {
        // Si tu stockes des informations sensibles.
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setAuthor($this);
        }

        return $this;
    }
 /**
     * Récupère le token de réinitialisation du mot de passe.
     *
     * @return string|null
     */
    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    /**
     * Définit le token de réinitialisation du mot de passe.
     *
     * @param string|null $resetToken
     * @return $this
     */
    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    /**
     * Récupère la date d'expiration du token de réinitialisation.
     *
     * @return \DateTimeInterface|null
     */
    public function getTokenExpiryDate(): ?\DateTimeInterface
    {
        return $this->tokenExpiryDate;
    }

    /**
     * Définit la date d'expiration du token de réinitialisation.
     *
     * @param \DateTimeInterface|null $tokenExpiryDate
     * @return $this
     */
    public function setTokenExpiryDate(?\DateTimeInterface $tokenExpiryDate): self
    {
        $this->tokenExpiryDate = $tokenExpiryDate;

        return $this;
    }

    /**
     * Vérifie si le token de réinitialisation est expiré.
     *
     * @return bool
     */
    public function isTokenExpired(): bool
    {
        return $this->tokenExpiryDate <= new \DateTime();
    }
}
