import {
    ClassicEditor as BaseClassicEditor,
    Autoformat,
    Bold,
    Italic,
    Underline,
    BlockQuote,
    CKFinder,
    CKFinderUploadAdapter,
    CloudServices,
    Essentials,
    Heading,
    Image,
    ImageCaption,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    Link,
    List,
    MediaEmbed,
    Paragraph,
    PasteFromOffice,
    Table,
    TableToolbar,
    TextTransformation
} from 'ckeditor5';
import { Math as MathPlugin, AutoformatMath } from '@isaul32/ckeditor5-math';
import 'ckeditor5/ckeditor5.css';
import '@isaul32/ckeditor5-math/theme/mathform.css';

class ClassicEditor extends BaseClassicEditor {}

ClassicEditor.builtinPlugins = [
    Autoformat,
    Bold,
    Italic,
    Underline,
    BlockQuote,
    CKFinder,
    CKFinderUploadAdapter,
    CloudServices,
    Essentials,
    Heading,
    Image,
    ImageCaption,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    Link,
    List,
    MediaEmbed,
    Paragraph,
    PasteFromOffice,
    Table,
    TableToolbar,
    TextTransformation,
    MathPlugin,
    AutoformatMath
];

ClassicEditor.defaultConfig = {
    toolbar: {
        items: [
            'heading',
            '|',
            'bold',
            'italic',
            'underline',
            'link',
            'bulletedList',
            'numberedList',
            '|',
            'outdent',
            'indent',
            '|',
            'uploadImage',
            'blockQuote',
            'insertTable',
            'mediaEmbed',
            'math',
            'undo',
            'redo'
        ],
        shouldNotGroupWhenFull: true
    },
    image: {
        toolbar: [
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'toggleImageCaption',
            'imageTextAlternative'
        ]
    },
    table: {
        contentToolbar: [
            'tableColumn',
            'tableRow',
            'mergeTableCells'
        ]
    },
    math: {
        engine: 'mathjax',
        outputType: 'span',
        forceOutputType: false,
        enablePreview: true
    }
};

export default ClassicEditor;
