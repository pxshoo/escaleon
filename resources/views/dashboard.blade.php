<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tu espacio de trabajo') }}
        </h2>
    </x-slot>

    <div class="py-10 escaleon-dashboard" data-dashboard-refresh-url="{{ route('dashboard.refresh') }}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <section class="escaleon-hero">
                <div>
                    <span class="escaleon-eyebrow">ESCALEON HUB · TU DÍA, EN ORDEN</span>
                    <h1>Hola, {{ explode(' ', auth()->user()->name)[0] }}<span class="text-violet-300">.</span></h1>
                    <p>Menos pestañas. Más claridad. Tu agenda, tus correos y tu asistente en un solo lugar.</p>
                    <a href="#centro-control" class="escaleon-hero-link">Organizar mi día <span aria-hidden="true">↗</span></a>
                </div>
                <div class="escaleon-hero-note">
                    <span class="escaleon-live-dot" aria-hidden="true"></span>
                    <span>{{ $connectedAccounts->count() }} {{ $connectedAccounts->count() === 1 ? 'cuenta conectada' : 'cuentas conectadas' }}</span>
                    <p>Tu próximo paso empieza con una pregunta.</p>
                </div>
            </section>

            @if(isset($connectedAccounts['google']))
                <div class="escaleon-stats">
                    <div><span class="escaleon-stat-symbol text-rose-500" aria-hidden="true">✉</span><p>Correos recientes <strong>{{ count($emails) }}</strong><small>En la vista de Gmail</small></p></div>
                    <div><span class="escaleon-stat-symbol text-blue-500" aria-hidden="true">▦</span><p>Próximos eventos <strong>{{ count($events) }}</strong><small>En tu calendario de Google</small></p></div>
                    <div><span class="escaleon-stat-symbol text-amber-500" aria-hidden="true">✓</span><p>Tareas en la vista <strong>{{ count($tasks) }}</strong><small>Desde Google Tasks</small></p></div>
                </div>
            @endif

            @if (session('status'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded shadow-sm">
                    <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
                </div>
            @endif

            <!-- Tarjetas de Conexión OAuth -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <span class="escaleon-section-label">01 / CONEXIONES</span>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Tus cuentas, conectadas</h3>
                <p class="text-sm text-gray-500 mb-6">Vincula tus cuentas para que Escaleon pueda sincronizar tu agenda, correos y tareas.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Google Workspace -->
                    <div class="border rounded-lg p-5 flex items-center justify-between {{ isset($connectedAccounts['google']) ? 'border-emerald-200 bg-emerald-50/30' : 'border-gray-200' }}">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 flex items-center justify-center rounded-full bg-white shadow-sm border border-gray-100">
                                <svg class="w-6 h-6" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.36 24 12 24z"/>
                                    <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.98 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.36 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Google Workspace</h4>
                                <p class="text-xs text-gray-500">Gmail, Google Calendar, Tasks</p>
                                @if (isset($connectedAccounts['google']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 mt-1">Conectado</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-600 mt-1">No vinculado</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            @if (isset($connectedAccounts['google']))
                                <form action="{{ route('oauth.google.disconnect') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold rounded-md transition border border-red-200">
                                        Desconectar
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('oauth.connect', ['provider' => 'google']) }}" class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold rounded-md transition shadow-sm">
                                    Conectar
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Outlook empresarial -->
                    <div class="border rounded-lg p-5 flex items-center justify-between {{ isset($connectedAccounts['microsoft']) ? 'border-emerald-200 bg-emerald-50/30' : 'border-gray-200' }}">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 flex items-center justify-center rounded-full bg-white shadow-sm border border-gray-100">
                                <svg class="w-6 h-6" viewBox="0 0 23 23">
                                    <path fill="#f35325" d="M1 1h10v10H1z"/>
                                    <path fill="#81bc06" d="M12 1h10v10H12z"/>
                                    <path fill="#05a6f0" d="M1 12h10v10H1z"/>
                                    <path fill="#ffba08" d="M12 12h10v10H12z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Outlook empresarial</h4>
                                <p class="text-xs text-gray-500">Microsoft 365 · Correo, agenda y To Do</p>
                                @if (isset($connectedAccounts['microsoft']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 mt-1">Conectado</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-600 mt-1">No vinculado</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            @if (isset($connectedAccounts['microsoft']))
                                <span class="text-xs text-gray-400">Activo</span>
                            @else
                                <a href="{{ route('oauth.connect', ['provider' => 'microsoft']) }}" class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold rounded-md transition shadow-sm">
                                    Conectar
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

{{-- Centro de Control de Escaleon --}}
    <div id="centro-control" class="escaleon-control bg-white p-6 rounded-2xl border border-gray-100 shadow-sm my-8">
        <span class="escaleon-section-label">02 / TU ASISTENTE</span>
        <h3 class="text-lg font-bold text-gray-800">Centro de Control de Escaleon</h3>
        <p class="text-sm text-gray-500 mb-4">Escribe o habla. Escaleon te ayuda a encontrar lo importante y organizar tus próximos pasos.</p>
        <div class="escaleon-suggestions" aria-label="Ejemplos de consultas">
            <button type="button" data-assistant-suggestion="Resume mis correos recientes">✉ Resumir correos</button>
            <button type="button" data-assistant-suggestion="¿Cuáles son mis próximas reuniones?">▦ Próximas reuniones</button>
            <button type="button" data-assistant-suggestion="Ayúdame a priorizar las tareas disponibles">✓ Priorizar tareas</button>
        </div>

        @if(session('assistant_reply'))
            <div class="mb-5 p-4 rounded-xl bg-violet-50 border border-violet-100 text-gray-800 text-sm whitespace-pre-line shadow-sm">
                <span class="font-bold text-violet-700 block mb-1">Respuesta de Escaleon:</span>
                {{ session('assistant_reply') }}
            </div>
        @endif

        @error('assistant')
            <p role="alert" class="mb-4 text-sm text-red-700">{{ $message }}</p>
        @enderror
        @error('prompt')
            <p role="alert" class="mb-4 text-sm text-red-700">{{ $message }}</p>
        @enderror

        <form action="{{ route('assistant.ask') }}" method="POST" data-assistant-form>
            @csrf
            <textarea
                name="prompt"
                aria-label="Consulta para el asistente"
                rows="4"
                placeholder="Ej: Resume mis tareas pendientes o planifica mi semana..."
                class="w-full rounded-xl border-gray-200 focus:border-violet-500 focus:ring-violet-500 text-sm text-gray-700 p-3 mb-4 resize-none"
                required
                maxlength="4000"
            >{{ old('prompt') }}</textarea>
            <p data-dictation-status role="status" aria-live="polite" class="text-sm text-gray-500 mb-3">Puedes escribir o dictar tu consulta y revisar el texto antes de enviarlo.</p>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <button type="button" data-dictation-button aria-pressed="false" class="inline-flex items-center gap-2 px-4 py-2.5 border border-violet-200 rounded-lg text-violet-700 hover:bg-violet-50 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium">
                    <svg aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="2" width="6" height="12" rx="3" />
                        <path d="M5 10v2a7 7 0 0 0 14 0v-2M12 19v3M8 22h8" />
                    </svg>
                    <span data-dictation-label>Dictar con micrófono</span>
                </button>
                <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-medium text-sm rounded-lg transition-colors shadow">
                    Ejecutar Instrucción
                </button>
            </div>
        </form>
    </div>

            <p data-refresh-status role="status" class="text-xs text-gray-500">La información se actualiza automáticamente cada 5 minutos.</p>
            <div data-workspace-data>
            @fragment('workspace-data')
            {{-- Tarjetas de Datos de Google Workspace --}}
        @if(isset($connectedAccounts['google']))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                {{-- Correos Recientes --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            Gmail (Recientes)
                        </h3>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ count($emails) }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($emails as $email)
                            <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                                <p class="text-xs font-semibold text-gray-900 truncate">{{ $email['subject'] }}</p>
                                <p class="text-[11px] text-gray-500 truncate">{{ $email['from'] }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">No se encontraron correos nuevos.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Próximos Eventos --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            Google Calendar
                        </h3>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ count($events) }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($events as $event)
                            <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                                <p class="text-xs font-semibold text-gray-900 truncate">{{ $event['summary'] }}</p>
                                <p class="text-[11px] text-blue-600 font-medium">
                                    {{ $event['start'] ? \Carbon\Carbon::parse($event['start'])->isoFormat('D MMM, HH:mm') : 'Todo el día' }}
                                </p>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">Sin reuniones próximas programadas.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Tareas Pendientes --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            Google Tasks
                        </h3>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ count($tasks) }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($tasks as $task)
                            <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                                <p class="text-xs font-semibold text-gray-900 truncate">{{ $task['title'] }}</p>
                                <span class="inline-block text-[10px] bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded border border-amber-200 mt-1">
                                    Pendiente
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">No tienes tareas pendientes activas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
            @if(isset($connectedAccounts['microsoft']))
                <h3 class="text-lg font-bold text-gray-800 mb-4">Tu día en Outlook</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    @foreach(['emails' => 'Correos de hoy', 'events' => 'Próximas reuniones', 'tasks' => 'Tareas pendientes y atrasadas'] as $type => $title)
                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                            <h4 class="font-semibold text-gray-800 text-sm mb-4">{{ $title }}</h4>
                            <div class="space-y-3">
                                @forelse(($outlook[$type] ?? []) as $item)
                                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                                        <p class="text-sm font-semibold text-gray-900">{{ $item['subject'] ?? $item['summary'] ?? $item['title'] }}</p>
                                        @if($type === 'emails')
                                            <p class="text-xs text-gray-500 truncate">{{ $item['from'] }}</p>
                                        @elseif($type === 'events')
                                            <p class="text-xs text-blue-600 mt-1">{{ $item['start'] ? \Carbon\Carbon::parse($item['start'])->isoFormat('D MMM, HH:mm') : 'Todo el día' }}</p>
                                        @else
                                            <p class="text-xs mt-1 {{ $item['overdue'] ? 'text-red-700' : 'text-gray-500' }}">{{ $item['overdue'] ? 'Atrasada · ' : 'Pendiente · ' }}{{ $item['due'] ?? 'Sin vencimiento' }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-500">No hay datos disponibles. Se cargarán al actualizar; verifica los permisos si siguen vacíos.</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @endfragment
            </div>

        </div>
    </div>
</x-app-layout>
