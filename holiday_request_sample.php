<?php
include_once('./_common.php');

//auth_check($auth[$sub_menu], "w");
$page_arr = ["daily_paid", "onsite_expenses", "personal_signoff", "expenditure_plan", "builder_statement", "building_account", "mng_adjustment", "bill_payment", "household_refund"];

$sign_off_sql = "SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'";
$sign_off_row = sql_fetch($sign_off_sql);

$mngs = get_manger($sign_off_row['mng_id']);

//결재서류명
$approval_name = approval_category_name($sign_off_row['sign_off_category']);

$g5['title'] = $approval_name;
include_once(G5_PATH.'/head.sub.php');

$holiday_sql = "SELECT * FROM a_holiday_person WHERE is_del = 0 and sign_id = '{$sign_id}' ORDER BY hp_idx asc";
$holiday_res = sql_query($holiday_sql);
$holiday_total = sql_num_rows($holiday_res);

//신청자 서명 가져오기
$sql_sign_off_img = "SELECT soi.*, sig.fil_name FROM a_sign_off_img as soi
                     LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                     WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}'";
$row_sign_off_img = sql_fetch($sql_sign_off_img);
// echo $sql_sign_off_img;

$sign_off_mng_id1 = get_manger($sign_off_row['sign_off_mng_id1']);
$sign_off_mng_id2 = get_manger($sign_off_row['sign_off_mng_id2']);
$sign_off_mng_id3 = get_manger($sign_off_row['sign_off_mng_id3']);

//파일첨부 확인
$file_sql = "SELECT * FROM g5_board_file WHERE bo_table = 'signOff' and wr_id = '{$sign_id}' ORDER BY bf_no asc";

$file_res = sql_query($file_sql);
$file_total = sql_num_rows($file_res);
//echo $file_total;
//print_r2($sign_off_row);
?>
<style>
    /* width: 210mm;
    height: 297mm;
    margin: auto;
    padding: 45mm 10mm;
    position: relative;
    background: url(/images/building_news_sample.jpg) no-repeat center center;
    background-size: cover; */
.building_news_sample_wrap {position: relative;min-width:210mm;margin: 0 auto;}

.news_content {
    width: 100%;
    width: 210mm;
    min-height: 297mm;
    margin: auto;
    /* padding: 10mm 5mm 10mm; */
    /* padding: 40mm 5mm 10mm; */
    padding: 48mm 5mm 10mm;
    position: relative;
    display: flex;
}

.news_content.news_content2 {
    padding: 48mm 5mm 10mm;
}

/* padding: 41mm 5mm 10mm; */

.building_news_sample_hd {width: 100%;padding:15px;display: flex;justify-content:flex-end;}
.building_news_sample_hd button {padding:10px 15px;border-radius:6px;border:none;background: var(--colorMain);color: #fff;font-size: 14px;}


.new_info_hd {width: 100%;max-width: 210mm;left: 50%;transform:translateX(-50%);display: flex;align-items:center;justify-content:space-between;position: absolute;top:25px;padding: 0 16px;}
.news_number {font-size: 12px;width: 78px;text-align:justify;}
.news_number span {display: inline-block;width: 78px;text-align:justify;}
.news_number span:after {content:"";display:inline-block;width:100%;}
.news_number_label {position: relative;top:10px;}
.news_number span.news_number_box1 {width: 55px;}

.sign_off_sample_info {display: flex;flex-direction:column;flex:1;justify-content:space-between;gap:30px 0;}
.sign_box_wrap {display: flex;position: absolute;top:50px;right: 5mm;}
.sign_box {width: 100px;border: 1px solid #121212;border-left: none;}
.sign_box_wrap .sign_box:first-child {border-left: 1px solid #121212;}
.sign_box_tit_box {border-bottom: 1px solid #121212;height: 25px;display: flex;align-items:center;justify-content:center;}
.sign_img_box {width: 100px;height: 80px;display: flex;align-items:center;justify-content:center;}
.sign_img_box img {max-width: 100%;max-height:100%;width: auto;height: auto;}
.news_tit_box {font-size: 30px;font-weight: 600;text-align: center;width: 100%;max-width: 210mm;margin-bottom: 40px;}

.write_info_wrap {width: 40%;border-left: 1px solid #999;border-bottom: 1px solid #999;border-right: 1px solid #999;margin-bottom: 30px;}
.write_box {display: flex;}
.write_box > div {height: 30px;}
.write_label {width: 100px;background: #fff1e0;border: 1px solid #999;border-left: none;border-bottom: none;font-size: 14px;font-weight: 500;display: flex;align-items:center;justify-content:center;}
.write_info {width: calc(100% - 100px);font-size: 13px;font-weight: 400;padding-left: 15px;display: flex;align-items:center;border-top: 1px solid #999;}

.sign_box_labels {font-size: 17px;font-weight: 500;position: relative;color: #121212;padding-left: 20px;margin-bottom: 10px;}
.sign_box_labels:before {content:"";width: 12px;height: 12px;background: #121212;position: absolute;top:3px;left: 0;}

.user_list {margin-bottom: 30px;}
.user_list_wrap {display: table;width: 100%;border: 1px solid #999;border-right: none;}
.user_list_hd {display: table-row;font-size: 14px;font-weight: 500;background: #fefff2;}
.user_list_hd_box {display:table-cell;text-align: center;padding: 10px 0;border-right: 1px solid #999;}
.user_list_hd_box.ver1 {width: 15%;}
.user_list_hd_box.ver2 {width: 30%;}
.user_list_hd_box.ver3 {width: 45%;}
.user_list_hd.ver2 {background: #fff;border-top: 1px solid #999;font-weight: 400;}
.user_list_hd.ver2 .user_list_hd_box {border-top: 1px solid #999;}

.memo_cont_box {min-height:200px;padding:20px;border: 1px solid #999;font-size: 14px;}
.sign_off_date {font-size: 18px;text-align: center;}
.request_sign_box {display: flex;justify-content:flex-end;font-size: 14px;align-items:center;margin-top: 30px;padding-right: 10mm;}
.reqeust_sign_name {min-width: 120px;border-bottom: 1px solid #999;min-height:20px;text-align: center;}
.request_sign_in {position: relative;}
.request_sign_in img {position: absolute;max-width: 120px;top: -13px;left: -60px;}

.holiday_wrap {border-top: 1px solid #999;border-right: 1px solid #999;}
.holiday_box_wrap {display: flex;}
.holiday_box_inner {display: flex;width: 100%;}
.holiday_box_inner > div {padding: 15px 0;font-size: 14px;border-bottom: 1px solid #999;}
.holiday_box_inner > div.holiday_box_td {width: calc(100% - 150px);padding:  15px;}
.holiday_box_inner > div.holiday_box_th {width: 150px;text-align: center;border-right: 1px solid #999;font-weight: 500;border-left: 1px solid #999;background: #fefff2;display: flex;align-items:center;justify-content:center;font-size: 15px;}
.holiday_box_inner > div.holiday_box_td.ver2 {min-height:250px}
.holiday_box_inner > div.holiday_box_th.ver2 {text-align:justify;width: 150px;}

.info_text_wrap {margin-top: 20px;}
.info_text {font-size: 13px;color: #FA1C1C;position: relative;padding-left: 10px;}
.info_text + .info_text {margin-top: 10px;}
.info_text:before {content:"*";position: absolute;top:0;left: 0;}

.important_text_wrap {margin-top: 165px;}
.important_text_wrap.ver2 {margin-top: 82px;}
.important_text {font-size: 18px;font-weight: 500;text-align: center;}

.sign_off_img_box {display: flex;align-items:center;justify-content:center;flex-direction:column;gap:20px 0;margin-bottom: 30px;}
.sign_off_img_box img {max-width: 100%;}

.duty_report_wrap {width: 100%;border-top:1px solid #999;border-right: 1px solid #999;}
.duty_report_box {display: flex;}
.duty_report_box > div {padding: 15px 0;font-size: 14px;border-bottom: 1px solid #999;}
.duty_report_box_left {width: 150px;text-align: center;border-right: 1px solid #999;font-weight: 500;border-left: 1px solid #999;background: #fefff2;display: flex;align-items:center;justify-content:center;font-size: 15px;}
.duty_report_box > div.duty_report_box_right {width: calc(100% - 150px);padding:  15px;}
.duty_report_box > div.duty_report_box_right.ver2 {min-height:250px;}

.overtime_wrap {width: 100%;border-top: 1px solid #999;}
.overtime_box {display: flex;}
.overtime_box > div {font-size: 14px;border-bottom: 1px solid #999;}
.overtime_box_left {width: 150px;text-align: center;border-right: 1px solid #999;font-weight: 500;border-left: 1px solid #999;background: #fefff2;display: flex;align-items:center;justify-content:center;font-size: 15px;padding: 15px 0;}
.overtime_box > div.overtime_box_right {width: calc(100% - 150px);}
.overtime_box_right.ver2 {display: flex;}
.overtime_box_right_box {width: calc(100% - 100px);display: flex;align-items:center;justify-content:center;flex-direction:column;border-right: 1px solid #999;flex:2;}
.overtime_box_right_box.ver2 {text-align: center;flex:1;}
.overtime_box_right_box.ver2 > div {padding: 10px 0;width: 100%;}
.overtime_box_right_box.ver2 > div:first-child {border-bottom: 1px solid #999;}
.overtime_box_right.ver3 {min-height:250px;border-right: 1px solid #999;padding: 15px;}
</style>
<input type="hidden" name="editorImage" id="editorImage">
<img id="preview" alt="이미지 미리보기" style="display:none">
<canvas id="canvas"  style="display:none"></canvas>
<!-- <button type="button" onclick="bbsToImg();">이미지 다운로드</button> -->
<div class="building_news_sample_wrap">
    <div class="news_content">
        <div class="sign_off_sample_info">
            <div class="sign_box_wrap">
                <div class="sign_box">
                    <div class="sign_box_tit_box"><?php echo $sign_off_mng_id1['mg_name']; ?></div>
                    <div class="sign_img_box">
                        <?php 
                        $sql_sign_off_img = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id1'";
                        $sign_img_row = sql_fetch($sql_sign_off_img);
                        ?>
                       <?php if($sign_img_row){?>
                        <img src="/data/file/approval/<?php echo $sign_img_row['fil_name']; ?>" alt="">
                        <?php }?>
                    </div>
                </div>
                <?php if($sign_off_row['sign_off_mng_id2'] != ""){
                    // print_r2($sign_off_row['sign_off_mng_id2']);

                    // echo $sign_off_row['sign_off_mng_id2'];
                    ?>
                <div class="sign_box">
                    <div class="sign_box_tit_box"><?php echo $sign_off_mng_id2['mg_name']; ?></div>
                    <div class="sign_img_box">
                        <?php 
                        $sql_sign_off_img2 = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id2'";
                        $sign_img_row2 = sql_fetch($sql_sign_off_img2);
                        ?>
                       <?php if($sign_img_row2){?>
                        <img src="/data/file/approval/<?php echo $sign_img_row2['fil_name']; ?>" alt="">
                        <?php }?>
                    </div>
                </div>
                <?php }?>
                <?php if($sign_off_row['sign_off_mng_id3'] != ""){?>
                <div class="sign_box">
                    <div class="sign_box_tit_box"><?php echo $sign_off_mng_id3['mg_name']; ?></div>
                    <div class="sign_img_box">
                     <?php 
                        $sql_sign_off_img3 = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id3'";
                        $sign_img_row3 = sql_fetch($sql_sign_off_img3);
                        ?>
                       <?php if($sign_img_row3){?>
                        <img src="/data/file/approval/<?php echo $sign_img_row3['fil_name']; ?>" alt="">
                        <?php }?>
                    </div>
                </div>
                <?php }?>
            </div>
            <div class="sign_off_top">
                <p class="news_tit_box sign_off_title"><?php echo $approval_name; ?></p>
                <div class="write_info_wrap">
                    <div class="write_box">
                        <div class="write_label">작성자</div>
                        <div class="write_info"><?php echo $mngs['mng_name']; ?></div>
                    </div>
                    <div class="write_box">
                        <div class="write_label">부서명</div>
                        <div class="write_info"><?php echo $mngs['md_name']; ?></div>
                    </div>
                    <div class="write_box">
                        <div class="write_label">직급</div>
                        <div class="write_info"><?php echo $mngs['mg_name']; ?></div>
                    </div>
                </div>

                <!-- paid_holiday -->
                <?php if($sign_off_row['sign_off_category'] == 'paid_holiday'){?>
                <div class="user_list">
                    <div class="sign_box_labels"><?php echo $sign_off_row['sign_off_year']; ?>년 <?php echo $sign_off_row['sign_off_month']; ?>월 연차사용 계획</div>
                    <div class="user_list_wrap">
                        <div class="user_list_hd">
                            <div class="user_list_hd_box ver1">성 명</div>
                            <div class="user_list_hd_box ver1">사용일수</div>
                            <div class="user_list_hd_box ver2">사용일자</div>
                            <div class="user_list_hd_box ver3">비고</div>
                        </div>
                        <?php for($i=0;$holiday_row = sql_fetch_array($holiday_res);$i++){?>
                        <div class="user_list_hd ver2">
                            <div class="user_list_hd_box ver1"><?php echo $holiday_row['hp_name']; ?></div>
                            <div class="user_list_hd_box ver1">
                                <?php 
                                $days = "";
                                $end_date = "";
                                switch($holiday_row['hp_day']){
                                    case "am_half":
                                        $days = "오전반차";
                                        break;
                                    case "pm_half":
                                        $days = "오후반차";
                                        break;
                                    case "halfhalf":
                                        $days = "반반차";
                                        break;
                                    default:
                                        $days = $holiday_row['hp_day'];

                                        $days2 = $days - 1;
                                        $end_date = " ~ ".date('m월 d일',strtotime($holiday_row['hp_date']."+".$days2." day")); 
                                }
                                echo $days; 
                                ?>
                            </div>
                            <div class="user_list_hd_box ver2"><?php echo date("m월 d일", strtotime($holiday_row['hp_date']))?><?php echo $days == '1' ? "" : $end_date; ?></div>
                            <div class="user_list_hd_box ver3"><?php echo $holiday_row['hp_memo']; ?></div>
                        </div>
                        <?php }?>
                    </div>
                </div>
                <!-- paid_holiday -->
                <div class="memo_box">
                    <div class="sign_box_labels">기타사항</div>
                    <div class="memo_cont_box"><?php echo nl2br($sign_off_row['sign_off_memo']); ?></div>
                </div>
                <?php }?>

                 <!-- holiday -->
                 <?php if($sign_off_row['sign_off_category'] == 'holiday'){?>
                 <div class="holiday_wrap">
                    <div class="holiday_box_wrap flex">
                        <div class="holiday_box_inner">
                            <div class="holiday_box_th">사용기간</div>
                            <div class="holiday_box_td">
                                <?php echo date("m월 d일", strtotime($sign_off_row['holiday_date']));?>
                                <?php 
                                $days = "";
                                $end_date = "";
                                switch($sign_off_row['holiday_day']){
                                    case "am_half":
                                        $days = "오전반차";
                                        break;
                                    case "pm_half":
                                        $days = "오후반차";
                                        break;
                                    case "halfhalf":
                                        $days = "반반차";
                                        break;
                                    default:
                                        $days = $holiday_row['hp_day'];
                                        $end_date = " ~ ".date('m월 d일',strtotime($holiday_row['hp_date']."+".$days." day")); 
                                }
                               
                                ?>
                                <?php
                                 $end_date = "";
                                 echo $days; 
                                ?>
                            </div>
                        </div>
                        <div class="holiday_box_inner">
                            <div class="holiday_box_th">사용일수</div>
                            <div class="holiday_box_td">
                                <?php
                                if($sign_off_row['holiday_day'] == 'half_half'){
                                    $holidays = '반반차';
                                }else if($sign_off_row['holiday_day'] == 'am_half'){
                                    $holidays = '오전반차';
                                }else if($sign_off_row['holiday_day'] == 'pm_half'){
                                    $holidays = '오후반차';
                                }else{
                                    $holidays = $sign_off_row['holiday_day'];
                                }
                                 echo $holidays; 
                                 ?>
                            </div>
                        </div>
                    </div>
                    <div class="holiday_box_wrap">
                        <div class="holiday_box_inner">
                            <div class="holiday_box_th ver2">사&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;유</div>
                            <div class="holiday_box_td ver2"><?php echo nl2br($sign_off_row['holiday_memo']); ?></div>
                        </div>
                    </div>
                </div>
                <div class="info_text_wrap">
                    <p class="info_text">휴가 신청은 부득이 한 경우를 제외하고는 7일전 신청하여야 합니다.</p>
                    <p class="info_text">휴가 사용은 업무에 지장이 없는 범위 내에서 사용하여야 합니다.</p>
                </div>
                <div class="important_text_wrap">
                    <p class="important_text">상기와 같이 연차유급휴가를 사용하고자 합니다. </p>
                </div>
                <?php }?>
                <?php if(in_array($sign_off_row['sign_off_category'], $page_arr)){
                ?>
                <?php if($file_total > 0){?>
                <div class="sign_off_img_box">
                    <?php for($i=0;$file_row = sql_fetch_array($file_res);$i++){?>
                        <img src="/data/file/signOff/<?php echo $file_row['bf_file']; ?>"  alt="">
                    <?php }?>
                </div>
                <?php }?>
                <div class="memo_box">
                    <div class="sign_box_labels">기타사항</div>
                    <div class="memo_cont_box"><?php echo nl2br($sign_off_row['sign_off_memo']); ?></div>
                </div>
                <?php }?>
                <?php if($sign_off_row['sign_off_category'] == "duty_report"){?>
                    <div class="duty_report_wrap">
                        <div class="duty_report_box">
                            <div class="duty_report_box_left">당직기간</div>
                            <div class="duty_report_box_right">
                                <?php echo date("Y년 m월 d일", strtotime($sign_off_row['duty_sdate']));?>&nbsp;&nbsp;~&nbsp;&nbsp;
                                <?php echo date("Y년 m월 d일", strtotime($sign_off_row['duty_edate']));?>
                            </div>
                        </div>
                        <div class="duty_report_box">
                            <div class="duty_report_box_left">특이사항</div>
                            <div class="duty_report_box_right ver2">
                                <?php echo nl2br($sign_off_row['significant_memo']); ?>
                            </div>
                        </div>
                        <div class="duty_report_box">
                            <div class="duty_report_box_left">기타사항</div>
                            <div class="duty_report_box_right ver2"><?php echo nl2br($sign_off_row['holiday_memo']); ?></div>
                        </div>
                    </div>
                    <div class="important_text_wrap ver2">
                        <p class="important_text">위의 당직근무 내역이 사실임을 확인합니다.</p>
                    </div>
                <?php }?>
                <?php if($sign_off_row['sign_off_category'] == "overtime_work_request" || $sign_off_row['sign_off_category'] == "overtime_work_report"){?>
                    <div class="overtime_wrap">
                        <div class="overtime_box">
                            <div class="overtime_box_left">일시</div>
                            <div class="overtime_box_right ver2">
                                <div class="overtime_box_right_box"><?php echo date("Y년 m월 d일", strtotime($sign_off_row['extension_date']));?></div>
                                <div class="overtime_box_right_box ver2">
                                    <div class="overtime_box_right_box2">시작시간</div>
                                    <div class="overtime_box_right_box2">종료시간</div>
                                </div>
                                <div class="overtime_box_right_box ver2">
                                    <div class="overtime_box_right_box2">
                                        <?php
                                        $stimes = explode(":", $sign_off_row['extension_stime']);
                                         echo $stimes[0]."시 ".$stimes[1]."분"; 
                                         ?>
                                    </div>
                                    <div class="overtime_box_right_box2">
                                    <?php
                                        $etimes = explode(":", $sign_off_row['extension_etime']);
                                         echo $etimes[0]."시 ".$etimes[1]."분"; 
                                         ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="overtime_box">
                            <div class="overtime_box_left">내용</div>
                            <div class="overtime_box_right ver3"><?php echo nl2br($sign_off_row['sign_off_memo']); ?></div>
                        </div>
                    </div>

                    <?php if($sign_off_row['sign_off_category'] == "overtime_work_request"){?>
                    <div class="important_text_wrap">
                        <p class="important_text">위 내용으로 연장 근무를 신청합니다.</p>
                    </div>
                    <?php }else{ ?>
                    <div class="important_text_wrap">
                        <p class="important_text">위의 연장근무 내역이 사실임을 확인합니다.</p>
                    </div>
                    <?php }?>
                <?php }?>
            </div>
            <div class="sign_off_bottom">
               
                <div class="sign_off_date"><?php echo date("Y년 m월 d일", strtotime($sign_off_row['wdate']));?></div>
                <div class="request_sign_box">
                    <div class="request_sign_label">신청자 : </div>
                    <div class="reqeust_sign_name"><?php echo $mngs['mng_name']; ?></div>
                    <div class="request_sign_in">
                        <div class="request_sign_in_text">(인)</div>
                        <img src="/data/file/approval/<?php echo $row_sign_off_img['fil_name']; ?>" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p><?php echo $approval_name; ?>을 저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
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

let ip = "<?php echo $_SERVER['REMOTE_ADDR']?>";
let deip = "<?php echo ADMIN_IP; ?>";

$(function(){
    bbsToImg();
    // if(ip != deip) bbsToImg();
});


function bbsToImg(){

    let mem_type = "<?php echo $mem_type; ?>";

    buildingInfoPopOpen();

    let sign_id = "<?php echo $sign_id; ?>";
    let sign_off_category = "<?php echo $sign_off_row['sign_off_category']; ?>";
    let editor = document.querySelector('.building_news_sample_wrap');
    editor.style.backgroundColor = "#fff"; // 배경색 추가 (투명 방지)

    html2canvas(editor, {
        scale: 3, 
        allowTaint: true, // 크로스오리진 이미지 허용
        useCORS: true     // CORS 이미지 캡처
    }).then(canvas => {
        let imgData = canvas.toDataURL("image/png");
        document.getElementById('editorImage').value = imgData;
        document.getElementById('preview').src = imgData;
        console.log("이미지 변환 성공!", imgData);


        let formData = new FormData();
        formData.append('editorImage', imgData); // Base64 이미지 데이터
        formData.append('sign_id', sign_id);
        // jQuery의 $.ajax()를 사용한 POST 요청
        $.ajax({
            url: '/holiday_request_sample_update.php', // 요청할 URL
            type: 'POST', // HTTP 메서드
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
                    return false;
                }else{
                    //buildingInfoPopClose();

                    showToast(data.msg);

                    // setTimeout(() => {
                    //     location.href = "/building_news_info_form.php?w=u&bb_id=" + bb_id
                    // }, 1000);
                    setTimeout(() => {

                        if(mem_type == 'admin'){
                            location.replace("/adm/approval_form.php?w=u&sign_id=" + sign_id);
                        }else if(mem_type == 'sign_user'){
                            location.replace("/adm/approval_info.php?w=u&sign_id=" + sign_id);
                        }else if(mem_type == "sign_user2"){
                            location.replace("/holiday_reqeust_info.php?types=" + sign_off_category + "&sign_id=" + sign_id + "&mng=Y");
                        }else{
                            location.replace("/holiday_reqeust_info.php?types=" + sign_off_category + "&sign_id=" + sign_id);
                        }
                    }, 300);
                }
                
            }
        });
    });
}

</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>