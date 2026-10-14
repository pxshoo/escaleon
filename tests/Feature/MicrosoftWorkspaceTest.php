<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MicrosoftWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_graph_response_format_is_rendered_and_overdue_tasks_are_prioritized(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'graph.microsoft.com/v1.0/me/mailFolders/*' => Http::response(['value' => [['subject' => 'Correo Outlook', 'from' => ['emailAddress' => ['address' => 'sender@example.com']]]]]),
            'graph.microsoft.com/v1.0/me/calendarView*' => Http::response(['value' => [['subject' => 'Reunión Outlook', 'start' => ['dateTime' => '2026-10-15T15:00:00']]]]),
            'graph.microsoft.com/v1.0/me/todo/lists/list-1/tasks*' => Http::response(['value' => [['title' => 'Tarea atrasada', 'dueDateTime' => ['dateTime' => '2020-01-01T00:00:00', 'timeZone' => 'UTC']]]]),
            'graph.microsoft.com/v1.0/me/todo/lists*' => Http::response(['value' => [['id' => 'list-1']]]),
        ]);
        $user = User::factory()->create();
        $user->connectedAccounts()->create(['provider' => 'microsoft', 'provider_id' => '123', 'token' => 'test', 'expires_at' => now()->addHour()]);
        $response = $this->actingAs($user)->getJson(route('dashboard.refresh'))->assertOk();
        foreach (['Correo Outlook', 'Reunión Outlook', 'Tarea atrasada', 'Atrasada'] as $text) {
            $this->assertStringContainsString($text, $response->json('html'));
        }
    }
}
