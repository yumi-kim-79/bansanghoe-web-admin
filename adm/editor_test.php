<?php
/**
 * CKEditor 5 테스트 페이지 (단독 실행)
 * 이미지 업로드 nonce 생성을 위해 Gnuboard 공통 파일 로드
 */
$sub_menu = "";
require_once './_common.php';
require_once G5_EDITOR_LIB; // ft_nonce_create() 함수 필요

$ed_nonce = ft_nonce_create('cheditor');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CKEditor 5 테스트</title>
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.css">
<style>
    body { font-family: 'Malgun Gothic', '맑은 고딕', sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
    .test-wrap { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { font-size: 20px; color: #333; margin: 0 0 20px 0; }
    .btn-area { margin-top: 15px; text-align: right; }
    .btn-test { padding: 10px 24px; background: #388FCD; color: #fff; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; }
    .btn-test:hover { background: #2d7ab8; }
    .ck-editor__editable { min-height: 300px; font-family: 'Arial Black', Gadget, sans-serif; font-size: 16px; }
</style>
</head>
<body>
<div class="test-wrap">
    <h1>CKEditor 5 에디터 테스트</h1>
    <div id="ck5_test_editor">
        <p>테스트 내용을 입력하세요.</p>
    </div>
    <div class="btn-area">
        <button type="button" class="btn-test" onclick="showEditorData();">저장 테스트</button>
    </div>
</div>

<script type="importmap">
{
    "imports": {
        "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.js",
        "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/43.3.1/"
    }
}
</script>
<script type="module">
import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Bold,
    Italic,
    Underline,
    Strikethrough,
    Font,
    Alignment,
    Link,
    List,
    Image,
    ImageUpload,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageInsert,
    SimpleUploadAdapter,
    Table,
    TableToolbar,
    BlockQuote,
    Indent,
    IndentBlock,
    Heading,
    Undo,
    HorizontalLine,
    SourceEditing
} from 'ckeditor5';
import koTranslations from 'ckeditor5/translations/ko.js';

ClassicEditor.create(document.querySelector('#ck5_test_editor'), {
    plugins: [
        Essentials, Paragraph, Bold, Italic, Underline, Strikethrough,
        Font, Alignment, Link, List,
        Image, ImageUpload, ImageResize, ImageStyle, ImageToolbar, ImageInsert,
        SimpleUploadAdapter,
        Table, TableToolbar, BlockQuote, Indent, IndentBlock,
        Heading, Undo, HorizontalLine, SourceEditing
    ],
    toolbar: [
        'heading', '|',
        'bold', 'italic', 'underline', 'strikethrough', '|',
        'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', '|',
        'alignment', '|',
        'bulletedList', 'numberedList', 'outdent', 'indent', '|',
        'link', 'insertImage', 'insertTable', 'blockQuote', 'horizontalLine', '|',
        'undo', 'redo', '|',
        'sourceEditing'
    ],
    language: koTranslations,
    image: {
        toolbar: ['imageStyle:inline', 'imageStyle:block', 'imageStyle:side', '|', 'imageTextAlternative'],
        resizeUnit: 'px',
        resizeOptions: [
            { name: 'resizeImage:original', value: null, label: '원본' },
            { name: 'resizeImage:200', value: '200', label: '200px' },
            { name: 'resizeImage:400', value: '400', label: '400px' }
        ]
    },
    simpleUpload: {
        uploadUrl: '../plugin/editor/cheditor5/imageUpload/upload_ckeditor5.php?nonce=' + encodeURIComponent('<?php echo $ed_nonce; ?>'),
        withCredentials: true
    },
    fontFamily: {
        options: [
            'Arial Black, Gadget, sans-serif',
            'default',
            'Arial, Helvetica, sans-serif',
            'Courier New, Courier, monospace',
            'Georgia, serif',
            'Verdana, Geneva, sans-serif',
            '맑은 고딕, Malgun Gothic, sans-serif'
        ]
    },
    fontSize: {
        options: [10, 12, 14, 16, 18, 20, 24, 28, 32, 36],
        supportAllValues: true
    },
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
    }
}).then(editor => {
    window.ck5TestEditor = editor;
}).catch(error => {
    console.error('CKEditor 5 init error:', error);
});
</script>
<script>
function showEditorData(){
    if(window.ck5TestEditor){
        alert(window.ck5TestEditor.getData());
    } else {
        alert('에디터가 아직 로드되지 않았습니다.');
    }
}
</script>
</body>
</html>
