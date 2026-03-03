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

// 출력행 계산
$setRows	= ceil( ( $startDays + $days ) / 7 );

$complain_status = "SELECT * FROM a_complain_status WHERE is_del = 0 ORDER BY is_prior asc, cs_idx asc";
$cs_res = sql_query($complain_status);

//$mng_building_t = "'".implode("','", $mng_building)."'";
//print_r2($mng_building);
//echo $mng_building_t;
$calendar_sql = "SELECT * FROM a_calendar_setting WHERE is_view = 1 ORDER BY is_prior asc, cal_id asc";
$calendar_res = sql_query($calendar_sql);
?>
<div id="wrappers" class="bgs">
    <div class="wrap_container">
        <div class="inner">
            <ul class="tab_lnb ver_sm">
                <li class="tab01 on" onclick="tab_handler('1', 'schedule')">일정</li>
                <li class="tab02" onclick="tab_handler('2', 'inout')">전출세대</li>
                <li class="tab03" onclick="tab_handler('3', 'complain')">민원</li>
            </ul>
            <div class="sm_section sm_section1">
                <select name="category" id="category" class="bansang_sel" onchange="cal_code_change();">
                    <?php for($i=0;$calendar_row = sql_fetch_array($calendar_res);$i++){?>
                        <option value="<?php echo $calendar_row['cal_code']; ?>"><?php echo $calendar_row['cal_name'].' 캘린더'; ?></option>
                    <?php }?>
                </select>
            </div>
            <div class="tab_btn_wrapper sm_section sm_section3 mgb20">
                <div class="tab_btn_wrap ver2">
                    <?php for($i=0;$cs_row = sql_fetch_array($cs_res);$i++){?>
                        <div class="tab_btn tab_btn0<?php echo $i + 1; ?> ver2 <?php echo $i == 0 ? 'on' : '';?>" onclick="tabHandler2('<?php echo $i + 1; ?>','<?php echo $cs_row['cs_code']; ?>')"><?php echo $cs_row['cs_name']; ?></div>
                    <?php }?>
                </div>
            </div>
            <div class="calendar_area_wrap mgt15 sm_section sm_section1 sm_section2">
                <div class="calendar_area">
                    
                </div>
                <ul class="cal_state">
                    <li class="cal_state_li cal_state_li1">					
                        <p class="red"></p>
                        <span>일정</span>
                    </li>
                    <li class="cal_state_li cal_state_li2">					
                        <p class="green"></p>
                        <span>전출세대</span>
                    </li>
                </ul>
            </div>
            <div class="car_visit_btn_box sm_section sm_section1">
                <a href="javascript:addSchedule();" class="car_visit_btn ver2">
                일정추가
                </a>
            </div>
        </div>
        <div class="bar ver3 sm_section sm_section2 sm_section1"></div>
        <div class="inner">
            <div class="sch_box_wrap mgb20 sm_section sm_section1 sm_section3">
                <div class="ipt_box ipt_flex ipt_box_ver2">
                    <input type="text" name="sch_text" id="sch_text" class="bansang_ipt ver2 ver4" placeholder="단지명을 입력하세요.">
                    <button type="button" class="sch_button" onclick="schedule_sch_handler();">
                        <img src="/images/sch_icons.svg" alt="">
                    </button>
                </div>
            </div>
            <div class="sm_schedule_list">
                <div class="sm_schedule_list1 schedule_lists sm_section sm_section1">
                </div>
                <div class="sm_schedule_list2 sm_mv_list sm_section sm_section2">
                </div>
                <div class="sm_schedule_list2 sm_schedule_list_complain sm_section sm_section3">
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$todays = date("Y-m-d");
$pop_list = "SELECT pop.*, files.bf_file FROM a_popup as pop
                LEFT JOIN g5_board_file as files on pop.pop_id = files.wr_id
                WHERE pop.is_del = 0 and pop.is_view = 1 and pop.pop_app = 'sm_mng' and (pop_edate = '' or (pop_sdate <= '{$todays}' and pop_edate >= '{$todays}') ) and files.bo_table = 'popup' ORDER BY pop.is_prior asc, pop.pop_id desc";
$pop_res = sql_query($pop_list);
$pop_total = sql_num_rows($pop_res);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    // echo $pop_total;
}
?>
<!-- style="display:block;" -->
<?php
// $styles = $member['mb_id'] == 'mng1' ? 'display:block;' : '';
if($pop_total > 0){
?>
<div class="cm_pop" id="banner_sm_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont ver_banner">
        <div class="bn_pop_cont">
            <!-- <?php echo $pop_list;?> -->
            <div class="swiper ban_swp">
                <div class="swiper-wrapper">
                    <?php for($i=0;$file_row = sql_fetch_array($pop_res);$i++){?>
                    <div class="swiper-slide">
                        <div>
                            <img src="/data/file/popup/<?php echo $file_row['bf_file'];?>" alt="">
                        </div>
                    </div>
                    <?php }?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
        <div class="bn_pop_btn_wrap">
            <button type="button" onclick="hideForOneDay();">오늘 하루 안보기</button>
            <button type="button" onclick="closePopup();">닫기</button>
        </div>
	</div>
</div>
<?php }?>
<script>
let swiper = new Swiper(".ban_swp", {
    slidesPerView: "auto",
    pagination: {
        el: '.swiper-pagination',
        type: 'fraction',
    },
    autoHeight: true,
});

function setCookie(name, value, days) {
    const date = new Date();
    date.setDate(date.getDate() + days);
    document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/';
}

function getCookie(name) {
    const value = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
    return value ? value[2] : null;
}

// 팝업 닫기 (쿠키 설정 없음)
function closePopup() {
    document.getElementById('banner_sm_pop').style.display = 'none';
}

// 하루 동안 보지 않기 (쿠키 설정)
function hideForOneDay() {
    setCookie('hidePopupSM', 'yes', 1);
    closePopup();
}

window.onload = function () {

    console.log('cookie',getCookie('hidePopupSM'));
    if (getCookie('hidePopupSM') !== 'yes') {
        document.getElementById('banner_sm_pop').style.display = 'block';
    }
}

let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";
let tabCode = "<?php echo $tabCode ?? 'schedule'; ?>";
let nowYear = "<?php echo date("Y");?>";
let nowMonth = "<?php echo date("m");?>";
let page = 1;
tab_handler(tabIdx, tabCode);

// $("#category").val('schedule').change();

function tab_handler(index, code){

    console.log(index);
    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    if(index != 3){
        $(".cal_state_li").hide();
        $(".cal_state_li" + index).css('display', 'flex');
        moveCal(nowYear, nowMonth, index);
    }else{

    }

    $(".sm_section").hide();
    $(".sm_section" + index).show();      

    if(index == 3){
        complain_list();
    }
}

function addSchedule(){

    let calendar_code = $("#category option:selected").val();

    location.href = "/schedule_add.php?cal_code=" + calendar_code;
}

function complain_list(status = 'CB'){
    
    let mb_id = "<?php echo $member['mb_id']; ?>";
    let department = "<?php echo $mng_info['mng_department']; ?>";

    $.ajax({

        url : "/sm_index_complain_list.php", //ajax 통신할 파일
        type : "POST", // 형식
        data: { "mb_id":mb_id, "status":status, 'department':department}, //파라미터 값
        success: function(msg){ //성공시 이벤트
            //console.log(msg);
            $(".sm_schedule_list_complain").html(msg);
        }

    });
}

function moveCal(year, month, type, calendar_code = 'schedule'){

    $("#sch_text").val(""); //검색초기화

    let mb_id = "<?php echo $member['mb_id']; ?>";

    nowYear = year;
    nowMonth = month;
    // let calendar_code = "";
    // if(type == '1'){
    //     calendar_code = $("#category option:selected").val();

    //     console.log('calendar_code',calendar_code);
    // }

    $.ajax({
        type: "POST",
        url: "/inc/get_calendar.php",
        data: {toYear:year, toMonth:month, type:type, mb_id:mb_id, calendar_code:calendar_code}, 
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {
            $(".calendar_area").empty().append(data);
        }
    });

    if(type == '1'){
        $.ajax({
            type: "POST",
            url: "/inc/get_schedule.php",
            data: {toYear:year, toMonth:month, type:type, mb_id:mb_id, calendar_code:calendar_code}, 
            cache: false,
            async: true,
            contentType : "application/x-www-form-urlencoded; charset=UTF-8",
            success: function(data) {
                $(".schedule_lists").empty().append(data);
            }
        });
    }

    if(type == '2'){
        $.ajax({
            type: "POST",
            url: "/inc/get_move_list.php",
            data: {toYear:year, toMonth:month, type:type, mb_id:mb_id}, 
            cache: false,
            async: true,
            contentType : "application/x-www-form-urlencoded; charset=UTF-8",
            success: function(data) {
                $(".sm_mv_list").empty().append(data);
            }
        });
    }
}

function cal_code_change(){
    //let calendar_code = $("#category option:selected").val();
    let mb_id = "<?php echo $member['mb_id']; ?>";

    var calCodeSelect = document.getElementById("category");
    var calCodeValue = calCodeSelect.options[calCodeSelect.selectedIndex].value;
    let sch_text = $("#sch_text").val();
    console.log('calCodeValue::::',calCodeValue);

    moveCal(nowYear, nowMonth, '1', calCodeValue);
}

moveCal(nowYear, nowMonth, tabIdx);

$(document).on("click", ".cal_td_box1", function(){
    //$(this).addClass("select_dates");

    if ($(this).hasClass("select_dates")) {
        // 이미 선택되어 있으면 해제만 함 (AJAX 호출 안 함)
        $(this).removeClass("select_dates");

        selectDate = "";

        schedule_sch_handler();
        return;
    }

    $(".cal_td_box1").removeClass("select_dates");
    $(this).addClass("select_dates");
    
    let calendar_code = $("#category option:selected").val();
    let checkDate = $(this).data('date');
    let sch_text = $("#sch_text").val();
    let mb_id = "<?php echo $member['mb_id']; ?>";

    $.ajax({
        type: "POST",
        url: "/inc/get_schedule.php",
        data: {toYear:nowYear, toMonth:nowMonth, type:'1', mb_id:mb_id, calendar_code:calendar_code, checkDate:checkDate, sch_text:sch_text, page:1}, 
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {
            $(".schedule_lists").empty().append(data);
        }
    });
    //console.log('select_dates');
});

function schedule_sch_handler(){
    let calendar_code = $("#category option:selected").val();
    let sch_text = $("#sch_text").val();
    let mb_id = "<?php echo $member['mb_id']; ?>";

    $.ajax({
        type: "POST",
        url: "/inc/get_schedule.php",
        data: {toYear:nowYear, toMonth:nowMonth, type:'1', mb_id:mb_id, calendar_code:calendar_code, sch_text:sch_text}, 
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {
            $(".schedule_lists").empty().append(data);
        }
    });
}

$(document).on("click", ".cal_td_box2", function(){
    //$(this).addClass("select_dates");
    $(".cal_td_box2").removeClass("select_dates");
    $(this).addClass("select_dates");

    let checkDate = $(this).data('date');
    let mb_id = "<?php echo $member['mb_id']; ?>";

    $.ajax({
        type: "POST",
        url: "/inc/get_move_list.php",
        data: {toYear:nowYear, toMonth:nowMonth, type:'2', mb_id:mb_id, checkDate:checkDate}, 
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {
            $(".sm_mv_list").empty().append(data);
        }
    });
});


let tabIdx2 = "<?php echo $tabIdx2 ?? '1'; ?>";
let tabStatus = "<?php echo $tabStatus ?? 'CB'; ?>";
tabHandler2(tabIdx2, tabStatus);

function tabHandler2(index, tcode){
    $(".tab_btn_wrap .tab_btn").removeClass("on");
    $(".tab_btn0" + index).addClass("on");

    complain_list(tcode);
}

$(".tab_btn_wrap .tab_btn").on("click", function(){
    $(".tab_btn_wrap .tab_btn").removeClass("on");
    $(this).addClass("on");
});


$(document).on('click', '.pg_page_noti', function() {
    page = $(this).data('page');
    var cls = '.pg_page' + page;

    var checkDate = '';
    if ($('.cal_td_box').hasClass("select_dates")) {
        console.log('있음');

        // console.log('선택된 데이트',$(".select_dates").data('date'));

        checkDate = $(".select_dates").data('date');
    }

    $(this).addClass('on').siblings().removeClass('on');
    
    let calendar_code = $("#category option:selected").val();
    let sch_text = $("#sch_text").val();
    let mb_id = "<?php echo $member['mb_id']; ?>";

    $.ajax({
        type: "POST",
        url: "/inc/get_schedule.php",
        data: {toYear:nowYear, toMonth:nowMonth, type:'1', mb_id:mb_id, calendar_code:calendar_code, sch_text:sch_text, checkDate:checkDate, page:page}, 
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {
            $(".schedule_lists").empty().append(data);
        }
    });
});
</script>
<?php
include_once(G5_PATH.'/tail.php');