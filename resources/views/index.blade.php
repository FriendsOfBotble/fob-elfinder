@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div id="elfinder"></div>
@endsection

@push('footer')
    <script>
        $(function() {
            $('#elfinder').elfinder(
                {
                    cssAutoLoad : false,
                    baseUrl : './',
                    url : ELFINDER_CONNECTOR_URL,
                    lang: ELFINDER_LANG || 'en',
                    width: '100%',
                    height: $('.page-body').height() - 20,
                    rememberLastDir: true
                },
                function(fm, extraObj) {
                    fm.bind('init', function() {
                        if (fm.lang === 'ja') {
                            fm.loadScript(
                                [ '//cdn.rawgit.com/polygonplanet/encoding.js/1.0.26/encoding.min.js' ],
                                function() {
                                    if (window.Encoding && Encoding.convert) {
                                        fm.registRawStringDecoder(function(s) {
                                            return Encoding.convert(s, {to:'UNICODE',type:'string'});
                                        });
                                    }
                                },
                                { loadType: 'tag' }
                            );
                        }
                    });
                }
            );
        });
    </script>
@endpush
