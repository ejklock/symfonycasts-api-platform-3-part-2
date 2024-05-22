<?php

namespace App\Validator;

use App\Entity\DragonTreasure;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TreasuresAllowedOwnerChangeValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManagerInterface, Security $security)
    {
        $this->entityManager = $entityManagerInterface;
        $this->security = $security;
    }
    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof TreasuresAllowedOwnerChange);

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof Collection);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }


        $unitOfWork = $this->entityManager->getUnitOfWork();

        foreach ($value as $dragonTreasure) {
            assert($dragonTreasure instanceof DragonTreasure);

            $originalData = $unitOfWork->getOriginalEntityData($dragonTreasure);

            $originalOwnerId = $originalData['owner_id'];

            $newOwner = $dragonTreasure->getOwner()->getId();

            if (!$originalOwnerId || $originalOwnerId === $newOwner) {

                return;
            }
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
