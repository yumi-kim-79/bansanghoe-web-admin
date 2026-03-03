<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

/********** 사용자 설정값 **********/
$startYear        = date( "Y" );
$endYear        = date( "Y" ) + 4;

/********** 입력값 **********/
$year            = ( $_GET['toYear'] )? $_GET['toYear'] : date( "Y" );
$month            = ( $_GET['toMonth'] )? $_GET['toMonth'] : date( "m" );
$doms            = array( "일", "월", "화", "수", "목", "금", "토" );
//$doms            = array( "SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT" );

/********** 계산값 **********/
$mktime            = mktime( 0, 0, 0, $month, 1, $year );      // 입력된 값으로 년-월-01을 만든다
$days            = date( "t", $mktime );                        // 현재의 year와 month로 현재 달의 일수 구해오기
$startDays        = date( "w", $mktime );                        // 시작요일 알아내기

// 지난달 일수 구하기
$prevDayCount    = date( "t", mktime( 0, 0, 0, $month, 0, $year ) ) - $startDays + 1;

$nowDayCount    = 1;                                            // 이번달 일자 카운팅
$nextDayCount    = 1;                                          // 다음달 일자 카운팅

// 이전, 다음 만들기
$prevYear        = ( $month == 1 )? ( $year - 1 ) : $year;
$prevMonth        = ( $month == 1 )? 12 : ( $month - 1 );
$nextYear        = ( $month == 12 )? ( $year + 1 ) : $year;
$nextMonth        = ( $month == 12 )? 1 : ( $month + 1 );


$prevYear2        = ( $month == 1 )? ( $year - 1 ) : $year;
$prevMonth2        = ( $month == 1 )? 12 : ( $month - 1 );
$nextYear2        = ( $month == 12 )? ( $year + 1 ) : $year;
$nextMonth2        = ( $month == 12 )? 1 : ( $month + 1 );

// 출력행 계산
$setRows	= ceil( ( $startDays + $days ) / 7 );

$ho_sql = "SELECT ho.*, building.building_name, dong.dong_name FROM a_building_ho as ho
            LEFT JOIN a_building as building on ho.building_id = building.building_id
            LEFT JOIN a_building_dong as dong on ho.dong_id = dong.dong_id
            WHERE ho.ho_id = '{$ho_id}'";
$ho_row = sql_fetch($ho_sql);
//echo $ho_sql;

$car_sql = "SELECT * FROM a_building_car WHERE ho_id = '{$ho_id}' and is_del = 0 ORDER BY car_id asc";
$car_res = sql_query($car_sql);

//관리단
$mng_sql = "SELECT mng.*, mng_gr.gr_name, COUNT(*) as cnt FROM a_mng_team as mng
            LEFT JOIN a_mng_team_grade as mng_gr on mng.mt_grade = mng_gr.gr_id
            WHERE mng.ho_id = '{$ho_id}' and mng.mt_hp = '{$ho_row['ho_tenant_hp']}' and mng.is_del = 0";
//echo $mng_sql;
$mng_row = sql_fetch($mng_sql);

//세대
$hh_sql = "SELECT * FROM a_building_household WHERE ho_id = '{$ho_id}' and is_del = 0 and hh_relationship != '' and hh_name != '' ORDER BY hh_id asc";
$hh_res = sql_query($hh_sql);
$hh_total = sql_num_rows($hh_res);

//히스토리
$history_sql = "SELECT * FROM a_building_household_history WHERE ho_id = '{$ho_id}' ORDER BY history_idx desc";
$history_res = sql_query($history_sql);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // print_r2($ho_row);
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
               <div class="house_hold_wrap ver2">
                  <div class="house_hold_box">
                    <div class="house_hold_l">단지</div>
                    <div class="house_hold_r"><?php echo $ho_row['building_name']; ?></div>
                  </div>
                  <div class="house_hold_box">
                    <div class="house_hold_l">동</div>
                    <div class="house_hold_r"><?php echo $ho_row['dong_name']; ?>동</div>
                  </div>
                  <div class="house_hold_box">
                    <div class="house_hold_l">호수</div>
                    <div class="house_hold_r"><?php echo $ho_row['ho_name']; ?>호</div>
                  </div>
                  <div class="house_hold_box">
                    <div class="house_hold_l">면적</div>
                    <div class="house_hold_r"><?php echo $ho_row['ho_size']; ?>m²</div>
                  </div>
                  <div class="house_hold_box">
                    <div class="house_hold_l">소유자</div>
                    <div class="house_hold_r"><?php echo $ho_row['ho_owner']; ?></div>
                  </div>
                  <div class="house_hold_box">
                    <div class="house_hold_l">소유자 연락처</div>
                    <div class="house_hold_r">
                        <a href="tel:<?php echo $ho_row['ho_owner_hp']; ?>">
                            <img src="/images/phone_icons_b.svg" alt="">
                            <span><?php echo $ho_row['ho_owner_hp']; ?></span>
                        </a>
                    </div>
                  </div>
               </div>
               <div class="house_hold_wrap ver2">
                    <div class="house_hold_box">
                        <div class="house_hold_l">입주자</div>
                        <div class="house_hold_r"><?php echo $ho_row['ho_status'] == 'Y' ? $ho_row['ho_tenant'] : '-'; ?></div>
                    </div>
                    <div class="house_hold_box">
                        <div class="house_hold_l">입주자 연락처</div>
                        <div class="house_hold_r">
                            <?php if($ho_row['ho_status'] == "Y"){?>
                            <a href="tel:<?php echo $ho_row['ho_tenant_hp']; ?>">
                                <img src="/images/phone_icons_b.svg" alt="">
                                <span><?php echo $ho_row['ho_tenant_hp']; ?></span>
                            </a>
                            <?php }else{?>
                            -
                            <?php }?>
                        </div>
                    </div>
                    <div class="house_hold_box">
                        <div class="house_hold_l">입주일</div>
                        <div class="house_hold_r"><?php echo $ho_row['ho_tenant_at'] != '' ? date("Y.m.d", strtotime($ho_row['ho_tenant_at'])) : ''; ?></div>
                    </div>
                    <div class="house_hold_box">
                        <div class="house_hold_l">차량정보</div>
                        <div class="house_hold_r ver2">
                            <?php for($i=0;$car_row = sql_fetch_array($car_res);$i++){?>
                            <div class="house_hold_car_box"><?php echo $car_row['car_type'].' '.$car_row['car_name'];?></div>
                            <?php }?>
                        </div>
                    </div>
                    <div class="house_hold_box">
                        <div class="house_hold_l">관리단</div>
                        <div class="house_hold_r">
                            <?php echo $mng_row['cnt'] > 0 ? $mng_row['gr_name'] : '입주민';?>
                        </div>
                    </div>
               </div>
            </div>
        </div>
        
            <ul class="house_hold_menu_list">
                <?php if($hh_total > 0){?>
                <li onclick="popOpen('house_hold_list_pop');">세대 구성원 (<?php echo $hh_total;?>)</li>
                <?php }else{ ?>
                    <li class="disabled">세대 구성원 (<?php echo $hh_total;?>)</li>
                <?php }?>
                <li onclick="popOpen('visit_car_list_pop');">방문 차량 정보</li>
                <li onclick="popOpen('bill_month_pop')">고지서 월별 내역</li>
                <li onclick="popOpen('in_out_pop')">입/퇴실 내역</li>
                <?php if($ho_row['ho_memo'] != ''){?>
                <li onclick="popOpen('ho_memo_pop')">메모</li>
                <?php }?>
            </ul>
   
    </div>
</div>
<div class="cm_pop" id="house_hold_list_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onclick="popClose('house_hold_list_pop')">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">세대 구성원</div>
        <div class="cm_house_hold_box_wrapper">
            <?php for($i=0;$hh_row = sql_fetch_array($hh_res);$i++){?>
                <div class="cm_house_hold_box_wrap mgt20">
                    <div class="cm_house_hold_box">
                        <div class="cm_house_hold_box_left">관계</div>
                        <div class="cm_house_hold_box_right"><?php echo $hh_row['hh_relationship']; ?></div>
                    </div>
                    <div class="cm_house_hold_box">
                        <div class="cm_house_hold_box_left">이름</div>
                        <div class="cm_house_hold_box_right"><?php echo $hh_row['hh_name']; ?></div>
                    </div>
                    <div class="cm_house_hold_box">
                        <div class="cm_house_hold_box_left">연락처</div>
                        <div class="cm_house_hold_box_right">
                            <a href="tel:<?php echo $hh_row['hh_hp']; ?>">
                                <img src="/images/phone_icons_b.svg" alt="">
                                <span><?php echo $hh_row['hh_hp']; ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php }?>
        </div>
    </div>
</div>

<div class="cm_pop" id="visit_car_list_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onclick="popClose('visit_car_list_pop')">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">방문 차량 정보</div>
        <div class="cal_header_wrapper">
            <div class="cal_header_wrap mgt20">
                <section class="cal_header">
                    <button type="button" onClick="moveCal_vc('<?php echo $prevYear2?>', '<?php echo $prevMonth2?>');">
                        <img src="/images/icon_cal_prev.svg" alt="">
                    </button>
                    <p><?php echo $year; ?>년 <?php echo $month; ?>월</p>
                    <button type="button" onClick="moveCal_vc('<?php echo $nextYear2?>', '<?php echo $nextMonth2?>');">
                        <img src="/images/icon_cal_next.svg" alt="">
                    </button>
                </section>
            </div>
            <div class="car_visit_pop_wrap mgt20">
            </div>
        </div>
    </div>
</div>


<div class="cm_pop" id="ho_memo_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onclick="popClose('ho_memo_pop')">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">메모</div>
        <div class="ho_memo_wrap mgt20">
            <textarea name="ho_memo" id="ho_memo" class="bansang_ipt ver2 ta" readonly><?php echo $ho_row['ho_memo']; ?></textarea>
        </div>
    </div>
</div>


<script>
moveCal_vc('<?php echo $year?>', '<?php echo $month; ?>');
function moveCal_vc(year, month){

    console.log(year, month);

    let ho_id = "<?php echo $ho_id; ?>";
    

    $.ajax({
        type: "POST",
        url: "/household_mng_visit_car.php",
        data: {toYear:year, toMonth:month, ho_id:ho_id}, 
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {
            $(".cal_header_wrapper").empty().append(data);
        }
    });
}
</script>

<?php
$nowDate = date("Y-m-d");
?>
<div class="cm_pop" id="bill_month_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onclick="popClose('bill_month_pop')">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">고지서 월별 내역</div>
        <div class="bill_cal_wrapper">
            <div class="cal_header_wrap mgt20">
                <section class="cal_header">
                    <button type="button" onClick="moveCal('<?php echo $prevYear?>', '<?php echo $prevMonth?>', '<?php echo $type; ?>');">
                        <img src="/images/icon_cal_prev.svg" alt="">
                    </button>
                    <p><?php echo date("Y", strtotime($nowDate)); ?>년 <?php echo date("m", strtotime($nowDate)); ?>월</p>
                    <button type="button" onClick="moveCal('<?php echo $nextYear?>', '<?php echo $nextMonth?>', '<?php echo $type; ?>');">
                        <img src="/images/icon_cal_next.svg" alt="">
                    </button>
                </section>
            </div>
            <div class="pop_label_box mgt15">
                <div class="pop_label">관리비 상세 내역</div>
                <div class="pop_label_sub">(부가 가치세 포함)</div>
            </div>
            <div class="pop_bill_wrap mgt10">
                <div class="pop_bill_box">
                    <div class="pop_bill_label">청소비</div>
                    <div class="pop_bill_price"><?php echo number_format(10000)?>원</div>
                </div>
                <div class="pop_bill_box">
                    <div class="pop_bill_label">경비비</div>
                    <div class="pop_bill_price"><?php echo number_format(10000)?>원</div>
                </div>
                <div class="pop_bill_box">
                    <div class="pop_bill_label">소독비</div>
                    <div class="pop_bill_price"><?php echo number_format(10000)?>원</div>
                </div>
                <div class="pop_bill_box">
                    <div class="pop_bill_label">난방비</div>
                    <div class="pop_bill_price"><?php echo number_format(10000)?>원</div>
                </div>
                <div class="pop_bill_box">
                    <div class="pop_bill_label">가스비</div>
                    <div class="pop_bill_price"><?php echo number_format(10000)?>원</div>
                </div>
                <div class="pop_bill_box">
                    <div class="pop_bill_label">전기료</div>
                    <div class="pop_bill_price"><?php echo number_format(10000)?>원</div>
                </div>
                <div class="pop_bill_box">
                    <div class="pop_bill_label">수도료</div>
                    <div class="pop_bill_price"><?php echo number_format(10000)?>원</div>
                </div>
            </div>
            <div class="pop_total_price_box mgt10">
                <div class="pop_label"><?php echo date("m", strtotime($nowDate)); ?>월 총 관리비</div>
                <div class="pop_total_price"><span><?php echo number_format(10000)?></span>원</div>
            </div>
        </div>
    </div>
</div>
<script>
    let nowYear = "<?php echo date("Y"); ?>";
    let nowMonth = "<?php echo date("n"); ?>";

    moveCal(nowYear, nowMonth);
    function moveCal(year, month){

        let building_id = "<?php echo $ho_row['building_id']; ?>";
        let dong_name = "<?php echo $ho_row['dong_name']; ?>동";
        let ho_name = "<?php echo $ho_row['ho_name']; ?>";

        $.ajax({
            type: "POST",
            url: "/household_mng_bill_ajax.php",
            data: {toYear:year, toMonth:month, building_id:building_id, dong_name:dong_name, ho_name:ho_name}, 
            cache: false,
            async: true,
            contentType : "application/x-www-form-urlencoded; charset=UTF-8",
            success: function(data) {
                $(".bill_cal_wrapper").empty().append(data);
            }
        });
    }
</script>

<div class="cm_pop" id="in_out_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onclick="popClose('in_out_pop')">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">입/퇴실 내역</div>
        <div class="pop_in_out_wrap mgt20">
            <?php for($i=0;$history_row = sql_fetch_array($history_res);$i++){
                // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                //     print_r2($history_row);
                // }
                ?>
            <div class="pop_in_out_box_wrap">
                <div class="pop_in_out_box">
                    <div class="pop_in_out_dates"><?php echo $history_row['history_tenant_date'] != '0000-00-00' ? date("Y.m.d", strtotime($history_row['history_tenant_date'])) : date("Y.m.d", strtotime($history_row['created_at']));?></div>
                    <div class="pop_in_out_status <?php echo $history_row['history_status'] == 'IN' ? 'ver2' : ''?>"><?php echo $history_row['history_status'] == 'IN' ? '입주' : '퇴실';?></div>
                </div>
                <div class="pop_in_out_info">
                    <div class="pop_in_out_info_name"><?php echo $history_row['history_name']; ?></div>
                    <div class="pop_in_out_info_tel">
                        <a href="tel:<?php echo $history_row['history_hp']; ?>">
                            <img src="/images/phone_icons_b.svg" alt="">
                            <span><?php echo $history_row['history_hp']; ?></span>
                        </a>
                    </div>
                </div>
            </div>
            <?php }?>
            <?php if($i==0){?>
            <div class="empty_history">등록된 입/퇴실 내역이 없습니다.</div>
            <?php }?>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>