<?php
require_once "./_common.php";

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // die(result_data(false, "", $_POST));
}

$today = date("Y-m-d H:i:s");
$tenant_at = date("Y-m-d");
$ip_info = $_SERVER['REMOTE_ADDR'];
$building_info = get_builiding_info($building_id); //단지정보

$member_sql = "";

//휴대폰번호가 변경되었다면
if($now_hp != $mb_hp){
    if($regist_certi == "") die(result_data(false, "휴대폰 번호 인증을 완료해주세요.", "certi_number"));
    $member_sql .= " ,
                    mb_hp = '{$mb_hp}' ";
}

// 비밀번호 변경시 유효성검증
if($mb_password != ""){
    if(mb_strlen($mb_password) < 6) die(result_data(false, "비밀번호는 영문 + 숫자의 조합으로 6자리 이상, 16자리 미만으로 입력해주세요.", "mb_password"));
    if($mb_password_re == "") die(result_data(false, "비밀번호를 한번 더 입력해주세요.", "mb_password_re"));
    if(mb_strlen($mb_password_re) < 6) die(result_data(false, "비밀번호는 영문 + 숫자의 조합으로 6자리 이상, 16자리 미만으로 입력해주세요.", "mb_password_re"));
    if($mb_password_re != $mb_password) die(result_data(false, "비밀번호를 동일하게 입력해주세요.", "mb_password"));
    if(!validatePassword($mb_password_re)) die(result_data(false, "비밀번호는 영문 + 숫자의 조합으로 6자리 이상, 16자리 미만으로 입력해주세요.", "mb_password_re"));

    $pws = get_encrypt_string($mb_password);

    $member_sql .= " ,
                    mb_password = '{$pws}' ";
 
}

if($types == "sm"){

    $update_member = "UPDATE g5_member SET
                        mb_ip = '{$_SERVER['REMOTE_ADDR']}'
                        {$member_sql}
                        WHERE mb_id = '{$mb_id}' ";
    sql_query($update_member);

}else{

    $update_member = "UPDATE a_member SET
                        mb_ip = '{$_SERVER['REMOTE_ADDR']}'
                        {$member_sql}
                        WHERE mb_id = '{$mb_id}' ";
    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // die(result_data(false, $update_member, []));
    }

    sql_query($update_member);

    //차량정보 변경
    if(count($car_type) > 0){

        for($i=0;$i<count($car_type);$i++){

            if($car_id[$i] == ''){

                if($car_type[$i] != '' && $car_name[$i] != ''){
                    $ho_info = sql_fetch("SELECT * FROM a_building_ho WHERE ho_tenant_hp = '{$mb_hp}' and is_del = 0 ORDER BY ho_id desc");

                    $building_mng = "SELECT mng_building.*, mb.mb_token FROM a_mng_building as mng_building LEFT JOIN g5_member as mb ON mng_building.mb_id = mb.mb_id WHERE mng_building.building_id = '{$building_id}'";
                    $buidling_mng_res = sql_query($building_mng);

                    $push_title = "[차량등록] 차량이 등록되었습니다.";
                    $push_content = $mb_name."님이 ". $building_info['building_name']." 단지에 차량을 등록하였습니다.";

                    while($buidling_mng_row = sql_fetch_array($buidling_mng_res)){
                        $insert_push = "INSERT INTO a_push SET
                                recv_id_type = 'sm',
                                recv_id = '{$buidling_mng_row['mb_id']}',
                                push_title = '{$push_title}',
                                push_content = '{$push_content}',
                                wid = '{$mb_id}',
                                push_type = 'car',
                                push_idx = '{$ho_id}',
                                is_send = 0,
                                created_at = '{$today}'";
                        sql_query($insert_push);

                        if($buidling_mng_row['mb_token'] != ""){ //토큰이 있는경우 푸시 발송
                            // fcm_send($buidling_mng_row['mb_token'], $push_title, $push_content, 'car', "{$building_id}", "/sm_car_manage.php?building_id=");
                        }
                    }

                    $update_car = "INSERT INTO a_building_car SET
                                    building_id = '{$building_id}',
                                    dong_id = '{$dong_id}',
                                    ho_id = '{$ho_id}',
                                    mb_id = '{$mb_id}',
                                    car_type = '{$car_type[$i]}',
                                    car_name = '{$car_name[$i]}',
                                    ip_info = '{$ip_info}',
                                    created_at = '{$today}'";
    
                    sql_query($update_car);
                }
               
            }else{

                $sql_car = "SELECT car_type, car_name FROM a_building_car WHERE car_id = '{$car_id[$i]}' ";
                $row_car = sql_fetch($sql_car);

                if($row_car['car_type'] != $car_type[$i] || $row_car['car_name'] != $car_name[$i]){

                    if($car_type[$i] == '' && $car_name[$i] == ''){
                        $push_title = "[차량삭제] 차량이 삭제되었습니다.";
                        $push_content = $mb_name."님이 ". $building_info['building_name']." 단지에 차량정보를 삭제하였습니다.";
                    }else{
                        $push_title = "[차량변경] 차량정보가 변경되었습니다.";
                        $push_content = $mb_name."님이 ". $building_info['building_name']." 단지에 차량정보를 변경하였습니다.";
                    }

                    $add = '';
                    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                        // die(result_data(false, $building_mng, []));
                        $add = " and mng_building.mb_id = 'thdaudwns' "; //관리자용 
                    }

                    $building_mng = "SELECT mng_building.*, mb.mb_token FROM a_mng_building as mng_building LEFT JOIN g5_member as mb ON mng_building.mb_id = mb.mb_id WHERE mng_building.building_id = '{$building_id}' {$add}";
                    $buidling_mng_res = sql_query($building_mng);

                    while($buidling_mng_row = sql_fetch_array($buidling_mng_res)){
                        $insert_push = "INSERT INTO a_push SET
                                recv_id_type = 'sm',
                                recv_id = '{$buidling_mng_row['mb_id']}',
                                push_title = '{$push_title}',
                                push_content = '{$push_content}',
                                wid = '{$mb_id}',
                                push_type = 'car',
                                push_idx = '{$ho_id}',
                                is_send = 0,
                                created_at = '{$today}'";
                        sql_query($insert_push);
                    }
                }

                $update_car = "UPDATE a_building_car SET
                                car_type = '{$car_type[$i]}',
                                car_name = '{$car_name[$i]}'
                                WHERE car_id = '{$car_id[$i]}'";

                sql_query($update_car);
            }

        }
    
    }
}

echo result_data(true, "내 정보가 수정되었습니다.", []);
