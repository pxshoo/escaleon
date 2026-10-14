const dashboard = document.querySelector('[data-dashboard-refresh-url]');

if (dashboard) {
    const status = dashboard.querySelector('[data-refresh-status]');
    let pending = false;
    let lastAttempt = Date.now();

    const refresh = async () => {
        if (pending || document.hidden) return;
        pending = true;
        lastAttempt = Date.now();
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 60000);
        status.textContent = 'Actualizando correos, eventos y tareas…';
        try {
            const response = await fetch(dashboard.dataset.dashboardRefreshUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store',
                signal: controller.signal,
            });
            if (response.status === 401 || response.status === 403) {
                clearInterval(interval);
                status.textContent = 'Tu sesión terminó. Recarga la página para volver a ingresar.';
                return;
            }
            if (!response.ok) throw new Error('refresh-failed');
            const data = await response.json();
            if (typeof data.html !== 'string' || !Array.isArray(data.counts)) throw new Error('invalid-data');
            dashboard.querySelector('[data-workspace-data]').innerHTML = data.html;
            dashboard.querySelectorAll('.escaleon-stats strong').forEach((counter, index) => {
                counter.textContent = data.counts[index] ?? 0;
            });
            status.textContent = `Última actualización: ${new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })}. Actualización cada 5 minutos.`;
        } catch {
            status.textContent = 'No se pudo actualizar la información. Se reintentará en 5 minutos.';
        } finally {
            clearTimeout(timeout);
            pending = false;
        }
    };

    const interval = setInterval(refresh, 5 * 60 * 1000);
    refresh();
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && Date.now() - lastAttempt >= 5 * 60 * 1000) refresh();
    });
    window.addEventListener('pagehide', () => clearInterval(interval));
}
