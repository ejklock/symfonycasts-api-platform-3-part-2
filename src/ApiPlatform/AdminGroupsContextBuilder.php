<?php

namespace App\ApiPlatform;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Request;


#[AsDecorator('api_platform.serializer.context_builder')]
class AdminGroupsContextBuilder implements SerializerContextBuilderInterface
{
    private SerializerContextBuilderInterface $decorated;
    private Security $security;

    public function __construct(SerializerContextBuilderInterface $decorated, Security $security)
    {
        $this->decorated = $decorated;
        $this->security = $security;
    }

    private function addAdminRulesToNormalizationContextIfAdminIsAuthenticated(array $context, bool $isNormalizationContext = false): array
    {
        if (isset($context['groups']) && $this->security->isGranted('ROLE_ADMIN')) {
            $context['groups'][] = $isNormalizationContext ? 'admin:read' : 'admin:write';
        }

        return $context;
    }
    public function createFromRequest(
        Request $request,
        bool $normalization,
        ?array $extractedAttributes = null
    ): array {;

        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        return $this->addAdminRulesToNormalizationContextIfAdminIsAuthenticated($context, $normalization);
    }
}
