<x-app-layout>

    <div class="py-10 escaleon-dashboard" data-dashboard-refresh-url="{{ route('dashboard.refresh') }}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="escaleon-toolbar">
                <div><span class="escaleon-breadcrumb">Espacio personal <span aria-hidden="true">/</span> Vista general</span></div>
                <time datetime="{{ now('America/Santiago')->toDateString() }}">{{ now('America/Santiago')->locale('es')->isoFormat('dddd D [de] MMMM') }}</time>
            </div>

            <section class="escaleon-hero">
                <div>
                    
                    <h1>Hola, {{ explode(' ', auth()->user()->name)[0] }}<span class="text-violet-300">.</span></h1>
                    <p>Todo lo que necesitas para avanzar, en un solo lugar. Revisa tu agenda y enfócate en lo importante.</p>
                    <a href="#centro-control" class="escaleon-hero-link">Organizar mi día <span aria-hidden="true">↗</span></a>
                </div>
                
            </section>

            <nav class="escaleon-section-nav" aria-label="Secciones del dashboard">
                <a href="#conexiones">Conexiones</a>
                <a href="#centro-control">Asistente</a>
                <a href="#productividad">Productividad</a>
            </nav>

            @if(isset($connectedAccounts['google']))
                <div class="escaleon-stats">
                    <div><p>Correos recientes <strong>{{ count($emails) }}</strong><small>En la vista de Gmail</small></p></div>
                    <div><p>Próximos eventos <strong>{{ count($events) }}</strong><small>En tu calendario de Google</small></p></div>
                    <div><p>Tareas en la vista <strong>{{ count($tasks) }}</strong><small>Desde Google Tasks</small></p></div>
                </div>
            @endif

            @if (session('status'))
                <div class="escaleon-notice bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded shadow-sm">
                    <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
                </div>
            @endif

            <!-- Tarjetas de Conexión OAuth -->
            <div id="conexiones" class="escaleon-connections bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
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
        
        <div class="escaleon-control-heading">
            <div class="escaleon-mascot-heading">
                <img src="{{ asset('images/brand/escaleon-leon.png') }}" alt="Escaleon, el león asistente" width="70" height="116" class="escaleon-mascot">
                <div><h3 class="text-lg font-bold text-gray-800">Centro de Control de Escaleon</h3><span class="escaleon-mascot-caption">Tu león asistente</span></div>
            </div>
            <span class="escaleon-ai-badge">Asistente IA</span>
        </div>
        <p class="text-sm text-gray-500 mb-4">Escribe o habla. Escaleon te ayuda a encontrar lo importante y organizar tus próximos pasos.</p>
        <div class="escaleon-suggestions" aria-label="Ejemplos de consultas">
            <button type="button" data-assistant-suggestion="Resume mis correos recientes">Resumir correos</button>
            <button type="button" data-assistant-suggestion="¿Cuáles son mis próximas reuniones?">Próximas reuniones</button>
            <button type="button" data-assistant-suggestion="Ayúdame a priorizar las tareas disponibles">Priorizar tareas</button>
        </div>

        @if(session('assistant_reply'))
            <div class="escaleon-response mb-5 p-4 rounded-xl bg-violet-50 border border-violet-100 text-gray-800 text-sm shadow-sm">
                <span class="font-bold text-violet-700 block mb-1">Respuesta de Escaleon:</span>
                <div class="escaleon-answer">{!! \Illuminate\Support\Str::markdown(session('assistant_reply'), ['html_input' => 'strip', 'allow_unsafe_links' => false, 'max_nesting_level' => 20]) !!}</div>
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
            <div data-assistant-loading class="escaleon-loading" role="status" aria-live="polite" hidden>
                <span class="escaleon-loading-spinner" aria-hidden="true"></span>
                <div><strong>Preparando respuesta…</strong><p>Estoy revisando tus datos y organizando la información.</p></div>
            </div>
            <label for="assistant-prompt" class="escaleon-prompt-label">¿En qué te puedo ayudar?</label>
            <textarea
                id="assistant-prompt"
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

            <section id="productividad" class="escaleon-productivity-heading">
                <h2>Tu productividad</h2>
                <button type="button" data-refresh-button class="escaleon-refresh-button">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M6.1 7a7 7 0 0 1 11.5-1L20 9M4 15l2.4 3A7 7 0 0 0 17.9 17"/></svg>
                    <span data-refresh-label>Actualizar datos</span>
                </button>
                <p>Correos, reuniones y tareas de tus cuentas conectadas.</p>
                <p data-refresh-status role="status" class="text-xs text-gray-500">La información se actualiza automáticamente cada 5 minutos.</p>
            </section>
            <div data-workspace-data>
            @fragment('workspace-data')
            {{-- Tarjetas de Datos de Google Workspace --}}
        @if(isset($connectedAccounts['google']))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                {{-- Correos Recientes --}}
                <div id="google-emails" x-data="privacyCard('google-emails')" class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            Gmail (Recientes)
                        </h3>
                        <div class="flex items-center gap-2"><span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ count($emails) }}</span><x-privacy-toggle /></div>
                    </div>
                    <div class="space-y-3 escaleon-private-content" :class="{ 'is-concealed': concealed }" :aria-hidden="concealed.toString()" :inert="concealed">
                        @forelse($emails as $email)
                            <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                                <x-workspace-link :href="$email['link'] ?? (isset($email['id']) ? 'https://mail.google.com/mail/u/0/#all/'.rawurlencode($email['id']) : null)">
                                <p class="text-xs font-semibold text-gray-900 truncate">{{ $email['subject'] }}</p>
                                <p class="text-[11px] text-gray-500 truncate">{{ $email['from'] }}</p>
                                </x-workspace-link>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">No se encontraron correos nuevos.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Próximos Eventos --}}
                <div id="google-events" x-data="privacyCard('google-events')" class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            Google Calendar
                        </h3>
                        <div class="flex items-center gap-2"><span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ count($events) }}</span><x-privacy-toggle /></div>
                    </div>
                    <div class="space-y-3 escaleon-private-content" :class="{ 'is-concealed': concealed }" :aria-hidden="concealed.toString()" :inert="concealed">
                        @forelse($events as $event)
                            <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                                <x-workspace-link :href="$event['link'] ?? null">
                                <p class="text-xs font-semibold text-gray-900 truncate">{{ $event['summary'] }}</p>
                                <p class="text-[11px] text-blue-600 font-medium">
                                    {{ $event['start'] ? \Carbon\Carbon::parse($event['start'])->isoFormat('D MMM, HH:mm') : 'Todo el día' }}
                                </p>
                                </x-workspace-link>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">Sin reuniones próximas programadas.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Tareas Pendientes --}}
                <div id="google-tasks" x-data="privacyCard('google-tasks')" class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            Google Tasks
                        </h3>
                        <div class="flex items-center gap-2"><span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ count($tasks) }}</span><x-privacy-toggle /></div>
                    </div>
                    <div class="space-y-3 escaleon-private-content" :class="{ 'is-concealed': concealed }" :aria-hidden="concealed.toString()" :inert="concealed">
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
                        <div id="microsoft-{{ $type }}" x-data="privacyCard('microsoft-{{ $type }}')" class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                            <div class="flex items-center justify-between mb-4 gap-2"><h4 class="font-semibold text-gray-800 text-sm">{{ $title }}</h4><x-privacy-toggle /></div>
                            <div class="space-y-3 escaleon-private-content" :class="{ 'is-concealed': concealed }" :aria-hidden="concealed.toString()" :inert="concealed">
                                @forelse(($outlook[$type] ?? []) as $item)
                                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                                        <x-workspace-link :href="$type !== 'tasks' ? ($item['link'] ?? null) : null">
                                        <p class="text-sm font-semibold text-gray-900">{{ $item['subject'] ?? $item['summary'] ?? $item['title'] }}</p>
                                        @if($type === 'emails')
                                            <p class="text-xs text-gray-500 truncate">{{ $item['from'] }}</p>
                                        @elseif($type === 'events')
                                            @if(!empty($item['today']))
                                                <p class="text-xs text-purple-800 mt-1">{{ $item['organizer'] ?? 'Un participante' }} organizó una reunión para hoy.</p>
                                            @else
                                                <p class="text-xs text-gray-500 mt-1">Organiza: {{ $item['organizer'] ?? 'No disponible' }}</p>
                                            @endif
                                            <p class="text-xs text-blue-600 mt-1">{{ $item['start'] ? \Carbon\Carbon::parse($item['start'])->isoFormat('D MMM, HH:mm') : 'Todo el día' }}</p>
                                        @else
                                            <p class="text-xs mt-1 {{ $item['overdue'] ? 'text-red-700' : 'text-gray-500' }}">{{ $item['overdue'] ? 'Atrasada · ' : 'Pendiente · ' }}{{ $item['due'] ?? 'Sin vencimiento' }}</p>
                                        @endif
                                        </x-workspace-link>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-500">No hay datos disponibles. Se cargarán al actualizar; verifica los permisos si siguen vacíos.</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @if(isset($connectedAccounts['microsoft']))
                <div id="microsoft-chats" x-data="privacyCard('microsoft-chats')" class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mt-6">
                    <div class="flex items-center justify-between mb-4"><h3 class="font-semibold text-sm">Microsoft Teams · Chats con mensajes no leídos</h3><x-privacy-toggle /></div>
                    <div class="space-y-3 escaleon-private-content" :class="{ 'is-concealed': concealed }" :aria-hidden="concealed.toString()" :inert="concealed">
                        @forelse(($outlook['chats'] ?? []) as $chat)
                            <div><x-workspace-link :href="$chat['link']"><p class="font-semibold text-sm">Tienes mensajes no leídos de: {{ $chat['from'] }}</p><p class="text-xs text-gray-500">{{ $chat['title'] }}</p><p class="text-xs mt-1">{{ $chat['preview'] }}</p></x-workspace-link></div>
                        @empty
                            @if(($outlook['teams_status'] ?? '') === 'ready')
                                <p class="text-sm text-gray-500">No se detectaron mensajes no leídos en los 30 chats recientes revisados.</p>
                            @else
                                <p class="text-sm text-gray-500">Teams requiere el permiso Chat.Read. Tras autorizarlo en Microsoft Entra, vuelve a conectar tu cuenta.</p>
                                <a class="text-sm underline" href="{{ route('oauth.connect', ['provider' => 'microsoft']) }}">Autorizar acceso a Teams</a>
                            @endif
                        @endforelse
                    </div>
                </div>
            @endif
            @endfragment
            </div>

            <footer class="escaleon-company-footer">
                <img src="{{ asset('images/brand/grupo-escanor.png') }}" alt="Grupo Escanor · Ecosistema integral de soluciones tecnológicas" width="240" height="49" loading="lazy">
                <span>Escaleon · Tu centro de productividad</span>
            </footer>
        </div>
    </div>
</x-app-layout>
