<?php
include_once('./_common.php');

//auth_check($auth[$sub_menu], "w");

$g5['title'] = '안내문 인쇄하기';
include_once(G5_PATH.'/head.sub.php');

$editor_url = G5_EDITOR_URL.'/'.$config['cf_editor'];
?>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link href="<?php echo $editor_url ?>/summernote3/summernote-lite.min.css" rel="stylesheet">
<script src="<?php echo $editor_url ?>/summernote3/summernote.min.js"></script>
<link rel="stylesheet" href="/adm/css/editor.css">
<!-- include summernote css/js -->

<script src="<?php echo $editor_url ?>/summernote3/lang/summernote-ko-KR.js"></script>
<style>
    /* A4 크기에 맞춘 스타일 */
    .print-container {
        width: 210mm;
        height: 297mm;
        margin: auto;
        padding: 66px 5mm 123px;
        position: relative;
        background: url('/images/building_news_sample.jpg') no-repeat center center;
        background-size: cover;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    /* Summernote 에디터 */
    .note-editable {
        background: transparent !important;
        min-height: 220mm;
        font-size: 16px;
    }

    .panel {background: transparent !important;}

    /* 인쇄할 때 A4 크기 유지 */
    @media print {
        body {
            visibility: hidden;
        }
        .print-container {
            visibility: visible;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
        }
    }
</style>
<h2>안내문 작성</h2>
<form id="saveForm">
    <div class="print-container">
        <textarea id="summernote"></textarea>
    </div>
    <button type="submit">저장</button>
    <button type="button" onclick="printPage()">인쇄</button>
</form>

<script>
    $(document).ready(function() {
        let presetTemplate = `
            <h1 style="text-align: center;">🏢 안내문 제목 🏢</h1>
            <p style="text-align: center; font-size: 16px;">이곳에 안내문 내용을 작성하세요.</p>
            <hr>
            <p>🔹 여기에 내용을 입력하세요...</p>
        `;

        // Summernote 초기화
        $('#summernote').summernote({
            height: 400,
            focus: true,
            fontNames: [
                "Arial",
                "Arial Black",
                "Comic Sans MS",
                "Courier New",
                "GungSeo",
                "AppleMyungjo",
                "NanumGothic",
                "NanumMyeongjo",
                "Gulim",
                "Pretendard",
            ],
            fontNamesIgnoreCheck: [
                "Arial",
                "Arial Black",
                "Comic Sans MS",
                "Courier New",
                "GungSeo",
                "AppleMyungjo",
                "NanumGothic",
                "NanumMyeongjo",
                "Gulim",
                "Pretendard",
            ],
            fontSizes: [
                "8",
                "9",
                "10",
                "11",
                "12",
                "13",
                "14",
                "15",
                "16",
                "17",
                "18",
                "19",
                "20",
                "24",
                "30",
                "36",
                "48",
                "64",
            ],
            dialogsInBody: true,
            // toolbar
            toolbar: [
                //["style", ["style"]],
                [
                "font",
                [
                    "bold",
                    "italic",
                    "underline",
                    "strikethrough",
                    "superscript",
                    "subscript",
                    "clear",
                ],
                ],
                ["fontname", ["fontname"]],
                ["fontsize", ["fontsize"]],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["height", ["height"]],
                ["table", ["table"]],
                ["insert", ["link", "picture", "video"]],
                ["view", ["fullscreen", "codeview"]],
                ["help", ["help"]],
            ],
            callbacks: {
                onInit: function() {
                    $('#summernote').summernote('code', presetTemplate);
                }
            }
        });

        // 저장 버튼 클릭 시 HTML 저장
        $('#saveForm').on('submit', function(e) {
            e.preventDefault();
            let content = $('#summernote').summernote('code');

            // 서버로 전송 (AJAX 활용)
            $.post('/print_save.php', {content: content}, function(response) {
                alert('저장 완료!');
            });
        });
    });

    // 인쇄 기능
    function printPage() {
        window.print();
    }
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>