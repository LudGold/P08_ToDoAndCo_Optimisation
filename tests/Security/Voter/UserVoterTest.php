<?php

namespace App\tests\Security\Voter;

use App\Entity\User;
use App\Security\Voter\UserVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoterTest extends TestCase
{
    /** @var AuthorizationCheckerInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $authChecker;

    private $voter;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Création du mock AuthorizationCheckerInterface
        $this->authChecker = $this->createMock(AuthorizationCheckerInterface::class);

        // Initialisation de UserVoter avec le mock
        $this->voter = new UserVoter($this->authChecker);

        // Création du mock TokenInterface
        $this->token = $this->createMock(TokenInterface::class);
    }

    public function testNormalUserCannotEditRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        // Configurer le token pour retourner l'utilisateur
        $this->token->method('getUser')->willReturn($user);

        // Simuler que l'utilisateur connecté n'est pas admin
        $this->authChecker->method('isGranted')->willReturn(false);

        // Vérifier que l'utilisateur ne peut pas modifier les rôles
        $result = $this->voter->vote($this->token, ['ROLE_USER'], ['EDIT_ROLES']);
        $this->assertEquals(Voter::ACCESS_DENIED, $result);
    }

    public function testAdminCanEditRoles(): void
    {
        $admin = new User();
        $admin->setRoles(['ROLE_ADMIN']);

        // Configurer le token pour retourner l'admin
        $this->token->method('getUser')->willReturn($admin);

        // Simuler que l'utilisateur connecté est admin
        $this->authChecker->method('isGranted')->willReturn(true);

        // Vérifier que l'administrateur peut modifier les rôles
        $result = $this->voter->vote($this->token, ['ROLE_USER'], ['EDIT_ROLES']);
        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }
}
