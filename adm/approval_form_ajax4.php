<?php
require_once './_common.php';

//duty_report 당직보고서

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

//신청자 서명 가져오기 최신걸로
// $sql_sign = "SELECT * FROM a_sign_off_img WHERE is_del = 0 and mng_id = '{$member['mb_id']}' ORDER BY so_idx desc limit 0, 1";
$verCl = "ver2";
$readonlys = "";

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
                <input type="hidden" name="mng_department" value="<?php echo $w == "u" ? $row['mng_department'] : $mng_info['md_idx']; ?>">
                <input type="text" name="mng_department_name" class="bansang_ipt <?php echo $verCl;?>" value="<?php echo $w == "u" ? get_department_name($row['mng_department']) : $mng_info['md_name']; ?>" readonly required>
                <!-- <select name="mng_department" id="mng_department" class="bansang_sel">
                    <option value="">선택</option>
                </select> -->
            </td>
        </tr>
        <tr>
            <th>직급</th>
            <td>
                <input type="hidden" name="mng_grade" value="<?php echo $w == "u" ? $row['mng_grade'] : $mng_info['mg_idx']; ?>">
                <input type="text" name="mng_grades_name" class="bansang_ipt <?php echo $verCl;?>" value="<?php echo $w == "u" ? get_mng_grade_name($row['mng_grade']) : $mng_info['mg_name']; ?>" readonly required>
            </td>
            <th>당직근무일</th>
            <td>
                <div class="ipt_box flex_ver">
                    <input type="text" name="duty_sdate" class="bansang_ipt <?php echo $verCl;?> ipt_date" value="<?php echo $row['duty_sdate']; ?>" readonly required> - <input type="text" name="duty_edate" class="bansang_ipt <?php echo $verCl;?> ipt_date" value="<?php echo $row['duty_edate']; ?>" readonly required>
                </div>
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
                        <img src="/data/file/approval/<?php echo $sign_img_row['fil_name']; ?>" alt="" class="mgt10">
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

                        $sql_sign_off_img2 = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id2'";
                        $sign_img_row2 = sql_fetch($sql_sign_off_img2);
                    ?>
                        <input type="hidden" name="sign_off_mng_id2" id="sign_off_mng_id2" value="<?php echo $row['sign_off_mng_id2'];?>" class="bansang_ipt" readonly>
                        <input type="text" name="sign_off_mng2" value="<?php echo $sign_off_mng2['mng_name'].' 결재완료';?>" class="bansang_ipt" readonly>
                        <?php if($sign_img_row2){?>
                        <img src="/data/file/approval/<?php echo $sign_img_row2['fil_name']; ?>" alt="" class="mgt10">
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
                        <input type="hidden" name="sign_off_mng_id3" id="sign_off_mng_id3" value="<?php echo $row['sign_off_mng_id3'];?>" class="bansang_ipt" readonly>
                        <input type="text" name="sign_off_mng3" value="<?php echo $sign_off_mng3['mng_name'].' 결재완료';?>" class="bansang_ipt" readonly>
                        <?php if($sign_img_row3){?>
                        <img src="/data/file/approval/<?php echo $sign_img_row3['fil_name']; ?>" alt="" class="mgt10">
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
            <th>특이사항</th>
            <td colspan="3">
                <textarea name="significant_memo" id="significant_memo" class="bansang_ipt <?php echo $verCl;?> ta full" <?php echo $readonlys;?>><?php echo $row['significant_memo']; ?></textarea>
            </td>
        </tr>
        <tr>
            <th>기타사항</th>
            <td colspan="3">
                <textarea name="holiday_memo" id="holiday_memo" class="bansang_ipt <?php echo $verCl;?> ta full" <?php echo $readonlys;?>><?php echo $row['holiday_memo']; ?></textarea>
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
                <input type="hidden" name="approval_signature" id="approval_signature">
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
    <p class="certi_texts">위의 당직근무 내역이 사실임을 확인합니다.</p>
</div>
<script>
$(function(){
    //maxDate: "+365d", minDate:"-365d"
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

</script>