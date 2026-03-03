<?php
$sub_menu = "300100";
require_once './_common.php';

check_demo();

if (!(isset($_POST['chk']) && is_array($_POST['chk']))) {
    alert($_POST['act_button'] . " 처리 하실 항목을 하나 이상 체크하세요.");
}

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$mb_datas = array();
$msg = '퇴실 처리가 완료되었습니다.';

$today = date("Y-m-d H:i:s");

if ($_POST['act_button'] == "퇴실") {
    for ($i = 0; $i < count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

        $ho_info = sql_fetch("SELECT * FROM a_building_ho WHERE ho_id = '{$k}' and is_del = 0");
        

        $ho_status_t = "OUT";

        //해당 호수의 세대구성원 삭제
        $update_household = "UPDATE a_building_household SET
                            is_del = 1,
                            deleted_at = '{$today}'
                            WHERE ho_id = '{$k}'";
        // echo $update_household.'<br>';
        sql_query($update_household);

        //해당 호수의 차량정보 삭제
        $update_car = "UPDATE a_building_car SET
                        is_del = 1,
                        deleted_at = '{$today}'
                        WHERE ho_id = '{$k}'";
        // echo $update_car.'<br>';
        sql_query($update_car);


        //퇴실 내역 추가
        $history_tenant_date = date("Y-m-d");

        $insert_history = "INSERT INTO a_building_household_history SET
                        ho_id = '{$k}',
                        history_id = '{$ho_info['ho_tenant_id']}',
                        history_name = '{$ho_info['ho_tenant']}',
                        history_hp = '{$ho_info['ho_tenant_hp']}',
                        history_status = '{$ho_status_t}',
                        history_tenant_date = '{$history_tenant_date}',
                        created_at = '{$today}'";

        // echo $insert_history.'<br>';
        sql_query($insert_history);


        //호수에서 입주자 비우기
        $delete_ho = "UPDATE a_building_ho SET
                            ho_tenant_id = '',
                            ho_tenant = '',
                            ho_tenant_hp = '',
                            ho_tenant_at = '',
                            ho_status = 'N'
                            WHERE ho_id = '{$k}'";
        // echo $delete_ho.'<br>';
        sql_query($delete_ho);


        $mb_chk = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho WHERE ho_tenant_id = '{$ho_info['ho_tenant_id']}' and ho_status = 'Y'");

        if($mb_chk['cnt'] == 0){

            //퇴실한 사람 회원 삭제처리 자동로그인 및 토큰도 삭제
            $delete_member = "UPDATE a_member SET 
                                mb_auto = 0,
                                mb_token = '',
                                is_del = 1,
                                deleted_at = '{$today}'
                                WHERE mb_id = '{$ho_info['ho_tenant_id']}'";
            sql_query($delete_member);
        }

        // echo "SELECT * FROM a_building_ho WHERE ho_id = '{$k}' and is_del = 0".'<br>';
        //exit;
        // sql_query($del_update);
    }
}


if ($msg) {
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);
}

goto_url('./house_hold_list.php?' . $qstr);
