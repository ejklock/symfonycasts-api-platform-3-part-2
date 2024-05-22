<?php

namespace App\Validator;

use App\Entity\User;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidOwnerValidator extends ConstraintValidator
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public function validate($value, Constraint $constraint)
    {

        assert($constraint instanceof IsValidOwner);
        /* @var App\Validator\IsValidOwner $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof User);

        $user = $this->security->getUser();

        if (!$user) {
            throw new LogicException('IsOwnerValidator requires a user to be authenticated');
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }


        if ($value !== $user) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
