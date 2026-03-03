<?php
include_once('./_common.php');

//auth_check($auth[$sub_menu], "w");

$g5['title'] = '안내문 인쇄하기';
include_once(G5_PATH.'/head.sub.php');
?>
<style>
    .print-container {
        width: 210mm;
        height: 297mm;
        margin: auto;
        padding: 153px 5mm 123px;
        position: relative;
        background: url('/images/building_news_sample.jpg') no-repeat center center;
        background-size: cover;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    @media print {
        body {
        visibility: hidden; /* 전체 요소 숨김 */
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        }
        .print-container {
        background: url('/images/building_news_sample.jpg') no-repeat center center !important;
        background-size: cover !important;
        }
        #preview, #preview * {
            visibility: visible; /* preview 컨텐츠만 보이도록 설정 */
        }
        #preview {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
        }
    }
</style>
<h2>미리보기 및 인쇄</h2>
<div id="preview" class="print-container"></div>
<button onclick="printPage()">인쇄하기</button>

<script>
    // 서버에서 저장된 HTML 불러오기
    fetch('saved_guide.html')
        .then(response => response.text())
        .then(data => {
            document.getElementById('preview').innerHTML = data;
        });

    function printPage() {
        window.print();
    }

    // function printPage() {
    //     var content = document.querySelector(".print-container").outerHTML;
    //     var originalContent = document.body.innerHTML; // 기존 페이지 내용 저장

    //     document.body.innerHTML = content; // 특정 영역만 출력하도록 변경
    //     window.print(); // 인쇄 실행

    //     document.body.innerHTML = originalContent; // 원래 페이지 복원
    //     location.reload(); // JavaScript로 원래 상태 복구 (스크립트/이벤트 재적용)
    // }
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>