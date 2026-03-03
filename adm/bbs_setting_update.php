<?php
$sub_menu = "920920";
require_once './_common.php';

check_demo();

// if (!(isset($_POST['chk']) && is_array($_POST['chk']))) {
//     alert($_POST['act_button'] . " 하실 항목을 하나 이상 체크하세요.");
// }

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$mb_datas = array();
$msg = '게시판 설정이 수정되었습니다.';

$today = date("Y-m-d H:i:s");

if ($_POST['act_button'] == "수정") {

    //print_r2($_POST);
    for($i=0;$i<count($bbs_id);$i++){

        //변경되었는지 확인
        $confirm_row = sql_fetch("SELECT bbs_title FROM a_bbs_setting WHERE bbs_id = '{$bbs_id[$i]}'");
        
       
        $bbs_setting_update = "UPDATE a_bbs_setting SET
                            bbs_title = '{$bbs_title[$i]}',
                            is_view = '{$is_view[$i]}',
                            updated_at = '{$today}'
                            WHERE bbs_id = '{$bbs_id[$i]}'";
        // echo $bbs_setting_update.'<br>';
        sql_query($bbs_setting_update);
    
        
    }
}

// exit;

if ($msg) {
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);
}

goto_url('./bbs_setting.php');
