import './bootstrap';
import './assistant-dictation';
import './dashboard-refresh';

document.querySelectorAll('[data-assistant-suggestion]').forEach(button => {
    button.addEventListener('click', () => {
        const input = document.querySelector('[data-assistant-form] [name="prompt"]');
        if (!input || input.readOnly) return;
        input.value = button.dataset.assistantSuggestion;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
    });
});

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
