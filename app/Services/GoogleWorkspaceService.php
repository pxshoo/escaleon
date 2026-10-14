<?php

namespace App\Services;

use App\Models\ConnectedAccount;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GoogleWorkspaceService
{
    private bool $refreshed = false;

    public function __construct(private ?ConnectedAccount $account) {}

    public function isConnected(): bool
    {
        return (bool) $this->account?->token;
    }

    private function refreshToken(): bool
    {
        if ($this->refreshed || ! $this->account?->refresh_token) {
            return false;
        }
        $this->refreshed = true;
        try {
            $response = Http::asForm()->connectTimeout(3)->timeout(6)->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'), 'client_secret' => config('services.google.client_secret'),
                'refresh_token' => $this->account->refresh_token, 'grant_type' => 'refresh_token',
            ]);
            if (! $response->successful() || ! $response->json('access_token')) {
                return false;
            }
            $this->account->forceFill(['token' => $response->json('access_token'),
                'expires_at' => now()->addSeconds((int) $response->json('expires_in', 3600)),
                'refresh_token' => $response->json('refresh_token') ?: $this->account->refresh_token])->save();

            return true;
        } catch (ConnectionException) {
            return false;
        }
    }

    private function get(string $url, array $query): array
    {
        if (! $this->isConnected()) {
            return [];
        }
        if ($this->account->expires_at?->isPast() && ! $this->refreshToken()) {
            return [];
        }
        try {
            $response = Http::withToken($this->account->token)->connectTimeout(2)->timeout(4)->get($url, $query);
            if ($response->status() === 401 && $this->refreshToken()) {
                $response = Http::withToken($this->account->token)->connectTimeout(2)->timeout(4)->get($url, $query);
            }

            return $response->successful() ? ($response->json() ?? []) : [];
        } catch (ConnectionException) {
            return [];
        }
    }

    public function getRecentEmails(int $limit = 5): array
    {
        if ($limit < 1) {
            return [];
        }
        $base = 'https://gmail.googleapis.com/gmail/v1/users/me/messages';
        $list = $this->get($base, ['maxResults' => min($limit, 500), 'labelIds' => 'INBOX']);
        $emails = [];
        foreach ($list['messages'] ?? [] as $message) {
            if (empty($message['id'])) {
                continue;
            }
            $detail = $this->get($base.'/'.rawurlencode($message['id']), ['format' => 'metadata']);
            if (! $detail) {
                continue;
            }
            $headers = [];
            foreach ($detail['payload']['headers'] ?? [] as $header) {
                $headers[strtolower($header['name'])] = $header['value'];
            }
            $emails[] = ['subject' => $headers['subject'] ?? '(Sin asunto)', 'from' => $headers['from'] ?? '',
                'id' => $message['id'], 'snippet' => $detail['snippet'] ?? '', 'date' => $headers['date'] ?? ''];
        }

        return $emails;
    }

    public function getUpcomingEvents(int $limit = 5): array
    {
        if ($limit < 1) {
            return [];
        }
        $data = $this->get('https://www.googleapis.com/calendar/v3/calendars/primary/events', [
            'maxResults' => min($limit, 2500), 'timeMin' => now()->toRfc3339String(), 'singleEvents' => 'true', 'orderBy' => 'startTime',
        ]);

        return array_map(fn ($event) => ['id' => $event['id'] ?? '', 'summary' => $event['summary'] ?? '(Sin título)',
            'start' => $event['start']['dateTime'] ?? null, 'link' => $event['htmlLink'] ?? '#'], $data['items'] ?? []);
    }

    public function getTasks(int $limit = 5): array
    {
        if ($limit < 1) {
            return [];
        }
        $data = $this->get('https://tasks.googleapis.com/tasks/v1/lists/@default/tasks', [
            'maxResults' => min($limit, 100), 'showCompleted' => 'false', 'showHidden' => 'false',
        ]);

        return array_map(fn ($task) => ['title' => $task['title'] ?? '(Sin título)'], $data['items'] ?? []);
    }
}
