<?php
$sub_menu = "200700";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '추가';
}

$g5['title'] .= '공문(이전자료)';
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
//require_once G5_EDITOR_LIB;

$sql = "SELECT ofn.*, est.address, est.name as building_name, s3.url FROM official_notice as ofn
        LEFT JOIN estate as est ON ofn.estate = est.seq
        LEFT JOIN s3_file as s3 ON ofn.s3_file = s3.seq
        WHERE ofn.id = {$bb_id}";
$row = sql_fetch($sql);


$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;

    //print_r2($row);
}

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
$editor_url = G5_EDITOR_URL.'/'.$config['cf_editor'];
?>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link href="<?php echo $editor_url ?>/summernote3/summernote-lite.min.css" rel="stylesheet">
<script src="<?php echo $editor_url ?>/summernote3/summernote.min.js"></script>
<link rel="stylesheet" href="/adm/css/editor.css">
<!-- include summernote css/js -->

<script src="<?php echo $editor_url ?>/summernote3/lang/summernote-ko-KR.js"></script>
<style>
.note-editor {
min-height: 230mm;
font-size: 16px;
}

.note-editable {
    width: 210mm;
    height: 297mm !important;
    margin: auto;
    padding: 45mm 10mm 38mm !important;
    background: url('/images/building_news_sample.jpg') no-repeat center center;
    background-size: cover;
    box-sizing: border-box;

    /* 내용 넘치면 잘리게 처리 */
    overflow: hidden !important;
    position: relative;
    line-height:1.3;
}
.note-editable table {
    width: 100%;
    table-layout: fixed;
}

.note-editable img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
}
</style>
<form name="fbuildingbbs" id="fbuildingbbs" action="./building_news_public_form_update.php" onsubmit="return fbuildingbbs_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="bb_id" value="<?php echo $row['bb_id']; ?>">
    <input type="hidden" name="bbs_type" value="public">
    <input type="hidden" name="type" value="<?php echo $type; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">공문 정보</h2>
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th>지역</th>
                    <td>
                        <?php
                        $addr = explode(' ', $row['address']);
                        echo $addr[0];
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>단지</th>
                    <td>
                        <?php echo $row['building_name']; ?>
                    </td>
                </tr>
                <th>문서번호</th>
                <td>
                    <?php echo $row['doc_num']; ?>
                </td>
                <tr>
                    <th>제목</th>
                    <td >
                       <?php echo $row['title']; ?>
                    </td>
                </tr>
                <tr>
                    <th>내용</th>
                    <td >
                        <textarea name="bb_content" id="bb_content"></textarea>
                    </td>
                </tr>
               
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <a href="./building_news_public_bf.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    </div>
</form>



<script>

function preview_info() // 회원 엑셀 업로드를 위하여 추가
{ 
    const title = $("#bb_title").val();
    const content = $('#bb_content').summernote('code');
    const building_id = $("#building_id").val();
    const bb_number = $("#bb_number").val();

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "./building_news_view.php";
    form.target = "win_news";

    //제목
    const inputTitle = document.createElement("input");
    inputTitle.type = "hidden";
    inputTitle.name = "title";
    inputTitle.value = title;
    form.appendChild(inputTitle);

    // textarea 대신 hidden input 사용 내용
    const inputContent = document.createElement("input");
    inputContent.type = "hidden";
    inputContent.name = "content";
    inputContent.value = content;
    form.appendChild(inputContent);

    //빌딩정보
    const inputBuildingId = document.createElement("input");
    inputBuildingId.type = "hidden";
    inputBuildingId.name = "building_ids";
    inputBuildingId.value = building_id;
    form.appendChild(inputBuildingId);

    //문서번호
    const bbNumber = document.createElement("input");
    bbNumber.type = "hidden";
    bbNumber.name = "bb_numbers";
    bbNumber.value = bb_number;
    form.appendChild(bbNumber);

    const inputGigan = document.createElement("input");
    inputGigan.type = "hidden";
    inputGigan.name = "bbs_gigan";
    inputGigan.value = "1";
    form.appendChild(inputGigan);

    document.body.appendChild(form);

    const opt = "width=810,height=1200,left=10,top=10";
    window.open('', "win_news", opt);
    form.submit();

    return false;
}

    
function print_info(bb_idx)
{ 

    var opt = "width=810,height=1200,left=10,top=10"; 
    var url = "./building_news_print.php?bb_idx=" + bb_idx;

    window.open(url, "win_news", opt); 

    return false; 

}

function sendFile(file) {
    const data = new FormData();
    data.append("SummernoteFile", file);

    $.ajax({
        data: data,
        type: "POST",
        url: "<?php echo $editor_url ?>/upload.php",
        cache: false,
        contentType: false,
        processData: false,
        success: function (response) {
            try {
                const obj = JSON.parse(response);
                if (obj.success) {
                    const img = document.createElement("img");
                    img.src = obj.save_url;
                    img.style.maxWidth = "100%";
                    img.style.height = "auto";
                    img.style.display = "block";

                    if (currentRange) {
                        currentRange.insertNode(img);
                    } else {
                        $('#bb_content').summernote("insertNode", img);
                    }
                } else {
                    alert("이미지 업로드 실패: 오류 코드 " + obj.error);
                }
            } catch (e) {
                alert("서버 응답 파싱 오류");
                console.error(e);
            }
        },
        error: function (xhr, status, error) {
            alert("이미지 업로드 실패");
            console.error(error);
        }
    });
}

function isOverflowing() {
    const editable = document.querySelector('.note-editable');

    // console.log('editable scrollHeight', editable.scrollHeight);
    // console.log('editable clientHeight', editable.clientHeight);
    return editable.scrollHeight > editable.clientHeight;
}

let currentRange;
let lastValidContent = '';

$(document).ready(function () {
    let bb_content = `<?php echo $row['content']; ?>`;
    let w = "<?php echo $w; ?>";

    $('#bb_content').summernote({
        lang: "ko-KR",
        height: 1200,
        focus: true,
        dialogsInBody: true,
        // fontNames: [
        //     "Arial", "NanumGothic", "NanumMyeongjo", "Gulim", "Pretendard"
        // ],
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
            "8", "9", "10", "11", "12", "14", "16", "18", "20", "24", "30", "36", "48"
        ],
        toolbar: [
            ["font", ["bold", "italic", "underline", "clear"]],
            ["fontname", ["fontname"]],
            ["fontsize", ["fontsize"]],
            ["color", ["color"]],
            ["para", ["ul", "ol", "paragraph"]],
            ["height", ["height"]],
            ["insert", ["link", "picture", "table"]],
            ["view", ["fullscreen"]],
            //"codeview"
        ],
        callbacks: {
            onInit: function () {
                $('#bb_content').summernote('code', w === '' ? '<p><br></p>' : bb_content);

                lastValidContent = $('#bb_content').summernote('code');
            },
            onKeyup: function () {
                currentRange = $('#bb_content').summernote('createRange');

                const currentContent = $('#bb_content').summernote('code'); //최근에 입력한 내용 저장

                if (isOverflowing()) {
                    alert("A4 용지 크기를 초과했습니다. 더 이상 입력할 수 없습니다.");
                    //$('#bb_content').summernote('code', lastValidContent); // 이전 상태로 복원
                }else{
            
                    lastValidContent = currentContent;
                }
            },
            onMouseUp: function () {
                currentRange = $('#bb_content').summernote('createRange');

                lastValidContent = $('#bb_content').summernote('code');
            },
            onImageUpload: function (files) {
                const maxSize = 20 * 1024 * 1024; // 20MB
                for (let file of files) {
                    if (file.size > maxSize) {
                        alert(`${file.name} 파일이 업로드 용량(20MB)을 초과하였습니다.`);
                    } else {
                        sendFile(file);
                    }
                }

                const currentContent = $('#bb_content').summernote('code'); //최근에 입력한 내용 저장

                if (isOverflowing()) {
                    alert("A4 용지 크기를 초과했습니다. 더 이상 입력할 수 없습니다.");
                    //$('#bb_content').summernote('code', lastValidContent); // 이전 상태로 복원
                }else{
            
                    lastValidContent = currentContent;
                }
            },
            onPaste: function (e) {
                const clipboardData = e.originalEvent.clipboardData;
                if (clipboardData && clipboardData.items.length) {
                    const item = clipboardData.items[0];
                    if (item.kind === "file" && item.type.indexOf("image/") !== -1) {
                        e.preventDefault(); // 붙여넣기 이미지 막기
                    }
                }
            }
        }
    });
});

function fbuildingbbs_submit(f) {

    if (isOverflowing()) {
        alert("내용이 A4 용지 크기를 초과했습니다.\n다시 조정해주세요.");
        return false;
    }

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

