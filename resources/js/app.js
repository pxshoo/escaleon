import './bootstrap';
import './assistant-dictation';
import './assistant-loading';
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

const privacyStates = new Map();
Alpine.data('privacyCard', (key) => ({
    concealed: privacyStates.get(key) ?? false,
    togglePrivacy() {
        this.concealed = !this.concealed;
        privacyStates.set(key, this.concealed);
    },
}));

Alpine.start();
