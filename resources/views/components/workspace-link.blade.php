@props(['href' => null])
@if(is_string($href) && filter_var($href, FILTER_VALIDATE_URL) && parse_url($href, PHP_URL_SCHEME) === 'https')
    <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" class="escaleon-workspace-link" title="Abrir en una nueva pestaña">{{ $slot }}</a>
@else
    <div>{{ $slot }}</div>
@endif
