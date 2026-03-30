<?php
require_once './_common.php';

if($w == 'u' && $cal_idx != ''){
    $cal_infos = sql_fetch("SELECT * FROM a_calendar WHERE cal_idx = '{$cal_idx}'");
    // echo $cal_infos;
    $cal_code = $cal_code == "" ? $cal_infos['cal_code'] : $cal_code;
}

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


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '등록';
}

$g5['title'] .= "사내용 캘린더 - ".$cal_setting['cal_name'].' '. $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;


$sql = "SELECT * FROM a_calendar
        WHERE cal_idx = {$cal_idx}";
$row = sql_fetch($sql);

//지역
$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

$wheres = "";
if($cal_code == "schedule"){
    $wheres = " WHERE cal_code != 'schedule' ";
}else{
    //250908 캘린더 수정, 추가시 종류 전체선택 가능하도록
    //$wheres = " WHERE cal_code = '{$cal_code}' ";

    $wheres = " WHERE cal_code != 'schedule' and is_view = 1 ";
}

//캘린더 종류

$calendar_type_sql = "SELECT * FROM a_calendar_setting {$wheres} ORDER BY is_prior asc, cal_id asc";
$calendar_type_res = sql_query($calendar_type_sql);

$mb_ids = $member['mb_id'];
$mng_infos = get_manger($mb_ids);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $sql.'<br>';
    echo $calendar_type_sql.'<br>';
    //print_r2($row);
}



if($w == 'u'){

    if($row['is_del']){
        alert('삭제된 일정입니다.');
    } 

    if($row['noti_repeat'] != 'N'){ //반복설정 되어있을 때

        $cal_date_def = date('Y-m-d', strtotime($cal_date_def));

       

        //날짜 시작일보다 이전날짜는 안됨
        if($cal_date_def < $row['cal_date']) {
            alert('존재하지 않는 일정입니다.');
        }else{

            if($row['cal_edate'] != ''){
                //날짜 마감일보다 이후날짜는 안됨
                if($cal_date_def > $row['cal_edate']) {
                    alert('존재하지 않는 일정입니다.');
                }
            }

            if($row['noti_repeat'] == 'MONTH'){
                $cal_date_row2 = date('d', strtotime($row['cal_date']));
                $cal_date2 = date('d', strtotime($cal_date_def));

                // echo $cal_date_row2.'<br>';
                // echo $cal_date2.'<br>';
                if($cal_date_row2 != $cal_date2){
                    alert('존재하지 않는 일정입니다.');
                }
            }else{
                $cal_date_year2 = date('m-d', strtotime($row['cal_date']));
                $cal_year2 = date('m-d', strtotime($cal_date_def));

                
                if($cal_date_year2 != $cal_year2){
                    alert('존재하지 않는 일정입니다.');
                }
            }

        }
    }else{ //반복설정 안되어 있을 때
         
        //날짜가 동일하지 않으면 안됨
        if($cal_date_def != $row['cal_date']) {
            alert('존재하지 않는 일정입니다.');
        }
    }
}

//처리완료 프로세스 변경
$is_process_chk = false;

$process_row = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = '{$row['cal_idx']}' and process_date = '{$cal_date_def}' ");


if($process_row['cnt'] > 0){
    $is_process_chk = true;
}

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    //print_r2($process_row);
    echo "SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = '{$row['cal_idx']}' and process_date = '{$cal_date_def}'"."<br>";
}
// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js

?>
<?php if($w == ""){?>
<style>
    .tr_show {display: none;}
</style>
<?php }?>
<form name="fcalendar" id="fcalendar" action="./calendar_form_update2.php" onsubmit="return fcalendar_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="cal_code" value="<?php echo $cal_code; ?>">
    <input type="hidden" name="cal_idx" value="<?php echo $row['cal_idx']; ?>">
    <?php if($w == 'u'){?>
        <input type="hidden" name="wid" value="<?php echo $row['wid']; ?>">
        <input type="hidden" name="cal_date_def" value="<?php echo $cal_date_def; ?>">
        <input type="hidden" name="exception_idx" value="<?php echo $row['exception_idx']; ?>">
    <?php }?>

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">캘린더 정보</h2>
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <?php if($w != ''){?>
                <tr>
                    <th>처리</th>
                    <td colspan='3'>
                        <?php if($is_process_chk){?>
                            <div class="process_p mgb5">
                                <?php echo $process_row['created_at']; ?> 처리완료
                            </div>
                        <?php }?>
                        <button type="button" onclick="calendar_process();" class="btn <?php echo $is_process_chk ? 'btn_02' : 'btn_03';?>" <?php echo $is_process_chk ? 'disabled' : '';?>>처리완료</button>
                    </td>
                </tr>
                <?php }?>
                <tr>
                    <th>캘린더종류</th>
                    <td colspan="3">
                        <!-- <?=$row['noti_repeat'];?> -->
                        <select name="cal_code" id="cal_code" class="bansang_sel arr93" required onchange="calCodeChange();" <?php echo $is_process_chk || ($row['noti_repeat'] != 'N' && $w == 'u') ? 'readonly' : '';?>>
                            <option value="">캘린더 종류를 선택하세요.</option>
                           <?php for($i=0;$calendar_type_row = sql_fetch_array($calendar_type_res);$i++){?>
                            <option value="<?php echo $calendar_type_row['cal_code']?>" <?php echo get_selected($row['cal_code'], $calendar_type_row['cal_code']); ?>><?php echo $calendar_type_row['cal_name'].' 캘린더'; ?></option>
                            <?php }?>
                        </select>
                        <script>
                            function calCodeChange(){
                                var calcodeSelect = document.getElementById("cal_code");
                                var calcodeValue = calcodeSelect.options[calcodeSelect.selectedIndex].value;


                                //$("#post_id").html(`<option value="">선택</option>`);
                                $("#mng_department").html(`<option value="">선택</option>`);
                                $("#building_id").val('');
                                $("#building_name").val('');
                                $("#mng_id").html(`<option value="">선택</option>`);

                                if(calcodeValue == ""){
                                    $(".tr_show").hide();
                                }else{
                                    $(".tr_show").css("display", "table-row");

                                    $.ajax({

                                    url : "./post_ajax.php", //ajax 통신할 파일
                                    type : "POST", // 형식
                                    data: {}, //파라미터 값
                                    success: function(msg){ //성공시 이벤트

                                        //console.log(msg);
                                        $("#post_id").html(msg);
                                    }

                                    });
                                }
                            }
                        </script>
                    </td>
                </tr>
               
                <tr class="tr_show">
                    <th>지역</th>
                    <td>
                        <!-- required 필수제거 250908 -->
                        <select name="post_id" id="post_id" class="bansang_sel" onchange="post_change();"  <?php echo $is_process_chk || $row['noti_repeat'] != 'N' ? 'readonly' : '';?>>
                            <option value="">선택</option>
                            <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                                <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($row['post_id'], $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                            <?php }?>
                        </select>
                        <script>
                            function post_change(){
                                var postSelect = document.getElementById("post_id");
                                var postValue = postSelect.options[postSelect.selectedIndex].value;

                                console.log('postValue', postValue);

                                $("#mng_department").html(`<option value="">선택</option>`);
                                $("#mng_id").html(`<option value="">선택</option>`);
                                $("#building_id").val('');
                                $("#building_name").val('');

                                // $.ajax({

                                // url : "./post_building_ajax.php", //ajax 통신할 파일
                                // type : "POST", // 형식
                                // data: { "post_id":postValue}, //파라미터 값
                                // success: function(msg){ //성공시 이벤트

                                //     //console.log(msg);
                                //     $("#building_id").html(msg);
                                // }

                                // });
                            }
                        </script>
                    </td>
                    <!-- <th>단지</th>
                    <td>
                        <?php 
                        $sql_building = "SELECT * FROM a_building WHERE post_id = '{$row['post_id']}' and is_del = 0";
                        $res_building = sql_query($sql_building);
                        ?>
                        <select name="building_id" id="building_id" class="bansang_sel" onchange="building_change();" required <?php echo $row['is_process'] ? 'readonly' : '';?>>
                            <option value="">선택</option>
                            <?php while($row_building = sql_fetch_array($res_building)){ ?>
                                <option value="<?php echo $row_building['building_id']?>" <?php echo get_selected($row['building_id'], $row_building['building_id']); ?>><?php echo $row_building['building_name'];?></option>
                            <?php }?>
                        </select>
                        <script>
                            function building_change(){
                                var buildingSelect = document.getElementById("building_id");
                                var buildingValue = buildingSelect.options[buildingSelect.selectedIndex].value;

                                var selectedCalcode = $("#cal_code option:selected").val();

                                //console.log('buildingValue', buildingValue);
                                $("#mng_id").html(`<option value="">선택</option>`);

                                $.ajax({

                                url : "./building_department.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "building_id":buildingValue, "calcode":selectedCalcode}, //파라미터 값
                                success: function(msg){ //성공시 이벤트

                                    //console.log(msg);
                                    $("#mng_department").html(msg);
                                }

                                });
                            }
                        </script>
                    </td> -->
                </tr>
                <tr class="tr_show">
                    <th>단지</th>
                    <td colspan='3'>
                        <?php
                        $build_sql = "SELECT * FROM a_building WHERE building_id = '{$row['building_id']}'";
                        $build_row = sql_fetch($build_sql);
                        ?>
                        <div class="sch_box_wrap ">
                            <div class="sch_box_left">
                                <div class="sch_result_box">
                                    <!-- <button type="button">푸르지오</button>
                                    <button type="button">푸르지오2</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button> -->
                                </div>
                                <!-- 검색어를 입력해주세요. -->
                                <input type="text" name="building_sch" id="building_sch" class="bansang_ipt <?php echo $is_process_chk || $row['noti_repeat'] != 'N' ? '' : 'ver2';?>" size="50" placeholder="단지명을 입력하세요." <?php echo $is_process_chk || $row['noti_repeat'] != 'N' ? 'readonly' : '';?>>
                            </div>
                           
                        </div>
                        <input type="hidden" name="building_id" id="building_id" value="<?php echo $row['building_id']; ?>">
                        <input type="text" name="building_name" id="building_name" class="bansang_ipt ver2 mgt10" size="100" placeholder="선택한 단지가 보여집니다." readonly value="<?php echo $build_row['building_name']; ?>"  >
                        <!-- required 필수제거 -->
                        <script>

                        //단지 입력시 ajax
                        $(document).on("keyup", "#building_sch", function(){
                        
                        var post_id = $("#post_id option:selected").val();
                        let sch_text = this.value;

                        if(sch_text != ""){

                            $(".sch_result_box").show();

                            $.ajax({

                            url : "./manage_form_sch_ajax.php", //ajax 통신할 파일
                            type : "POST", // 형식
                            data: { "building_name":sch_text, "post_id":post_id}, //파라미터 값
                            success: function(msg){ //성공시 이벤트

                                console.log('keyup',msg);
                            
                                $(".sch_result_box").html(msg); //.select_box2에 html로 나타내라..
                            }

                            });
                        }else{
                            $(".sch_result_box").html("");
                        }

                        
                        });

                        //단지선택..
                        function building_select(id, name){
                            // alert(id);

                           

                            $(".sch_result_box").hide();
                            $("#building_sch").val("");

                            let sendData2 = {'building_id': id};

                            $.ajax({
                                type: "POST",
                                url: "./building_post_ajax.php",
                                data: sendData2,
                                cache: false,
                                async: false,
                                dataType: "json",
                                success: function(data) {
                                    console.log('data:::', data);

                                    if(data.result == false) { 
                                        alert(data.msg);
                                        return false;
                                    }else{
                                        
                                        $("#post_id").val(data.msg).change();
                                    
                                    }
                                },
                            });

                            $("#mng_id").html(`<option value="">선택</option>`);

                            var selectedCalcode = $("#cal_code option:selected").val();

                            $.ajax({

                            url : "./building_department.php", //ajax 통신할 파일
                            type : "POST", // 형식
                            data: { "building_id":id, "calcode":selectedCalcode}, //파라미터 값
                            success: function(msg){ //성공시 이벤트

                                //console.log(msg);
                                $("#mng_department").html(msg);
                            }

                            });

                            $("#building_id").val(id);
                            $("#building_name").val(name);
                        }
                        </script>
                    </td>
                </tr>
                <tr class="tr_show">
                    <th>부서</th>
                    <td>
                        <?php
                        $sql_where = " WHERE mng_b.building_id = '{$row['building_id']}' and mng_b.is_del = 0 ";
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
                        //echo $sql_building;
                        //exit;
                        $res_building = sql_query($sql_building);
                        
                        ?>
                        <!-- required 필수제거 250908 -->
                        <select name="mng_department" id="mng_department" class="bansang_sel" onchange="department_change();"  <?php echo $is_process_chk ? 'readonly' : '';?>>
                            <option value="">선택</option>
                            <?php if($row['mng_department'] == '-1'){?>
                                <option value="-1" <?php echo $row['mng_department'] == '-1' ? 'selected' : ''; ?>>전체</option>
                            <?php }?>
                            <?php while($row_building = sql_fetch_array($res_building)){ ?>
                            <option value="<?php echo $row_building['mng_department']?>" <?php echo get_selected($row['mng_department'], $row_building['mng_department']); ?>><?php echo $row_building['md_name'];?></option>
                            <?php }?>
                        </select>
                        <script>
                            function department_change(){
                                var departmentSelect = document.getElementById("mng_department");
                                var departmentValue = departmentSelect.options[departmentSelect.selectedIndex].value;

                                var buildingId = $("#building_id").val();

                                console.log('departmentValue', departmentValue);

                                $.ajax({

                                url : "./building_mng_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "departmentValue":departmentValue, "building_id":buildingId}, //파라미터 값
                                success: function(msg){ //성공시 이벤트

                                    console.log(msg);
                                    $("#mng_id").html(msg);
                                }

                                });
                            }
                        </script>
                    </td>
                    <th>담당자</th>
                    <td>
                        <?php
                        $sql_where = " WHERE mng_b.building_id = '{$row['building_id']}' and mng.mng_department = '{$row['mng_department']}' and mng_b.is_del = 0 ";
                        $sql_where2 = "";
                        
                        $sql_building = "SELECT mng_b.*, mng.mng_name, mng.mng_department, depart.md_name, mng_grade.mg_name FROM
                                         a_mng_building as mng_b
                                         LEFT JOIN a_mng as mng on mng_b.mb_id = mng.mng_id
                                         LEFT JOIN a_mng_department as depart on mng.mng_department = depart.md_idx
                                         LEFT JOIN a_mng_grade as mng_grade on mng.mng_grades = mng_grade.mg_idx
                                         {$sql_where} {$sql_where2} ";
                        $res_building = sql_query($sql_building);

                        // echo $sql_building.'<br>';
                        ?>
                        
                        <select name="mng_id" id="mng_id" class="bansang_sel" <?php echo $is_process_chk ? 'readonly' : '';?>>
                            <option value="">선택</option>
                            <?php if($row['mng_id'] == '-1'){?>
                            <option value="-1" <?php echo $row['mng_id'] == '-1' ? 'selected' : ''; ?>>전체</option>
                            <?php }?>
                            <?php
                            while($row_building = sql_fetch_array($res_building)){
                            ?>
                            <option value="<?php echo $row_building['mb_id']?>" <?php echo get_selected($row['mng_id'], $row_building['mb_id']); ?>><?php echo $row_building['mng_name'].' '.$row_building['mg_name'];?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <tr class="tr_show">
                    <th>날짜</th>
                    <td>
                        <div class="ipt_date_boxs_wrap">
                            <div class="ipt_date_boxs">
                                <!-- readonly -->
                                <input type="hidden" name="cal_date" value="<?php echo $row['cal_date']; ?>">
                                <input type="text" name="cal_date2" class="bansang_ipt <?php echo $is_process_chk || $row['noti_repeat'] != 'N' ? 'ipt_date_not' : 'ver2 ipt_date';?> ipt_date_cal" value="<?php echo $cal_date_def; ?>"  required <?php echo $is_process_chk || $row['noti_repeat'] != 'N' ? 'readonly' : '';?>>
                                <!-- <button type="button" onclick="date_del('ipt_date_cal', 'date_del_btn1')" class="date_del_btn date_del_btn1 <?php echo $row['cal_date'] != '' ? '' : 'date_del_btn_hd'; ?>">
                                    <span></span>
                                    <span></span>
                                </button> -->
                            </div>
                        </div>
                        <!-- <script>
                            // date input 삭제
                            function date_del(ele, btnele){
                                $("." + ele).val("");
                                $("." + btnele).hide();
                            }

                        </script> -->
                    </td>
                    <th>반복 설정</th>
                    <td>
                        <?php if($is_process_chk || $row['noti_repeat'] != 'N'){
                            
                            switch($row['noti_repeat']){
                                case "N":
                                    $noti_repeat_s = "안함";
                                break;
                                case "MONTH":
                                    $noti_repeat_s = "월간";
                                break;
                                case "YEAR":
                                    $noti_repeat_s = "연간";
                                break;
                            }    
                            echo $noti_repeat_s;
                        ?>
                        <input type="hidden" name="noti_repeat" value="<?php echo $row['noti_repeat']; ?>">
                        <?php }else{ ?>
                            <div class="radio_chk_wrap">
                                <div class="radio_chk_box">
                                    <input type="radio" name="noti_repeat" id="noti_repeat1" value="N" <?php echo $row['noti_repeat'] == "N" ? "checked" : "checked";?>>
                                    <label for="noti_repeat1">안함</label>
                                </div>
                                <div class="radio_chk_box">
                                    <input type="radio" name="noti_repeat" id="noti_repeat2" value="MONTH" <?php echo $row['noti_repeat'] == "MONTH" ? "checked" : "";?>>
                                    <label for="noti_repeat2">월간</label>
                                </div>
                                <div class="radio_chk_box">
                                    <input type="radio" name="noti_repeat" id="noti_repeat3" value="YEAR" <?php echo $row['noti_repeat'] == "YEAR" ? "checked" : "";?>>
                                    <label for="noti_repeat3">연간</label>
                                </div>
                            </div>
                        <?php }?>
                    </td>
                </tr>
               <tr class="tr_show">
                  <th>제목</th>
                  <td colspan="3">
                    <input type="text" name="cal_title" id="cal_title" class="bansang_ipt <?php echo $is_process_chk ? '' : 'ver2';?>" size="100" value="<?php echo $row['cal_title']; ?>" required <?php echo $is_process_chk ? 'readonly' : '';?>>
                  </td>
               </tr>
               <tr class="tr_show">
                    <th>내용</th>
                    <td colspan="3">
                        <textarea name="cal_content" id="cal_content" class="bansang_ipt ver2 full ta"><?php echo $row['cal_content']; ?></textarea>
                    </td>
               </tr>
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <?php
        $toYear = date("Y", strtotime($cal_date_def));
        $toMonth = date("m", strtotime($cal_date_def));
        ?>
        <a href="./calendar_list.php?cal_code=<?=$cal_code; ?>&toYear=<?php echo $toYear;?>&toMonth=<?php echo $toMonth;?>&<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <!-- 수정상태, 최고관리자, 작성자, 담당자 일때 삭제처리 -->
        <?php if($w == 'u' && ($member['mb_level'] == 10 || $row['mng_id'] == $member['mb_id'] || $row['wid'] == $member['mb_id'])){?>
        <?php }?>
        <?php if($w == 'u'){?>
        <button class="btn btn_01" type="button" onclick="calendarDelPopOpen();">삭제</button>
        <?php }?>
        <input type="submit" value="<?php echo $w == 'u' ? '수정' : '저장';?>" class="btn_submit btn btn_02" accesskey='s'>
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

<?php if($w == 'u' && $row['noti_repeat'] != 'N'){ ?>
<div id="calendar_del_pop" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;background:rgba(0,0,0,0.5);">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:10px;padding:25px 20px;min-width:320px;text-align:center;">
        <p style="font-size:15px;font-weight:600;margin-bottom:15px;">삭제 방식을 선택해주세요.</p>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <button type="button" class="btn btn_01" style="width:100%;" onclick="calendar_del('this_only');">이 날짜 일정만 삭제</button>
            <button type="button" class="btn btn_01" style="width:100%;" onclick="calendar_del('after_this');">이 날짜 이후 반복 일정 전체 삭제</button>
            <button type="button" class="btn btn_01" style="width:100%;" onclick="calendar_del('all');">반복 일정 전체 삭제</button>
            <button type="button" class="btn btn_02" style="width:100%;" onclick="$('#calendar_del_pop').hide();">취소</button>
        </div>
    </div>
</div>
<?php } ?>
<script>
function buildingInfoPopOpen(){
    $("#building_info_pop").show();
    bodyLock();
}

function buildingInfoPopClose(){
    $("#building_info_pop").hide();
    bodyUnlock();
}


$(function(){
    //maxDate: "+365d", minDate:"0d",
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(dateText, inst) {
        console.log("선택된 날짜: ", inst); // 선택된 날짜를 콘솔에 출력
        // 다른 처리 로직도 추가 가능
        $(this).siblings(".date_del_btn").show();
    } });
});


function calendar_process(){
    if (!confirm("해당 일정을 처리완료 하시겠습니까?")) {
        return false;
    }

    let cal_idx = "<?php echo $cal_idx; ?>";
    let cal_date = "<?php echo $cal_date_def; ?>";
    let mb_id = "<?php echo $member['mb_id']; ?>";

    var formData = new FormData();
    formData.append('cal_idx', cal_idx);
    formData.append('cal_date', cal_date);
    formData.append('mb_id', mb_id);

    $.ajax({
        type: "POST",
        url: "./calendar_process2.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                alert(data.msg);

                setTimeout(() => {
                    window.location.reload();
                }, 100);
                //$(".btn_submit").attr('disabled', false);
                return false;
            }else{
                alert(data.msg);

                setTimeout(() => {
                    window.location.reload();
                }, 100);
            }
        },
        error:function(e){
            alert(e);
        }
    });
}


//삭제 팝업 열기
function calendarDelPopOpen(){
    let noti_chk = "<?php echo $row['noti_repeat']; ?>";

    // 반복일정이 아니면 바로 삭제 confirm
    if(noti_chk == "N"){
        if(confirm("해당 일정을 정말 삭제하시겠습니까?")){
            calendar_del('all');
        }
        return;
    }

    // 반복일정이면 커스텀 팝업
    $("#calendar_del_pop").show();
}

//일정 삭제
function calendar_del(del_mode){
    $("#calendar_del_pop").hide();

    let cal_idx = "<?php echo $cal_idx; ?>";
    let cal_date = "<?php echo $cal_date_def; ?>";
    let cal_code = "<?php echo $cal_code; ?>";

    var formData = new FormData();
    formData.append('cal_idx', cal_idx);
    formData.append('cal_date', cal_date);
    formData.append('del_mode', del_mode);

    $.ajax({
        type: "POST",
        url: "./calendar_del_update2.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) {
                alert(data.msg);
                return false;
            }else{
                alert(data.msg);

                setTimeout(() => {
                    window.location.replace("./calendar_list.php?cal_code=" + cal_code);
                }, 700);
            }
        },
        error:function(e){
            alert(e);
        }
    });
}

function fcalendar_submit(f) {

    let notiRepeat = "<?php echo $row['noti_repeat'];?>";
    let calEdate = "<?php echo $row['cal_edate'];?>";
    let cal_date2 = "<?php echo $cal_date_def; ?>";

    if(f.w.value == 'u' && notiRepeat != "N" && calEdate == ''){
        if(!confirm("현재 일정 수정 시 " + cal_date2 + " 날짜 이후의 반복 일정이 모두 변경됩니다.\n계속 진행하시겠습니까?")){
            return false;
        }
    }

    buildingInfoPopOpen();

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

