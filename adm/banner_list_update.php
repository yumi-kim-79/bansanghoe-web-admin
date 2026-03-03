<?php
$sub_menu = "100100";
require_once './_common.php';

check_demo();

if (!(isset($_POST['chk']) && is_array($_POST['chk']))) {
    alert($_POST['act_button'] . " 하실 항목을 하나 이상 체크하세요.");
}

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$mb_datas = array();
$msg = '배너가 삭제되었습니다.';

$today = date("Y-m-d H:i:s");

if ($_POST['act_button'] == "선택삭제") {
    for ($i = 0; $i < count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

        $del_update = "UPDATE a_banner SET
                            is_del = 1,
                            deleted_at = '{$today}'
                            WHERE banner_id = {$k}";
        //echo $del_update.'<br>';
        //exit;
        sql_query($del_update);
    }
}

//exit;

if ($msg) {
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);
}

goto_url('./banner_list.php?' . $qstr);
