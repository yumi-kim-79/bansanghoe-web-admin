<?php
require_once './_common.php';

$ex_info = sql_fetch("SELECT * FROM a_expense_report WHERE ex_id = '{$ex_id}'");

$ex_file = "SELECT * FROM g5_board_file WHERE bo_table = 'expense' and wr_id = '{$ex_id}' ORDER BY bf_no asc";
$ex_file_res = sql_query($ex_file);

$g5['title'] = $ex_info['ex_title'];
include_once(G5_PATH.'/head.sub.php');
?>
<style>
.building_news_sample_wrap {position: relative;min-width:210mm;margin: 0 auto;background: #fff;}
.news_content {
    width: 100%;
    width: 210mm;
    min-height: 297mm;
    margin: auto;
    padding: 10mm 5mm 10mm;
    position: relative;
    display: flex;
}

.sign_off_sample_info {display: flex;flex-direction:column;flex:1;justify-content:space-between;gap:30px 0;}
.sign_box_wrap {display: flex;justify-content:flex-end;margin-bottom:60px;}
.sign_box {width: 100px;border: 1px solid #121212;border-left: none;}
.sign_box_wrap .sign_box:first-child {border-left: 1px solid #121212;}
.sign_box_tit_box {border-bottom: 1px solid #121212;height: 50px;display: flex;align-items:center;justify-content:center;text-align: center;flex-direction:column}
.sign_box_tit_box p + p {margin-top: 5px;}
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

.sign_off_img_box {display: flex;justify-content:center;gap:20px 0;margin-bottom: 30px;flex-wrap:wrap;}
.sign_off_img_box img {max-width: 25%;}

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

.expense_content {margin-bottom: 30px;}

.building_news_sample_hd {width: 100%;padding:15px;display: flex;justify-content:flex-end;min-width:210mm}
.building_news_sample_hd button {padding:10px 15px;border-radius:6px;border:none;background: var(--colorMain);color: #fff;font-size: 14px;}
</style>
<?php
// echo $ex_file.'<br>';
// print_r2($ex_info);
?>
<div class="building_news_sample_hd">
    <button type="button" onclick="printBuildingNews();">인쇄</button>
</div>
<div class="building_news_sample_wrap">
    <div class="news_content">
        <div class="sign_off_sample_info">
            <div class="sign_off_top_wrap">
                <!-- <div class="sign_box_wrap">
                    <div class="sign_box">
                        <div class="sign_box_tit_box">
                            <p>최초 결재자</p>
                            <?php
                            $approval_info1 = get_mng_team($ex_info['ex_approver1']);
                            // print_r2($approval_info1);
                            ?>
                            <p><?php echo get_dong($approval_info1['dong_id'])['dong_name'].'-'.$approval_info1['gr_name']; ?></p>
                        </div>
                        <div class="sign_img_box">
                            <?php 
                            $sign_img_sql = "SELECT * FROM a_sign_off_img WHERE mng_id = '{$sign_off_row['sign_off_mng_id1']}' and so_cont = 'sign_off_status' and so_type = 'approval_sm' and sign_id = '{$sign_id}'";
                            //echo $sign_img_sql;
                            $sign_img_row = sql_fetch($sign_img_sql);

                            ?>
                            <?php if($sign_off_row['sign_off_status'] && $sign_img_row){?>
                            <img src="/data/file/approval/<?php echo $sign_img_row['so_name']; ?>" alt="">
                            <?php }?>
                        </div>
                    </div>
                    <?php if($ex_info['ex_approver2'] != ""){?>
                    <div class="sign_box">
                        <div class="sign_box_tit_box">
                            <p>중간 결재자</p>
                            <?php
                            $approval_info2 = get_mng_team($ex_info['ex_approver2']);
                            // print_r2($approval_info1);
                            ?>
                            <p><?php echo get_dong($approval_info2['dong_id'])['dong_name'].'-'.$approval_info2['gr_name']; ?></p>
                        </div>
                        <div class="sign_img_box">

                        </div>
                    </div>
                    <?php }?>
                    <?php if($ex_info['ex_approver3'] != ""){?>
                    <div class="sign_box">
                        <div class="sign_box_tit_box">
                            <p>최종 결재자</p>
                            <?php
                            $approval_info3 = get_mng_team($ex_info['ex_approver3']);
                            // print_r2($approval_info1);
                            ?>
                            <p><?php echo get_dong($approval_info3['dong_id'])['dong_name'].'-'.$approval_info3['gr_name']; ?></p>
                        </div>
                        <div class="sign_img_box">

                        </div>
                    </div>
                    <?php }?>
                </div> -->
                <div class="sign_off_top">
                    <p class="news_tit_box sign_off_title"><?php echo $ex_info['ex_title']; ?></p>
                    <div class="write_info_wrap">
                        <div class="write_box">
                            <div class="write_label">작성자</div>
                            <div class="write_info"><?php echo $ex_info['ex_name']; ?></div>
                        </div>
                        <div class="write_box">
                            <div class="write_label">부서명</div>
                            <div class="write_info"><?php echo get_department_name($ex_info['ex_department']); ?></div>
                        </div>
                        <div class="write_box">
                            <div class="write_label">직급</div>
                            <div class="write_info"><?php echo $ex_info['ex_grade']; ?></div>
                        </div>
                    </div>
                </div>
                <div class="expense_content">
                    <?php echo $ex_info['ex_content']; ?>
                </div>
                <div class="sign_off_img_box">
                    <?php for($i=0;$file_row = sql_fetch_array($ex_file_res);$i++){?>
                        <img src="/data/file/expense/<?php echo $file_row['bf_file']; ?>"  alt="">
                    <?php }?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function printBuildingNews() {
    var printContent = document.querySelector(".building_news_sample_wrap").cloneNode(true);
    var originalContent = document.body.innerHTML;

    // 인쇄 전용 스타일 추가
    var printStyle = document.createElement("style");
    printStyle.innerHTML = `

        @page {
            size: A4 portrait; /* 세로 방향으로 고정 */
            margin: 0;
        }

        @media print {
            body { margin: 0; padding: 0; }
            .building_news_sample_hd { display: none !important; } /* 인쇄 버튼 숨김 */
            .news_content {
                width: 100%;
                width: 210mm;
                min-height: 297mm;
                margin: auto;
                padding: 10mm 5mm 10mm;
                position: relative;
                display: flex;
                background: #fff;
            }

            .sign_off_sample_info {display: flex;flex-direction:column;flex:1;justify-content:space-between;gap:30px 0;}
            .sign_box_wrap {display: flex;justify-content:flex-end;margin-bottom:60px;}
            .sign_box {width: 100px;border: 1px solid #121212;border-left: none;}
            .sign_box_wrap .sign_box:first-child {border-left: 1px solid #121212;}
            .sign_box_tit_box {border-bottom: 1px solid #121212;height: 50px;display: flex;align-items:center;justify-content:center;text-align: center;flex-direction:column}
            .sign_box_tit_box p + p {margin-top: 5px;}
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

            .sign_off_img_box {display: flex;justify-content:center;gap:20px 0;margin-bottom: 30px;flex-wrap:wrap;}
            .sign_off_img_box img {max-width: 25%;}

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

            .expense_content {margin-bottom: 30px;}

            .building_news_sample_hd {width: 100%;padding:15px;display: flex;justify-content:flex-end;min-width:210mm}
            .building_news_sample_hd button {padding:10px 15px;border-radius:6px;border:none;background: var(--colorMain);color: #fff;font-size: 14px;}
        }
    `;

    document.head.appendChild(printStyle); // 스타일 적용
    document.body.innerHTML = "";
    document.body.appendChild(printContent);

    window.print(); // 인쇄 실행

    // 원래 페이지 복원
    document.body.innerHTML = originalContent;
    location.reload();
}
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>