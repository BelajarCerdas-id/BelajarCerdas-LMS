import './bootstrap';
import Swal from 'sweetalert2';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

window.Swal = Swal;
window.ClassicEditor = ClassicEditor;
window.flatpickr = flatpickr;

import Echo from 'laravel-echo';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});