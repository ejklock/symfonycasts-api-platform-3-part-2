<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class DragonTreasureSetOwnerProcessor implements ProcessorInterface
{
    private ProcessorInterface $processor;
    private Security $security;

    public function __construct(ProcessorInterface $innerProcessor, Security $security)
    {
        $this->processor = $innerProcessor;
        $this->security = $security;
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($data instanceof DragonTreasure && $data->getOwner() === null && $this->security->getUser()) {
            $data->setOwner($this->security->getUser());
        }
        $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
