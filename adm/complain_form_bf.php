<?php
$sub_menu = "500200";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '등록';
}

$g5['title'] .= '민원(이전자료)';
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;

$sql = "SELECT 
        qa.*,
        u.name as 'rname',
	    u.contact as 'rhp',
        a1.duty,
	    concat(a1.username, '(' ,a1.nick_name, ')') as 'complete_name',
        h.dong,
        h.ho,
        e.name as 'building_name',
        e.address
        FROM question_answer as qa
        LEFT JOIN `admin` a1 on qa.admin = a1.seq 
        LEFT JOIN house h on qa.house = h.seq 
        LEFT JOIN user u on h.tenant = u.seq 
        LEFT JOIN estate e on qa.estate = e.seq 
        WHERE qa.seq = {$complain_idx}";
$row = sql_fetch($sql);


//추가
$add_sql = "SELECT * FROM question_answer_comment WHERE question_answer = '{$complain_idx}' ORDER BY id asc";
$add_res = sql_query($add_sql);
$add_total = sql_num_rows($add_res);

$file_sql = "SELECT qai.*, s3.url FROM 
            question_answer_image as qai 
            LEFT JOIN s3_file as s3 ON qai.s3_file = s3.seq
            WHERE qai.qa = '{$complain_idx}'
            ORDER BY qai.id asc";
$file_res = sql_query($file_sql);
$file_total = sql_num_rows($file_res);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql.'<br>';
    echo $add_sql.'<br>';
    echo $file_sql.'<br>';
    //print_r2($row);
}
?>

<form name="fcomplain" id="fcomplain" action="./student_form_update.php" onsubmit="return fcomplain_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="complain_idx" value="<?php echo $row['complain_idx']; ?>">
   
    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">민원 정보</h2>
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
                    <th>접수 구분</th>
                    <td colspan="3">
                        <?php echo $row['register_type'] == "ADMIN" ? "관리자 접수 민원" : "앱 접수 민원";?>
                    </td>
                </tr>
                <tr>
                    <th>지역</th>
                    <td colspan="3">
                        <?php
                        $addr = explode(" ", $row['address']);
                        echo $addr[0];
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>단지</th>
                    <td colspan="3">
                        <?php echo $row['building_name']; ?>
                    </td>
                </tr>
                <tr>
                    <th>동</th>
                    <td>
                        <?php echo $row['dong'].'동';?>
                    </td>
                    <th>호수</th>
                    <td>
                        <?php echo $row['ho'].'호';?>
                    </td>
                </tr>
                <tr>
                    <th>접수일</th>
                    <td colspan="3">
                        <?php echo date("Y-m-d", strtotime($row['create_date'])); ?>
                    </td>
                </tr>
                <tr>
                    <th>민원인</th>
                    <td>
                       <?php echo $row['rname']; ?>
                    </td>
                    <th>민원인 연락처</th>
                    <td>
                        <?php echo $row['rhp']; ?>
                    </td>
                </tr>
                <tr>
                    <th>담당자 직급</th>
                    <td>
                        <?php echo $row['duty']; ?>
                    </td>
                    <th>담당자</th>
                    <td>
                       <?php echo $row['complete_name']; ?>
                    </td>
                </tr>
                <?php if($row['mng_id'] != ''){?>
                <tr class="mng_change_memo_tr">
                    <th>담당자 변경 사유</th>
                    <td colspan='3'>
                        <textarea name="mng_change_memo" id="mng_change_memo" class="bansang_ipt full ta" readonly><?php echo $row['mng_change_memo'];?></textarea>
                    </td>
                </tr>
                <?php }?>
                <tr>
                    <th>상태</th>
                    <td>
                        <?php
                        $status = '';
                        switch($row['status']){
                            case "0":
                                $status = "접수대기";
                                break;
                            case "1":
                                $status = "완료";
                                break;
                            case "2":
                                $status = "진행중";
                                break;
                        }
                            echo $status; 
                            ?>
                    </td>
                </tr>
                <tr>
                    <th>민원제목</th>
                    <td colspan="3"><?php echo $row['title']; ?></td>
                </tr>
                <tr>
                    <th>민원내용</th>
                    <td colspan="<?php echo $w == 'u' ? '' : '3'; ?>">
                       <?php echo nl2br($row['question']);?>
                    </td>
                    <?php if($w == "u"){?>
                    <th>민원 답변</th>
                    <td>
                    <?php echo nl2br($row['answer']);?>
                    </td>
                    <?php }?>
                </tr>
                <?php if($file_total > 0){?>
                <tr>
                    <th>첨부사진</th>
                    <td colspan='3'>
                        <style>
                            .complain_file_wrap {display: flex;gap:0 15px;}
                            .complain_bf_file_box {width:auto;height: 120px;}
                            .complain_bf_file_box img {width: 100%;height: 100%;object-fit:contain;}
                        </style>
                        <div class="complain_file_wrap">
                            <?php
                            foreach($file_res as $file_row){
                            ?>
                            <div class="complain_bf_file_box" onclick="bigSize('<?php echo $file_row['url'];?>')">
                                <img src="<?php echo $file_row['url'];?>" alt="">
                            </div>
                            <?php }?>   
                        </div>
                    </td>
                </tr>
                <?php }?>
            </tbody>
        </table>
    </div>
    <?php if($add_total > 0){?>
    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm">추가 내용</h2>
        <table>
            <tr>
                <th>추가 내용</th>
                <td>
                <!-- <textarea name="complain_memo" id="complain_memo" class="bansang_ipt ver2 full ta"><?php echo $row['complain_memo']; ?></textarea> -->
                 <?php foreach($add_res as $add_idx => $add_row){?>
                    <div>
                        <?php echo nl2br($add_row['content']); ?>
                    </div>
                <?php }?>
                </td>
            </tr>
        </table>
    </div>
    <?php }?>
  
    <div class="btn_fixed_top">
        <a href="./complain_list_bf.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    </div>
</form>

<div id="big_size_pop">
    <div class="od_cancel_inner"></div>
	<button type="button" class="big_size_pop_x" onclick="bigSizeOff();">
		<span></span>
		<span></span>
	</button>
	<div class="od_cancel_cont">
		<img src="" id="big_img" alt="확대 보기">
	</div>
</div>

<!-- 변경사유 팝업 -->
<div class="cm_pop" id="mng_change_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('mng_change_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_cont_wrapper ct_wrapper">
            <div class="cm_pop_title">변경 사유</div>
            <div class="mng_change_memo_wrap mgt20">
                <textarea name="mng_change_memo" id="mng_change_memo" class="bansang_ipt ver2 full ta ta_n"><?php echo $row['mng_change_memo']; ?></textarea>
            </div>
            <div class="cm_pop_btn_box">
                <button type="button" onClick="popClose('mng_change_pop');" class="cm_pop_btn ver2">확인</button>
            </div>
        </div>
    </div>
</div>

<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
<script>
function bigSize(url){
	const windowHeight = window.innerHeight;
	$("#big_size_pop .od_cancel_cont").css("height", `${windowHeight}px`);
	$("#big_img").attr("src", url);
	$("#big_size_pop").show();
}

function bigSizeOff(){
	$("#big_size_pop").hide();
	$("#big_img").attr("src", "");
}

function buildingInfoPopOpen(){
    $("#building_info_pop").show();
    bodyLock();
}

function buildingInfoPopClose(){
    $("#building_info_pop").hide();
    bodyUnlock();
}

$(document).on("keyup", ".phone", function(){
    var value = this.value.replace(/[^0-9]/g, "");

  // 길이에 따라 하이픈 삽입
  if (value.length <= 3) {
    // 3자리까지는 아무것도 하지 않음
    this.value = value;
  } else if (value.length <= 7) {
    // 4자리까지는 '010-XXXX' 형태
    this.value = value.replace(/(\d{3})(\d{0,4})/, "$1-$2");
  } else if (value.length <= 11) {
    // 11자리까지는 '010-XXXX-YYYY' 형태
    this.value = value.replace(/(\d{3})(\d{4})(\d{0,4})/, "$1-$2-$3");
  } else {
    // 11자리를 초과하는 경우는 잘라서 처리
    this.value = value
      .substring(0, 11)
      .replace(/(\d{3})(\d{4})(\d{0,4})/, "$1-$2-$3");
  }
});


function post_change(){
    var postIdSelect = document.getElementById("post_id");
    var postValue = postIdSelect.options[postIdSelect.selectedIndex].value;

    console.log(postValue);

    let html = `<option value="">선택</option>"`;

   

    if(postValue != ""){
         //초기화
        
    }else{
         //초기화
        $("#ho_tenant").val("");
        $("#ho_tenant_hp").val("");
        $("#dong_id").html(html);
        $("#ho_id").html(html);
        $("#building_id").val("");
        $("#building_name").val("");
    }
}


//단지 입력시 ajax
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

//검색버튼 클릭
function building_handler(){

    var post_id = $("#post_id option:selected").val();
    let building_text = $("#building_sch").val();

    if(post_id == ""){
        alert("지역을 먼저 선택하세요.");
        return false;
    }

    if(building_text == ""){
        alert("검색어를 입력해주세요.");
        return false;
    } 

    $.ajax({

    url : "./manage_form_sch_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "building_name":building_text, "post_id":post_id}, //파라미터 값
    success: function(msg){ //성공시 이벤트

        console.log(msg);
        $(".sch_result_box").html(msg);
        //$(".select_box2").html(msg); //.select_box2에 html로 나타내라..
    }

    });
}

//단지선택..
function building_select(id, name){
    //alert(id);

    $("#building_id").val(id);
    $("#building_name").val(name);

    let sendData2 = {'building_id': id};

    $.ajax({
        type: "POST",
        url: "./building_post_ajax.php",
        data: sendData2,
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
            
            }
        },
    });

    let html = `<option value="">선택</option>"`;

    $.ajax({

    url : "./building_dong_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "building_id":id}, //파라미터 값
    success: function(msg){ //성공시 이벤트

        //console.log(msg);
        $("#dong_id").html(msg);
        $(".sch_result_box").hide();
        $("#building_sch").val("");

        //초기화
        $("#ho_tenant").val("");
        $("#ho_tenant_hp").val("");
        $("#ho_id").html(html);
    }

    });
}

//동 변경시
function dong_change(){
    var dongSelect = document.getElementById("dong_id");
    var dongValue = dongSelect.options[dongSelect.selectedIndex].value;

    //console.log('dongValue', dongValue);

    $.ajax({

    url : "./building_ho_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "dong_id":dongValue, "type":"complain"}, //파라미터 값
    success: function(msg){ //성공시 이벤트

        //console.log(msg);
        $("#ho_id").html(msg);

        $("#ho_tenant").val("");
        $("#ho_tenant_hp").val("");
    }

    });
}


$(function(){
    //minDate:"0d" 
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});


// 파일첨부
var fileNo = 0;
var filesArr = new Array();
let fileImg = new Array();
var imgdatas = '';
var attFileCnt1 = 0;
var attFileCnt2 = document.querySelectorAll('.filebox').length; //이미 추가된 파일

//이미 추가된 파일이 있는 경우 빈값입력
for(var j=0;j<attFileCnt2;j++){

    //console.log("fileArr index", j);
    filesArr.splice(j, 0, new Blob([''], { type: 'application/octet-stream' }));

}

function addFile(obj){
    var maxFileCnt = 5;   // 첨부파일 최대 개수
    var attFileCnt = document.querySelectorAll('.filebox').length;    // 기존 추가된 첨부파일 개수
    var remainFileCnt = maxFileCnt - attFileCnt;    // 추가로 첨부가능한 개수
    var curFileCnt = obj.files.length;  // 현재 선택된 첨부파일 개수

    let cnt = attFileCnt;

    // 첨부파일 개수 확인
    if (curFileCnt > remainFileCnt) {
        alert("민원 첨부파일은 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
    } else {
        for (const file of obj.files) {

            console.log('file', file);
            // 첨부파일 검증
            if (validation(file)) {
                // 파일 배열에 담기
                var reader = new FileReader();
                reader.onload = function (e) {
                    filesArr.push(file);

                    // let previewHTML = `
                    //     <div class="filebox">
                    //         <img src="${e.target.result}" alt="">
                    //     </div>
                    // `;
                    console.log('cnt', cnt);

                    let previewHTML = `
                        <div class="img_upload_box_wrapper4 img_upload_box_wrapper41">
                            <div class="img_upload_box ver4 filebox">
                                <input type="file" name="img_up${cnt}" id="img_up${cnt + 1}" accept="image/*" onchange="fileUp(this, 'img_up${cnt + 1}', ${cnt}, 'before')">
                                <label for="img_up${cnt + 1}">
                                    <img src="${e.target.result}" class="img_up${cnt + 1}" alt="">
                                </label>
                            </div>
                            <button type="button" class="img_del_btn" onclick="file_dels(this);">
                                삭제
                            </button>
                        </div>
                    `;

                    cnt++;

                    $('.img_upload_wrap1').append(previewHTML);
                };
                reader.readAsDataURL(file);

                attFileCnt22 = document.querySelectorAll('.filebox').length + curFileCnt;

                // console.log('attFileCnt2', attFileCnt2 + curFileCnt);

                // if(attFileCnt22 == maxFileCnt){
                //     $(".work_img_up1").hide();
                // }
            } else {
                continue;
            }

            
        }
    }
    // 초기화
    //document.querySelector("input[type=file]").value = "";
}

function file_dels(btn) {
    // 클릭된 버튼이 포함된 wrapper 찾기
    const wrapper = btn.closest('.img_upload_box_wrapper41');

    // 해당 요소의 index를 구함 (현재 렌더링된 순서 기준)
    const wrappers = Array.from(document.querySelectorAll('.img_upload_box_wrapper41'));
    const index = wrappers.indexOf(wrapper);

    if (index !== -1) {
        // 1. filesArr에서 해당 파일 제거
        filesArr.splice(index, 1);

        // 2. DOM에서 제거
        wrapper.remove();

        // 3. 남은 요소들을 다시 정렬
        const newWrappers = document.querySelectorAll('.img_upload_box_wrapper41');
        newWrappers.forEach((el, i) => {
            const fileInput = el.querySelector('input[type="file"]');
            const label = el.querySelector('label');
            const img = el.querySelector('img');

            // ID, name, class 재설정
            fileInput.name = `img_up${i}`;
            fileInput.id = `img_up${i + 1}`;
            fileInput.setAttribute('onchange', `fileUp(this, 'img_up${i + 1}', ${i}, 'before')`);
            label.setAttribute('for', `img_up${i + 1}`);
            img.className = `img_up${i + 1}`;
        });

        // 4. 업로드 버튼 다시 보이게 (선택 사항)
        // if (filesArr.length < 7) {
        //     $(".work_img_up1").show();
        // }
    }
}

function fileUp(input, type, index, datas){
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('.' + type).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);

        //filesArr.push(input.files[0]);
    
        filesArr[index] = input.files[0];

        console.log('file up file arr', filesArr)
      
    }
}


//답변 파일 첨부
// 파일첨부
var filesArr2 = new Array();
var attFileCntAnw1 = 0;
var attFileCntAnw2 = document.querySelectorAll('.filebox2').length; //이미 추가된 파일

//이미 추가된 파일이 있는 경우 빈값입력
for(var j=0;j<attFileCntAnw2;j++){

    //console.log("fileArr index", j);
    filesArr2.splice(j, 0, new Blob([''], { type: 'application/octet-stream' }));

}

function addFile2(obj){

    console.log('2222');

    var maxFileCnt = 5;   // 첨부파일 최대 개수
    var attFileCnt = document.querySelectorAll('.filebox2').length;    // 기존 추가된 첨부파일 개수
    var remainFileCnt = maxFileCnt - attFileCnt;    // 추가로 첨부가능한 개수
    var curFileCnt = obj.files.length;  // 현재 선택된 첨부파일 개수

    let cnt = attFileCnt;

    // 첨부파일 개수 확인
    if (curFileCnt > remainFileCnt) {
        alert("답변 첨부파일은 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
    } else {
        for (const file of obj.files) {

            console.log('file', file);
            // 첨부파일 검증
            if (validation(file)) {
                // 파일 배열에 담기
                var reader = new FileReader();
                reader.onload = function (e) {
                    filesArr2.push(file);

                    // let previewHTML = `
                    //     <div class="filebox">
                    //         <img src="${e.target.result}" alt="">
                    //     </div>
                    // `;
                    console.log('cnt', cnt);

                    let previewHTML = `
                        <div class="img_upload_box_wrapper4 img_upload_box_wrapper42">
                            <div class="img_upload_box ver4 filebox2">
                                <input type="file" name="img_a_up${cnt}" id="img_a_up${cnt + 1}" accept="image/*" onchange="fileUp2(this, 'img_a_up${cnt + 1}', ${cnt}, 'before')">
                                <label for="img_a_up${cnt + 1}">
                                    <img src="${e.target.result}" class="img_a_up${cnt + 1}" alt="">
                                </label>
                            </div>
                            <button type="button" class="img_del_btn" onclick="file_dels2(this);">
                                삭제
                            </button>
                        </div>
                    `;

                    cnt++;

                    $('.img_upload_wrap2').append(previewHTML);
                };
                reader.readAsDataURL(file);

                attFileCnt23 = document.querySelectorAll('.filebox2').length + curFileCnt;

                // console.log('attFileCnt2', attFileCnt2 + curFileCnt);

                // if(attFileCnt22 == maxFileCnt){
                //     $(".work_img_up1").hide();
                // }
            } else {
                continue;
            }

            
        }
    }
    // 초기화
    //document.querySelector("input[type=file]").value = "";
}

function file_dels2(btn) {
    // 클릭된 버튼이 포함된 wrapper 찾기
    const wrapper = btn.closest('.img_upload_box_wrapper42');

    // 해당 요소의 index를 구함 (현재 렌더링된 순서 기준)
    const wrappers = Array.from(document.querySelectorAll('.img_upload_box_wrapper42'));
    const index = wrappers.indexOf(wrapper);

    if (index !== -1) {
        // 1. filesArr에서 해당 파일 제거
        filesArr2.splice(index, 1);

        // 2. DOM에서 제거
        wrapper.remove();

        // 3. 남은 요소들을 다시 정렬
        const newWrappers = document.querySelectorAll('.img_upload_box_wrapper42');
        newWrappers.forEach((el, i) => {
            const fileInput = el.querySelector('input[type="file"]');
            const label = el.querySelector('label');
            const img = el.querySelector('img');

            // ID, name, class 재설정
            fileInput.name = `img_a_up${i}`;
            fileInput.id = `img_a_up${i + 1}`;
            fileInput.setAttribute('onchange', `fileUp2(this, 'img_a_up${i + 1}', ${i}, 'before')`);
            label.setAttribute('for', `img_a_up${i + 1}`);
            img.className = `img_a_up${i + 1}`;
        });


        console.log('filesArr:',filesArr);
        console.log('filesArr2:',filesArr2);
        // 4. 업로드 버튼 다시 보이게 (선택 사항)
        // if (filesArr.length < 7) {
        //     $(".work_img_up1").show();
        // }
    }
}

function fileUp2(input, type, index, datas){
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('.' + type).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);

        //filesArr.push(input.files[0]);
    
        filesArr2[index] = input.files[0];

        console.log('file up file arr 2', filesArr)
      
    }
}

//추가내용 파일 첨부
// 파일첨부
var filesArr3 = new Array();
var attFileCntAdd = 0;
var attFileCntAdd2 = document.querySelectorAll('.filebox3').length; //이미 추가된 파일

//이미 추가된 파일이 있는 경우 빈값입력
for(var j=0;j<attFileCntAdd2;j++){

    //console.log("fileArr index", j);
    filesArr3.splice(j, 0, new Blob([''], { type: 'application/octet-stream' }));

}

function addFile3(obj){

    console.log('3333');

    var maxFileCnt = 5;   // 첨부파일 최대 개수
    var attFileCnt = document.querySelectorAll('.filebox3').length;    // 기존 추가된 첨부파일 개수
    var remainFileCnt = maxFileCnt - attFileCnt;    // 추가로 첨부가능한 개수
    var curFileCnt = obj.files.length;  // 현재 선택된 첨부파일 개수

    let cnt = attFileCnt;

    // 첨부파일 개수 확인
    if (curFileCnt > remainFileCnt) {
        alert("추가내용 첨부파일은 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
    } else {
        for (const file of obj.files) {

            console.log('file', file);
            // 첨부파일 검증
            if (validation(file)) {
                // 파일 배열에 담기
                var reader = new FileReader();
                reader.onload = function (e) {
                    filesArr3.push(file);

                    // let previewHTML = `
                    //     <div class="filebox">
                    //         <img src="${e.target.result}" alt="">
                    //     </div>
                    // `;
                    console.log('cnt', cnt);

                    let previewHTML = `
                        <div class="img_upload_box_wrapper4 img_upload_box_wrapper43 img_upload_box_wrapper8">
                            <div class="img_upload_box ver4 filebox3">
                                <input type="file" name="img_add_up${cnt}" id="img_add_up${cnt + 1}" accept="image/*" onchange="fileUp3(this, 'img_add_up${cnt + 1}', ${cnt}, 'before')">
                                <label for="img_add_up${cnt + 1}">
                                    <img src="${e.target.result}" class="img_add_up${cnt + 1}" alt="">
                                </label>
                            </div>
                             <button type="button" class="img_del_btn" onclick="file_dels3(this);">
                                삭제
                            </button>
                        </div>
                    `;

                    cnt++;

                    $('.img_upload_wrap3').append(previewHTML);
                };
                reader.readAsDataURL(file);

                attFileCnt233 = document.querySelectorAll('.filebox3').length + curFileCnt;

                // console.log('attFileCnt2', attFileCnt2 + curFileCnt);

                // if(attFileCnt22 == maxFileCnt){
                //     $(".work_img_up1").hide();
                // }
            } else {
                continue;
            }

            
        }
    }
    // 초기화
    //document.querySelector("input[type=file]").value = "";
}

function file_dels3(btn) {
    // 클릭된 버튼이 포함된 wrapper 찾기
    const wrapper = btn.closest('.img_upload_box_wrapper43');

    // 해당 요소의 index를 구함 (현재 렌더링된 순서 기준)
    const wrappers = Array.from(document.querySelectorAll('.img_upload_box_wrapper43'));
    const index = wrappers.indexOf(wrapper);

    if (index !== -1) {
        // 1. filesArr에서 해당 파일 제거
        filesArr2.splice(index, 1);

        // 2. DOM에서 제거
        wrapper.remove();

        // 3. 남은 요소들을 다시 정렬
        const newWrappers = document.querySelectorAll('.img_upload_box_wrapper43');
        newWrappers.forEach((el, i) => {
            const fileInput = el.querySelector('input[type="file"]');
            const label = el.querySelector('label');
            const img = el.querySelector('img');

            // ID, name, class 재설정
            fileInput.name = `img_add_up${i}`;
            fileInput.id = `img_add_up${i + 1}`;
            fileInput.setAttribute('onchange', `fileUp3(this, 'img_add_up${i + 1}', ${i}, 'before')`);
            label.setAttribute('for', `img_add_up${i + 1}`);
            img.className = `img_add_up${i + 1}`;
        });


        // console.log('filesArr:',filesArr);
        // console.log('filesArr2:',filesArr2);
        // 4. 업로드 버튼 다시 보이게 (선택 사항)
        // if (filesArr.length < 7) {
        //     $(".work_img_up1").show();
        // }
    }
}

function fileUp3(input, type, index, datas){
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('.' + type).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);

        //filesArr.push(input.files[0]);
    
        filesArr3[index] = input.files[0];

        console.log('file up file arr 3', filesArr)
      
    }
}

/* 첨부파일 검증 */
function validation(obj){
    const fileTypes = ['application/pdf', 'image/gif', 'image/jpeg', 'image/png', 'image/bmp', 'image/tif', 'image/heic'];
    //'application/haansofthwp', 'application/x-hwp'
    if (obj.name.length > 100) {
        alert("파일명이 100자 이상인 파일은 제외되었습니다.");
        return false;
    } else if (obj.size > (100 * 1024 * 1024)) {
        alert("최대 파일 용량인 100MB를 초과한 파일은 제외되었습니다.");
        return false;
    } else if (obj.name.lastIndexOf('.') == -1) {
        alert("확장자가 없는 파일은 제외되었습니다.");
        return false;
    } else if (!fileTypes.includes(obj.type)) {
        alert("첨부가 불가능한 파일은 제외되었습니다.");
        return false;
    } else {
        return true;
    }
}

function fcomplain_submit(f) {

    let w_status = "<?php echo $w; ?>";
   
    let complain_type = $('input[name="complain_type"]:checked').val();
    let post_id;

    if(complain_type == "user"){
        post_id = $("#post_id").val();
    }else{
        post_id = $("#post_id option:selected").val();
    }

    let building_id = $("#building_id").val();
    let dong_id;

    if(complain_type == "user"){
        dong_id = $("#dong_id").val();
    }else{
        dong_id = $("#dong_id option:selected").val();
    }

    let ho_id;

    if(complain_type == "user"){
        ho_id = $("#ho_id").val();
    }else{
        ho_id = $("#ho_id option:selected").val();
    }
    
    let wdate = $("#wdates").val();
    let complain_id = $("#complain_id").val();
    let complain_name = $("#complain_name").val();
    let complain_hp = $("#complain_hp").val();
    let mng_department = $("#mng_department option:selected").val();
    let mng_id = $("#mng_id option:selected").val();
    let complain_status_bf = $("#complain_status_bf").val();
    let complain_status = $("#complain_status option:selected").val();
    let complain_title = $("#complain_title").val();
    let complain_content = $("#complain_content").val();
    let complain_memo = $("#complain_memo").val();

    let mb_id = "<?php echo $member['mb_id']; ?>";

    let complain_answer = "";
    if(w_status == "u"){
        complain_answer = $("#complain_answer").val();
    }

    let mng_change_memo = "";

    if(mng_id != ""){
        mng_change_memo = $("#mng_change_memo").val();
    }
    
    var formData = new FormData();
    formData.append('w', w_status);
    formData.append('type', "<?php echo $type; ?>");
    formData.append('complain_idx', "<?php echo $complain_idx; ?>");
    formData.append('complain_type', complain_type);
    formData.append('post_id', post_id);
    formData.append('building_id', building_id);
    formData.append('dong_id', dong_id);
    formData.append('ho_id', ho_id);
    formData.append('wdate', wdate);
    formData.append('complain_id', complain_id);
    formData.append('complain_name', complain_name);
    formData.append('complain_hp', complain_hp);
    formData.append('mng_department', mng_department);
    formData.append('mng_id', mng_id);
    formData.append('complain_status_bf', complain_status_bf);
    formData.append('complain_status', complain_status);
    formData.append('complain_title', complain_title);
    formData.append('complain_content', complain_content);
    formData.append('complain_memo', complain_memo);
    formData.append('mb_id', mb_id);
    formData.append('mng_change_memo', mng_change_memo);

    if(w_status == "u"){
        formData.append('complain_answer', complain_answer);
    }

    for (var i = 0; i < filesArr.length; i++) {
        // 삭제되지 않은 파일만 폼데이터에 담기
        formData.append("complain_file[]", filesArr[i]);
    }

    // 파일삭제 체크된 삭제 항목 추가
    $("input[name^=complain_file_del]").each(function() {
        if($(this).is(":checked") == true){
            formData.append("complain_file_del[]", '1'); // 체크된 파일의 번호 추가
        }else{
              formData.append("complain_file_del[]", '0'); // 체크된 파일의 번호 추가
        }
    });


    //답변 파일 첨부
    for (var i = 0; i < filesArr2.length; i++) {
        // 삭제되지 않은 파일만 폼데이터에 담기
        formData.append("answer_file[]", filesArr2[i]);
    }

    // 답변파일삭제 체크된 삭제 항목 추가
    $("input[name^=complain_answer_file_del]").each(function() {
        if($(this).is(":checked") == true){
            formData.append("complain_answer_file_del[]", '1'); // 체크된 파일의 번호 추가
        }else{
              formData.append("complain_answer_file_del[]", '0'); // 체크된 파일의 번호 추가
        }
    });


    //추가 파일 첨부
    for (var i = 0; i < filesArr3.length; i++) {
        // 삭제되지 않은 파일만 폼데이터에 담기
        formData.append("answer_add_file[]", filesArr3[i]);
    }

    // 추가파일 체크된 삭제 항목 추가
    $("input[name^=complain_add_file_del]").each(function() {
        if($(this).is(":checked") == true){
            formData.append("complain_add_file_del[]", '1'); // 체크된 파일의 번호 추가
        }else{
              formData.append("complain_add_file_del[]", '0'); // 체크된 파일의 번호 추가
        }
    });
    

    console.log('mng_department', mng_department);

    //return false;
    buildingInfoPopOpen();

    setTimeout(() => {
        $.ajax({
            type: "POST",
            url: "./complain_form_update.php",
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

                    buildingInfoPopClose();
                    //$(".btn_submit").attr('disabled', false);
                    return false;
                }else{
                    alert(data.msg);

                    setTimeout(() => {
                        if(w_status == 'u'){
                            location.reload();
                        }else{
                            
                            location.replace("./complain_list.php?type=<?php echo $type?>&<?php echo $qstr ?>");
                            // window.location.href = './complain_form.php?type=<?php echo $type?>&complain_idx=' + data.data;
                        }
                        
                    }, 1000);

                    buildingInfoPopClose();
                }
            },
            error:function(e){
                alert(e);
            }
        });
    }, 50);

    
    
    //return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

