<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class UserHashPasswordProcessor implements ProcessorInterface
{

    private ProcessorInterface $innerProcessor;
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(ProcessorInterface $innerProcessor, UserPasswordHasherInterface $passwordHasher)
    {
        $this->innerProcessor = $innerProcessor;
        $this->passwordHasher = $passwordHasher;
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($data instanceof User && $data->getPlainPassword()) {

            $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPlainPassword()));
        }

        $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
