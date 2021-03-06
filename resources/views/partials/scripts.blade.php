<script src="{{ Statamic::cpAssetUrl('js/manifest.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ Statamic::cpAssetUrl('js/vendor.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ Statamic::cpAssetUrl('js/app.js') }}?v={{ Statamic::version() }}"></script>

@foreach (Statamic::availableExternalScripts(request()) as $url)
    <script src="{{ $url }}"></script>
@endforeach

@foreach (Statamic::availableScripts(request()) as $name => $path)
    <script src="{{ Statamic::vendorAssetUrl("$name/js/$path") }}"></script>
@endforeach

<script>
    Statamic.config(@json(array_merge(Statamic::jsonVariables(request()), [
        'wrapperClass' => $__env->getSection('wrapper_class', 'max-w-xl')
    ])));
    Statamic.start();
</script>

