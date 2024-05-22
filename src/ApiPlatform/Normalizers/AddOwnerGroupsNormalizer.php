<?php

namespace App\ApiPlatform\Normalizers;

use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsDecorator('api_platform.jsonld.normalizer.item')]
class AddOwnerGroupsNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    private NormalizerInterface $normalizer;

    private Security $security;

    public function __construct(NormalizerInterface $normalizerInterface, Security $security)
    {
        $this->normalizer = $normalizerInterface;
        $this->security = $security;
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if ($this->normalizer instanceof SerializerAwareInterface) {

            $this->normalizer->setSerializer($serializer);
        }
    }

    protected function addOwnerReadToContextGroupIfAuthenticatedUserIsOwner($context, $object)
    {
        if ($object instanceof DragonTreasure && $this->security->getUser() === $object->getOwner()) {

            $context['groups'][] = 'owner:read';
        }

        return $context;
    }

    protected function addIsMineFieldItAuthenticatedUserIsOwner(array|string|int|float|bool|\ArrayObject|null $normalized, $object)
    {
        if ($object instanceof DragonTreasure && $this->security->getUser() === $object->getOwner()) {
            $normalized['isMine'] = true;
        }

        return $normalized;
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {

        $context = $this->addOwnerReadToContextGroupIfAuthenticatedUserIsOwner($context, $object);

        $normalized = $this->normalizer->normalize($object, $format, $context);

        return $this->addIsMineFieldItAuthenticatedUserIsOwner($normalized, $object);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->normalizer->supportsNormalization($data, $format, $context);
    }
}
