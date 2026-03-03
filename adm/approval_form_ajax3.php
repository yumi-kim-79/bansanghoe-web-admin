<?php
require_once './_common.php';

//echo $selectValue;
$sign = sql_fetch("SELECT sign_cate_name FROM a_sign_off_category WHERE sign_cate_code = '{$selectValue}'");


$mng_info = sql_fetch("SELECT mng.*, mng_department.md_idx, mng_department.md_name, mng_grade.mg_idx, mng_grade.mg_name FROM 
            a_mng as mng
            LEFT JOIN a_mng_department as mng_department on mng.mng_department = mng_department.md_idx
            LEFT JOIN a_mng_grade as mng_grade on mng.mng_grades = mng_grade.mg_idx
            WHERE mng.mng_id = '{$mng_id}'");

$year = date("Y");
$three_year = $year + 3;

$month = date("n");
$start_month = 12 - (12 - $month);

switch($mng_info['mng_certi']){
    case "A":
        $certi = "('A', 'B')";
        break;
    case "B":
    case "C":
        $certi = "('B')";
    case "D":
        $certi = "('B', 'C')";
        break;
}

//매니저 직급 팀장
//mng.mng_department = '{$mng_info['mng_department']}' and
$mng_sql = "SELECT mng.*, mng_gr.mg_name FROM a_mng as mng
            LEFT JOIN a_mng_grade as mng_gr on mng.mng_grades = mng_gr.mg_idx
            WHERE mng.mng_certi = 'B' and mng.mng_status = 1 ORDER BY mng.mng_idx desc ";
// echo $mng_sql;
$mng_res = sql_query($mng_sql);

//매니저 직급 임원
$mng_sp_sql = "SELECT mng.*, mng_gr.mg_name FROM a_mng as mng
            LEFT JOIN a_mng_grade as mng_gr on mng.mng_grades = mng_gr.mg_idx
            WHERE mng.mng_department = '4' and mng.mng_status = 1 ORDER BY mng.mng_idx desc ";
//echo $mng_sp_sql;
$mng_sp_res1 = sql_query($mng_sp_sql);
$mng_sp_res2 = sql_query($mng_sp_sql);

$verCl = "ver2";
$readonlys = "";

//신청자 서명 가져오기 최신걸로
// $sql_sign = "SELECT * FROM a_sign_off_img WHERE is_del = 0 and mng_id = '{$member['mb_id']}' ORDER BY so_idx desc limit 0, 1";
// $sql_sign = "SELECT * FROM a_sign_off_img WHERE is_del = 0 and mng_id = '{$row['mng_id']}' and sign_id = '{$sign_id}' ORDER BY so_idx desc";

if($w == "u"){
    $sql = "SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'";
    $row = sql_fetch($sql);

    $verCl = $row['sign_status'] != 'N' ? "" : "ver2";
    $readonlys = $row['sign_status'] != 'N' ? "readonly" : "";

     //신청자 서명 가져오기 최신걸로
    $sql_sign_off_img = "SELECT soi.*, sig.fil_name FROM a_sign_off_img as soi
                            LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                            WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}'";
    $row_sign = sql_fetch($sql_sign_off_img);

    //echo $sql;

    $file_sql = "SELECT * FROM g5_board_file WHERE bo_table = 'signOff' and wr_id = '{$sign_id}' ORDER BY bf_no asc";
    $file_res = sql_query($file_sql);
}
?>
<div class="tbl_frm01 tbl_wrap">
    <h2 class="h2_frm"><?php echo $sign['sign_cate_name']?> 신청</h2>
    <table>
        <tr>
            <th>작성일</th>
            <td colspan="3">
                <!-- readonly -->
                <input type="text" name="wdate" id="wdate" class="bansang_ipt <?php echo $verCl;?> ipt_date" value="<?php echo date("Y-m-d");?>" required <?php echo $w == "u" ? "disabled" : ""?>>
            </td>
        </tr>
        <tr>
            <th>작성자</th>
            <td>
                <input type="hidden" name="wid" id="wid" class="bansang_ipt" value="<?php echo $w == "u" ? $row['mng_id'] : $member['mb_id']; ?>">
                <input type="text" name="wname" id="wname" class="bansang_ipt <?php echo $verCl;?>" value="<?php echo $w == "u" ? get_member($row['mng_id'])['mb_name'] : $member['mb_name']; ?>" readonly required>
            </td>
            <th>부서</th>
            <td>
                <input type="hidden" name="mng_department" id="mng_department" value="<?php echo $w == "u" ? $row['mng_department'] : $mng_info['md_idx']; ?>">
                <input type="text" name="mng_department_name" class="bansang_ipt <?php echo $verCl;?>" value="<?php echo $w == "u" ? get_department_name($row['mng_department']) : $mng_info['md_name']; ?>" readonly required>
                <!-- <select name="mng_department" id="mng_department" class="bansang_sel">
                    <option value="">선택</option>
                </select> -->
            </td>
        </tr>
        <tr>
            <th>직급</th>
            <td>
                <input type="hidden" name="mng_grade" id="mng_grade" value="<?php echo $w == "u" ? $row['mng_grade'] : $mng_info['mg_idx']; ?>">
                <input type="text" name="mng_grades_name" class="bansang_ipt <?php echo $verCl;?>" value="<?php echo $w == "u" ? get_mng_grade_name($row['mng_grade']) : $mng_info['mg_name']; ?>" readonly required>
            </td>
        </tr>
        <tr>
            <th>1차 결재자 선택</th>
            <td colspan="3">
                <?php echo help("선택한 부서의 팀장급 리스트가 보여집니다.");?>
                <div class="sign_off_selector">
                    <?php if($row['sign_off_mng_id1'] != '' && $row['sign_off_status']){
                        $sign_off_mng1 = get_manger($row['sign_off_mng_id1']);

                        //서명이미지
                        $sql_sign_off_img = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id1'";
                        $sign_img_row = sql_fetch($sql_sign_off_img);
                    ?>
                        <input type="hidden" name="sign_off_mng_id1" id="sign_off_mng_id1" value="<?php echo $row['sign_off_mng_id1'];?>" class="bansang_ipt" readonly>
                        <input type="text" name="sign_off_mng1" value="<?php echo $sign_off_mng1['mng_name'].' 결재완료';?>" class="bansang_ipt" readonly>
                        <?php if($sign_img_row){?>
                            <div class="mng_sign_img_box">
                                <img src="/data/file/approval/<?php echo $sign_img_row['fil_name']; ?>" alt="" class="mgt10">
                            </div>
                        <?php }?>
                    <?php }else{?>
                    <select name="sign_off_mng_id1" id="sign_off_mng_id1" class="bansang_sel" required>
                        <option value="">선택</option>
                        <?php for($i=0;$mng_row = sql_fetch_array($mng_res);$i++){?>
                            <option value="<?php echo $mng_row['mng_id']; ?>" <?php echo get_selected($row['sign_off_mng_id1'], $mng_row['mng_id']); ?>><?php echo $mng_row['mng_name'].' '.$mng_row['mg_name']; ?></option>
                        <?php }?>
                    </select>
                    <?php }?>
                </div>
            </td>
        </tr>
        <tr>
            <th>2차 결재자 선택</th>
            <td colspan="3">
                <?php echo help("임원급 리스트가 보여집니다.");?>
                <div class="sign_off_selector">
                    <?php if($row['sign_off_mng_id2'] != '' && $row['sign_off_status2']){
                        $sign_off_mng2 = get_manger($row['sign_off_mng_id2']);

                        //서명이미지
                        $sql_sign_off_img2 = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id2'";

                        //if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $sql_sign_off_img2;
                        $sign_img_row2 = sql_fetch($sql_sign_off_img2);
                    ?>
                        <input type="hidden" name="sign_off_mng_id2" id="sign_off_mng_id2" value="<?php echo $row['sign_off_mng_id2'];?>" class="bansang_ipt" readonly>
                        <input type="text" name="sign_off_mng2" value="<?php echo $sign_off_mng2['mng_name'].' 결재완료';?>" class="bansang_ipt" readonly>
                        <?php if($sign_img_row2){?>
                            <div class="mng_sign_img_box">
                                <img src="/data/file/approval/<?php echo $sign_img_row2['fil_name']; ?>" alt="" class="mgt10">
                            </div>
                        <?php }?>
                    <?php }else{?>
                    <select name="sign_off_mng_id2" id="sign_off_mng_id2" class="bansang_sel" required <?php echo $readonlys;?>>
                        <option value="">선택</option>
                        <?php for($i=0;$mng_row2 = sql_fetch_array($mng_sp_res1);$i++){?>
                            <option value="<?php echo $mng_row2['mng_id']; ?>" <?php echo get_selected($row['sign_off_mng_id2'], $mng_row2['mng_id']); ?>><?php echo $mng_row2['mng_name'].' '.$mng_row2['mg_name']; ?></option>
                        <?php }?>
                    </select>
                    <?php }?>
                </div>
            </td>
        </tr>
        <tr>
            <th>3차 결재자 선택</th>
            <td colspan="3">
                <?php echo help("임원급 리스트가 보여집니다.");?>
                <div class="sign_off_selector">
                    <?php if($row['sign_off_mng_id3'] != '' && $row['sign_off_status3']){
                        $sign_off_mng3 = get_manger($row['sign_off_mng_id3']);

                        $sql_sign_off_img3 = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id3'";
                        $sign_img_row3 = sql_fetch($sql_sign_off_img3);
                    ?>
                        <input type="hidden" name="sign_off_mng_id3" value="<?php echo $row['sign_off_mng_id3'];?>" class="bansang_ipt" readonly>
                        <input type="text" name="sign_off_mng3" id="sign_off_mng_id3" value="<?php echo $sign_off_mng3['mng_name'].' 결재완료';?>" class="bansang_ipt" readonly>
                        <?php if($sign_img_row3){?>
                            <div class="mng_sign_img_box">
                                <img src="/data/file/approval/<?php echo $sign_img_row3['fil_name']; ?>" alt="" class="mgt10">
                            </div>
                        <?php }?>
                    <?php }else{?>
                    <select name="sign_off_mng_id3" id="sign_off_mng_id3" class="bansang_sel" required <?php echo $readonlys;?>>
                        <option value="">선택</option>
                        <?php for($i=0;$mng_row3 = sql_fetch_array($mng_sp_res2);$i++){?>
                            <option value="<?php echo $mng_row3['mng_id']; ?>" <?php echo get_selected($row['sign_off_mng_id3'], $mng_row3['mng_id']); ?>><?php echo $mng_row3['mng_name'].' '.$mng_row3['mg_name']; ?></option>
                        <?php }?>
                    </select>
                    <?php }?>
                </div>
            </td>
        </tr>
        <tr>
            <th>사진첨부</th>
            <td colspan="3">
                <?php echo help('사진은 최대 8장까지 등록가능합니다.'); ?>
                <div class="ipt_box">
                    <div class="img_upload_wrap img_upload_wrap2">
                        <?php if($row['sign_status'] == 'N' || $w == ''){?>
                        <div class="img_upload_box ver1">
                            <input type="file" name="file_up[]" id="file_up" onchange="addFile(this);" multiple accept="image/*">
                            <label for="file_up">
                                <img src="/images/file_plus.svg" alt="">
                            </label>
                        </div>
                        <?php }?>
                        <?php if($w == "u" && $file_res){?>
                            <?php for($i=0;$file_row = sql_fetch_array($file_res);$i++){?>
                                <div class="img_upload_box_wrapper8">
                                    <div class="img_upload_box ver4 filebox">
                                        <input type="file" name="file_up<?php echo $i + 1;?>" id="file_up<?php echo $i + 1;?>" accept="image/*" onchange="fileUp(this, 'file_up<?php echo $i + 1;?>', <?php echo $i; ?>, 'before')">
                                       
                                        <img src="/data/file/signOff/<?php echo $file_row['bf_file']; ?>" class="file_up<?php echo $i + 1;?>" alt="" onclick="bigSize('/data/file/signOff/<?php echo $file_row['bf_file']; ?>')">
                                        
                                        <div class="file_del">
                                            <input type="checkbox" name="file_del[<?php echo $file_row['bf_no'];?>]" id="file_del<?php echo $i+1;?>" class="file_del" value="1">
                                            <label for="file_del<?php echo $i+1;?>">삭제</label>
                                        </div>
                                    </div>
                                    <label class="img_labels" for="file_up<?php echo $i + 1;?>">
                                    이미지 첨부
                                    </label>
                                </div>
                            <?php }?>
                        <?php }?>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>기타사항</th>
            <td colspan="3">
                <textarea name="sign_off_memo" id="sign_off_memo" class="bansang_ipt <?php echo $verCl;?> full ta" <?php echo $readonlys; ?>><?php echo $row['sign_off_memo']; ?></textarea>
            </td>
        </tr>
        <tr>
            <th>신청자 서명</th>
            <td colspan="3">
            <?php 
                //내 사인 있는지 확인
                // $signature_check = "SELECT *, COUNT(*) as cnt FROM a_signature WHERE mb_id = '{$member['mb_id']}'";

                $signature_check = "SELECT s.*, t.cnt
                                    FROM (
                                        SELECT * 
                                        FROM a_signature 
                                        WHERE mb_id = '{$member['mb_id']}' 
                                        ORDER BY sg_idx DESC 
                                        LIMIT 1
                                    ) s
                                    JOIN (
                                        SELECT COUNT(*) AS cnt 
                                        FROM a_signature 
                                        WHERE mb_id = '{$member['mb_id']}'
                                    ) t ON 1";
                // echo $signature_check;
                $signature_check_row = sql_fetch($signature_check);
                // print_r2($signature_check_row);
                ?>
                <input type="hidden" name="approval_signature" id="approval_signature" value="<?php echo $w == 'u' ? $signature_check_row['signature_data'] : ''; ?>">
                <?php if($w == "u"){?>
                <button type="button" onclick="signHandler('sign_boxs_img1');" disabled class="btn btn_02">서명완료</button>
                <?php }else{ ?>
                    <?php if($signature_check_row['cnt'] > 0){?>
                        <div class="sign_button_wrap">
                            <button type="button" onclick="signHandler('sign_boxs_img1');" class="btn btn_03">다시 서명하기</button>
                            <button type="button" onclick="signLoad('<?php echo $member['mb_id']; ?>', 'sign_boxs_img1');" class="btn btn_03">서명불러오기</button>
                        </div>
                    <?php }else{?>
                        <button type="button" onclick="signHandler('sign_boxs_img1');" class="btn btn_03">서명하기</button>
                    <?php }?>
                <?php }?>
                <div class="sign_boxs_img sign_boxs_img1">
                    <?php if($row_sign){?>
                        <img src="/data/file/approval/<?php echo $row_sign['fil_name']; ?>" alt="">
                    <?php }?>
                </div>
            </td>
        </tr>
    </table>
</div>

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


function signLoad(id, ele){
    let sendData = {'mb_id': id};

    $.ajax({
        type: "POST",
        url: "/sign_load_ajax.php",
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
                // showToast(data.msg);
                $("#approval_signature").val(data.data.signature_data);
    
                let imgSRc = "/data/file/approval/" + data.data.fil_name;
                let imgs = `<img src='${imgSRc}' />`;
                $("." + ele).html(imgs);
            }
        },
    });
}

$(function(){
    //maxDate: "+365d", minDate:"0d"
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
});

document.querySelectorAll('.ipt_date').forEach(function(input) {
    input.setAttribute('maxlength', '10');
});

document.querySelectorAll('.ipt_date').forEach(function (input) {
    input.addEventListener('input', function () {
        let val = this.value.replace(/\D/g, '').substring(0, 8);
        if (val.length >= 5) {
            val = val.replace(/(\d{4})(\d{2})(\d{0,2})/, '$1-$2-$3');
        } else if (val.length >= 3) {
            val = val.replace(/(\d{4})(\d{0,2})/, '$1-$2');
        }
        this.value = val;
    });
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
    var maxFileCnt = 8;   // 첨부파일 최대 개수
    var attFileCnt = document.querySelectorAll('.filebox').length;    // 기존 추가된 첨부파일 개수
    var remainFileCnt = maxFileCnt - attFileCnt;    // 추가로 첨부가능한 개수
    var curFileCnt = obj.files.length;  // 현재 선택된 첨부파일 개수

    let cnt = attFileCnt;

    // 첨부파일 개수 확인
    if (curFileCnt > remainFileCnt) {
        alert("첨부파일은 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
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
                        <div class="img_upload_box_wrapper8">
                            <div class="img_upload_box ver4 filebox">
                                <input type="file" name="file_up${cnt}" id="file_up${cnt + 1}" accept="image/*" onchange="fileUp(this, 'file_up${cnt + 1}', ${cnt}, 'before')">
                                <label for="file_up${cnt + 1}">
                                    <img src="${e.target.result}" class="file_up${cnt + 1}" alt="">
                                </label>
                            </div>
                            <button type="button" class="img_del_btn" onclick="file_dels(this);">
                                삭제
                            </button>
                        </div>
                    `;

                    cnt++;

                    $('.img_upload_wrap').append(previewHTML);
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

// 파일삭제
function file_dels(btn) {
    // 클릭된 버튼이 포함된 wrapper 찾기
    const wrapper = btn.closest('.img_upload_box_wrapper8');

    // 해당 요소의 index를 구함 (현재 렌더링된 순서 기준)
    const wrappers = Array.from(document.querySelectorAll('.img_upload_box_wrapper8'));
    const index = wrappers.indexOf(wrapper);

    if (index !== -1) {
        // 1. filesArr에서 해당 파일 제거
        filesArr.splice(index, 1);

        // 2. DOM에서 제거
        wrapper.remove();

        // 3. 남은 요소들을 다시 정렬
        const newWrappers = document.querySelectorAll('.img_upload_box_wrapper8');
        newWrappers.forEach((el, i) => {
            const fileInput = el.querySelector('input[type="file"]');
            const label = el.querySelector('label');
            const img = el.querySelector('img');

            // ID, name, class 재설정
            fileInput.name = `file_up${i}`;
            fileInput.id = `file_up${i + 1}`;
            fileInput.setAttribute('onchange', `fileUp(this, 'file_up${i + 1}', ${i}, 'before')`);
            label.setAttribute('for', `file_up${i + 1}`);
            img.className = `file_up${i + 1}`;
        });

        // 4. 업로드 버튼 다시 보이게 (선택 사항)
        // if (filesArr.length < 7) {
        //     $(".work_img_up1").show();
        // }

        console.log('fire arr after delete', filesArr);
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
</script>