const form = document.querySelector('[data-assistant-form]');

if (form) {
    let submitting = false;
    const button = form.querySelector('[type="submit"]');
    const originalLabel = button.textContent;
    const loading = form.querySelector('[data-assistant-loading]');
    const input = form.querySelector('[name="prompt"]');
    const mic = form.querySelector('[data-dictation-button]');
    let previousMicDisabled = mic?.disabled;

    form.addEventListener('submit', (event) => {
        // The dictation handler can cancel submission while it stops listening.
        if (event.defaultPrevented) return;
        if (submitting) {
            event.preventDefault();
            return;
        }
        submitting = true;
        previousMicDisabled = mic?.disabled;
        form.setAttribute('aria-busy', 'true');
        loading.hidden = false;
        button.disabled = true;
        button.textContent = 'Consultando…';
        input.readOnly = true;
        if (mic) mic.disabled = true;
        document.querySelectorAll('[data-assistant-suggestion]').forEach(item => { item.disabled = true; });
    });

    // Restore controls when returning through the browser's back/forward cache.
    window.addEventListener('pageshow', () => {
        if (!submitting) return;
        submitting = false;
        form.removeAttribute('aria-busy');
        loading.hidden = true;
        button.disabled = false;
        button.textContent = originalLabel;
        input.readOnly = false;
        if (mic) mic.disabled = previousMicDisabled;
        document.querySelectorAll('[data-assistant-suggestion]').forEach(item => { item.disabled = false; });
    });
}
