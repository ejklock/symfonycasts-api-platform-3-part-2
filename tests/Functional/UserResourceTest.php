<?php

namespace App\Tests\Functional;

use App\Entity\DragonTreasure;
use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserResourceTest extends ApiTestCase
{

    use Factories;
    use ResetDatabase;


    const USER_RESOURCE_URI = '/api/users';


    public function testPostCreateUser(): void
    {

        $this->browser()
            ->post(self::USER_RESOURCE_URI, HttpOptions::json([
                'username' => 'a',
                'email' => 'a@b.com',
                'password' => 'password',
            ]))
            ->assertStatus(201)
            ->post('/login', HttpOptions::json([
                'email' => 'a@b.com',
                'password' => 'password',
            ]))
            ->assertSuccessful();
    }

    public function testPatchUpdateUser(): void
    {

        $user = UserFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->patch(self::USER_RESOURCE_URI . "/" . $user->getId(), [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'json' => [
                    'username' => 'changed'
                ],

            ])
            ->assertSuccessful()
            ->assertJsonMatches('username', 'changed');
    }

    public function testTreasuresCannotBeStolen(): void
    {

        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();

        $dragonTreasure = DragonTreasureFactory::createOne([
            'owner' => $otherUser
        ]);

        $this->browser()
            ->actingAs($user)
            ->patch(self::USER_RESOURCE_URI . "/" . $user->getId(), [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'json' => [
                    'username' => 'changed',
                    'dragonTreasures' => [
                        '/api/treasures/' . $dragonTreasure->getId()
                    ]
                ],

            ])
            ->assertStatus(422);
    }

    public function testPatchUnpublishedTreasuresNotReturned(): void
    {

        $user = UserFactory::createOne();

        DragonTreasureFactory::createOne([
            'isPublished' => false,
            'owner' => $user
        ]);

        $this->browser()
            ->actingAs(UserFactory::createOne())
            ->get(self::USER_RESOURCE_URI . "/" . $user->getId())
            ->assertJsonMatches('length("dragonTreasures")', 0);
    }
}
