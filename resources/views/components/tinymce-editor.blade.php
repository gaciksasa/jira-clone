@props(['id' => 'content', 'name' => 'content', 'value' => '', 'placeholder' => 'Enter your content...'])

<textarea
    id="{{ $id }}"
    name="{{ $name }}"
    class="form-control @error($name) is-invalid @enderror"
    {{ $attributes }}
>{{ $value }}</textarea>

@push('scripts')
<script src="{{ asset('js/tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: '#{{ $id }}',
            height: 300,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
            placeholder: '{{ $placeholder }}',
            branding: false,
            promotion: false,
        });
    });
</script>
@endpush