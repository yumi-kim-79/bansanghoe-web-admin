<?php
require_once './_common.php';

switch($cal_code){
    case "one_site":
        $sub_menu = "930200";
    break;
    case "secretary":
        $sub_menu = "930300";
    break;
    case "computation":
        $sub_menu = "930400";
    break;
    case "move_out_settlement":
        $sub_menu = "930500";
    break;
    case "meter_reading":
        $sub_menu = "930600";
    break;
    case "schedule":
        $sub_menu = "930100";
    break;
    case "etc1":
        $sub_menu = "930700";
    break;
    case "etc2":
        $sub_menu = "930800";
    break;
    case "etc3":
        $sub_menu = "930900";
    break;
}

$cal_setting = sql_fetch("SELECT cal_name FROM a_calendar_setting WHERE cal_code = '{$cal_code}'");
//echo $bbs_code;

auth_check_menu($auth, $sub_menu, 'r');


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

$sql_common = " from a_calendar ";

$now_month = $year.'-'.$month;

if($cal_code == "schedule"){
    $sql_search = " where (1) and cal_date like '{$now_month}%' and is_del = '0' ";
}else{
    $sql_search = " where (1) and cal_date like '{$now_month}%' and cal_code = '{$cal_code}' and is_del = '0' ";
}

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_tel':
        case 'pr_name':
            $sql_search .= " (par.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
    $sst = "std.st_idx";
    $sod = "desc";
}

$sql_order = " order by cal_idx desc ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
//echo $sql.'<br>';
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
//$rows = 5;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 탈퇴회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
//echo $sql;
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
//echo $sql;
$row = sql_fetch($sql);
$stop_count = $row['cnt'];

$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$g5['title'] = "사내용 캘린더 - ".$cal_setting['cal_name'];

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 5;

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $sql;
}
//echo $st_status;
//echo $sub_menu;

?>

<form name="fstudentlist" id="fstudentlist" action="./student_list_update.php" onsubmit="return fstudentlist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">

    <div class="calendar_wrapper">
        <div class="calendar_wrap">
            <div class="calendar_area"></div>
        </div>
        <div class="cal_schedule_list_wrap">
            <div class="cal_schedule_label mgb10">일정 내역</div>
            <div class="cal_schedule_box_wrap">

            </div>
        </div>
    </div>
    <div class="btn_fixed_top">
        <?php if ($is_admin == 'super') { ?>
            <a href="./calendar_form.php?cal_code=<?php echo $cal_code?>" id="member_add" class="btn btn_03">등록</a>
        <?php } ?>
    </div>


</form>

<!-- <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?> -->

<script>

function cal_year_change(){
    var calYearSelect = document.getElementById("cal_year");
    var calYearValue = calYearSelect.options[calYearSelect.selectedIndex].value;

    let calMonthValue = $("#cal_month option:selected").val();
    //console.log('calYearValue', calYearValue, calMonthValue);

    moveCal(calYearValue, calMonthValue, "1", "<?php echo $cal_code; ?>");
}

function cal_month_change(){
    var calMonthSelect = document.getElementById("cal_month");
    var calMonthValue = calMonthSelect.options[calMonthSelect.selectedIndex].value;

    let calYearValue = $("#cal_year option:selected").val();
    //console.log('calMonthValue', calYearValue, calMonthValue);

    moveCal(calYearValue, calMonthValue, "1", "<?php echo $cal_code; ?>");
}


function moveCal(year, month, type, calcode){
    $.ajax({
        type: "POST",
        url: "./get_calendar2.php",
        data: {toYear:year, toMonth:month, type:type, calcode:calcode},
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {
            $(".calendar_area").empty().append(data);
        }
    });

    $.ajax({
        type: "POST",
        url: "./calendar_schedule_list2.php",
        data: {toYear:year, toMonth:month, calcode:calcode, selectDate:'', building_stx:($("#cal_building_search").val()||"")},
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {

            console.log('data', data);
            $(".cal_schedule_box_wrap").empty().append(data);
        }
    });
}

let nowYear = "<?php echo $_GET['toYear'] != '' ? $_GET['toYear'] : date("Y");?>";
let nowMonth = "<?php echo $_GET['toMonth'] != '' ? $_GET['toMonth'] : date("m");?>";

moveCal(nowYear, nowMonth, "1", "<?php echo $cal_code; ?>");

let selectedDates = '';
$(document).on("click", ".cal_td_box", function(){

    let year = $("#cal_year option:selected").val();
    let month = $("#cal_month option:selected").val();
    let calcode = "<?php echo $cal_code;?>";
    var selectDate = $(this).data('date');

    selectedDates = selectDate;

    if ($(this).hasClass("select_dates")) {
        // 이미 선택되어 있으면 해제만 함 (AJAX 호출 안 함)
        $(this).removeClass("select_dates");

        selectDate = "";
        selectedDates = '';

        calendar_schedule_handler(year, month, calcode, selectDate);
        return;
    }

    // 다른 모든 선택 제거 후 현재 클릭한 것만 선택
    $(".cal_td_box").removeClass("select_dates");
    $(this).addClass("select_dates");

    console.log('selectDate', selectDate);

    $.ajax({
        type: "POST",
        url: "./calendar_schedule_list2.php",
        data: {toYear:year, toMonth:month, calcode:calcode, selectDate:selectDate, building_stx:($("#cal_building_search").val()||"")},
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {

            console.log('data', data);
            $(".cal_schedule_box_wrap").empty().append(data);
        }
    });
    //console.log('select_dates');
});

function calendar_schedule_handler(year, month, calcode, selectDate) {

    console.log('handler', year, month, calcode, selectDate);
    $.ajax({
        type: "POST",
        url: "./calendar_schedule_list2.php",
        data: {toYear:year, toMonth:month, calcode:calcode, selectDate:selectDate, building_stx:($("#cal_building_search").val()||"")},
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {

            console.log('data', data);
            $(".cal_schedule_box_wrap").empty().append(data);
        }
    });
}


$(document).on('click', '.pg_page_noti', function() {
    var page = $(this).data('page');
    var cls = '.pg_page' + page;


    let year = $("#cal_year option:selected").val();
    let month = $("#cal_month option:selected").val();
    let calcode = "<?php echo $cal_code;?>";
    // var selectDate = $(this).data('date');

    $.ajax({
        type: "POST",
        url: "./calendar_schedule_list2.php",
        data: {toYear:year, toMonth:month, calcode:calcode, selectDate:selectedDates, page:page, building_stx:($("#cal_building_search").val()||"")},
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {

            console.log('data', data);
            $(".cal_schedule_box_wrap").empty().append(data);
        }
    });
});


function doCalSearch() {
    var year    = $("#cal_year option:selected").val();
    var month   = $("#cal_month option:selected").val();
    var calcode = "<?php echo $cal_code;?>";
    var stx     = $("#cal_building_search").val().trim();
    $("#cal_search_label").text(stx ? "\"" + stx + "\" 검색 중" : "");
    $.ajax({
        type: "POST",
        url: "./calendar_schedule_list2.php",
        data: {toYear:year, toMonth:month, calcode:calcode, selectDate:"", building_stx:stx},
        cache: false, async: true,
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {
            $(".cal_schedule_box_wrap").empty().append(data);
        }
    });
}

function resetCalSearch() {
    $("#cal_building_search").val("");
    $("#cal_search_label").text("");
    var year    = $("#cal_year option:selected").val();
    var month   = $("#cal_month option:selected").val();
    var calcode = "<?php echo $cal_code;?>";
    $.ajax({
        type: "POST",
        url: "./calendar_schedule_list2.php",
        data: {toYear:year, toMonth:month, calcode:calcode, selectDate:"", building_stx:""},
        cache: false, async: true,
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {
            $(".cal_schedule_box_wrap").empty().append(data);
        }
    });
}

$(document).ready(function(){
    $(document).on("keypress", "#cal_building_search", function(e){
        if (e.which == 13) doCalSearch();
    });
});

function fstudentlist_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 회원을 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    if (document.pressed == "선택승인") {
        if (!confirm("선택한 회원을 승인하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
require_once './admin.tail.php';