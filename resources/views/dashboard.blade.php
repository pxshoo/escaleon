<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Escaleon Hub') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded shadow-sm">
                    <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
                </div>
            @endif

            <!-- Tarjetas de Conexión OAuth -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Integraciones de Servicios</h3>
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
                                <span class="text-xs text-gray-400">Activo</span>
                            @else
                                <a href="{{ route('oauth.connect', ['provider' => 'google']) }}" class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold rounded-md transition shadow-sm">
                                    Conectar
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Microsoft 365 -->
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
                                <h4 class="font-semibold text-gray-900">Microsoft 365</h4>
                                <p class="text-xs text-gray-500">Outlook, Teams, To Do</p>
                                @if (isset($connectedAccounts['microsoft']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 mt-1">Conectado</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-600 mt-1">En pausa</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            @if (isset($connectedAccounts['microsoft']))
                                <span class="text-xs text-gray-400">Activo</span>
                            @else
                                <button disabled class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-400 text-xs font-semibold rounded-md cursor-not-allowed">
                                    Pendiente
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            <!-- Asistente de IA (Interfaz de pruebas) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Centro de Control de Escaleon</h3>
                <p class="text-sm text-gray-500 mb-4">Envía una instrucción o consulta para procesar con el asistente.</p>

                <form action="#" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <textarea rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Ej: Resume mis tareas pendientes o planifica mi semana..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-md transition shadow-sm">
                            Ejecutar Instrucción
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>