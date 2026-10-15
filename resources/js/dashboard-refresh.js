import { notifyActivity } from './activity-notifications';

const dashboard = document.querySelector('[data-dashboard-refresh-url]');

if (dashboard) {
    const status = dashboard.querySelector('[data-refresh-status]');
    const button = dashboard.querySelector('[data-refresh-button]');
    const label = button.querySelector('[data-refresh-label]');
    let pending = false;
    let lastAttempt = Date.now();
    let knownActivities = null;

    const refresh = async () => {
        if (pending || document.hidden) return;
        pending = true;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        label.textContent = 'Actualizando…';
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
                notifyActivity('Sesión finalizada', 'Recarga la página para volver a ingresar.', true);
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
            const activities = Array.isArray(data.activities) ? data.activities : [];
            const firstUpdate = knownActivities === null;
            const fresh = knownActivities ? activities.filter(item => !knownActivities.has(item.key)) : [];
            if (!knownActivities) knownActivities = new Set();
            activities.forEach(item => knownActivities.add(item.key));
            // Keep only identifiers from this visit; no mail content is persisted.
            if (knownActivities.size > 5000) knownActivities = new Set(activities.map(item => item.key));
            if (fresh.length) {
                const labels = {
                    google: { emails: ['correo de Gmail', 'correos de Gmail'], events: ['reunión de Google Calendar', 'reuniones de Google Calendar'], tasks: ['tarea de Google Tasks', 'tareas de Google Tasks'] },
                    microsoft: { emails: ['correo de Outlook', 'correos de Outlook'], events: ['reunión de Outlook', 'reuniones de Outlook'], tasks: ['tarea de Microsoft To Do', 'tareas de Microsoft To Do'], chats: ['mensaje no leído de Teams', 'mensajes no leídos de Teams'] },
                };
                for (const [provider, types] of Object.entries(labels)) {
                    for (const [type, names] of Object.entries(types)) {
                        const items = fresh.filter(item => item.provider === provider && item.type === type);
                        const count = items.length;
                        if (!count) continue;
                        const feminine = type === 'events' || type === 'tasks';
                        const adjective = feminine ? (count === 1 ? 'Nueva' : 'Nuevas') : (count === 1 ? 'Nuevo' : 'Nuevos');
                        const title = `${adjective}${count === 1 ? '' : ' ' + count} ${names[count === 1 ? 0 : 1]}`;
                        for (const [index, item] of items.entries()) {
                            notifyActivity(count === 1 ? title : `${feminine ? 'Nueva' : 'Nuevo'} ${names[0]} (${index + 1} de ${count})`,
                                'Pulsa para abrir la actividad.', false, { provider, type, href: item.href });
                        }
                    }
                }
            } else {
                notifyActivity('Datos actualizados', firstUpdate ? 'Tus actividades están listas para revisar.' : 'No hay nuevos elementos en las tarjetas de esta revisión.');
            }
        } catch {
            status.textContent = 'No se pudo actualizar la información. Se reintentará en 5 minutos.';
            notifyActivity('No se pudo actualizar', 'Conservamos la información visible. Puedes volver a pulsar Actualizar datos.', true);
        } finally {
            clearTimeout(timeout);
            pending = false;
            button.disabled = false;
            button.removeAttribute('aria-busy');
            label.textContent = 'Actualizar datos';
        }
    };

    const interval = setInterval(refresh, 5 * 60 * 1000);
    button.addEventListener('click', refresh);
    refresh();
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && Date.now() - lastAttempt >= 5 * 60 * 1000) refresh();
    });
    window.addEventListener('pagehide', () => clearInterval(interval));
}
