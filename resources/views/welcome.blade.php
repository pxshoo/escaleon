<x-guest-layout>
    <div class="escaleon-welcome">
        <img src="{{ asset('images/brand/escaleon-leon.png') }}" alt="Escaleon, tu león asistente" width="90" height="150">
        <h1>Tu día, más claro.</h1>
        <p>Reúne tus correos, reuniones y tareas. Pregúntale a Escaleon y organiza tus próximos pasos desde un solo lugar.</p>
        <div class="escaleon-welcome-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="escaleon-main-link">Ir a mi panel</a>
            @else
                <a href="{{ route('login') }}" class="escaleon-main-link">Ingresar</a>
                <a href="{{ route('register') }}" class="escaleon-secondary-link">Crear cuenta</a>
            @endauth
        </div>
        <p class="escaleon-welcome-services">Google Workspace · Microsoft 365 · Asistente IA</p>
    </div>
</x-guest-layout>
