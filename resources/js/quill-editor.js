import Quill from 'quill';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill editors
    const editorElements = document.querySelectorAll('.quill-editor');
    
    editorElements.forEach(function(element) {
        const hiddenInput = document.querySelector(`#${element.dataset.input}`);
        
        const quill = new Quill(element, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['clean'],
                    ['link', 'image', 'video']
                ]
            },
            placeholder: 'Write something...'
        });
        
        // Set initial content if available
        if (hiddenInput && hiddenInput.value) {
            quill.root.innerHTML = hiddenInput.value;
        }
        
        // Update hidden input when text changes
        quill.on('text-change', function() {
            if (hiddenInput) {
                hiddenInput.value = quill.root.innerHTML;
            }
        });
        
        // For forms - ensure content is saved before submission
        const form = element.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                if (hiddenInput) {
                    hiddenInput.value = quill.root.innerHTML;
                }
            });
        }
    });
});