<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];



if($w == "u"){

    $confirm_hp = "SELECT COUNT(*) as cnt FROM g5_member WHERE mb_hp = '{$mng_hp}' and mb_id != '{$mng_id}' ";
    $confirm_hp_row = sql_fetch($confirm_hp);

    if($confirm_hp_row['cnt'] > 0) alert("이미 등록된 휴대폰 번호입니다.");

    $update_query = "UPDATE a_mng SET
                        mng_name = '{$mng_name}',
                        mng_hp = '{$mng_hp}',
                        mng_department = '{$mng_department}',
                        mng_grades = '{$mng_grades}',
                        mng_certi = '{$mng_certi}',
                        mng_status = '{$mng_status}',
                        joined_at = '{$joined_at}'
                        WHERE mng_id = '{$mng_id}'";
    
    //echo $update_query.'<br>';
    sql_query($update_query);

    $sql_pwd = "";
    if($mng_password != ""){
        $pws = get_encrypt_string($mng_password);

        $sql_pwd = " mb_password = '{$pws}', ";
    }

    $update_member = "UPDATE g5_member SET
                        {$sql_pwd}
                        mb_name = '{$mng_name}',
                        mb_nick = '{$mng_name}',
                        mb_hp = '{$mng_hp}'
                        WHERE mb_id = '{$mng_id}'";

    sql_query($update_member);


    //단지 배정
    if($select_af_chk != ""){
        $select_af_chk_arr = explode(",", $select_af_chk);

        for($i=0;$i<count($select_af_chk_arr);$i++){

            // $update_building = "UPDATE a_building SET
            //                         mng_id = '{$mng_id}'
            //                         WHERE building_id = '{$select_af_chk_arr[$i]}'";
            //echo $update_building.'<br>';
            $building_mng_confirm = "SELECT COUNT(*) as cnt FROM a_mng_building WHERE mb_id = '{$mng_id}' and building_id = '{$select_af_chk_arr[$i]}'";

            //echo $building_mng_confirm.'<br>';
            $building_mng_confirm_row = sql_fetch($building_mng_confirm);

            if($building_mng_confirm_row['cnt'] == 0){
                $insert_building_mng = "INSERT INTO a_mng_building SET
                                        building_id = '{$select_af_chk_arr[$i]}',
                                        mb_id = '{$mng_id}',
                                        created_at = '{$today}'";

                //echo '배정완료::'.$insert_building_mng.'<br>';
                sql_query($insert_building_mng);
            }
        }
    }

    

    //단지 배정 취소
    if($select_bf_chk != ""){
        $select_bf_chk_arr = explode(",", $select_bf_chk);

        for($i=0;$i<count($select_bf_chk_arr);$i++){

            $building_mng_confirm = "SELECT COUNT(*) as cnt FROM a_mng_building WHERE mb_id = '{$mng_id}' and building_id = '{$select_bf_chk_arr[$i]}'";
            $building_mng_confirm_row = sql_fetch($building_mng_confirm);

            if($building_mng_confirm_row['cnt'] > 0){
                $update_building_mng = "DELETE FROM a_mng_building
                                        WHERE mb_id = '{$mng_id}' and building_id = '{$select_bf_chk_arr[$i]}'";
                //echo '배정취소::'.$update_building_mng.'<br>';

                 sql_query($update_building_mng);
            }
        }
    }


    $mng_infos = get_manger($mng_id);

    //퇴사상태로 변경되었다면 단지 배정 전부 취소
    if($mng_infos['mng_status'] == 1 && $mng_status == 2){
        $update_building_mng = "UPDATE a_mng_building SET 
                                is_del = 1,
                                deleted_at = '{$today}'
                                WHERE mb_id = '{$mng_id}'";
        sql_query($update_building_mng);
    }

    //exit;

}else{

    $confirm_id = "SELECT COUNT(*) as cnt FROM g5_member WHERE mb_id = '{$mng_id}' ";
    $confirm_row = sql_fetch($confirm_id);

    if($confirm_row['cnt'] > 0) alert("이미 등록된 담당자 아이디입니다.");

    $confirm_hp = "SELECT COUNT(*) as cnt FROM g5_member WHERE mb_hp = '{$mng_hp}' ";
    $confirm_hp_row = sql_fetch($confirm_hp);

    if($confirm_hp_row['cnt'] > 0) alert("이미 등록된 휴대폰 번호입니다.");
    

    $insert_query = "INSERT INTO a_mng SET
                        mng_id = '{$mng_id}',
                        mng_name = '{$mng_name}',
                        mng_hp = '{$mng_hp}',
                        mng_department = '{$mng_department}',
                        mng_grades = '{$mng_grades}',
                        mng_certi = '{$mng_certi}',
                        mng_status = '{$mng_status}',
                        joined_at = '{$joined_at}',
                        created_at = '{$today}',
                        mng_ip = '{$ip_info}'";

    //echo $insert_query.'<br>';
    sql_query($insert_query);
    //$complain_idx = sql_insert_id(); //팝업 idx

    $pws = get_encrypt_string($mng_password);

    $insert_member = "INSERT INTO g5_member SET
                        mb_id = '{$mng_id}',
                        mb_password = '{$pws}',
                        mb_name = '{$mng_name}',
                        mb_nick = '{$mng_name}',
                        mb_level = 9,
                        mb_hp = '{$mng_hp}',
                        mb_datetime = '{$today}'";

    //echo $insert_member.'<br>';
    sql_query($insert_member);
}

//exit;

if($w == 'u'){
    alert('담당자 정보가 수정되었습니다.');
}else{
    alert('담당자가 등록되었습니다.', './sm_manager_form.php?' . $qstr . '&amp;w=u&amp;mng_id=' . $mng_id);
}
?>