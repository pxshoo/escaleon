<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_does_not_wait_for_google(): void
    {
        Http::preventStrayRequests();
        $user = User::factory()->create();
        $user->connectedAccounts()->create(['provider' => 'google', 'provider_id' => '123', 'token' => 'token']);
        $this->actingAs($user)->get('/dashboard')->assertOk();
        Http::assertNothingSent();
    }

    public function test_new_verified_google_user_can_login_without_workspace_permissions(): void
    {
        $identity = (new GoogleUser)->map(['id' => 'google-123', 'name' => 'Usuario', 'email' => 'new@example.com']);
        $identity->user = ['email_verified' => true];
        Socialite::shouldReceive('driver')->with('google')->andReturn($driver = \Mockery::mock());
        $driver->shouldReceive('setHttpClient')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($identity);
        $this->withSession(['google_login' => true])->get('/oauth/google/callback')->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'google_id' => 'google-123']);
        $this->assertDatabaseCount('connected_accounts', 0);
    }

    public function test_existing_email_is_not_automatically_linked(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);
        $identity = (new GoogleUser)->map(['id' => 'google-123', 'name' => 'Usuario', 'email' => 'existing@example.com']);
        $identity->user = ['email_verified' => true];
        Socialite::shouldReceive('driver')->with('google')->andReturn($driver = \Mockery::mock());
        $driver->shouldReceive('setHttpClient')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($identity);
        $this->withSession(['google_login' => true])->get('/oauth/google/callback')->assertRedirect('/login');
        $this->assertGuest();
    }
}
