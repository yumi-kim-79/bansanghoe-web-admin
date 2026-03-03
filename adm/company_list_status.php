<?php
$sub_menu = "810100";
require_once './_common.php';

if (!(isset($_POST['chk']) && is_array($_POST['chk']))) {
    alert($_POST['act_button'] . " 하실 항목을 하나 이상 체크하세요.");
}

//print_r2($_POST);

$msg = '선택하신 업체가 거래 중지되었습니다.';

if ($_POST['act_button'] == "거래중지") {
    for ($i = 0; $i < count($_POST['chk']); $i++) {

        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

        $update_query = "UPDATE a_manage_company SET
                            transaction_status = 'N'
                            WHERE company_idx = '{$k}'";
        //echo $update_query.'<br>';

        sql_query($update_query);
    }
}

//exit;

if ($msg) {
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);
}

goto_url('./company_list.php?' . $qstr);