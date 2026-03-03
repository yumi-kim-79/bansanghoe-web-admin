<?php
$sub_menu = $_GET['type'] == "progress" ? "200600" : "200700";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '추가';
}

$g5['title'] .= '공문 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
//require_once G5_EDITOR_LIB;

$sql = "SELECT * FROM a_building_bbs
        WHERE bb_id = {$bb_id}";
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
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th>지역</th>
                    <td>
                        <select name="post_id" id="post_id" class="bansang_sel" required onchange="post_change();">
                            <option value="">선택</option>
                            <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                                <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($row['post_id'], $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                            <?php }?>
                        </select>
                        <script>
                             function post_change(){
                                var postSelect = document.getElementById("post_id");
                                var postValue = postSelect.options[postSelect.selectedIndex].value;

                                console.log('postValue', postValue);

                                $.ajax({

                                url : "./post_building_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "post_id":postValue, "all":"Y"}, //파라미터 값
                                success: function(msg){ //성공시 이벤트

                                    //console.log(msg);
                                    $("#building_id").html(msg);
                                }

                                });
                            }
                        </script>
                    </td>
                    <th>단지</th>
                    <td>
                        <?php
                        $sql_building = "SELECT * FROM a_building WHERE post_id = '{$row['post_id']}' and is_del = 0";
                        $res_building = sql_query($sql_building);
                        
                        ?>
                        <select name="building_id" id="building_id" class="bansang_sel" onchange="building_change();" required>
                            <option value=""><?php echo $w == 'u' ? '단지를' : '지역을'; ?> 선택해주세요.</option>
                            <?php if($w == 'u'){?>
                            <option value="-1" <?php echo get_selected($row['building_id'], '-1'); ?>>전체</option>
                            <?php }?>
                            <?php
                            while($row_building = sql_fetch_array($res_building)){
                            ?>
                            <option value="<?php echo $row_building['building_id']?>" <?php echo get_selected($row['building_id'], $row_building['building_id']); ?>><?php echo $row_building['building_name'];?></option>
                            <?php }?>
                        </select>
                        <script>
                            function building_change(){
                                let w = "<?php echo $w; ?>";
                                var buildingSelect = document.getElementById("building_id");
                                var buildingValue = buildingSelect.options[buildingSelect.selectedIndex].value;

                                let post_id = $("#post_id option:selected").val();
                                console.log('buildingValue', buildingValue);

                                let sendData = { 'post_id':post_id, 'building_id': buildingValue, 'bbs_type':'public'};

                                if(w != 'u'){
                                    $.ajax({
                                        type: "POST",
                                        url: "./building_news_info_number.php",
                                        data: sendData,
                                        cache: false,
                                        async: false,
                                        dataType: "json",
                                        success: function(data) {
                                            console.log('data:::', data);

                                            if(data.result == false) { 
                                                alert(data.msg);
                                            
                                                return false;
                                            }else{

                                                $("#bb_num").val(data.data);
                                                $("#bb_number").val(data.msg);
                                                // showToast(data.msg);
                                                // $("#mb_id").addClass('actives');
                                                // $("#id_chk").val(1);
                                            
                                            }
                                        },
                                    });
                                }
                            }
                        </script>
                    </td>
                </tr>
                <th>문서번호</th>
                <td colspan="3">
                    <input type="hidden" name="bb_num" id="bb_num" value="<?php echo $row['bn_number']; ?>">
                    <input type="text" name="bb_number" id="bb_number" class="bansang_ipt ver2" value="<?php echo $row['bb_number']; ?>" readonly>
                </td>
                <tr>
                    <th>제목</th>
                    <td colspan="3">
                        <input type="text" name="bb_title" class="bansang_ipt ver2" size="100" value="<?php echo $row['bb_title']; ?>" required>
                    </td>
                </tr>
                <tr>
                    <th>상태</th>
                    <td colspan="3">
                        <select name="is_view" id="is_view" class="bansang_sel" required>
                            <option value="">선택</option>
                            <option value="1" <?php echo get_selected($row['is_view'], '1'); ?>>노출</option>
                            <option value="0" <?php echo get_selected($row['is_view'], '0'); ?>>미노출</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>내용</th>
                    <td colspan="3">
                        <?php echo help('양식을 지우지 않도록 주의해주세요.'); ?>
                        <?php echo help('영역을 넘어가지 않도록 주의해주세요.'); ?>
                        <textarea name="bb_content" id="bb_content"></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <a href="./building_news_public.php?type=<?php echo $type;?>&<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <?php if($w == 'u'){?>
        <button type="button" onclick="print_info('<?php echo $bb_id; ?>');" class="btn btn_01">인쇄</button>
        <?php }?>
        <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'>
    </div>
</form>

<script>
function print_info(bb_idx) // 회원 엑셀 업로드를 위하여 추가
{ 

    var opt = "width=810,height=1200,left=10,top=10"; 
    var url = "./building_news_print.php?bb_idx=" + bb_idx;

    window.open(url, "win_news", opt); 

    return false; 

}

function sendFile(file, editor) {

    data = new FormData();
    data.append("SummernoteFile", file);
    $.ajax({
    data: data,
    type: "POST",
    url: "<?php echo $editor_url ?>/upload.php",
    cache: false,
    contentType: false,
    processData: false,
    success: function(data) {
        var obj =  JSON.parse(data);
        if (obj.success) {
            $(editor).summernote("insertImage", obj.save_url);
        } else {
            switch(parseInt(obj.error)) {
                case 1: alert('업로드 용량 제한에 걸렸습니다.'); break; 
                case 2: alert('MAX_FILE_SIZE 보다 큰 파일은 업로드할 수 없습니다.'); break;
                case 3: alert('파일이 일부분만 전송되었습니다.'); break;
                case 4: alert('파일이 전송되지 않았습니다.'); break;
                case 6: alert('임시 폴더가 없습니다.'); break;
                case 7: alert('파일 쓰기 실패'); break;
                case 8: alert('알수 없는 오류입니다.'); break;
                case 100: alert('이미지 파일이 아닙니다.(jpeg, jpg, gif, bmp, png 만 올리실 수 있습니다.)'); break; 
                case 101: alert('이미지 파일이 아닙니다.(jpeg, jpg, gif, bmp, png 만 올리실 수 있습니다.)'); break; 
                case 102: alert('0 byte 파일은 업로드 할 수 없습니다.'); break; 
            }
        }
    }
    });
}

$(document).ready(function() {
    let presetContent = `
        <div class="preset_info" style="
            width: 210mm;
            height: 297mm;
            margin: auto;
            padding: 40mm 5mm;
            position: relative;
            background: url('/images/building_news_sample.jpg') no-repeat center center;
            background-size: cover;
        ">
        </div>
    `;

    let w = "<?php echo $w; ?>";

    let bb_content = `<?php echo $row['bb_content']; ?>`;

    $('#bb_content').summernote({
        height: 1200, 
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
                //"superscript",
                //"subscript",
                "clear",
            ],
            ],
            ["fontname", ["fontname"]],
            ["fontsize", ["fontsize"]],
            ["color", ["color"]],
            ["para", ["paragraph"]],
            ["height", ["height"]],
            ["table", ["table"]],
            ["insert", ["link", "picture"]],
            ["view", ["fullscreen", "codeview"]],
            //["help", ["help"]],
        ],
        callbacks: {
            onInit: function() {
                if(w == ''){
                    $('#bb_content').summernote('code', presetContent);
                }else{
                    $('#bb_content').summernote('code', bb_content);
                }
            },
            onKeydown: function(e) {
                // Enter 키를 눌렀을 때만 작동하도록 처리
                if (e.keyCode === 13 && !e.shiftKey) { 
                    e.preventDefault(); // 기본 Enter 동작 방지
                    // <br> 태그 삽입 (Shift + Enter처럼 동작하도록)
                    var range = window.getSelection().getRangeAt(0);
                    var br = document.createElement('br');
                    range.deleteContents(); // 현재 커서 위치의 텍스트 삭제
                    range.insertNode(br); // <br> 태그 삽입
                    range.setStartAfter(br); // 커서를 <br> 태그 뒤로 이동시킴
                    range.setEndAfter(br);   // 커서를 <br> 태그 뒤로 이동시킴
                }
            },
            onImageUpload: function (files) {
                /** upload start */

                var maxSize = 1 * 1024 * 1024; // limit 1MB
                // TODO: implements insert image
                var isMaxSize = false;
                var maxFile = null;
                for (var i = 0; i < files.length; i++) {
                    if (files[i].size > maxSize) {
                    isMaxSize = true;
                    maxFile = files[i].name;
                    break;
                    }
                    //sendFile(files[i], this);
                }

                if (isMaxSize) {
                    // 사이즈 제한에 걸렸을 때
                    alert(
                    "[" + maxFile + "] 파일이 업로드 용량(1MB)을 초과하였습니다."
                    );
                } else {
                    for (var i = 0; i < files.length; i++) {
                    sendFile(files[i], this);
                    }
                }
            /** upload end */
            },
            onPaste: function (e) {
                var clipboardData = e.originalEvent.clipboardData;
                if (
                    clipboardData &&
                    clipboardData.items &&
                    clipboardData.items.length
                ) {
                    var item = clipboardData.items[0];
                    if (item.kind === "file" && item.type.indexOf("image/") !== -1) {
                    e.preventDefault();
                    }
                }
            },
        }
    });
});

function fbuildingbbs_submit(f) {
    

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

