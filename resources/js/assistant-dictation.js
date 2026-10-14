const form = document.querySelector('[data-assistant-form]');

if (form) {
    const input = form.querySelector('[name="prompt"]');
    const button = form.querySelector('[data-dictation-button]');
    const label = button.querySelector('[data-dictation-label]');
    const status = form.querySelector('[data-dictation-status]');
    const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!Recognition || !window.isSecureContext) {
        button.disabled = true;
        status.textContent = !window.isSecureContext
            ? 'El micrófono requiere HTTPS o localhost.'
            : 'Tu navegador no permite dictar. Puedes escribir tu consulta o probar con Chrome.';
    } else {
        const recognition = new Recognition();
        recognition.lang = 'es-CL';
        recognition.continuous = true;
        recognition.interimResults = true;
        let active = false;
        let failed = false;
        let prefix = '';

        const reset = () => {
            active = false;
            input.readOnly = false;
            button.setAttribute('aria-pressed', 'false');
            label.textContent = 'Dictar con micrófono';
        };

        button.addEventListener('click', () => {
            if (active) {
                recognition.stop();
                status.textContent = 'Terminando el dictado…';
                return;
            }
            prefix = input.value.trimEnd();
            failed = false;
            try {
                recognition.start();
                active = true;
                input.readOnly = true;
                button.setAttribute('aria-pressed', 'true');
                label.textContent = 'Detener dictado';
                status.textContent = 'Activando micrófono…';
            } catch {
                reset();
                status.textContent = 'No se pudo activar el micrófono. Vuelve a intentarlo.';
            }
        });

        recognition.onstart = () => {
            status.textContent = 'Escuchando… Habla en español y pulsa Detener dictado al terminar.';
        };

        recognition.onresult = (event) => {
            const spoken = Array.from(event.results).map(result => result[0].transcript).join(' ').trim();
            const text = prefix + (prefix && spoken ? ' ' : '') + spoken;
            input.value = text.slice(0, input.maxLength);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            if (text.length >= input.maxLength) {
                failed = true;
                status.textContent = 'Se alcanzó el límite de texto. Revisa la consulta antes de enviarla.';
                recognition.stop();
            }
        };

        recognition.onerror = (event) => {
            failed = true;
            const messages = {
                'not-allowed': 'Permite el acceso al micrófono en el navegador para poder dictar.',
                'service-not-allowed': 'El navegador no permite el servicio de dictado. Prueba con Chrome.',
                'audio-capture': 'No se encontró un micrófono disponible. Revisa su conexión.',
                'no-speech': 'No se detectó voz. Pulsa el micrófono y vuelve a intentarlo.',
                network: 'Falló la conexión del servicio de dictado. Revisa tu conexión a internet.',
                aborted: 'Dictado detenido. Puedes editar el texto.',
            };
            status.textContent = messages[event.error] || 'No se pudo transcribir tu voz. Vuelve a intentarlo.';
        };

        recognition.onend = () => {
            reset();
            if (!failed) status.textContent = 'Dictado terminado. Revisa el texto y pulsa Ejecutar Instrucción.';
        };

        form.addEventListener('submit', (event) => {
            if (active) {
                event.preventDefault();
                recognition.stop();
                status.textContent = 'Terminando el dictado. Revisa el texto y vuelve a pulsar Ejecutar Instrucción.';
            }
        });
        window.addEventListener('pagehide', () => recognition.abort());
    }
}
