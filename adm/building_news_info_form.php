<?php
$sub_menu = "200500";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '추가';
}

$g5['title'] .= '안내문 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
//require_once G5_EDITOR_LIB;

$sql = "SELECT bb.*, bn.bn_number FROM a_building_bbs as bb
        LEFT JOIN a_bbs_number as bn on bb.bb_id = bn.bb_id
        WHERE bb.bb_id = {$bb_id}";
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
    /* background: transparent !important; */
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
<form name="fbuildingbbs" id="fbuildingbbs" action="./building_news_info_update.php" onsubmit="return fbuildingbbs_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="bb_id" value="<?php echo $row['bb_id']; ?>">
    <input type="hidden" name="type" value="infomation">
    <input type="hidden" name="pr_idx" id="pr_idx" value="">

    <!-- 250317 ban -->

    <input type="hidden" name="editorImage" id="editorImage">
    <img id="preview" alt="이미지 미리보기" style="display:none">
    <canvas id="canvas"  style="display:none"></canvas>

    <!-- 250317 ban -->
    
    

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">안내문 정보</h2>
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
                    <!-- <th>단지</th>
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

                                let sendData = { 'post_id':post_id, 'building_id': buildingValue, 'bbs_type':'infomation'};

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
                    </td> -->
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
                            <input type="checkbox" name="building_all" id="building_all" value="1"  <?php echo $building_all_checked; ?>>
                            <label for="building_all">단지 전체</label>
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
                        <label for="building_name" class="sound_only">단지선택</label>
                        <input type="text" name="building_name" id="building_name" class="bansang_ipt <?php echo $row['building_id'] == '-1' ? '' : 'ver2'; ?> mgt10" size="100" placeholder="선택한 단지가 보여집니다." readonly value="<?php echo $building_names; ?>" readonly <?php echo $row['building_id'] == '-1' ? '' : 'required'; ?>>

                        <script>

                            function bbs_number_handler(building_id){

                                let w = "<?php echo $w; ?>";
                                let post_id = $("#post_id option:selected").val();
                                let building_or = "<?php echo $row['building_id']; ?>";

                                let sendData = { 'post_id':post_id, 'building_id': building_id, 'bbs_type':'infomation'};

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
                <tr>
                    <th>작성일</th>
                    <td colspan="3">
                        <input type="text" name="created_at" id="date_ipt" class="bansang_ipt ver2 ipt_date" value="<?php echo $w == 'u' ? date('Y-m-d', strtotime($row['created_at'])) : date("Y-m-d"); ?>">
                    </td>
                </tr>
                <tr>
                    <th>문서번호</th>
                    <td>
                        <input type="hidden" name="bb_num" id="bb_num" value="<?php echo $row['bn_number']; ?>">
                        <input type="text" name="bb_number" id="bb_number" class="bansang_ipt ver2" value="<?php echo $row['bb_number']; ?>" readonly size="50">
                    </td>
                    <th>게시 기한</th>
                    <td>
                        <div class="gigan_chk">
                            <input type="checkbox" name="bbs_gigan" id="bbs_gigan" value="1" <?php echo $row['bbs_gigan'] == '1' ? 'checked' : ''; ?>>
                            <label for="bbs_gigan">영구게시</label>
                        </div>
                        <script>
                            $("#bbs_gigan").click(function () {
                                //console.log($("#burl_use").is(":checked"));
                                if(!$("#bbs_gigan").is(":checked")){
                                    $(".ipt_date2").attr('disabled', false);
                                    $(".ipt_date2").attr('required', true);
                                    $(".ipt_date2").addClass('ver2');
                                }else{
                                    $(".ipt_date2").attr('disabled', true);
                                    $(".ipt_date2").attr('required', false);
                                    $(".ipt_date2").removeClass('ver2');
                                }
                            });
                        </script>
                        <div class="ipt_box flex_ver">
                            <input type="text" name="sdate" id="sdate" class="bansang_ipt <?php echo $row['bbs_gigan'] == '1' ? '' : 'ver2'; ?> ipt_date ipt_date2" value="<?php echo $row['sdate']; ?>"  <?php echo $row['bbs_gigan'] == '1' ? '' : 'required'; ?> <?php echo $row['bbs_gigan'] == '1' ? 'disabled' : ''; ?>> ~ <input type="text" name="edate" id="edate" class="bansang_ipt <?php echo $row['bbs_gigan'] == '1' ? '' : 'ver2'; ?> ipt_date ipt_date2" value="<?php echo $row['edate']; ?>" <?php echo $row['bbs_gigan'] == '1' ? '' : 'required'; ?> <?php echo $row['bbs_gigan'] == '1' ? 'disabled' : ''; ?>>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>상태</th>
                    <td colspan="3">
                            <?php
                            switch($row['is_submit']){
                                case "S":
                                    $status = "발행";
                                break;
                                case "R":
                                    $status = "회수";
                                break;
                                default:
                                    $status = "발행전";
                                break;
                            }
                             
                             ?>
                        <input type="text" name="bb_status" class="bansang_ipt ver2" value="<?php echo $status; ?>" readonly>
                    </td>
                </tr>
                <tr>
                    <th>제목</th>
                    <td colspan="3">
                        <input type="text" name="bb_title" id="bb_title" class="bansang_ipt ver2" size="100" value="<?php echo $row['bb_title']; ?>" required>
                    </td>
                </tr>
                <tr>
                    <th>안내문</th>
                    <td colspan="3">
                        <div class="preset_wrap">
                            <div class="preset_left_box">
                                <?php if($w == ""){?>
                                <div class="preset_label">프리셋</div>
                                <div class="preset_select_button">
                                    <button type="button" onclick="popOpen('preset_select_pop');" class="preset_btn">프리셋 선택</button>
                                </div>
                                <?php }?>
                            </div>
                            <div class="preset_right_box">
                                <div class="preset_setting_button">
                                    <button type="button" onclick="popOpen('preset_setting_pop');" class="preset_btn">프리셋 관리</button>
                                </div>
                            </div>
                        </div>
                        <textarea name="bb_content" id="bb_content"></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <button type="button" onclick="print_info();" class="btn btn_05">미리보기</button>

        <a href="./building_news_info.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <?php if($row['is_submit'] != 'S'){?>
        <input type="submit" value="저장" class="btn_submit btn" accesskey='s'>
        <?php }?>
        <?php if($w == 'u'){ ?>
            <?php
                $preset_bbid_confirm = "SELECT COUNT(*) as cnt FROM a_bbs_preset WHERE bb_id = '{$bb_id}'";
                $preset_bbid_confirm_row = sql_fetch($preset_bbid_confirm);

                if($preset_bbid_confirm_row['cnt'] > 0){
                    $onclicks = "alert('이미 프리셋으로 저장된 안내문입니다.');";
                    $pr_btn = "btn_02";
                }else{
                    $onclicks = "popOpen('preset_save_pop');";
                    $pr_btn = "btn_03";
                }
            ?>
            <button type="button" onclick="<?php echo $onclicks; ?>" class="btn <?php echo $pr_btn; ?>" >프리셋 저장</button>
            <?php if($row['is_submit'] == 'S'){?>
            <button type="button" onclick="bbs_recall()" class="btn btn_01">회수</button>
            <?php }else{ ?>
            <button type="button" onclick="bbs_submit()" class="btn btn_03">발행</button>
            <?php }?>
        <?php }?>
    </div>
</form>

<script>
    function print_info() // 회원 엑셀 업로드를 위하여 추가
    { 
        const title = $("#bb_title").val();
        const content = $('#bb_content').summernote('code');
        const building_id = $("#building_id").val();
        const bb_number = $("#bb_number").val();
        const isGiganChecked = $('#bbs_gigan').is(':checked');
        const edate = $('#edate').val();

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

        if (isGiganChecked) {
            const inputGigan = document.createElement("input");
            inputGigan.type = "hidden";
            inputGigan.name = "bbs_gigan";
            inputGigan.value = "1";
            form.appendChild(inputGigan);
        }else{
            const inputEdate = document.createElement("input");
            inputEdate.type = "hidden";
            inputEdate.name = "edates";
            inputEdate.value = edate;
            form.appendChild(inputEdate);
        }

        document.body.appendChild(form);

        const opt = "width=810,height=1200,left=10,top=10";
        window.open('', "win_news", opt);
        form.submit();

        return false;
    }
</script>

<div class="cm_pop" id="bbs_recall_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('bbs_recall_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">회수 사유</div>
        <div class="cm_pop_textarea mgt15">
            <textarea name="recall_memo" id="recall_memo" class="bansang_ipt ver2 ta ta_n full"><?php echo $row['recall_memo']; ?></textarea>
        </div>
		<div class="cm_pop_btn_box flex_ver flex_ver_ta">
			<button type="button" class="cm_pop_btn ver2" onClick="bbs_recall_submit();">확인</button>
		</div>
	</div>
</div>

<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>안내문을 저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>

<!-- 프리셋 선택 -->
<div class="cm_pop" id="preset_select_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onclick="popClose('preset_select_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">
            프리셋 선택
        </div>
        <div class="preset_setting_pop_cont">
            <input type="hidden" name="pw" value="u">
            <?php
            $preset_list = "SELECT * FROM a_bbs_preset WHERE is_del = 0 ORDER BY pr_idx desc";
            //echo $preset_list;
            $preset_res = sql_query($preset_list);
            ?>
            <div class="preset_setting_list preset_select_list">
                <?php for($i=0;$preset_row = sql_fetch_array($preset_res);$i++){?>
                    <div class="preset_select_box">
                        <input type="radio" name="preset_select" id="preset_select<?php echo $i+1;?>" value="<?php echo $preset_row['pr_idx']; ?>">
                        <label for="preset_select<?php echo $i+1;?>"><?php echo $preset_row['pr_name']; ?></label>
                    </div>
                <?php }?>
                <?php if($i==0){?>
                    <div class="preset_empty">등록된 프리셋이 없습니다.</div>
                <?php }?>
            </div>
        </div>
        <div class="preset_form_btn_wrap">
            <button type="button" onclick="popClose('preset_select_pop');" class="preset_btn_cancel">취소</button>
            <button type="button" onclick="preset_select_handler();" class="preset_btn_submit">선택</button>
        </div>
    </div>
</div>

<script>
    function preset_select_handler(){
        const selected = document.querySelector('input[name="preset_select"]:checked');
        if (!selected) {
            alert("프리셋을 선택해주세요.");
        }

        //console.log(selected.value);
        let sendData = {'pr_idx': selected.value};

        $.ajax({
            type: "POST",
            url: "./building_new_info_preset_ajax.php",
            data: sendData,
            cache: false,
            async: false,
            dataType: "json",
            success: function(data) {
                console.log('data:::', data);

                if(data.result == false) { 
                    showToast(data.msg);
                    return false;
                }else{
                    //showToast(data.msg);
                    $("#pr_idx").val(data.data.pr_idx)
                    // $("#bb_number").val(data.data.bb_number);
                    $("#bb_title").val(data.data.bb_title);
                   
                    $("#sdate").val(data.data.sdate);
                    $("#edate").val(data.data.edate);

                    $('#bb_content').summernote('code', data.data.bb_content);

                    // $('#post_id').val(data.data.post_id);

                    if(data.data.bbs_gigan){
                        $("#bbs_gigan").prop("checked", true);
                        $(".ipt_date2").attr('disabled', true);
                        $(".ipt_date2").attr('required', false);
                        $(".ipt_date2").removeClass('ver2');
                    }

                    $.ajax({

                    url : "./post_building_ajax.php", //ajax 통신할 파일
                    type : "POST", // 형식
                    data: { "post_id":data.data.post_id}, //파라미터 값
                    success: function(msg){ //성공시 이벤트

                        //console.log(msg);
                        // $("#building_id").html(msg);

                        setTimeout(() => {
                            // $('#building_id').val(data.data.building_id); 
                        }, 300);
                       
                    }

                    });
                  

                    popClose('preset_select_pop');
                }
            },
        });
    }
</script>

<!-- 프리셋 관리 -->
<div class="cm_pop" id="preset_setting_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onclick="popClose('preset_setting_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">
            프리셋 관리
        </div>
        <form name="fpresetsave" id="fpresetsave" action="./preset_form_update.php" onsubmit="return fpresetsave_submit(this);" method="post">
        <div class="preset_setting_pop_cont">
            <input type="hidden" name="pw" value="u">
            <?php
            $preset_list = "SELECT * FROM a_bbs_preset WHERE is_del = 0 ORDER BY pr_idx asc";
            //echo $preset_list;
            $preset_res = sql_query($preset_list);
            $preset_total = sql_num_rows($preset_res);
            ?>
            <div class="preset_setting_list">
                <?php for($i=0;$preset_row = sql_fetch_array($preset_res);$i++){?>
                    <div class="preset_list_box">
                        <input type="hidden" name="pr_idx[]" value="<?php echo $preset_row['pr_idx']; ?>">
                        <input type="text" name="pr_name[]" class="bansang_ipt ver2" value="<?php echo $preset_row['pr_name']; ?>" required>
                        <div class="preset_del_box">
                            <input type="checkbox" name="preset_del[<?php echo $i;?>]" id="preset_del<?php echo $i + 1;?>" value="1">
                            <label for="preset_del<?php echo $i + 1;?>">삭제</label>
                        </div>
                    </div>
                <?php }?>
                <?php if($i==0){?>
                    <div class="preset_empty">등록된 프리셋이 없습니다.</div>
                <?php }?>
            </div>
        </div>
        <div class="preset_form_btn_wrap">
            <button type="button" onclick="popClose('preset_setting_pop');" class="preset_btn_cancel <?php echo $preset_total == 0 ? 'full' : '';?>">취소</button>
            <?php if($preset_total > 0){?>
            <button type="submit" class="preset_btn_submit">저장</button>
            <?php }?>
        </div>
        </form>
    </div>
</div>

<!-- 프리셋 저장 -->
<div class="cm_pop" id="preset_save_pop" >
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onclick="popClose('preset_save_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">
            프리셋 저장
        </div>
        <div class="cm_pop_title2">
            해당 안내문을 프리셋 저장하시겠습니까?
        </div>
        <div class="preset_save_form_wrap">
            <form name="fpreset" id="fpreset" action="./preset_form_update.php" onsubmit="return fpreset_submit(this);" method="post">
                <input type="hidden" name="pw" value="">
                <input type="hidden" name="bb_id" value="<?php echo $bb_id; ?>">
                <div class="preset_form_box">
                    <div class="preset_form_label">프리셋명</div>
                    <div class="preset_form_ipt">
                        <input type="text" name="pr_name" class="bansang_ipt ver2 full" required placeholder="프리셋 명을 입력해주세요.">
                    </div>
                </div>
                <div class="preset_form_btn_wrap">
                    <button type="button" class="preset_btn_cancel">취소</button>
                    <button type="submit" class="preset_btn_submit">저장</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
function buildingInfoPopOpen(){
    $("#building_info_pop").show();
    bodyLock();
}

function buildingInfoPopClose(){
    $("#building_info_pop").hide();
    bodyUnlock();
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

                // setTimeout(() => {
                //     if (isOverflowing()) {
                //         alert("A4 용지 크기를 초과했습니다. 붙여넣기를 취소합니다.");
                //         $('#bb_content').summernote('code', lastValidContent);
                //     } else {
                //         lastValidContent = $('#bb_content').summernote('code');
                //     }
                // }, 100); // 붙여넣기 후 DOM 갱신 시간 고려

            }
        }
    });
});

$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
});

function bbs_submit(){
    if (!confirm("해당 글을 발행 상태로 변경하시겠습니까?\n발행 후에는 수정할 수 없습니다.")) {
        return false;
    }

    let building_id = $("#building_id").val();
    let post_id = $("#post_id option:selected").val();

    var formData = new FormData();
    formData.append('bb_id', "<?php echo $row['bb_id']; ?>");
    formData.append('building_id', building_id);
    formData.append('post_id', post_id);

    $.ajax({
        type: "POST",
        url: "./building_news_submit.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                alert(data.msg);
                return false;
            }else{
                alert(data.msg);

                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        },
        error:function(e){
            alert(e);
        }
    });
}

function bbs_recall(){
    if (!confirm("해당 글을 회수 상태로 변경하시겠습니까?")) {
        return false;
    }

    popOpen('bbs_recall_pop');
}

function bbs_recall_submit(){

    let recall_memo = $("#recall_memo").val();

    if(recall_memo == ""){
        alert("회수 사유를 입력해주세요.");
        return false;
    } 

    var formData = new FormData();
    formData.append('bb_id', "<?php echo $row['bb_id']; ?>");
    formData.append('recall_memo', recall_memo);

    $.ajax({
        type: "POST",
        url: "./building_news_recall.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                alert(data.msg);
                return false;
            }else{
                alert(data.msg);

                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        },
        error:function(e){
            alert(e);
        }
    });
}

//날짜형식
function checkValidDate(value) {
	var result = true;
	try {
	    var date = value.split("-");
	    var y = parseInt(date[0], 10),
	        m = parseInt(date[1], 10),
	        d = parseInt(date[2], 10);
	    
	    var dateRegex = /^(?=\d)(?:(?:31(?!.(?:0?[2469]|11))|(?:30|29)(?!.0?2)|29(?=.0?2.(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(?:\x20|$))|(?:2[0-8]|1\d|0?[1-9]))([-.\/])(?:1[012]|0?[1-9])\1(?:1[6-9]|[2-9]\d)?\d\d(?:(?=\x20\d)\x20|$))?(((0?[1-9]|1[012])(:[0-5]\d){0,2}(\x20[AP]M))|([01]\d|2[0-3])(:[0-5]\d){1,2})?$/;
	    result = dateRegex.test(d+'-'+m+'-'+y);
	} catch (err) {
		result = false;
	}    
    return result;
}



function fbuildingbbs_submit(f) {

    if (!f.bbs_gigan && !f.bbs_gigan.checked) {
        if(f.sdate.value != "" && f.edate.value != ""){
            if(!checkValidDate(f.sdate.value)){
                alert("게시기한 시작일을 날짜 형식에 맞게 입력해주세요.");
                f.sdate.focus();
                return false;
            }

            if(!checkValidDate(f.edate.value)){
                alert("게시기한 종료일을 날짜 형식에 맞게 입력해주세요.");
                f.edate.focus();
                return false;
            }

            if(f.sdate.value > f.edate.value){
                alert("게시 시작일이 종료일보다 이후일 수 없습니다.");
                f.sdate.focus();
                return false;
            }
        }
    }
   
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

