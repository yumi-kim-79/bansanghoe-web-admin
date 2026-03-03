<?php
$sub_menu = "200800";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '추가';
}

$g5['title'] .= '이벤트 ' . $html_title;
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
<form name="fbuildingbbs" id="fbuildingbbs" action="./building_news_event_form_update.php" onsubmit="return fbuildingbbs_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="bb_id" value="<?php echo $row['bb_id']; ?>">
    <input type="hidden" name="bbs_type" value="event">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">이벤트 정보</h2>
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
                    <td colspan='3'>
                        <select name="post_id" id="post_id" class="bansang_sel" required onchange="post_change();">
                            <option value="">전체</option>
                            
                            <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                                <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($row['post_id'], $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                            <?php }?>
                        </select>
                        <script>
                            //  function post_change(){
                            //     var postSelect = document.getElementById("post_id");
                            //     var postValue = postSelect.options[postSelect.selectedIndex].value;

                            //     console.log('postValue', postValue);

                            //     $.ajax({

                            //     url : "./post_building_ajax.php", //ajax 통신할 파일
                            //     type : "POST", // 형식
                            //     data: { "post_id":postValue, "all":"Y"}, //파라미터 값
                            //     success: function(msg){ //성공시 이벤트

                            //         //console.log(msg);
                            //         $("#building_id").html(msg);
                            //     }

                            //     });
                            // }

                            function post_change(){
                                var postSelect = document.getElementById("post_id");
                                var postValue = postSelect.options[postSelect.selectedIndex].value;

                                console.log('postValue', postValue);

                                if(postValue != ""){
                                }else{
                                    $("#building_id").val("");
                                    $("#building_name").val("");
                                    $("#bb_num").val("");
                                    $("#bb_number").val("");
                                }
                            }
                        </script>
                    </td>
                </tr>
                <tr>
                    <th>단지</th>
                    <td colspan='3'>
                        <?php
                        $building_all_checked = $row['building_id'] == '-1' ? 'checked' : '';
                        $all_checked_disable = $row['building_id'] == '-1' ? 'disabled' : '';

                        $building_names = $row['building_id'] != '' ? get_builiding_info($row['building_id'])['building_name'] : '';
                        ?>
                        <div class="building_all_chk_box mgb10">
                            <input type="checkbox" name="building_all" id="building_all" value="1" <?php echo $building_all_checked; ?>>
                            <label for="building_all">전체</label>
                        </div>
                        <div class="sch_box_wrap ">
                            <div class="sch_box_left">
                                <div class="sch_result_box">
                                    <!-- <button type="button">푸르지오</button>
                                    <button type="button">푸르지오2</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button> -->
                                </div>
                                <!-- 검색어를 입력해주세요. -->
                                <input type="text" name="building_sch" id="building_sch" class="bansang_ipt ver2" size="50" placeholder="단지명을 입력하세요." >
                            </div>
                            <!-- <div class="sch_box_right">
                                <button type="button" class="bansang_btns ver1" onclick="building_handler();">검색</button>
                            </div> -->
                        </div>
                        <input type="hidden" name="building_id" id="building_id" value="<?php echo $row['building_id']; ?>">
                        <input type="text" name="building_name" id="building_name" class="bansang_ipt <?php echo $row['building_id'] == '-1' ? '' : 'ver2'; ?> mgt10" size="100" placeholder="선택한 단지가 보여집니다." readonly value="<?php echo $building_names; ?>" readonly <?php echo $row['building_id'] == '-1' ? '' : 'required'; ?>>

                        <script>

                            function bbs_number_handler(building_id){

                                let post_id = $("#post_id option:selected").val();
                                let building_or = "<?php echo $row['building_id']; ?>";

                                let sendData = { 'post_id':post_id, 'building_id': building_id, 'bbs_type':'event'};

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

                                            if(building_id != building_or){
                                                $("#bb_num").val(data.data);
                                                $("#bb_number").val(data.msg);
                                            }

                                           
                                            // showToast(data.msg);
                                            // $("#mb_id").addClass('actives');
                                            // $("#id_chk").val(1);
                                        
                                        }
                                    },
                                });
                            }

                            $("#building_all").click(function () {
                                //console.log($("#burl_use").is(":checked"));
                                let post_id = $("#post_id option:selected").val();

                                if(post_id == ""){
                                    alert("지역을 먼저 선택해주세요.");
                                    $("#building_all").prop('checked', false);
                                    return false;
                                }

                                let w = "<?php $w; ?>";

                                if(!$("#building_all").is(":checked")){
                                    $("#building_sch").attr('readonly', false);
                                    $("#building_sch").addClass('ver2');

                                    $("#building_name").attr('readonly', false);
                                    $("#building_name").attr('required', true);
                                    $("#building_name").addClass('ver2');
                                    $("#building_id").val('');

                                    $("#bb_num").val("");
                                    $("#bb_number").val("");
                                
                                }else{
                                    $("#building_sch").attr('readonly', true);
                                    $("#building_sch").removeClass('ver2');

                                    $("#building_name").attr('readonly', true);
                                    $("#building_name").removeClass('ver2');
                                    $("#building_name").val('');
                                    $("#building_name").attr('required', false);

                                    $("#building_id").val('-1');
                                   
                                    bbs_number_handler(-1);
                                   
                                }
                            });

                            $(document).on("keyup", "#building_sch", function(){
                                var post_id = $("#post_id option:selected").val();
                                let sch_text = this.value;

                                if(sch_text != ""){

                                    $(".sch_result_box").show();

                                    $.ajax({

                                    url : "./manage_form_sch_ajax.php", //ajax 통신할 파일
                                    type : "POST", // 형식
                                    data: { "building_name":sch_text, "post_id":post_id}, //파라미터 값
                                    success: function(msg){ //성공시 이벤트

                                        console.log('keyup',msg);
                                    
                                        $(".sch_result_box").html(msg); //.select_box2에 html로 나타내라..
                                    }

                                    });
                                }else{
                                    $(".sch_result_box").html("");
                                }
                            });

                            //단지선택..
                            function building_select(id, name){
                                //alert(id);
                                let w = "<?php $w; ?>";

                                $("#building_id").val(id);
                                $("#building_name").val(name);
                                $("#building_sch").val("");
                                $(".sch_result_box").hide();

                                //지역 변경
                                let sendData = {'building_id': id};

                                $.ajax({
                                    type: "POST",
                                    url: "./building_post_ajax.php",
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
                                        
                                            $("#post_id").val(data.msg).change();

                                            setTimeout(() => {
                                                 //문서번호
                                                bbs_number_handler(id);
                                            }, 300);
                                        
                                        }
                                    },
                                });
                                
                            }
                        </script>
                    </td>
                </tr>
                <th>문서번호</th>
                <td colspan="3">
                    <input type="hidden" name="bb_num" id="bb_num" value="<?php echo $row['bn_number']; ?>">
                    <input type="text" name="bb_number" id="bb_number" class="bansang_ipt ver2" value="<?php echo $row['bb_number']; ?>" readonly size="100">
                </td>
                <tr>
                    <th>제목</th>
                    <td colspan="3">
                        <input type="text" name="bb_title" id="bb_title" class="bansang_ipt ver2" size="100" value="<?php echo $row['bb_title']; ?>" required>
                    </td>
                </tr>
                <tr>
                    <th>상태</th>
                    <td colspan='3'>
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
        <button type="button" onclick="preview_info();" class="btn btn_05">미리보기</button>
 
        <a href="./building_news_event.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <?php if($w == 'u'){?>
        <button type="button" onclick="print_info('<?php echo $bb_id; ?>');" class="btn btn_01">인쇄</button>
        <?php }?>
        <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'>
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

function print_info(bb_idx) // 회원 엑셀 업로드를 위하여 추가
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
    let bb_content = `<?php echo $row['bb_content']; ?>`;
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

