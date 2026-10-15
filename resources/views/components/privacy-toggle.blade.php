<button type="button" @click="togglePrivacy" :aria-pressed="concealed.toString()" :aria-label="concealed ? 'Mostrar contenido' : 'Ocultar contenido'" :title="concealed ? 'Mostrar contenido' : 'Ocultar contenido'" class="escaleon-privacy-toggle">
    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
        <circle cx="12" cy="12" r="3" />
        <path x-show="concealed" d="m3 3 18 18" />
    </svg>
</button>
