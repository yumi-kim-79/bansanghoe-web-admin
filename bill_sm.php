<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$sql = "SELECT * FROM a_sign_off_category WHERE is_del = 0 and is_use = 1 ORDER BY is_prior asc, sign_cate_id asc";
$res = sql_query($sql);
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <p class="approval_tit">내 담당</p>
                <a href="/approval_document.php" class="approval_btn mgt10">결재 서류함</a>
            </div>
        </div>
        <div class="inner">
            <ul class="approval_menu_list">
                <?php while($row = sql_fetch_array($res)){?>
                <li>
                    <a href="/holiday_reqeust.php?types=<?php echo $row['sign_cate_code']?>"><?php echo $row['sign_cate_name']; ?></a>
                </li>
                <?php }?>
                <!-- <li>
                    <a href="/holiday_reqeust.php?types=holiday_pay">연차 유급 휴가 사용 계획서</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=day_out">일일 지출 현황표</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=refund_req">세대 환급 요청 기안서</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=site_out">현장 소장 지출</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=personal_approval">개인 결재</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=payments">지출 기안서</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=building_adjust">건축주 정산서</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=building_payment">건물 자체 계좌 지출 기안서</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=mng_adjust">관리단 정산서</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=public_payment">공과금 납부 리스트</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=extension_work">연장 근무 신청서</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=duty_report">당직 보고서</a>
                </li>
                <li>
                    <a href="/holiday_reqeust.php?types=extension_work_report">연장 근무 보고서</a>
                </li> -->
            </ul>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>