<?php

namespace App\Services;

use App\Models\ConnectedAccount;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftWorkspaceService
{
    private bool $refreshed = false;

    public function __construct(private ConnectedAccount $account) {}

    private function refreshToken(): bool
    {
        if ($this->refreshed || ! $this->account->refresh_token) {
            return false;
        }
        $this->refreshed = true;
        $tenant = rawurlencode(config('services.azure.tenant'));
        $response = Http::asForm()->connectTimeout(3)->timeout(8)
            ->post('https://login.microsoftonline.com/'.$tenant.'/oauth2/v2.0/token', [
                'client_id' => config('services.azure.client_id'), 'client_secret' => config('services.azure.client_secret'),
                'refresh_token' => $this->account->refresh_token, 'grant_type' => 'refresh_token',
            ]);
        if (! $response->successful() || ! $response->json('access_token')) {
            return false;
        }
        $this->account->forceFill([
            'token' => $response->json('access_token'),
            'refresh_token' => $response->json('refresh_token') ?: $this->account->refresh_token,
            'expires_at' => now()->addSeconds((int) $response->json('expires_in', 3600)),
        ])->save();

        return true;
    }

    private function get(string $path, array $query = []): array
    {
        try {
            if ($this->account->expires_at?->isPast() && ! $this->refreshToken()) {
                return [];
            }
            $send = fn () => Http::withToken($this->account->token)->acceptJson()
                ->withHeaders(['Prefer' => 'outlook.timezone="UTC"'])->connectTimeout(3)->timeout(6)
                ->get('https://graph.microsoft.com/v1.0/'.$path, $query);
            $response = $send();
            if ($response->status() === 401 && $this->refreshToken()) {
                $response = $send();
            }
            if (! $response->successful()) {
                Log::warning('Microsoft Graph: consulta rechazada.', ['status' => $response->status(), 'endpoint' => $path]);

                return [];
            }

            return $response->json('value', []);
        } catch (ConnectionException) {
            return [];
        }
    }

    public function getTodayEmails(int $limit = 10): array
    {
        $start = now('America/Santiago')->startOfDay()->utc();
        $end = $start->copy()->addDay();
        $data = $this->get('me/mailFolders/inbox/messages', [
            '$filter' => 'receivedDateTime ge '.$start->toIso8601ZuluString().' and receivedDateTime lt '.$end->toIso8601ZuluString(),
            '$orderby' => 'receivedDateTime desc', '$top' => $limit,
            '$select' => 'id,subject,from,receivedDateTime,bodyPreview',
        ]);

        return array_map(fn ($email) => [
            'subject' => $email['subject'] ?? '(Sin asunto)',
            'from' => $email['from']['emailAddress']['address'] ?? '',
            'snippet' => $email['bodyPreview'] ?? '', 'date' => $email['receivedDateTime'] ?? null,
        ], $data);
    }

    public function getUpcomingEvents(int $limit = 10): array
    {
        $data = $this->get('me/calendarView', [
            'startDateTime' => now()->utc()->toIso8601ZuluString(),
            'endDateTime' => now()->utc()->addDays(30)->toIso8601ZuluString(),
            '$orderby' => 'start/dateTime', '$top' => $limit,
            '$select' => 'subject,start,end,isAllDay,isCancelled',
        ]);

        return array_values(array_map(fn ($event) => [
            'summary' => $event['subject'] ?? '(Sin título)',
            'start' => ! empty($event['isAllDay']) ? null : Carbon::parse($event['start']['dateTime'], 'UTC')->setTimezone('America/Santiago')->toIso8601String(),
        ], array_filter($data, fn ($event) => empty($event['isCancelled']))));
    }

    public function getPendingTasks(int $limit = 20): array
    {
        $tasks = [];
        foreach ($this->get('me/todo/lists', ['$top' => 20]) as $list) {
            foreach ($this->get('me/todo/lists/'.rawurlencode($list['id']).'/tasks', ['$filter' => "status ne 'completed'", '$top' => 100]) as $task) {
                $due = isset($task['dueDateTime']['dateTime']) ? Carbon::parse($task['dueDateTime']['dateTime'], $task['dueDateTime']['timeZone'] ?? 'UTC')->toDateString() : null;
                $tasks[] = ['title' => $task['title'] ?? '(Sin título)', 'due' => $due,
                    'overdue' => $due !== null && $due < now('America/Santiago')->toDateString()];
            }
            if (count($tasks) >= $limit) {
                break;
            }
        }
        usort($tasks, fn ($a, $b) => ($b['overdue'] <=> $a['overdue']) ?: strcmp($a['due'] ?? '9999', $b['due'] ?? '9999'));

        return array_slice($tasks, 0, $limit);
    }
}
