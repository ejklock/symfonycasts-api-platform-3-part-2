<?php

namespace App\Tests\Functional;

use App\Entity\ApiToken;
use App\Entity\DragonTreasure;
use App\Factory\ApiTokenFactory;
use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\Json;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DragonTreasureResourceTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    const TREASURE_API_URI = '/api/treasures';


    public function testGetCollectionOfTreasures(): void
    {

        DragonTreasureFactory::createMany(10, [
            'isPublished' => true
        ]);

        DragonTreasureFactory::createOne(['isPublished' => false]);

        $json = $this->browser()
            ->get(self::TREASURE_API_URI)
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 10)
            ->json();

        $this->assertSame(array_keys($json->decoded()['hydra:member'][0]), [
            '@id',
            '@type',
            'name',
            'description',
            'value',
            'coolFactor',
            'owner',
            'shortDescription',
            'plunderedAtAgo'
        ]);
    }

    public function testGetOnePublishedTreasure404s(): void
    {
        $dragonTreasure = DragonTreasureFactory::createOne(['isPublished' => false]);

        $this->browser()
            ->get(self::TREASURE_API_URI . "/" . $dragonTreasure->getId())
            ->assertStatus(404);
    }

    public function testPostToCreateTreasure(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->post(
                self::TREASURE_API_URI,
                [
                    'json' => []
                ]
            )->assertStatus(422)
            ->post(
                '/api/treasures',
                HttpOptions::json([
                    'name' => 'foo',
                    'description' => 'bar',
                    'value' => 100,
                    'coolFactor' => 5
                ])
            )
            ->assertStatus(201)
            ->assertJsonMatches('name', 'foo');
    }

    public function testPostToCreateTreasureWithApiKey(): void
    {
        $token = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_CREATE]
        ]);

        $this->browser()
            ->post(
                self::TREASURE_API_URI,
                [
                    'json' => [],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token->getToken()
                    ]
                ]
            )->assertStatus(422);
    }

    public function testPostToCreateTreasureDeniedWithoutScope(): void
    {
        $token = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT]
        ]);

        $this->browser()
            ->post(
                self::TREASURE_API_URI,
                [
                    'json' => [],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token->getToken()
                    ]
                ]
            )->assertStatus(403);
    }

    public function testPatchToUpdateTreasure(): void
    {

        $user = UserFactory::createOne();
        $dragonTreasure = DragonTreasureFactory::createOne([
            'owner' => $user
        ]);

        $this->browser()
            ->actingAs($user)
            ->patch(
                self::TREASURE_API_URI . "/" . $dragonTreasure->getId(),
                HttpOptions::json([
                    'value' => 12345,
                ])
            )
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345);

        $user2 = UserFactory::createOne();

        $this->browser()
            ->actingAs($user2)
            ->patch(
                self::TREASURE_API_URI . "/" . $dragonTreasure->getId(),
                HttpOptions::json([
                    'value' => 6789,
                ])
            )
            ->assertStatus(403);
    }

    public function testPatchUnpublishedWorks()
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'owner' => $user,
            'isPublished' => false,
        ]);

        $this->browser()
            ->actingAs($user)
            ->patch('/api/treasures/' . $treasure->getId(), [
                'json' => [
                    'value' => 12345,
                ],
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345);
    }


    public function testAdminUserCanPatchTreasure(): void
    {
        $user = UserFactory::new()->asAdmin()->create();
        $dragonTreasure = DragonTreasureFactory::createOne([
            'isPublished' => true
        ]);

        $this->browser()
            ->actingAs($user)
            ->patch(
                self::TREASURE_API_URI . "/" . $dragonTreasure->getId(),
                HttpOptions::json([
                    'value' => 12345,
                ])
            )
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345)
            ->assertJsonMatches('isPublished', true);
    }

    public function testOwnerCanSeeIsPublishedAndIsMineField(): void
    {
        $user = UserFactory::createOne();
        $dragonTreasure = DragonTreasureFactory::createOne([
            'isPublished' => true,
            'owner' => $user
        ]);

        $this->browser()
            ->actingAs($user)
            ->patch(
                self::TREASURE_API_URI . "/" . $dragonTreasure->getId(),
                HttpOptions::json([
                    'value' => 12345,
                ])
            )
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345)
            ->assertJsonMatches('isMine', true)
            ->assertJsonMatches('isPublished', true);
    }
}
