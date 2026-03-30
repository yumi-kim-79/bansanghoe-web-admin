<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$cal_sql = "SELECT cal.*, building.building_name FROM a_calendar as cal
            LEFT JOIN a_building as building ON cal.building_id = building.building_id
            WHERE cal.cal_idx = '{$cal_idx}'";
$cal_row = sql_fetch($cal_sql);

//echo $cal_code;
if($cal_code == "schedule"){
    $cal_code_sql = "SELECT * FROM a_calendar_setting WHERE cal_code != '{$cal_code}' ORDER BY is_prior asc, cal_id asc";
    $cal_code_res = sql_query($cal_code_sql);

    // echo $cal_code_sql;
}else{

    if($cal_code == ''){
        $cal_sql = "SELECT cal.*, building.building_name FROM a_calendar as cal
            LEFT JOIN a_building as building ON cal.building_id = building.building_id
            WHERE cal.cal_idx = '{$cal_idx}'";
        $cal_row = sql_fetch($cal_sql);

        $cal_code = $cal_row['cal_code'];
    }

    $cal_row2 = sql_fetch("SELECT * FROM a_calendar_setting WHERE cal_code = '{$cal_row['cal_code']}'");
}
// print_r2($cal_row);

if($w == 'u'){

    if($cal_row['is_del']){
        alert('삭제된 일정입니다.');
    } 

    if($cal_row['noti_repeat'] != 'N'){ //반복설정 되어있을 때

        $cal_date_def = date('Y-m-d', strtotime($cal_date_def));

       

        //날짜 시작일보다 이전날짜는 안됨
        if($cal_date_def < $cal_row['cal_date']) {
            alert('존재하지 않는 일정입니다.');
        }else{

            if($cal_row['noti_repeat'] == 'MONTH'){
                $cal_date_row2 = date('d', strtotime($cal_row['cal_date']));
                $cal_date2 = date('d', strtotime($cal_date_def));

                // echo $cal_date_row2.'<br>';
                // echo $cal_date2.'<br>';
                if($cal_date_row2 != $cal_date2){
                    alert('존재하지 않는 일정입니다.');
                }
            }else{
                $cal_date_year2 = date('m-d', strtotime($cal_row['cal_date']));
                $cal_year2 = date('m-d', strtotime($cal_date_def));

                
                if($cal_date_year2 != $cal_year2){
                    alert('존재하지 않는 일정입니다.');
                }
            }

        }
    }else{ //반복설정 안되어 있을 때
         
        //날짜가 동일하지 않으면 안됨
        if($cal_date_def != $cal_row['cal_date']) {
            alert('존재하지 않는 일정입니다.');
        }
    }
}

//처리완료 프로세스 변경
$is_process_chk = false;

$process_row = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = '{$cal_row['cal_idx']}' and process_date = '{$cal_date_def}' ");

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
// echo "SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = '{$cal_row['cal_idx']}' and process_date = '{$cal_date_def}'";

}
if($w == 'u' && $process_row['cnt'] > 0){
    $is_process_chk = true;
}

?>
<div id="wrappers">
    <div class="wrap_container">
        <form action="">
            <input type="hidden" name="cal_idx" id="cal_idx" value="<?php echo $cal_idx; ?>">
            <div class="inner">
                <?php if($cal_code != "schedule"){?>
                    <input type="hidden" name="cal_code" id="cal_code" value="<?php echo $cal_row['cal_code']; ?>">
                    <div class="schedule_form_tit"><?php echo $cal_row2['cal_name'].' 캘린더'; ?></div>
                    <script>
                        let w = "<?php echo $w; ?>";
                        let cal_code = "<?php echo $cal_code; ?>";
                         if(cal_code != "schedule" && w == ""){
                            calCodeHandler(cal_code);
                        }
                        function calCodeHandler(calcode){

                            console.log('calcode:::',calcode);
                            $.ajax({

                            url : "/schedule_add_depart_ajax.php", //ajax 통신할 파일
                            type : "POST", // 형식
                            data: { "calcode":calcode}, //파라미터 값
                            success: function(msg){ //성공시 이벤트

                                console.log(msg);
                                $("#mng_department").html(msg);
                            }

                            });
                        }
                    </script>
                <?php }?>
                <ul class="regi_list">
                    <?php if($cal_code == 'schedule'){?>
                    <li>
                        <p class="regi_list_title">캘린더 종류</p>
                        <div class="bansang_sel_box">
                            <select name="cal_code" id="cal_code" class="bansang_sel" onchange="calCodeChange();">
                                <option value="">캘린더 종류를 선택하세요.</option>
                                <?php for($i=0;$cal_code_row = sql_fetch_array($cal_code_res);$i++){?>
                                    <option value="<?php echo $cal_code_row['cal_code']; ?>"><?php echo $cal_code_row['cal_name'].' 캘린더'; ?></option>
                                <?php }?>
                            </select>
                            <script>
                                function calCodeChange(){
                                    var calCodeSelect = document.getElementById("cal_code");
                                    var calCodeValue = calCodeSelect.options[calCodeSelect.selectedIndex].value;

                                    $.ajax({

                                    url : "/schedule_add_depart_ajax.php", //ajax 통신할 파일
                                    type : "POST", // 형식
                                    data: { "calcode":calCodeValue}, //파라미터 값
                                    success: function(msg){ //성공시 이벤트

                                        //console.log(msg);
                                        $("#mng_department").html(msg);
                                    }

                                    });
                                }
                            </script>
                        </div>
                    </li>
                    <?php }?>
                    <li>
                        <?php
                        $sql_where = " WHERE mng_b.building_id = '{$cal_row['building_id']}' and mng_b.is_del = 0 ";
                        $sql_where2 = "";
                        
                        switch($calcode){
                            case "one_site":
                            case "meter_reading":
                                $sql_where2 = " and mng.mng_department = 2 or mng.mng_department = 1 ";
                            break;
                            case "secretary":
                                $sql_where2 = " and mng.mng_department = 3 ";
                            break;
                            case "computation":
                            case "move_out_settlement":
                                $sql_where2 = " and mng.mng_department = 1 ";
                            break;
                            default:
                                $sql_where2 = "";
                            break;
                        }

                        $sql_building = "SELECT mng_b.*, mng.mng_name, mng.mng_department, depart.md_name, mng_grade.mg_name FROM
                                         a_mng_building as mng_b
                                         LEFT JOIN a_mng as mng on mng_b.mb_id = mng.mng_id
                                         LEFT JOIN a_mng_department as depart on mng.mng_department = depart.md_idx
                                         LEFT JOIN a_mng_grade as mng_grade on mng.mng_grades = mng_grade.mg_idx
                                         {$sql_where} {$sql_where2} GROUP BY mng.mng_department ";
                        // echo $sql_building;
                        //exit;
                        $res_building = sql_query($sql_building);
                        ?>
                        <div class="ipt_box ipt_flex ipt_box_ver2">
                            <div class="bansang_sel_box">
                                <p class="regi_list_title">부서</p>
                                <!-- <div class="mng_department_wrap"></div> -->
                                 <?php if($w == 'i'){
                                    $mngs = get_manger($cal_row['mng_id']);
                                    
                                    ?>
                                    <input type="text" name="mng_depart_name" class="bansang_ipt ver2" value="<?php echo $cal_row['mng_department'] == '-1' ? '전체' : get_department_name($cal_row['mng_department']); ?>" readonly>
                                <?php }else{ ?>
                                    <select name="mng_department" id="mng_department" class="bansang_sel" onchange="department_change();" <?php echo $is_process_chk ? 'readonly' : '';?>>
                                        <option value="">선택</option>
                                        <?php
                                        while($row_building = sql_fetch_array($res_building)){
                                        ?>
                                        <option value="<?php echo $row_building['mng_department']?>" <?php echo get_selected($cal_row['mng_department'], $row_building['mng_department']); ?>><?php echo $row_building['md_name'];?></option>
                                        <?php }?>
                                    </select>
                                    <script>
                                        function department_change(){
                                            var departmentSelect = document.getElementById("mng_department");
                                            var departmentValue = departmentSelect.options[departmentSelect.selectedIndex].value;

                                            var w = "<?php echo $w; ?>";
                                            var buildingIdVal = "<?php echo $cal_row['building_id']; ?>";

                                            console.log('departmentValue', departmentValue);
                                            console.log('buildingIdVal', buildingIdVal);

                                            $.ajax({

                                            url : "/schedule_add_mng_ajax2.php", //ajax 통신할 파일
                                            type : "POST", // 형식
                                            data: {"departmentValue":departmentValue, 'building_id':buildingIdVal}, //파라미터 값
                                            success: function(msg){ //성공시 이벤트

                                                console.log(msg);
                                                $("#mng_id").html(msg);
                                                // $(".bansang_sel_box_mng").html(msg);
                                            }

                                            });
                                        }
                                    </script>
                                <?php }?>
                            </div>
                            <?php
                            $sql_where = " WHERE mng_b.building_id = '{$cal_row['building_id']}' and mng.mng_department = '{$cal_row['mng_department']}' and mng_b.is_del = 0 ";
                            $sql_where2 = "";
                            
                            $sql_building2 = "SELECT mng_b.*, mng.mng_name, mng.mng_department, depart.md_name, mng_grade.mg_name FROM
                                            a_mng_building as mng_b
                                            LEFT JOIN a_mng as mng on mng_b.mb_id = mng.mng_id
                                            LEFT JOIN a_mng_department as depart on mng.mng_department = depart.md_idx
                                            LEFT JOIN a_mng_grade as mng_grade on mng.mng_grades = mng_grade.mg_idx
                                            {$sql_where} {$sql_where2} ";
                            $res_building2 = sql_query($sql_building2);
                            // echo $sql_building2.'<br>';

                            // echo $cal_row['mng_id'];
                            ?>
                            <div class="bansang_sel_box bansang_sel_box_mng">
                                <p class="regi_list_title">담당자</p>
                                <select name="mng_id" id="mng_id" class="bansang_sel" <?php echo $is_process_chk ? 'readonly' : '';?>>
                                    <option value="">선택</option>
                                    <?php
                                    while($row_building2 = sql_fetch_array($res_building2)){
                                    ?>
                                    <option value="<?php echo $row_building2['mb_id']; ?>" <?php echo get_selected($cal_row['mng_id'], $row_building2['mb_id']); ?>><?php echo $row_building2['mng_name'].' '.$row_building2['mg_name'];?></option>
                                    <?php }?>
                                </select>
                               
                            </div>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">단지</p>
                        <?php if($w == 'u'){?>
                            <?php if($cal_row['noti_repeat'] != 'N'){?>
                                <div class="ipt_box ipt_flex">
                                    <input type="hidden" name="building_id" id="building_id" value="<?php echo $cal_row['building_id']; ?>">
                                    <div class="building_name_box ver2">
                                        <input type="text" name="building_name" id="building_name" class="bansang_ipt" placeholder="선택" readonly value="<?php echo $cal_row['building_name']; ?>">
                                        <span class="cancel_building">취소</span>
                                    </div>
                                </div>
                            <?php }else{?>
                                <div class="ipt_box ipt_flex">
                                    <input type="hidden" name="building_id" id="building_id" value="<?php echo $cal_row['building_id']; ?>">
                                    <div class="building_name_box <?php echo $is_process_chk ? 'ver2' : ''; ?>">
                                        <input type="text" name="building_name" id="building_name" class="bansang_ipt <?php echo $is_process_chk ? '' : 'ver2'; ?>" placeholder="선택" readonly value="<?php echo $cal_row['building_name']; ?>">
                                        <span class="cancel_building">취소</span>
                                    </div>
                                    <?php if(!$is_process_chk){?>
                                    <button type="button" onclick="popOpen('building_sch')" class="regi_box_btn">검색</button>
                                    <?php }?>
                                </div>
                            <?php }?>
                        <?php }else{ ?>
                        <div class="ipt_box ipt_flex">
                            <input type="hidden" name="building_id" id="building_id" value="<?php echo $cal_row['building_id']; ?>">
                            <div class="building_name_box">
                                <input type="text" name="building_name" id="building_name" class="bansang_ipt ver2" placeholder="선택" readonly value="<?php echo $cal_row['building_name']; ?>">
                                <span class="cancel_building">취소</span>
                            </div>
                            <button type="button" onclick="popOpen('building_sch')" class="regi_box_btn">검색</button>
                        </div>
                        <?php }?>
                    </li>
                    <li>
                        <p class="regi_list_title">날짜</p>
                        <?php if($w == 'u'){?>
                            <?php if($cal_row['noti_repeat'] != 'N'){?>
                                <div class="ipt_box">
                                    <input type="hidden" name="cal_date" value="<?php echo $cal_row['cal_date']; ?>">
                                    <input type="text" name="cal_date2" class="bansang_ipt ipt_date_not ipt_date_cal" value="<?php echo $cal_date_def; ?>"  required <?php echo $is_process_chk || $cal_row['noti_repeat'] != 'N' ? 'readonly' : '';?>>
                                </div>
                            <?php }else{ ?>
                                <div class="ipt_box">
                                    <input type="hidden" name="cal_date" value="<?php echo $cal_row['cal_date']; ?>">
                                    <input type="text" name="cal_date2" class="bansang_ipt <?php echo $is_process_chk ? 'ipt_date_not' : 'ver2 ipt_date';?> ipt_date_cal" value="<?php echo $cal_date_def; ?>"  required <?php echo $is_process_chk || $cal_row['noti_repeat'] != 'N' ? 'readonly' : '';?>>
                                </div>
                            <?php }?>
                        <?php }else{ ?>
                            <div class="ipt_box">
                                <input type="hidden" name="cal_date" value="<?php echo $cal_row['cal_date']; ?>">
                                <input type="text" name="cal_date2" class="bansang_ipt <?php echo $is_process_chk ? 'ipt_date_not' : 'ver2 ipt_date';?> ipt_date_cal" value="<?php echo $cal_date_def; ?>"  required <?php echo $is_process_chk || $cal_row['noti_repeat'] != 'N' ? 'readonly' : '';?>>
                            </div>
                        <?php }?>
                    </li>
                    <li>
                        <p class="regi_list_title">반복 설정</p>
                        <?php if($is_process_chk){
                            $noti_repeat_t;
                            switch($cal_row['noti_repeat']){
                                case "N":
                                    $noti_repeat_t = "안함";
                                    break;
                                case "MONTH":
                                    $noti_repeat_t = "월간";
                                    break;
                                case "YEAR":
                                    $noti_repeat_t = "연간";
                                    break;
                            }
                            ?>
                            <input type="hidden" name="noti_repeat" id="noti_repeat" class="bansang_ipt" value="<?php echo $cal_row['noti_repeat']; ?>" readonly>
                            <input type="text" name="noti_repeat_name" class="bansang_ipt" value="<?php echo $noti_repeat_t; ?>" readonly>
                        <?php }else{ ?>
                            <?php if($w == 'u'){?>
                                <?php 
                                    if($cal_row['noti_repeat'] != 'N'){
                                        $noti_repeat_t;
                                        switch($cal_row['noti_repeat']){
                                            case "N":
                                                $noti_repeat_t = "안함";
                                                break;
                                            case "MONTH":
                                                $noti_repeat_t = "월간";
                                                break;
                                            case "YEAR":
                                                $noti_repeat_t = "연간";
                                                break;
                                        }
                                ?>
                                    <input type="hidden" name="noti_repeat" id="noti_repeat" class="bansang_ipt" value="<?php echo $cal_row['noti_repeat']; ?>" readonly>
                                    <input type="text" name="noti_repeat_name" class="bansang_ipt" value="<?php echo $noti_repeat_t; ?>" readonly>
                                <?php }else{ ?>
                                    <div class="ipt_box ipt_flex ver2">
                                        <div class="ipt_radio">
                                            <input type="radio" name="noti_repeat" id="repeat_setting1" value="N" <?php echo $cal_row['noti_repeat'] == 'N' ? 'checked' : ''; ?>>
                                            <label for="repeat_setting1">안함</label>
                                        </div>
                                        <div class="ipt_radio">
                                            <input type="radio" name="noti_repeat" id="repeat_setting2" value="MONTH" <?php echo $cal_row['noti_repeat'] == 'MONTH' ? 'checked' : ''; ?>>
                                            <label for="repeat_setting2">월간</label>
                                        </div>
                                        <div class="ipt_radio">
                                            <input type="radio" name="noti_repeat" id="repeat_setting3" value="YEAR" <?php echo $cal_row['noti_repeat'] == 'YEAR' ? 'checked' : ''; ?>>
                                            <label for="repeat_setting3">연간</label>
                                        </div>
                                    </div>
                                <?php }?>
                            <?php }else{ ?>
                                <div class="ipt_box ipt_flex ver2">
                                    <div class="ipt_radio">
                                        <input type="radio" name="noti_repeat" id="repeat_setting1" value="N" <?php echo $cal_row['noti_repeat'] == 'N' ? 'checked' : ''; ?>>
                                        <label for="repeat_setting1">안함</label>
                                    </div>
                                    <div class="ipt_radio">
                                        <input type="radio" name="noti_repeat" id="repeat_setting2" value="MONTH" <?php echo $cal_row['noti_repeat'] == 'MONTH' ? 'checked' : ''; ?>>
                                        <label for="repeat_setting2">월간</label>
                                    </div>
                                    <div class="ipt_radio">
                                        <input type="radio" name="noti_repeat" id="repeat_setting3" value="YEAR" <?php echo $cal_row['noti_repeat'] == 'YEAR' ? 'checked' : ''; ?>>
                                        <label for="repeat_setting3">연간</label>
                                    </div>
                                </div>
                            <?php }?>
                        <?php }?>
                    </li>
                </ul>
                <ul class="regi_list ver2">
                    <li>
                        <p class="regi_list_title">제목</p>
                        <div class="ipt_box">
                            <input type="text" name="cal_title" id="cal_title" class="bansang_ipt <?php echo !$is_process_chk ? 'ver2' : '';?>" placeholder="제목을 입력하세요." value="<?php echo $cal_row['cal_title']; ?>" <?php echo $is_process_chk ? 'readonly' : '';?>>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">내용</p>
                        <div class="ipt_box">
                            <textarea name="cal_content" id="cal_content" class="bansang_ipt ta ver2" placeholder="일정 내용을 입력하세요." <?php echo $w == 'i' ? 'readonly' : '';?>><?php echo $cal_row['cal_content']; ?></textarea>
                        </div>
                    </li>
                    <?php if($w != ''){?>
                    <li>
                        <button type="button" class="fix_btn <?php echo $is_process_chk ? '' : 'on'?>" id="fix_btn" onclick="popOpen('schedule_process_pop');" <?php echo $is_process_chk ? 'disabled' : ''?>><?php echo $is_process_chk ? $process_row['created_at'].' ' : '';?>처리완료</button>
                    </li>
                    <?php }?>
                </ul>
            </div>
            <div class="fix_btn_back_box"></div>
            <div class="fix_btn_box flex_ver ver3">
                <?php if($w != "i"){?>
                <button type="button" class="fix_btn" id="fix_btn" onClick="historyBack();">취소</button>
                <?php }?>
                <?php if($w != "i"){?>
                <button type="button" class="fix_btn on" id="fix_btn" onClick="calendar_submit();"><?php echo $w == "" ? "저장" : "수정"; ?></button>
                <?php }?>
               
            </div>

        </form>
    </div>
</div>
<?php
$building_sql = "SELECT * FROM a_building WHERE is_del = 0 and is_use = 1 ORDER BY building_id desc";
$building_res = sql_query($building_sql);
?>
<div class="cm_pop" id="building_sch">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_title">단지 검색</div>
        <div class="sch_box_wrap mgt20">
            <div class="ipt_box ipt_flex ipt_box_ver2">
                <input type="text" name="sch_text" id="sch_text" class="bansang_ipt ver2 ver4" placeholder="단지명을 입력하세요.">
                <button type="button" class="sch_button" onclick="schHandler();">
                    <img src="/images/sch_icons.svg" alt="">
                </button>
                <script>
                    function schHandler(){
                        let sch_text = $("#sch_text").val();

                        if(sch_text == ""){
                            showToast("검색어를 입력해주세요.");
                            return false;
                        }

                        $.ajax({

                        url : "/schedule_add_building_ajax.php", //ajax 통신할 파일
                        type : "POST", // 형식
                        data: { "sch_text":sch_text}, //파라미터 값
                        success: function(msg){ //성공시 이벤트

                            //console.log(msg);
                            
                            $(".sch_result").html(msg);
                        }

                        });
                    }
                </script>
            </div>
        </div>
        <div class="sch_result">
            <?php for($i=0;$building_row = sql_fetch_array($building_res);$i++){?>
            <div class="sch_building_box" data-idx="<?php echo $building_row['building_id']; ?>" data-name="<?php echo $building_row['building_name']; ?>">
                <div class="sch_building_tit"><?php echo $building_row['building_name']; ?></div>
                <div class="sch_building_addr mgt10"><?php echo $building_row['building_addr']; ?></div>
            </div>
            <?php }?>
        </div>
        <div class="cm_pop_btn_box ver2">
			<button type="button" class="cm_pop_btn ver2" onClick="popClose('building_sch');">확인</button>
		</div>
    </div>
</div>

<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>

<div class="cm_pop" id="schedule_del_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">삭제 방식을 선택해주세요.</p>
		<div class="cm_pop_btn_box" style="display:flex;flex-direction:column;gap:8px;">
			<button type="button" class="cm_pop_btn ver2" onClick="scheduleDelHandler('this_only');">이 날짜 일정만 삭제</button>
			<button type="button" class="cm_pop_btn ver2" onClick="scheduleDelHandler('after_this');">이 날짜 이후 반복 일정 전체 삭제</button>
			<button type="button" class="cm_pop_btn ver2" onClick="scheduleDelHandler('all');">반복 일정 전체 삭제</button>
			<button type="button" class="cm_pop_btn" onClick="popClose('schedule_del_pop');">취소</button>
		</div>
	</div>
</div>

<div class="cm_pop" id="schedule_process_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">해당 일정을 처리완료 하시겠습니까?</p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="popClose('schedule_process_pop');">취소</button>
            <button type="button" class="cm_pop_btn ver2" onClick="scheduleProcessHandler();">확인</button>
		</div>
	</div>
</div>

<script>
$(function(){
    // minDate:"0d" 
    //maxDate: "+365d",
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99"});
});

$(document).on("click", ".sch_building_box", function(){
    $(".sch_building_box").removeClass("selected");
    $(this).addClass("selected");

    let idx = $(this).data('idx');
    let name = $(this).data('name');

    $("#building_id").val(idx);
    $("#building_name").val(name);

    $(".cancel_building").css("display", "flex");
});

$(".cancel_building").click(function(){
    $("#building_name").val("");
    $("#building_id").val("");

    $(".cancel_building").css("display", "none");
});

function calendar_submit(){

    $("#building_info_pop").show();

    let calcode = "<?php echo $cal_code; ?>";
    
    if(calcode == "schedule"){
        calcode = $("#cal_code option:selected").val();
    }

    let w_status = "<?php echo $w; ?>";
    let cal_idx = "<?php echo $cal_idx; ?>";
    let wid = "<?php echo $member['mb_id']; ?>";

    
    let mng_department = $("#mng_department option:selected").val();
    let mng_id = $("#mng_id option:selected").val();
    let building_id = $("#building_id").val();
    let cal_date = "<?php echo $cal_date_def; ?>";
    // let cal_date = $("#cal_date").val();
    let noti_repeat; 
    if(w_status == "u"){
        noti_repeat = $("#noti_repeat").val();
    }else{
        noti_repeat = $("input[name='noti_repeat']:checked").val(); 
    }
    
    let cal_title = $("#cal_title").val();
    let cal_content = $("#cal_content").val();

    let calEdate = "<?php echo $cal_row['cal_edate'];?>";
    let cal_date2 = "<?php echo $cal_date_def; ?>";

    if(w_status == 'u' && noti_repeat != "N" && calEdate == ''){
        if(!confirm("현재 일정 수정 시 " + cal_date2 + " 날짜 이후의 반복 일정이 모두 변경됩니다.\n계속 진행하시겠습니까?")){

            $("#building_info_pop").hide();

            return false;
        }
    }

    let sendData = {'w':w_status, 'mb_id': wid, 'calcode':calcode, 'mng_department':mng_department, 'mng_id':mng_id, 'building_id':building_id, 'cal_date':cal_date, 'noti_repeat':noti_repeat, 'cal_title':cal_title, 'cal_content':cal_content, 'cal_idx':cal_idx};

    
        $.ajax({
            type: "POST",
            url: "/schedule_add_update2.php",
            data: sendData,
            cache: false,
            async: false,
            dataType: "json",
            success: function(data) {
                console.log('data:::', data);

                if(data.result == false) { 
                    showToast(data.msg);
                    //$(".btn_submit").attr('disabled', false);
                    $("#building_info_pop").hide();
                    // if(data.data != ""){
                    //     $("#" + data.data).focus();
                    // }
                    return false;
                }else{
                    showToast(data.msg);

                    $("#building_info_pop").hide();

                    setTimeout(() => {
                        location.replace("/sm_index.php");
                    }, 700);
                }
            },
        });
   
    
}

//일정삭제
function scheduleDelHandler(del_mode){
    let cal_idx = "<?php echo $cal_idx; ?>";
    let cal_date = "<?php echo $cal_date_def; ?>";
    let mb_id = "<?php echo $member['mb_id']; ?>";

    var modeLabels = {
        'this_only': '이 날짜 일정만 삭제',
        'after_this': '이 날짜 이후 반복 일정 전체 삭제',
        'all': '반복 일정 전체 삭제'
    };

    if (!confirm(modeLabels[del_mode] + " 하시겠습니까?")) {
        return false;
    }

    popClose('schedule_del_pop');

    let sendData = {cal_idx:cal_idx, cal_date:cal_date, mb_id:mb_id, del_mode:del_mode};

    $.ajax({
        type: "POST",
        url: "/schedule_add_del2.php",
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
                showToast(data.msg);

                setTimeout(() => {
                    location.replace("/sm_index.php");
                }, 700);
            }
        },
    });
}

//일정 처리완료
function scheduleProcessHandler(){
    let cal_idx = "<?php echo $cal_idx; ?>";
    let mb_id = "<?php echo $member['mb_id']; ?>";
    let cal_date = "<?php echo $cal_date_def; ?>";

    let sendData = {cal_idx:cal_idx, mb_id:mb_id, cal_date:cal_date};

    $.ajax({
        type: "POST",
        url: "/schedule_add_process2.php",
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
                showToast(data.msg);

                // setTimeout(() => {
                //     location.replace("/sm_index.php");
                // }, 700);

                setTimeout(() => {
                    window.location.reload();
                }, 700);
            }
        },
    });
}

var w_val = "<?php echo $w; ?>";
if(w_val == "i"){
    const homebtn = document.querySelector('.home_btn');
    const tooltipBox = document.querySelector('.tooltip_btn');
    homebtn.addEventListener('click', () => {
    const dropdown = document.querySelector('.tooltip_box');
    dropdown.style.display = 'block';
    });

    homebtn.addEventListener('blur', () => {
    const dropdown = document.querySelector('.tooltip_box');

    setTimeout(() => {
        dropdown.style.display = '';
    }, 200);
    });
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>