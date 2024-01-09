<script>
    window.ELFINDER_LANG = '{{ App::getLocale() }}'
    window.ELFINDER_BASEPATH = '{{ app('elfinder')->getBasePath() }}';
    window.ELFINDER_CONNECTOR_URL = '{{ route('elfinder.connector') }}';
    window.ELFINDER_EDITOR = {{ setting('elfinder_editor_enabled', true) ? 'true' : 'false' }};
    window.ELFINDER_REPLACE_DEFAULT_MEDIA = {{ ($replaceMedia = setting('elfinder_replace_default_media', false)) ? 'true' : 'false' }};
</script>

@if($replaceMedia)
    <style>
        [data-bb-toggle="upload-from-url"] {
            display: none;
        }
    </style>
@endif
