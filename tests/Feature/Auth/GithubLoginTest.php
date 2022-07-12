<?php

namespace Tests\Feature\Auth;

use Database\Seeders\ProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Tests\TestCase;

class GithubLoginTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        \URL::forceRootUrl(env('APP_AUTH_URL'));
    }

    /**
     * 서비스 제공자 리다이렉트
     *
     * @return void
     */
    public function testRedirect()
    {
        $response = $this->get('/login/github');

        $response->assertRedirectContains('https://github.com/login/oauth/authorize');
    }

    /**
     * 소셜 로그인
     *
     * @return void
     */
    public function testCallback()
    {
        $this->seed(ProviderSeeder::class);

        $githubUser = $this->createStub(SocialiteUser::class);

        $githubUser->email = $this->faker->safeEmail;
        $githubUser->name = $this->faker->name;
        $githubUser->id = Str::random();
        $githubUser->token = Str::random(32);
        $githubUser->refreshToken = null;

        Socialite::shouldReceive('driver->user')
            ->andReturn($githubUser);

        $response = $this->get("/login/github/callback");

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => $githubUser->email,
            'name' => $githubUser->name,
            'provider_uid' => $githubUser->id,
            'provider_token' => $githubUser->token,
            'provider_refresh_token' => $githubUser->refreshToken
        ]);

        $response->assertRedirect();
    }
}