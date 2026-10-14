<?php

namespace Tests\Feature;

use App\Models\ConnectedAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleDisconnectTest extends TestCase
{
    use RefreshDatabase;

    public function test_disconnect_only_removes_current_users_google_connection(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        foreach ([[$user, 'google'], [$user, 'microsoft'], [$other, 'google']] as [$owner, $provider]) {
            ConnectedAccount::create([
                'user_id' => $owner->id, 'provider' => $provider,
                'provider_id' => $owner->id.'-'.$provider, 'token' => 'test-token',
            ]);
        }
        Http::preventStrayRequests();
        $this->actingAs($user)->withSession(['assistant_reply' => 'Datos anteriores'])
            ->delete(route('oauth.google.disconnect'))->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'Cuenta de Google desconectada.')
            ->assertSessionMissing('assistant_reply');
        $this->assertDatabaseMissing('connected_accounts', ['user_id' => $user->id, 'provider' => 'google']);
        $this->assertDatabaseHas('connected_accounts', ['user_id' => $user->id, 'provider' => 'microsoft']);
        $this->assertDatabaseHas('connected_accounts', ['user_id' => $other->id, 'provider' => 'google']);
        $this->get('/dashboard')->assertOk()->assertSee('No vinculado')->assertSee('Conectar')->assertDontSee('Gmail (Recientes)');
    }

    public function test_guest_cannot_disconnect(): void
    {
        $this->delete(route('oauth.google.disconnect'))->assertRedirect(route('login'));
    }
}
