@props(['name', 'value' => '', 'height' => '200px'])

<!-- Hidden input to store HTML content for form submission -->
<input type="hidden" name="{{ $name }}" id="{{ $name }}-input" value="{{ $value }}">

<!-- QuillJS editor container -->
<div id="quill-{{ $name }}" style="height: {{ $height }}; margin-bottom: 20px;">
    {!! $value !!}
</div>

<!-- Initialize this specific editor instance -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quill = new Quill('#quill-{{ $name }}', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            },
            placeholder: 'Write something...'
        });
        
        // Store HTML in hidden input when text changes
        quill.on('text-change', function() {
            document.getElementById('{{ $name }}-input').value = quill.root.innerHTML;
        });
        
        // Set initial content if available
        if ('{!! $value !!}') {
            quill.root.innerHTML = '{!! $value !!}';
        }
    });
</script>