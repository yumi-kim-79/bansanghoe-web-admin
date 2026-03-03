<?php
$sub_menu = "930910";
require_once './_common.php';

check_demo();

// if (!(isset($_POST['chk']) && is_array($_POST['chk']))) {
//     alert($_POST['act_button'] . " 하실 항목을 하나 이상 체크하세요.");
// }

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$mb_datas = array();
$msg = '캘린더 설정이 수정되었습니다.';

$today = date("Y-m-d H:i:s");

if ($_POST['act_button'] == "수정") {

    //print_r2($_POST);
    for($i=0;$i<count($cal_id);$i++){

        //변경되었는지 확인
        $confirm_row = sql_fetch("SELECT cal_name FROM a_calendar_setting WHERE cal_id = '{$cal_id[$i]}'");
        
        // if($confirm_row['cal_name'] != $cal_name[$i]){
        // }
        $calendar_setting_update = "UPDATE a_calendar_setting SET
                            cal_name = '{$cal_name[$i]}',
                            is_view = '{$is_view[$i]}',
                            updated_at = '{$today}'
                            WHERE cal_id = '{$cal_id[$i]}'";
        // echo $calendar_setting_update.'<br>';
        sql_query($calendar_setting_update);
     
        
    }
}

if ($msg) {
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);
}

goto_url('./calendar_setting.php');
