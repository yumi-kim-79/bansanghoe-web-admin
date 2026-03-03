<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

//print_r2($mng_info);
$sign_off_sql = "SELECT * FROM a_sign_off WHERE is_del = 0 and sign_id = '{$sign_id}'";
$row = sql_fetch($sign_off_sql);

// print_r2($row);


$holiday_sql = "SELECT * FROM a_holiday_person WHERE is_del = 0 and sign_id = '{$sign_id}' ORDER BY hp_idx asc";
$holiday_res = sql_query($holiday_sql);
$holiday_total = sql_num_rows($holiday_res);
//echo $holiday_total;

//신청자 서명 가져오기 최신걸로
$sql_sign_off_img = "SELECT soi.*, sig.fil_name FROM a_sign_off_img as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}'";
//echo $sql_sign;
$row_sign = sql_fetch($sql_sign_off_img);

$page_arr = ["daily_paid", "onsite_expenses", "personal_signoff", "expenditure_plan", "builder_statement", "building_account", "mng_adjustment", "bill_payment", "household_refund"];

//매니저 직급 팀장
$mng_sql = "SELECT mng.*, mng_gr.mg_name FROM a_mng as mng
            LEFT JOIN a_mng_grade as mng_gr on mng.mng_grades = mng_gr.mg_idx
            WHERE mng.mng_department = '{$mng_info['mng_department']}' and mng.mng_grades > 1 and mng.mng_status = 1 ORDER BY mng.mng_idx desc ";
//echo $mng_sql;
$mng_res = sql_query($mng_sql);

//매니저 직급 임원
$mng_sp_sql = "SELECT mng.*, mng_gr.mg_name FROM a_mng as mng
            LEFT JOIN a_mng_grade as mng_gr on mng.mng_grades = mng_gr.mg_idx
            WHERE mng.mng_department = '4' and mng.mng_status = 1 ORDER BY mng.mng_idx desc ";
//echo $mng_sp_sql;
if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){
    // echo $mng_sp_sql.'<br>';
}
$mng_sp_res1 = sql_query($mng_sp_sql);
$mng_sp_res2 = sql_query($mng_sp_sql);

$year = date("Y");
$three_year = $year + 3;

$month = date("n");
$start_month = 12 - (12 - $month);

//파일첨부 확인
$file_sql = "SELECT * FROM g5_board_file WHERE bo_table = 'signOff' and wr_id = '{$sign_id}' ORDER BY bf_no asc";
$file_res = sql_query($file_sql);
$file_total = sql_num_rows($file_res);
?>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<form name="fsignOff" id="fsignOff" action="/holiday_reqeust_form_update.php" onsubmit="return fsignOff_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sign_id" value="<?php echo $sign_id; ?>">
<div id="wrappers">
    <div class="wrap_container">
        <div class="holiday_req_wrap">
            <div class="inner">
                <?php if($types == "holiday"){?>
                <div class="bbs_vote_notice" style="margin-bottom:20px">
                    <div class="bbs_vote_notice_inner ver2">
                    휴가 신청은 부득이한 경우를 제외하고는 7일전 신청하여야 합니다.
                    </div>
                    <div class="bbs_vote_notice_inner ver2">
                    휴가 사용은 업무에 지장이 없는 범위 내에서 사용하여야 합니다.
                    </div>
                </div>
                <?php }?>
                <ul class="regi_list m0">
                    <li>
                        <p class="regi_list_title">부서 <span>*</span></p>
                        <div class="ipt_box">
                            <input type="hidden" name="mng_department" id="mng_department" value="<?php echo $w == "u" ? $row['mng_department'] : $mng_info['mng_department']; ?>">
                            <input type="text" name="mng_department_name" class="bansang_ipt" value="<?php echo $w == "u" ? get_department_name($row['mng_department']) : $mng_info['md_name']; ?>" readonly required>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">직급 <span>*</span></p>
                        <div class="ipt_box">
                            <input type="hidden" name="mng_grade" id="mng_grade" value="<?php echo $w == "u" ? $row['mng_grade'] : $mng_info['mng_grades']; ?>">
                            <input type="text" name="mng_grades_name" class="bansang_ipt" value="<?php echo $w == "u" ? get_mng_grade_name($row['mng_grade']) : $mng_info['mg_name']; ?>" readonly required>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">작성자 <span>*</span></p>
                        <div class="ipt_box">
                            <input type="hidden" name="wid" id="wid" class="bansang_ipt ver2" value="<?php echo $w == "u" ? $row['mng_id'] : $mng_info['mng_id']; ?>">
                            <input type="text" name="wname" id="wname" class="bansang_ipt " value="<?php echo $w == "u" ? get_member($row['mng_id'])['mb_name'] : $mng_info['mng_name']; ?>" readonly required>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">1차 결재자 선택 <span>*</span> <span class="pic_ver">*선택한 부서의 팀장급 리스트가 보여집니다.</span></p>
                        <div class="ipt_box">
                            <select name="sign_off_mng_id1" id="sign_off_mng_id1" class="bansang_sel">
                                <option value="">선택</option>
                                <?php for($i=0;$mng_row = sql_fetch_array($mng_res);$i++){?>
                                    <option value="<?php echo $mng_row['mng_id']; ?>" <?php echo get_selected($row['sign_off_mng_id1'], $mng_row['mng_id']); ?>><?php echo $mng_row['mng_name'].' '.$mng_row['mg_name']; ?></option>
                                <?php }?>
                            </select>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">2차 결재자 선택 <span class="pic_ver">*임원급 리스트가 보여집니다.</span></p>
                        <div class="ipt_box">
                            <select name="sign_off_mng_id2" id="sign_off_mng_id2" class="bansang_sel">
                                <option value="">선택</option>
                                <?php for($i=0;$mng_row2 = sql_fetch_array($mng_sp_res1);$i++){?>
                                    <option value="<?php echo $mng_row2['mng_id']; ?>" <?php echo get_selected($row['sign_off_mng_id2'], $mng_row2['mng_id']); ?>><?php echo $mng_row2['mng_name'].' '.$mng_row2['mg_name']; ?></option>
                                <?php }?>
                            </select>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">3차 결재자 선택 <span class="pic_ver">*임원급 리스트가 보여집니다.</span></p>
                        <div class="ipt_box">
                            <select name="sign_off_mng_id3" id="sign_off_mng_id3" class="bansang_sel">
                                <option value="">선택</option>
                                <?php for($i=0;$mng_row3 = sql_fetch_array($mng_sp_res2);$i++){?>
                                    <option value="<?php echo $mng_row3['mng_id']; ?>" <?php echo get_selected($row['sign_off_mng_id3'], $mng_row3['mng_id']); ?>><?php echo $mng_row3['mng_name'].' '.$mng_row3['mg_name']; ?></option>
                                <?php }?>
                            </select>
                        </div>
                    </li>
                    <?php if($types == "paid_holiday"){?>
                    <li>
                        <p class="regi_list_title">년/월 선택 <span>*</span></p>
                        <div class="ipt_box ipt_flex ipt_box_ver2">
                            <select name="sign_off_year" id="sign_off_year" class="bansang_sel" required>
                                <option value="">년도를 선택하세요.</option>
                                <?php for($i=$year;$i<$three_year;$i++){?>
                                    <option value="<?php echo $i; ?>" <?php echo get_selected($row['sign_off_year'], $i);?>><?php echo $i; ?></option>
                                <?php }?>
                            </select>
                            <select name="sign_off_month" id="sign_off_month" class="bansang_sel" required>
                                <option value="">월을 선택하세요.</option>
                                <?php for($i=1;$i<=12;$i++){?>
                                    <option value="<?php echo $i; ?>" <?php echo get_selected($row['sign_off_month'], $i); ?>><?php echo $i < 10 ? '0'.$i.'월' : $i.'월'; ?></option>
                                <?php }?>
                            </select>
                        </div>
                    </li>
                    <?php }?>
                    
                    <?php if($types == "holiday"){?>
                    <li>
                        <p class="regi_list_title">연차기간 <span>*</span></p>
                        <div class="ipt_box">
                            <input type="text" name="holiday_date" id="holiday_date" class="bansang_ipt ver2 ipt_date" value="<?php echo $row['holiday_date']; ?>" readonly>
                        </div>
                    </li>
                    
                    <li>
                        <p class="regi_list_title">연차 일수 <span>*</span></p>
                        <div class="ipt_box">
                            <select name="holiday_day" id="holiday_day" class="bansang_sel">
                                <option value="">선택</option>
                                <option value="1" <?php echo $row['holiday_day'] == '1' ? 'selected' : '';?>>1일</option>
                                <option value="2" <?php echo $row['holiday_day'] == '2' ? 'selected' : '';?> >2일</option>
                                <option value="3" <?php echo $row['holiday_day'] == '3' ? 'selected' : '';?>>3일</option>
                                <option value="4" <?php echo $row['holiday_day'] == '4' ? 'selected' : '';?>>4일</option>
                                <option value="5" <?php echo $row['holiday_day'] == '5' ? 'selected' : '';?>>5일</option>
                                <option value="am_half" <?php echo $row['holiday_day'] == 'am_half' ? 'selected' : '';?>>오전반차</option>
                                <option value="pm_half" <?php echo $row['holiday_day'] == 'pm_half' ? 'selected' : '';?>>오후반차</option>
                                <option value="half_half" <?php echo $row['holiday_day'] == 'half_half' ? 'selected' : '';?>>반반차</option>
                            </select>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">사유 <span>*</span></p>
                        <div class="ipt_box">
                            <textarea name="holiday_memo" id="holiday_memo" class="bansang_ipt ver2 full ta" placeholder="사유를 입력하세요."><?php echo $row['holiday_memo']; ?></textarea>
                        </div>
                    </li>
                    <?php }?>
                  
                   
                    <?php if(in_array($types, $page_arr)){?>
                    <li>
						<p class="regi_list_title">사진첨부 <span class="pic_ver">*8장까지 등록 가능합니다.</span></p>
						<div class="ipt_box">
                            <div class="img_upload_wrap">
                                <div class="img_upload_box ver1">
                                    <input type="file" name="img_up[]" id="img_up" onchange="addFile(this);" multiple accept="image/*">
                                    <label for="img_up">
                                        <img src="/images/file_plus.svg" alt="">
                                    </label>
                                </div>
                                <?php if($w == "u" && $file_res){?>
                                    <?php for($i=0;$file_row = sql_fetch_array($file_res);$i++){?>
                                        <div class="img_upload_box filebox">
                                            <input type="file" name="file_up<?php echo $i + 1;?>" id="file_up<?php echo $i + 1;?>" accept="image/*" onchange="fileUp(this, 'file_up<?php echo $i + 1;?>', <?php echo $i; ?>, 'before')">
                                            <label for="file_up<?php echo $i + 1;?>">
                                                <img src="/data/file/signOff/<?php echo $file_row['bf_file']; ?>" class="file_up<?php echo $i + 1;?>" alt="">
                                            </label>
                                            <div class="file_del">
                                                <input type="checkbox" name="file_del[<?php echo $file_row['bf_no'];?>]" id="file_del<?php echo $i+1;?>" class="file_del" value="1">
                                                <label for="file_del<?php echo $i+1;?>">삭제</label>
                                            </div>
                                        </div>
                                    <?php }?>
                                <?php }?>
                            </div>
						</div>
					</li>
                    <?php }?>
                    <?php if($types == "duty_report"){?>
                    <li>
                        <p class="regi_list_title">당직 근무일 <span>*</span></p>
                        <div class="ipt_box ipt_flex ipt_box_ver2">
                            <input type="text" name="duty_sdate" id="duty_sdate" class="bansang_ipt ipt_date ver2" readonly>
                            <input type="text" name="duty_edate" id="duty_edate" class="bansang_ipt ipt_date ver2" readonly>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">특이사항</p>
                        <div class="ipt_box">
                            <textarea name="significant_memo" id="significant_memo" class="bansang_ipt ver2 ta" placeholder="기타사항을 입력해주세요."></textarea>
                        </div>
                    </li>
                    <?php }?>
                    <?php if($types == "overtime_work_request" || $types == "overtime_work_report"){?>
                    <li>
                        <p class="regi_list_title">연장 근무일시 <span>*</span></p>
                        <div class="ipt_box">
                            <input type="text" name="extension_date" id="extension_date" class="bansang_ipt ipt_date ver2" readonly>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">시간 <span>*</span></p>
                        <div class="ipt_box ipt_flex ipt_box_ver2">
                            <!-- readonly -->
                            <input type="text" name="extension_stime" id="extension_stime" class="bansang_ipt ver2" value="<?php echo $row['extension_stime']; ?>" required placeholder="시작 시간을 입력하세요. ex)12:00" maxlength='5' oninput="this.value = this.value.replace(/[^0-9:]/g, '')">
                            <input type="text" name="extension_etime" id="extension_etime" class="bansang_ipt ver2" value="<?php echo $row['extension_etime']; ?>" required placeholder="종료 시간을 입력하세요. ex)12:00" maxlength='5' oninput="this.value = this.value.replace(/[^0-9:]/g, '')">
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">사유</p>
                        <div class="ipt_box">
                            <textarea name="sign_off_memo" id="sign_off_memo" class="bansang_ipt ver2 ta" placeholder="건물명, 업무 내용 등을 상세히 기재하세요."></textarea>
                        </div>
                    </li>
                    <?php }?>
                    <?php if($types == "paid_holiday" || in_array($types, $page_arr) || $types == "site_out"){?>
                    <li>
                        <p class="regi_list_title">기타사항</p>
                        <div class="ipt_box">
                            <textarea name="sign_off_memo" id="sign_off_memo" class="bansang_ipt ver2 full ta" placeholder="기타사항을 입력해주세요."><?php echo $row['sign_off_memo']; ?></textarea>
                        </div>
                    </li>
                    <?php }?>
                    <?php if($types == "duty_report"){?>
                    <li>
                        <p class="regi_list_title">기타사항</p>
                        <div class="ipt_box">
                            <textarea name="holiday_memo" id="holiday_memo" class="bansang_ipt ver2 full ta" placeholder="사유를 입력하세요."><?php echo $row['holiday_memo']; ?></textarea>
                        </div>
                    </li>
                    <?php }?>
                    <?php if($types == "paid_holiday"){?>
                    <li>
                        <p class="regi_list_title">연차 신청자 정보 <span>*</span></p>
                        <div class="ipt_box">
                            <div class="holiday_pay_wrapper">
                                <?php if($w == "u" && $holiday_total > 0){?>
                                    <?php for($i=0;$holiday_row = sql_fetch_array($holiday_res);$i++){?>
                                        <div class="holiday_pay_wrap">
                                        <input type="hidden" name="hp_idx[]" class="bansang_ipt" value="<?php echo $holiday_row['hp_idx']; ?>">
                                        <div class="holiday_boxs">
                                            <p class="holiday_box_labels mgb10">이름</p>
                                            <div class="holiday_boxs_inpus">
                                                <input type="text" name="hp_name[]" class="bansang_ipt ver2" value="<?php echo $holiday_row['hp_name']; ?>">
                                            </div>
                                        </div>
                                        <div class="holiday_boxs">
                                            <p class="holiday_box_labels mgb10">사용일수</p>
                                            <div class="holiday_boxs_inpus">
                                                <select name="hp_day[]" class="bansang_sel">
                                                    <option value="1" <?php echo get_selected($holiday_row['hp_day'], '1'); ?>>1일</option>
                                                    <option value="2" <?php echo get_selected($holiday_row['hp_day'], '2'); ?>>2일</option>
                                                    <option value="3" <?php echo get_selected($holiday_row['hp_day'], '3'); ?>>3일</option>
                                                    <option value="4" <?php echo get_selected($holiday_row['hp_day'], '4'); ?>>4일</option>
                                                    <option value="5" <?php echo get_selected($holiday_row['hp_day'], '5'); ?>>5일</option>
                                                    <option value="am_half" <?php echo get_selected($holiday_row['hp_day'], 'am_half'); ?>>오전반차</option>
                                                    <option value="pm_half" <?php echo get_selected($holiday_row['hp_day'], 'pm_half'); ?>>오후반차</option>
                                                    <option value="halfhalf" <?php echo get_selected($holiday_row['hp_day'], 'halfhalf'); ?>>반반차</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="holiday_boxs">
                                            <p class="holiday_box_labels mgb10">사용일자</p>
                                            <div class="holiday_boxs_inpus">
                                                <input type="text" name="hp_date[]" class="bansang_ipt ver2 ipt_date" value="<?php echo $holiday_row['hp_date']; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="holiday_boxs">
                                            <p class="holiday_box_labels mgb10">비고</p>
                                            <div class="holiday_boxs_inpus">
                                                <textarea name="hp_memo[]" id="hp_memo" class="bansang_ipt ver2 full ta"><?php echo $holiday_row['hp_memo'];?></textarea>
                                            </div>
                                        </div>
                                        <div class="paid_holiday_del" style="<?php echo $i == 0 ? 'display:none;' : '';?>">
                                            <input type="checkbox" name="holiday_del[<?php echo $i; ?>]" id="holiday_del<?php echo $i + 1; ?>" value="1">
                                            <label for="holiday_del<?php echo $i + 1; ?>">삭제</label>
                                        </div>
                                    </div>
                                    <?php }?>
                                <?php }else{ ?>
                                    <div class="holiday_pay_wrap">
                                        <div class="holiday_boxs">
                                            <p class="holiday_box_labels mgb10">이름</p>
                                            <div class="holiday_boxs_inpus">
                                                <input type="text" name="hp_name[]" class="bansang_ipt ver2" value="<?php echo $hp_row['hp_name']; ?>">
                                            </div>
                                        </div>
                                        <div class="holiday_boxs">
                                            <p class="holiday_box_labels mgb10">사용일수</p>
                                            <div class="holiday_boxs_inpus">
                                                <select name="hp_day[]" class="bansang_sel">
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
                                        <div class="holiday_boxs">
                                            <p class="holiday_box_labels mgb10">사용일자</p>
                                            <div class="holiday_boxs_inpus">
                                                <input type="text" name="hp_date[]" class="bansang_ipt ver2 ipt_date" value="<?php echo $hp_row['hp_date']; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="holiday_boxs">
                                            <p class="holiday_box_labels mgb10">비고</p>
                                            <div class="holiday_boxs_inpus">
                                                <textarea name="hp_memo[]" id="hp_memo" class="bansang_ipt ver2 full ta"><?php echo $hp_row['hp_memo'];?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                <?php }?>
                           </div>
                           <div class="car_visit_btn_box">
                                <button type="button" onclick="request_info_add();" class="car_visit_btn">
                                추가
                                </button>
                            </div>
                        </div>
                      
                    </li>
                    <?php }?>
                </ul>
                <?php if($types == "holiday"){?>
                <p class="request_texts mgt30">상기와 같이 연차 유급 휴가를 사용하고자 합니다.</p>
                <?php }?>
                <?php if($types == "duty_report"){?>
                <p class="request_texts mgt30">위 당직근무 내역이 사실임을 확인합니다.</p>
                <?php }?>
                <?php if($types == "overtime_work_request"){?>
                <p class="request_texts mgt30">위 내용으로 연장 근무를 신청합니다.</p>
                <?php }?>
                <?php if($types == "overtime_work_report"){?>
                <p class="request_texts mgt30">위 연장근무 내역이 사실임을 확인합니다.</p>
                <?php }?>
            </div>
        </div>

        <?php
        //내 사인 있는지 확인
        $signature_check = "SELECT *, COUNT(*) as cnt FROM a_signature WHERE mb_id = '{$member['mb_id']}'";
        $signature_check_row = sql_fetch($signature_check);
        // print_r2($signature_check_row);
        ?>
        <div class="sign_wrap">
            <div class="inner">
                <div class="sign_boxs_wrap">
                    <div class="sign_boxs ver2">
                        <input type="hidden" name="approval_signature" id="approval_signature">
                        <div class="sign_label_box">
                            <p class="sign_label">
                                신청자
                            </p>
                            <?php if($w == "u"){?>
                            <button type="button" onclick="signHandler('sign_boxs_img1');" disabled class="sign_buttons">서명완료</button>
                            <?php }else{ ?>
                            <?php if($signature_check_row['cnt'] > 0){?>
                                <button type="button" onclick="signLoad('<?php echo $member['mb_id']; ?>', 'sign_boxs_img1')" class="sign_buttons">
                                    <img src="/images/icon_chk_white.svg" alt="">
                                    서명 불러오기
                                </button>
                                <?php }else{ ?>
                                <button type="button" onclick="signHandler('sign_boxs_img1')" class="sign_buttons">
                                    <img src="/images/icon_chk_white.svg" alt="">
                                    서명하기
                                </button>
                            <?php }?>
                            <?php }?>
                        </div>
                        <div class="sign_boxs_img sign_boxs_img1">
                        <?php if($w == "u" && $row_sign){?>
                            <img src="/data/file/approval/<?php echo $row_sign['fil_name']; ?>" alt="">
                        <?php }?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="inner">
            <div class="fix_btn_wrap flex_ver ver3" style="padding:20px 0;margin-top:0;">
                <button type="button" onclick="historyBack();" class="fix_btn" id="fix_btn" >취소</button>
                <button type="button" onclick="signOffRequestHandler();" class="fix_btn on" id="fix_btn" ><?php echo $w == "u" ? "수정" : "저장"; ?></button>
                <!-- <?php if(in_array($types, $page_arr)){?>
                <button type="button" onclick="popOpen('log_confirm_pop')" class="fix_btn on" id="fix_btn" >저장</button>
                <?php }else{ ?>
                <button type="submit" class="fix_btn on" id="fix_btn">저장</button>
                <?php }?> -->
            </div>
        </div>
    </div>
</div>
</form>
<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
<div class="cm_pop" id="sign_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <p class="cm_pop_title">전자서명</p>
        <div class="cm_pop_desc">
            <canvas id="signatureCanvas" width="600" height="150"></canvas>
        </div>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="popClose('sign_pop');">취소</button>
			<button type="button" class="cm_pop_btn ver3" onClick="clearSign();">다시입력</button>
			<button type="button" class="cm_pop_btn ver2" onClick="saveSign();">서명</button>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/heic2any/dist/heic2any.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>

let ele = "";
function signHandler(a){
    ele = a;

    popOpen('sign_pop');
    resizeCanvas();
}


// Get canvas and buttons
const canvas = document.getElementById('signatureCanvas');

// Initialize SignaturePad
const signaturePad = new SignaturePad(canvas);

function clearSign(){
    signaturePad.clear();
}

function saveSign(){
    if (signaturePad.isEmpty()) {
        showToast('Please provide a signature first.');
        return false;
    } else {
        const dataURL = signaturePad.toDataURL("image/png");

        console.log(dataURL);

        resizeImage(dataURL, 200, function(resizedDataURL) {
            $("#approval_signature").val(resizedDataURL);

            let imgs = `<img src='${resizedDataURL}' />`;
            $("." + ele).html(imgs);

            popClose('sign_pop');
        });
    }
}

const ratio =  Math.max(window.devicePixelRatio || 1, 1);
canvas.width = canvas.offsetWidth * ratio;
canvas.height = canvas.offsetHeight * ratio;
canvas.getContext("2d").scale(ratio, ratio);

function resizeCanvas() {
    const ratio =  Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    //signaturePad.clear(); // otherwise isEmpty() might return incorrect value
}

window.addEventListener("resize", resizeCanvas);
resizeCanvas();

// 이미지를 리사이징하는 함수
function resizeImage(base64Str, newWidth, callback) {
    let img = new Image();
    img.src = base64Str;
    img.onload = function() {
        let canvas = document.createElement("canvas");
        let ctx = canvas.getContext("2d");

        let scale = newWidth / img.width;
        canvas.width = newWidth;
        canvas.height = img.height * scale; // 높이 비율 유지

        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        let resizedBase64 = canvas.toDataURL("image/png");

        callback(resizedBase64);
    };
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

//신청정보 추가
function request_info_add(){
    let html = `<div class="holiday_pay_wrap">
        <div class="remove_btns_wrap mgb10">
            <button onclick="request_info_remove(this)" type="button"><img src="/images/remove_icons.svg" alt=""></button>
        </div>
        <div class="holiday_boxs">
            <p class="holiday_box_labels mgb10">이름</p>
            <div class="holiday_boxs_inpus">
                <input type="text" name="hp_name[]" class="bansang_ipt ver2">
            </div>
        </div>
        <div class="holiday_boxs">
            <p class="holiday_box_labels mgb10">사용일수</p>
            <div class="holiday_boxs_inpus">
                <select name="hp_day[]" class="bansang_sel">
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
        <div class="holiday_boxs">
            <p class="holiday_box_labels mgb10">사용일자</p>
            <div class="holiday_boxs_inpus">
                <input type="text" name="hp_date[]" class="bansang_ipt ver2 ipt_date" readonly>
            </div>
        </div>
        <div class="holiday_boxs">
            <p class="holiday_box_labels mgb10">비고</p>
            <div class="holiday_boxs_inpus">
                <textarea name="hp_memo[]" id="hp_memo" class="bansang_ipt ver2 full ta"></textarea>
            </div>
        </div>
    </div>`;

    $(".holiday_pay_wrapper").append(html);
}

//신청정보 삭제
function request_info_remove(ele){
    ele.closest('.holiday_pay_wrap').remove();
}


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
        showToast("첨부파일은 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
    } else {
        for (const file of obj.files) {

            console.log('file', file);
            // 첨부파일 검증
            if (validation(file)) {
                // 파일 배열에 담기
                var reader = new FileReader();
                reader.onload = async function (e) {
                    //filesArr.push(file);
                    let processedFile = file;
                    // let previewHTML = `
                    //     <div class="filebox">
                    //         <img src="${e.target.result}" alt="">
                    //     </div>
                    // `;
                    console.log('cnt', cnt);
                    let previewHTML;

                    if (file.type === "image/heic" || file.name.endsWith(".heic")) {

                        try {

                            const blob = await heic2any({ blob: file, toType: "image/jpeg" });
                            const url = URL.createObjectURL(blob);

                            console.log(url);

                            // 새 File 객체로 교체
                            processedFile = new File([blob], file.name.replace(/\.heic$/, '.jpg'), {
                                type: 'image/jpeg'
                            });

                            previewHTML = `
                                <div class="img_upload_box_wrapper4">
                                    <div class="img_upload_box ver4 filebox">
                                        <input type="file" name="img_up${cnt}" id="img_up${cnt + 1}" accept="image/*" onchange="fileUp(this, 'img_up${cnt + 1}', ${cnt}, 'before')">
                                        <label for="img_up${cnt + 1}">
                                            <img src="${url}" class="img_up${cnt + 1}" alt="">
                                        </label>
                                    </div>
                                     <button type="button" class="img_del_btn" onclick="file_dels(this);">
                                        삭제
                                    </button>
                                </div>
                            `;
                        } catch (err) {
                            console.log('err', err);
                        }

                    }else{
                        previewHTML = `
                            <div class="img_upload_box_wrapper4">
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
                    }

                    filesArr.push(processedFile);
                    
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

function file_dels(btn) {
    // 클릭된 버튼이 포함된 wrapper 찾기
    const wrapper = btn.closest('.img_upload_box_wrapper4');

    // 해당 요소의 index를 구함 (현재 렌더링된 순서 기준)
    const wrappers = Array.from(document.querySelectorAll('.img_upload_box_wrapper4'));
    const index = wrappers.indexOf(wrapper);

    if (index !== -1) {
        // 1. filesArr에서 해당 파일 제거
        filesArr.splice(index, 1);

        // 2. DOM에서 제거
        wrapper.remove();

        // 3. 남은 요소들을 다시 정렬
        const newWrappers = document.querySelectorAll('.img_upload_box_wrapper4');
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
    const fileTypes = ['image/gif', 'image/jpeg', 'image/png', 'image/bmp', 'image/tif', 'image/heic'];
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

$(document).on("focus", ".ipt_date", function(){
    if (!$(this).hasClass("hasDatepicker")) { // 중복 적용 방지
        $(this).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd",
            showButtonPanel: true,
            yearRange: "c-99:c+99",
            maxDate: "+365d",
            minDate: "-365d"
        }).datepicker("show"); // 클릭 시 바로 표시
    }
});
// $(document).ready(function(){
//     $('#extension_stime, #extension_etime').timepicker({
//         timeFormat: 'HH:mm',
//         interval: 1,
//         minTime: '09',
//         maxTime: '22:00pm',
//         startTime: '09:00',
//         dynamic: false,
//         dropdown: true,
//         scrollbar: true
//     });
// });

// function fsignOff_submit(f) {
   
//    if(f.sign_off_category.value == "duty_report"){
//        if(f.duty_sdate.value > f.duty_edate.value){
//            alert("당직 근무 시작일이 종료일보다 이후일 수 없습니다.");
//            f.duty_sdate.focus();
//            return false;
//        }
//    }

//    if(f.sign_off_category.value == "overtime_work_request" || f.sign_off_category.value == "overtime_work_report"){
//        if(f.extension_stime.value > f.extension_etime.value){
//            alert("연장 근무 시작시간이 종료시간보다 이후일 수 없습니다.");
//            f.extension_stime.focus();
//            return false;
//        }
//    }
   

//    return true;
// }

function signOffRequestHandler(){

    popOpen('building_info_pop');

    var page_arr = ["daily_paid", "onsite_expenses", "personal_signoff", "expenditure_plan", "builder_statement", "building_account", "mng_adjustment", "bill_payment", "household_refund"];

    let w_status = "<?php echo $w; ?>";
    let sign_id = "<?php echo $sign_id; ?>";
    let wid = "<?php echo $mng_info['mng_id']; ?>";
    let sign_off_category = "<?php echo $types; ?>";
    let sign_off_mng_id1 = $("#sign_off_mng_id1 option:selected").val();
    let sign_off_mng_id2 = $("#sign_off_mng_id2 option:selected").val();
    let sign_off_mng_id3 = $("#sign_off_mng_id3 option:selected").val();
    let mng_department = $("#mng_department").val();
    let mng_grade = $("#mng_grade").val();
    let sign_off_year = $("#sign_off_year option:selected").val();
    let sign_off_month = $("#sign_off_month option:selected").val();
    let sign_off_memo = $("#sign_off_memo").val();
    let approval_signature = $("#approval_signature").val();

    let holiday_date = $("#holiday_date").val();
    let holiday_day = $("#holiday_day option:selected").val();
    let holiday_memo = $("#holiday_memo").val();

    let duty_sdate = $("#duty_sdate").val();
    let duty_edate = $("#duty_edate").val();
    let significant_memo = $("#significant_memo").val();

    let extension_date = $("#extension_date").val();
    let extension_stime = $("#extension_stime").val();
    let extension_etime = $("#extension_etime").val();

    var formData = new FormData();
    formData.append('w', w_status);
    formData.append('sign_id', sign_id);
    formData.append('wid', wid);
    formData.append('sign_off_category', sign_off_category);
    formData.append('sign_off_mng_id1', sign_off_mng_id1);
    formData.append('sign_off_mng_id2', sign_off_mng_id2);
    formData.append('sign_off_mng_id3', sign_off_mng_id3);
    formData.append('mng_department', mng_department);
    formData.append('mng_grade', mng_grade);
    formData.append('sign_off_year', sign_off_year);
    formData.append('sign_off_month', sign_off_month);
    formData.append('sign_off_memo', sign_off_memo);
    formData.append('approval_signature', approval_signature);
    

    if(sign_off_category == "paid_holiday"){
         //연차 신청자 이름
        $(".bansang_ipt[name='hp_name[]']").each(function() {
            formData.append('hp_name[]', $(this).val());
        });

        //사용일수
        $(".bansang_sel[name='hp_day[]']").each(function() {
            formData.append('hp_day[]', $(this).val());
        });

        $(".bansang_ipt[name='hp_date[]']").each(function() {
            formData.append('hp_date[]', $(this).val());
        });

        $(".bansang_ipt[name='hp_memo[]']").each(function() {
            formData.append('hp_memo[]', $(this).val());
        });

        if(w_status == "u"){
            $(".bansang_ipt[name='hp_idx[]']").each(function() {
                formData.append('hp_idx[]', $(this).val());
            });
        }

        // 파일삭제 체크된 삭제 항목 추가
        $("input[name^=holiday_del]").each(function() {
            if($(this).is(":checked") == true){
                formData.append("holiday_del[]", '1'); // 체크된 파일의 번호 추가
            }else{
                formData.append("holiday_del[]", '0'); // 체크된 파일의 번호 추가
            }
        });
    }   

    if(sign_off_category == "holiday"){
        formData.append('holiday_date', holiday_date);
        formData.append('holiday_day', holiday_day);
        formData.append('holiday_memo', holiday_memo);
    }

    if(page_arr.includes(sign_off_category)){
        for (var i = 0; i < filesArr.length; i++) {
            // 삭제되지 않은 파일만 폼데이터에 담기
            formData.append("approval_file[]", filesArr[i]);
        }

         // 파일삭제 체크된 삭제 항목 추가
        $("input[name^=file_del]").each(function() {
            if($(this).is(":checked") == true){
                formData.append("file_del[]", '1'); // 체크된 파일의 번호 추가
            }else{
                formData.append("file_del[]", '0'); // 체크된 파일의 번호 추가
            }
        });
    }

    // duty_report
    if(sign_off_category == "duty_report"){
        formData.append('duty_sdate', duty_sdate);
        formData.append('duty_edate', duty_edate);
        formData.append('significant_memo', significant_memo);
    }

    if(sign_off_category == "overtime_work_request" || sign_off_category == "overtime_work_report"){
        formData.append('extension_date', extension_date);
        formData.append('extension_stime', extension_stime);
        formData.append('extension_etime', extension_etime);
    }


    


    setTimeout(() => {
        $.ajax({
            type: "POST",
            url: "/holiday_reqeust_form_update.php",
            data: formData,
            cache: false,
            async: false,
            dataType: "json",
            contentType: false,
            processData: false,
            success: function(data) {
                console.log('data:::', data);
                if(data.result == false) { 
                    showToast(data.msg);

                    popClose('building_info_pop');
                    //$(".btn_submit").attr('disabled', false);
                    return false;
                }else{
                    showToast(data.msg);

                    if(data.data != "") location.replace("/holiday_request_sample.php?sign_id=" + data.data);

                    // setTimeout(() => {
                    //     location.replace("/holiday_request_sample.php?sign_id=" + data.data);
                    // }, 700);
                }
            },
            error:function(request, status, error){
                alert(request.status + "|" + request.responseText + "|" + error);
            }
        });
        
    }, 300);

    

}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>