<?php

namespace App\Http\Controllers;

use App\Services\GoogleWorkspaceService;
use App\Services\MicrosoftWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private function data(Request $request, bool $refresh): array
    {
        $connectedAccounts = $request->user()->connectedAccounts()->get()->keyBy('provider');
        $account = $connectedAccounts->get('google');
        $emails = $events = $tasks = [];
        if ($account) {
            $key = 'workspace:'.$account->user_id.':'.$account->id;
            $data = Cache::get($key, ['emails' => [], 'events' => [], 'tasks' => []]);
            if ($refresh) {
                $service = new GoogleWorkspaceService($account);
                $data = ['emails' => $service->getRecentEmails(4), 'events' => $service->getUpcomingEvents(4), 'tasks' => $service->getTasks(4)];
                Cache::put($key, $data, now()->addMinutes(10));
            }
            ['emails' => $emails, 'events' => $events, 'tasks' => $tasks] = $data;
        }

        $outlook = ['emails' => [], 'events' => [], 'tasks' => []];
        if ($microsoft = $connectedAccounts->get('microsoft')) {
            $key = 'workspace:'.$microsoft->user_id.':'.$microsoft->id;
            $outlook = Cache::get($key, $outlook);
            if ($refresh) {
                $service = new MicrosoftWorkspaceService($microsoft);
                $outlook = ['emails' => $service->getTodayEmails(4), 'events' => $service->getUpcomingEvents(4), 'tasks' => $service->getPendingTasks(8)];
                $outlook['chats'] = $service->getUnreadChats();
                $outlook['teams_status'] = $service->teamsStatus;
                Cache::put($key, $outlook, now()->addMinutes(10));
            }
        }

        return compact('connectedAccounts', 'emails', 'events', 'tasks', 'outlook');
    }

    public function index(Request $request)
    {
        return view('dashboard', $this->data($request, false));
    }

    public function refresh(Request $request)
    {
        $data = $this->data($request, true);
        $activities = [];
        foreach (['google' => ['emails' => $data['emails'], 'events' => $data['events'], 'tasks' => $data['tasks']], 'microsoft' => $data['outlook']] as $provider => $groups) {
            foreach ($groups as $type => $items) {
                if (! is_array($items)) {
                    continue;
                }
                foreach ($items as $item) {
                    $identity = $item['id'] ?? $item['link'] ?? ($item['title'] ?? json_encode($item));
                    $link = $item['link'] ?? null;
                    $activities[] = [
                        'key' => hash('sha256', $provider.':'.$type.':'.$identity),
                        'type' => $type, 'provider' => $provider,
                        'href' => is_string($link) && filter_var($link, FILTER_VALIDATE_URL) && parse_url($link, PHP_URL_SCHEME) === 'https'
                            ? $link : '#'.$provider.'-'.$type,
                    ];
                }
            }
        }

        return response()->json([
            'html' => view('dashboard', $data)->fragment('workspace-data'),
            'counts' => [count($data['emails']), count($data['events']), count($data['tasks'])],
            'activities' => $activities,
        ])->header('Cache-Control', 'no-store');
    }
}
