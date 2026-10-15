let region;
const queue = [];
let active = false;

export function notifyActivity(title, message, error = false, source = {}) {
    queue.push({ title, message, error, source });
    if (!active) showNext();
}

function showNext() {
    const next = queue.shift();
    if (!next) { active = false; return; }
    active = true;
    const { title, message, error, source } = next;
    if (!region) {
        region = document.createElement('div');
        region.className = 'escaleon-notifications';
        region.setAttribute('aria-label', 'Notificaciones de actividades');
        region.setAttribute('aria-live', 'polite');
        region.setAttribute('aria-relevant', 'additions');
        document.body.append(region);
    }
    // One current update notice keeps the interface quiet during repeated refreshes.
    region.replaceChildren();
    const toast = document.createElement('div');
    toast.className = 'escaleon-toast' + (error ? ' escaleon-toast-error' : '');
    const icon = document.createElement('span');
    icon.className = 'escaleon-toast-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = error ? '!' : '✓';
    if (source.provider) {
        const logos = {
            microsoft: '<svg viewBox="0 0 23 23" width="25" height="25"><path fill="#f35325" d="M1 1h10v10H1z"/><path fill="#81bc06" d="M12 1h10v10H12z"/><path fill="#05a6f0" d="M1 12h10v10H1z"/><path fill="#ffba08" d="M12 12h10v10H12z"/></svg>',
            google: '<svg viewBox="0 0 24 24" width="25" height="25"><path fill="#4285f4" d="M23.7 12.3c0-.7-.1-1.4-.2-2.1H12v4.5h6.6c-.3 1.5-1.1 2.8-2.4 3.7v3h3.9c2.3-2.1 3.6-5.2 3.6-9.1z"/><path fill="#34a853" d="M12 24c3.2 0 5.9-1.1 7.9-2.9L16 18.4c-1.1.7-2.4 1.1-4 1.1-3.1 0-5.8-2.1-6.7-5H1.2v3.2C3.3 21.5 7.4 24 12 24z"/><path fill="#fbbc05" d="M5.3 14.5c-.5-1.6-.5-3.3 0-4.9V6.4H1.2c-1.6 3.5-1.6 7.7 0 11.3z"/><path fill="#ea4335" d="M12 4.7c1.8 0 3.4.6 4.6 1.8L20 3.1C17.9 1.2 15.2 0 12 0 7.4 0 3.3 2.5 1.2 6.4l4.1 3.2c.9-2.8 3.6-4.9 6.7-4.9z"/></svg>',
        };
        icon.innerHTML = logos[source.provider] || '';
    }
    const content = document.createElement('div');
    const heading = document.createElement('strong');
    heading.textContent = title;
    const description = document.createElement('p');
    description.textContent = message;
    const close = document.createElement('button');
    close.type = 'button';
    close.setAttribute('aria-label', 'Cerrar notificación');
    close.textContent = '×';
    content.append(heading, description);
    if (source.provider) {
        const meta = document.createElement('div');
        meta.className = 'escaleon-toast-source';
        const paths = {
            chats: '<path d="M21 11a8 8 0 0 1-8 8H5l-3 3V11a9 9 0 0 1 19 0Z"/><path d="M7 10h10M7 14h6"/>',
            emails: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/>',
            events: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>',
            tasks: '<path d="m4 6 2 2 4-4M13 6h7M4 13l2 2 4-4M13 13h7M4 20h16"/>',
        };
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '1.7');
        svg.setAttribute('aria-hidden', 'true');
        svg.innerHTML = paths[source.type] || '';
        meta.append(svg, document.createTextNode(source.provider === 'microsoft' ? 'Microsoft' : 'Google'));
        content.prepend(meta);
    }
    let destination = null;
    if (typeof source.href === 'string') {
        try {
            const url = new URL(source.href, window.location.href);
            if (url.protocol === 'https:' || (source.href.startsWith('#') && url.origin === window.location.origin)) destination = url;
        } catch { /* A malformed destination renders a non-clickable notice. */ }
    }
    const body = document.createElement(destination ? 'a' : 'div');
    body.className = 'escaleon-toast-body';
    if (destination) {
        body.href = destination.href;
        if (!source.href.startsWith('#')) {
            body.target = '_blank';
            body.rel = 'noopener noreferrer';
        }
    }
    body.append(icon, content);
    toast.append(body, close);
    region.append(toast);
    let timer;
    let dismissed = false;
    const dismiss = () => {
        if (dismissed) return;
        dismissed = true;
        clearTimeout(timer);
        toast.classList.add('is-leaving');
        setTimeout(() => { toast.remove(); showNext(); }, 220);
    };
    const schedule = () => { clearTimeout(timer); timer = setTimeout(dismiss, error ? 10000 : 6500); };
    close.addEventListener('click', dismiss);
    if (destination) body.addEventListener('click', dismiss);
    toast.addEventListener('mouseenter', () => clearTimeout(timer));
    toast.addEventListener('mouseleave', schedule);
    toast.addEventListener('focusin', () => clearTimeout(timer));
    toast.addEventListener('focusout', schedule);
    schedule();
}
