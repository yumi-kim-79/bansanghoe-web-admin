<?php
require_once './_common.php';

//paid_holiday 연차 유급휴가 사용 계획서
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
if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $mng_sp_sql.'<br>';
}
$mng_sp_res1 = sql_query($mng_sp_sql);
$mng_sp_res2 = sql_query($mng_sp_sql);

$verCl = "ver2";
$readonlys = "";

if($w == "u"){
    $sql = "SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'";
    $row = sql_fetch($sql);
    //echo $sql;

    $verCl = $row['sign_status'] != 'N' ? "" : "ver2";
    $readonlys = $row['sign_status'] != 'N' ? "readonly" : "";

    //신청자 서명 가져오기 최신걸로
    $sql_sign_off_img = "SELECT soi.*, sig.fil_name FROM a_sign_off_img as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}'";
    $row_sign = sql_fetch($sql_sign_off_img);

    //연차 신청자 정보
    $hp_sql = "SELECT * FROM a_holiday_person WHERE is_del = 0 and sign_id = '{$sign_id}'";
    // echo $hp_sql;
    $hp_res = sql_query($hp_sql);
    
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
            <th>년/월</th>
            <td>
                <div class="ipt_box flex_ver">
                    <select name="sign_off_year" id="year" class="bansang_sel" required <?php echo $readonlys;?>>
                        <option value="">년도 선택</option>
                        <?php for($i=$year;$i<$three_year;$i++){?>
                            <option value="<?php echo $i; ?>" <?php echo get_selected($row['sign_off_year'], $i);?>><?php echo $i; ?></option>
                        <?php }?>
                    </select>
                    <select name="sign_off_month" id="month" class="bansang_sel" required <?php echo $readonlys;?>>
                        <option value="">월 선택</option>
                        <?php for($i=1;$i<=12;$i++){?>
                            <option value="<?php echo $i; ?>" <?php echo get_selected($row['sign_off_month'], $i); ?>><?php echo $i < 10 ? '0'.$i.'월' : $i.'월'; ?></option>
                        <?php }?>
                    </select>
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

                        //서명이미지
                        $sql_sign_off_img3 = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id3'";
                        $sign_img_row3 = sql_fetch($sql_sign_off_img3);
                    ?>
                        <input type="hidden" name="sign_off_mng_id3" id="sign_off_mng_id3" value="<?php echo $row['sign_off_mng_id3'];?>" class="bansang_ipt" readonly>
                        <input type="text" name="sign_off_mng3" value="<?php echo $sign_off_mng3['mng_name'].' 결재완료';?>" class="bansang_ipt" readonly>
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
            <th>기타사항</th>
            <td colspan="3">
                <textarea name="sign_off_memo" id="sign_off_memo" class="bansang_ipt <?php echo $verCl;?> full ta" <?php echo $readonlys;?>><?php echo $row['sign_off_memo']; ?></textarea>
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
                //echo $signature_check;
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
</div>
<div class="tbl_frm01 tbl_wrap">
    <h2 class="h2_frm">연차 신청자 정보</h2>
    <div class="paid_holiday_request_wrappers">
        <?php if($w == "u"){?>
            <?php for($i=0;$hp_row = sql_fetch_array($hp_res);$i++){?>
                <div class="paid_holiday_request_wrapper">
                    <div class="paid_holiday_request_wrap">
                        <input type="hidden" name="hp_idx[]" value="<?php echo $hp_row['hp_idx']; ?>">
                        <div class="paid_holiday_request_box">
                            <div class="paid_holiday_info_box_wrap flex_ver3">
                                <div class="paid_holiday_info_box">
                                    <div class="paid_holiday_info_label">이름</div>
                                    <div class="paid_holiday_info_ipt">
                                        <input type="text" name="hp_name[]" class="bansang_ipt <?php echo $verCl;?>" value="<?php echo $hp_row['hp_name']; ?>" <?php echo $readonlys; ?>>
                                    </div>
                                </div>
                                <div class="paid_holiday_info_box">
                                    <div class="paid_holiday_info_label">사용일수</div>
                                    <div class="paid_holiday_info_ipt">
                                        <select name="hp_day[]" class="bansang_sel" <?php echo $readonlys; ?>>
                                            <option value="1" <?php echo get_selected($hp_row['hp_day'], '1'); ?>>1일</option>
                                            <option value="2" <?php echo get_selected($hp_row['hp_day'], '2'); ?>>2일</option>
                                            <option value="3" <?php echo get_selected($hp_row['hp_day'], '3'); ?>>3일</option>
                                            <option value="4" <?php echo get_selected($hp_row['hp_day'], '4'); ?>>4일</option>
                                            <option value="5" <?php echo get_selected($hp_row['hp_day'], '5'); ?>>5일</option>
                                            <option value="am_half" <?php echo get_selected($hp_row['hp_day'], 'am_half'); ?>>오전반차</option>
                                            <option value="pm_half" <?php echo get_selected($hp_row['hp_day'], 'pm_half'); ?>>오후반차</option>
                                            <option value="halfhalf" <?php echo get_selected($hp_row['hp_day'], 'halfhalf'); ?>>반반차</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="paid_holiday_info_box">
                                    <div class="paid_holiday_info_label">사용일자</div>
                                    <div class="paid_holiday_info_ipt">
                                        <input type="text" name="hp_date[]" class="bansang_ipt <?php echo $verCl;?> ipt_date" value="<?php echo $hp_row['hp_date']; ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="paid_holiday_info_box_wrap mgt15">
                                <div class="paid_holiday_info_box21">
                                    <div class="paid_holiday_info_label ver2">비고</div>
                                    <div class="paid_holiday_info_ipt ver2 mgt10">
                                        <textarea name="hp_memo[]" id="hp_memo" class="bansang_ipt <?php echo $verCl;?> full ta" <?php echo $readonlys;?>><?php echo $hp_row['hp_memo'];?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <?php if($i != 0){?>
                        <div class="paid_holiday_del">
                            <input type="checkbox" name="holiday_del[<?php echo $i; ?>]" id="holiday_del<?php echo $i + 1; ?>" value="1">
                            <label for="holiday_del<?php echo $i + 1; ?>">삭제</label>
                        </div>
                    <?php }?>
                </div>
            <?php }?>
        <?php }else{ ?>
        <div class="paid_holiday_request_wrapper">
            <div class="paid_holiday_request_wrap">
                <div class="paid_holiday_request_box">
                    <div class="paid_holiday_info_box_wrap flex_ver3">
                        <div class="paid_holiday_info_box">
                            <div class="paid_holiday_info_label">이름</div>
                            <div class="paid_holiday_info_ipt">
                                <input type="text" name="hp_name[]" class="bansang_ipt ver2" required>
                            </div>
                        </div>
                        <div class="paid_holiday_info_box">
                            <div class="paid_holiday_info_label">사용일수</div>
                            <div class="paid_holiday_info_ipt">
                                <select name="hp_day[]" class="bansang_sel" required>
                                    <option value="1">1일</option>
                                    <option value="2">2일</option>
                                    <option value="3">3일</option>
                                    <option value="4">4일</option>
                                    <option value="5">5일</option>
                                    <option value="am_half">오전반차</option>
                                    <option value="pm_half">오후반차</option>
                                    <option value="halfhalf">반반차</option>
                                </select>
                            </div>
                        </div>
                        <div class="paid_holiday_info_box">
                            <div class="paid_holiday_info_label">사용일자</div>
                            <div class="paid_holiday_info_ipt">
                                <input type="text" name="hp_date[]" class="bansang_ipt ver2 ipt_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="paid_holiday_info_box_wrap mgt15">
                        <div class="paid_holiday_info_box21">
                            <div class="paid_holiday_info_label ver2">비고</div>
                            <div class="paid_holiday_info_ipt ver2 mgt10">
                                <textarea name="hp_memo[]" id="hp_memo" class="bansang_ipt ver2 full ta"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <?php }?>
    </div>
    <?php if($row['sign_status'] == 'N' || $w == ''){?>
    <div class="paid_holiday_request_add_box mgt20">
        <button type="button" class="paid_holiday_request_add">추가</button>
    </div>
    <?php }?>
</div>
<script>
$(function(){
 
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99"});
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


//서명 불러오기
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