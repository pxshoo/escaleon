<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_does_not_consume_assistant_reply(): void
    {
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create())->withSession(['assistant_reply' => 'Mi respuesta'])
            ->getJson(route('dashboard.refresh'))->assertOk()
            ->assertJsonPath('counts', [0, 0, 0])->assertSessionHas('assistant_reply', 'Mi respuesta');
    }

    public function test_refresh_requires_login(): void
    {
        $this->getJson(route('dashboard.refresh'))->assertUnauthorized();
    }
}
