<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GestionEquiposLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_configured_url_is_opened(): void
    {
        config(['services.gestion_equipos.url' => 'https://equipos.example.com/dashboard']);
        $this->actingAs(User::factory()->create())->get(route('gestion-equipos'))
            ->assertRedirect('https://equipos.example.com/dashboard');
    }

    public function test_missing_url_shows_configuration_message(): void
    {
        config(['services.gestion_equipos.url' => null]);
        $this->actingAs(User::factory()->create())->get(route('gestion-equipos'))
            ->assertRedirect(route('dashboard'))->assertSessionHas('status');
    }
}
