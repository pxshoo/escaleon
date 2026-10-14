<?php

namespace Tests\Feature;

use App\Models\ConnectedAccount;
use App\Models\User;
use App\Services\GoogleWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleWorkspaceServiceTest extends TestCase
{
    use RefreshDatabase;

    private function account(array $attributes = []): ConnectedAccount
    {
        return ConnectedAccount::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'provider' => 'google',
            'provider_id' => 'google-user',
            'token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'expires_at' => now()->addHour(),
        ], $attributes));
    }

    public function test_dashboard_loads_google_data(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'gmail.googleapis.com/gmail/v1/users/me/messages?*' => Http::response(['messages' => [['id' => 'message-1']]]),
            'gmail.googleapis.com/gmail/v1/users/me/messages/message-1*' => Http::response(['payload' => ['headers' => [
                ['name' => 'Subject', 'value' => 'Correo de prueba'],
                ['name' => 'From', 'value' => 'sender@example.com'],
            ]]]),
            'www.googleapis.com/calendar/*' => Http::response(['items' => [
                ['summary' => 'Reunión de prueba', 'start' => ['dateTime' => now()->addHour()->toRfc3339String()]],
                ['summary' => 'Evento diario', 'start' => ['date' => '2026-10-15']],
            ]]),
            'tasks.googleapis.com/*' => Http::response(['items' => [['title' => 'Tarea de prueba']]]),
        ]);

        $account = $this->account();
        $response = $this->actingAs($account->user)->getJson(route('dashboard.refresh'))->assertOk();
        $html = $response->json('html');
        $this->assertStringContainsString('Correo de prueba', $html);
        $this->assertStringContainsString('Reunión de prueba', $html);
        $this->assertStringContainsString('Todo el día', $html);
        $this->assertStringContainsString('Tarea de prueba', $html);
    }

    public function test_dashboard_still_loads_when_google_rejects_requests(): void
    {
        Http::fake(['*' => Http::response([], 403)]);
        $account = $this->account();

        $this->actingAs($account->user)->get('/dashboard')->assertOk()
            ->assertViewHas('emails', [])->assertViewHas('events', [])->assertViewHas('tasks', []);
    }

    public function test_expired_token_is_refreshed_and_saved(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'new-token', 'expires_in' => 3600]),
            'tasks.googleapis.com/*' => Http::response(['items' => [['title' => 'Pendiente']]]),
        ]);
        $account = $this->account(['expires_at' => now()->subMinute()]);

        $this->assertSame([['title' => 'Pendiente']], (new GoogleWorkspaceService($account))->getTasks());
        $this->assertSame('new-token', $account->fresh()->token);
        $this->assertSame('refresh-token', $account->fresh()->refresh_token);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'tasks.googleapis.com')
            && $request->hasHeader('Authorization', 'Bearer new-token'));
    }

    public function test_unauthorized_request_is_retried_after_refresh(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'new-token']),
            'tasks.googleapis.com/*' => Http::sequence()->push([], 401)->push(['items' => [['title' => 'Pendiente']]]),
        ]);

        $this->assertCount(1, (new GoogleWorkspaceService($this->account()))->getTasks());
        Http::assertSentCount(3);
    }

    public function test_connection_failure_returns_empty_data(): void
    {
        Http::fake(['*' => Http::failedConnection()]);

        $this->assertSame([], (new GoogleWorkspaceService($this->account()))->getTasks());
    }
}
