<?php

namespace App\Security\Voter;

use App\Entity\Product;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductVoter extends Voter
{
    const UPDATE = 'PRODUCT_UPDATE';
    const DELETE = 'PRODUCT_DELETE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $product): bool
    {
        if (!in_array($attribute, [self::UPDATE, self::DELETE])) {
            return false;
        }
        if (!$product instanceof Product) {
            return false;
        }
        return true;
    }

    protected function voteOnattribute(string $attribute, mixed $product, TokenInterface $token): bool
    {
        // On récupère l'utilisateur à partir du token
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // On vérifie si l'utilisateur est admin
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // On vérifie les permissions
        switch($attribute){
            case self::UPDATE:
                // On vérifie si l'utilisateur peut éditer
                return $this->canUpdate();
                break;
            case self::DELETE:
                // On vérifie si l'utilisateur peut supprimer
                return $this->canDelete();
                break;
        }
    }

    private function canUpdate()
    {
        return $this->security->isGranted('ROLE_PRODUCT_ADMIN');
    }

    private function canDelete()
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}