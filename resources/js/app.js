import './bootstrap';
import Swal from 'sweetalert2';
import flatpickr from "flatpickr";

window.Swal = Swal;
window.flatpickr = flatpickr;

// Code-split CKEditor: lazy-load on demand when ClassicEditor.create is called
let classicEditorPromise = null;
window.ClassicEditor = {
    create: async (...args) => {
        if (!classicEditorPromise) {
            classicEditorPromise = import('./ckeditor').then((module) => {
                window.ClassicEditor = module.default;
                return module.default;
            });
        }
        const ClassicEditor = await classicEditorPromise;
        return ClassicEditor.create(...args);
    },
};