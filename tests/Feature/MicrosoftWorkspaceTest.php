<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MicrosoftWorkspaceService;
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
            'graph.microsoft.com/v1.0/me/chats*' => Http::response([], 403),
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

    public function test_teams_only_shows_unread_messages_from_other_people(): void
    {
        $user = User::factory()->create();
        $account = $user->connectedAccounts()->create(['provider' => 'microsoft', 'provider_id' => 'self', 'token' => 'token']);
        $chat = [
            'id' => 'chat-1', 'topic' => 'Proyecto', 'viewpoint' => ['lastMessageReadDateTime' => '2026-10-15T10:00:00Z'],
            'lastMessagePreview' => ['id' => 'message-1', 'messageType' => 'message', 'createdDateTime' => '2026-10-15T11:00:00Z',
                'from' => ['user' => ['id' => 'other', 'displayName' => 'Ana']], 'body' => ['content' => '<b>Hola</b>']],
        ];
        $own = $chat;
        $own['lastMessagePreview']['from']['user']['id'] = 'self';
        $read = $chat;
        $read['viewpoint']['lastMessageReadDateTime'] = '2026-10-15T12:00:00Z';
        Http::fake(['*' => Http::response(['value' => [$chat, $own, $read]])]);
        $service = new MicrosoftWorkspaceService($account);
        $messages = $service->getUnreadChats();
        $this->assertCount(1, $messages);
        $this->assertSame('Ana', $messages[0]['from']);
        $this->assertSame('Hola', $messages[0]['preview']);
        $this->assertSame('ready', $service->teamsStatus);
    }
}
