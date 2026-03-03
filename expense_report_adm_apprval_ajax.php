<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($signdata == "") die(result_data(false, "서명을 입력해주세요.", []));

$singature_row = sql_fetch("SELECT COUNT(*) as cnt FROM a_signature WHERE mb_id = '{$mb_id}' and signature_data = '{$signdata}'");


if($singature_row['cnt'] == 0){
    
    //서명 이미지 저장
    if($signdata != ""){
        $encoded_image = explode(",", $signdata);
        $decoded_image = base64_decode($encoded_image[1]);

        $file_name = md5(uniqid(rand(), TRUE)).".png";
        $file_name = preg_replace("/\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $file_name);

        $file_path = G5_DATA_PATH.'/file/approval_expense';

        @mkdir($file_path, G5_DIR_PERMISSION);
        @chmod($file_path, G5_DIR_PERMISSION);

        $file_name2 = $file_name;

        $tgt = $file_path.'/'.$file_name2;

        file_put_contents($tgt, $decoded_image);

        //서명저장
        $signature_insert = "INSERT INTO a_signature SET
                            mb_id = '{$mb_id}',
                            signature_data = '{$signdata}',
                            fil_name = '{$file_name2}',
                            created_at = '{$today}'";
        sql_query($signature_insert);
        $sg_idx = sql_insert_id(); //저장된 서명 idx

        $img_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_expense_report_sign WHERE approval_id = '{$mb_id}' and apprval_type = '{$data}' and ex_id = '{$ex_id}'");

        //사인 이미지 경로 및 데이터 저장
        if($img_confirm['cnt'] > 0){

            $update_img = "UPDATE a_expense_report_sign SET
                    sg_idx = '{$sg_idx}'
                    WHERE ex_id = '{$ex_id}' and apprval_type = '{$data}'";
            sql_query($update_img);

        }else{
            //사인 히스토리
            $insert_img = "INSERT INTO a_expense_report_sign SET
                            sg_idx = '{$sg_idx}',
                            ex_id = '{$ex_id}',
                            approval_id = '{$mb_id}',
                            apprval_type = '{$data}',
                            sign_img = '{$file_name2}',
                            created_at = '{$today}'";
            sql_query($insert_img);
        }

    }
}else{

    $singature_row2 = sql_fetch("SELECT * FROM a_signature WHERE mb_id = '{$mb_id}' and signature_data = '{$signdata}'");

    $insert_img = "INSERT INTO a_expense_report_sign SET
                    sg_idx = '{$singature_row2['sg_idx']}',
                    ex_id = '{$ex_id}',
                    approval_id = '{$mb_id}',
                    apprval_type = '{$data}',
                    sign_img = '{$singature_row2['fil_name']}',
                    created_at = '{$today}'";
    sql_query($insert_img);

}

//결재완료상태로 변경

//결재완료하려는 결재자 변수
$sign_chk =  "ex_".$data."_chk";

$update_ex = "UPDATE a_expense_report SET
                $sign_chk = '1'
                WHERE ex_id = '{$ex_id}'";
sql_query($update_ex);



//결재 후 결재상태 체크용
$ex_sql = "SELECT * FROM a_expense_report WHERE ex_id = '{$ex_id}'";
$ex_row = sql_fetch($ex_sql);

$sum_sign = $ex_row['ex_apprval1_chk'] + $ex_row['ex_apprval2_chk'] + $ex_row['ex_apprval3_chk'];

if ($sum_sign === 0) {
    $status = 'N';
} elseif ($sum_sign === 3) {
    $status = 'E';

    $ex_wid_info = get_member($ex_row['wid']);

    //완료시 작성자에게 푸시발송
    $push_title = "[품의서] 품의서 결재완료";
    $push_content = "작성하신 품의서 결재가 완료되었습니다.";

     //푸시발송
     $insert_push = "INSERT INTO a_push SET
                    recv_id_type = 'sm',
                    recv_id = '{$ex_row['wid']}',
                    push_title = '{$push_title}',
                    push_content = '{$push_content}',
                    wid = '{$ex_row['wid']}',
                    push_type = 'expense',
                    push_idx = '{$ex_id}',
                    created_at = '{$today}'";
    sql_query($insert_push);
    
    if($ex_wid_info['mb_token'] != "" && $ex_wid_info['noti5']){ //토큰이 있는경우 푸시 발송
        fcm_send($ex_wid_info['mb_token'], $push_title, $push_content, 'expense', "{$ex_id}", "/expense_report_info.php?types=sm&ex_id=");
    }


    //단지 세대원에게 푸시발송
    if($ex_row['dong_id'] != "-1"){
        $sq_where = " and dong_id = '{$ex_row['dong_id']}' ";
    }else{
        $sq_where = " and building_id = '{$ex_row['building_id']}' ";
    }
    $sql_ho = "SELECT * FROM a_building_ho WHERE ho_status = 'Y' {$sq_where} ";
    $res_ho = sql_query($sql_ho);

    while($ho_row = sql_fetch_array($res_ho)){
        $tenant_info = get_user($ho_row['ho_tenant_id']);

        $push_title2 = "[품의서] 품의서 등록";
        $push_content2 = "품의서가 등록되었습니다.";

        if($tenant_info['mb_token'] != "" && $tenant_info['noti6']){ //토큰이 있는경우 푸시 발송
            fcm_send($tenant_info['mb_token'], $push_title2, $push_content2, 'expense', "{$ex_id}", "/expense_report_info.php?ex_id=");
        }

        //푸시발송
        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'user',
                        recv_id = '{$tenant_info['mb_id']}',
                        push_title = '{$push_title2}',
                        push_content = '{$push_content2}',
                        wid = '{$ex_row['wid']}',
                        push_type = 'expense',
                        push_idx = '{$ex_id}',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }

} else {
    $status = 'P';

    //1차 결재자 결재 완료시 2차결재자에게 푸시발송
    if($data == "apprval1"){
        $ex_approver2_info = get_user($ex_row['ex_approver2']);

        $ex_department_name = get_department_name($ex_row['ex_department']);  //부서정보
       
        $push_title = "[품의서] 품의서 결재요청";
        $push_content = "제목 : {$ex_row['ex_title']}\n작성자 : {$ex_row['ex_name']}\n부서 : {$ex_department_name}\n직급 : {$ex_row['ex_grade']}\n\n결재를 진행해주세요.";

         //푸시발송
         $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'user',
                        recv_id = '{$ex_row['ex_approver2']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$ex_row['wid']}',
                        push_type = 'expense',
                        push_idx = '{$ex_id}',
                        created_at = '{$today}'";
        sql_query($insert_push);

        if($ex_approver2_info['mb_token'] != "" && $ex_approver2_info['noti6'] ){ //토큰이 있는경우 푸시 발송
            fcm_send($ex_approver2_info['mb_token'], $push_title, $push_content, 'expense', "{$ex_id}", "/expense_report_adm_info.php?ex_id=");
        }
    }

    //2차 결재자 결재 완료시 3차결재자에게 푸시발송
    if($data == "apprval2"){
        $ex_approver3_info = get_user($ex_row['ex_approver3']);

        $ex_department_name = get_department_name($ex_row['ex_department']);  //부서정보
       
        $push_title = "[품의서] 품의서 결재요청";
        $push_content = "제목 : {$ex_row['ex_title']}\n작성자 : {$ex_row['ex_name']}\n부서 : {$ex_department_name}\n직급 : {$ex_row['ex_grade']}\n\n결재를 진행해주세요.";

         //푸시발송
         $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'user',
                        recv_id = '{$ex_row['ex_approver3']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$ex_row['wid']}',
                        push_type = 'expense',
                        push_idx = '{$ex_id}',
                        created_at = '{$today}'";
        sql_query($insert_push);

        if($ex_approver3_info['mb_token'] != "" && $ex_approver3_info['noti6']){ //토큰이 있는경우 푸시 발송
            fcm_send($ex_approver3_info['mb_token'], $push_title, $push_content, 'expense', "{$ex_id}", "/expense_report_adm_info.php?ex_id=");
        }
    }
}

//상태 값 변경
$update_ex_status = "UPDATE a_expense_report SET
                        ex_status = '{$status}'
                        WHERE ex_id = '{$ex_id}'";
sql_query($update_ex_status);

echo result_data(true, "서명이 완료되었습니다.", []);