<?php
require_once './_common.php';

$today = date("Y-m-d H:i:s");

//결재서류명
$approval_name = approval_category_name($sign_off_category);

// if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
//     print_r2($_POST);
//     exit;
// }

$page_arr = ["daily_paid", "onsite_expenses", "personal_signoff", "expenditure_plan", "builder_statement", "building_account", "mng_adjustment", "bill_payment", "household_refund"];

if($w == "u"){

    //수정시 정보
    $sign_info = sql_fetch("SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'");

    if($sign_off_category == "paid_holiday"){
        $sql_common = " sign_off_year = '{$sign_off_year}',
                        sign_off_month = '{$sign_off_month}',
                        sign_off_memo = '{$sign_off_memo}' ";
    }

    if($sign_off_category == "holiday"){
        $sql_common = " holiday_date = '{$holiday_date}', 
                        holiday_day = '{$holiday_day}',
                        holiday_memo = '{$holiday_memo}' ";
    }

    if($sign_off_category == "duty_report"){
        $sql_common = " duty_sdate = '{$duty_sdate}',
                        duty_edate = '{$duty_edate}', 
                        holiday_memo = '{$holiday_memo}',
                        significant_memo = '{$significant_memo}' ";
    }

    if($sign_off_category == "overtime_work_request" || $sign_off_category == "overtime_work_report"){
        $sql_common = " extension_date = '{$extension_date}',
                        extension_stime = '{$extension_stime}', 
                        extension_etime = '{$extension_etime}',
                        sign_off_memo = '{$sign_off_memo}' ";
    }

    $sql_chk = "";

    //2차 결재자 선택 안하면 결재체크상태로
    if($sign_off_mng_id2 == ""){
        $sql_chk .= " sign_off_status2 = '1', ";
    }else{
        $sql_chk .= " sign_off_status2 = '0', ";
    }

    //3차 결재자 선택 안하면 결재체크상태로
    if($sign_off_mng_id3 == ""){
        $sql_chk .= " sign_off_status3 = '1', ";
    }else{
        $sql_chk .= " sign_off_status3 = '0', ";
    }

    //1차 결재자가 변경되었다면..
    if($sign_info['sign_off_mng_id1'] != $sign_off_mng_id1){
        $sign_off_id_info = get_member($sign_off_mng_id1); //1차 결재자 정보

        $sign_off_id = $sign_off_id_info['mb_id']; //1차 결재자 아이디

        $push_title = '[결재요청] '.$approval_name." 결재 요청이 있습니다.";
        $push_content = $wname.'님의 '.$approval_name." 결재 요청이 있습니다.";
    

        if($sign_off_id_info['mb_token'] != "" && $sign_off_id_info['noti1']){ //토큰이 있는경우 푸시 발송
            if($_SERVER['REMOTE_ADDR'] != ADMIN_IP) fcm_send($sign_off_id_info['mb_token'], $push_title, $push_content, "sign_off", "{$sign_id}", "/holiday_reqeust_info.php?mng=Y&sign_id=");
        }

        $insert_push = "INSERT INTO a_push SET
                        recv_id = '{$sign_off_id}',
                        recv_id_type = 'sm',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$wid}',
                        push_type = 'sign_off',
                        push_idx = '{$sign_id}',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }


    $update_query = "UPDATE a_sign_off SET
                        sign_off_mng_id1 = '{$sign_off_mng_id1}',
                        sign_off_mng_id2 = '{$sign_off_mng_id2}',
                        sign_off_mng_id3 = '{$sign_off_mng_id3}',
                        {$sql_chk}
                        {$sql_common}
                        WHERE sign_id = '{$sign_id}'";

    //echo $update_query.'<br>';
    sql_query($update_query);

    if($sign_off_category == "paid_holiday"){
        if(count($hp_name) > 0){

            for($i=0;$i<count($hp_name);$i++){

                if($hp_idx[$i] != ""){

                    $del_sql = "";

                    if($holiday_del[$i]){
                        $del_sql = " ,
                                    is_del = 1,
                                    deleted_at = '{$today}' ";
                    }

                    $update_hp = "UPDATE a_holiday_person SET
                                    hp_name = '{$hp_name[$i]}',
                                    hp_day = '{$hp_day[$i]}',
                                    hp_date = '{$hp_date[$i]}',
                                    hp_memo = '{$hp_memo[$i]}'
                                    {$del_sql}
                                    WHERE hp_idx = '{$hp_idx[$i]}'";
                    sql_query($update_hp);

                }else{
                    $insert_hp = "INSERT INTO a_holiday_person SET
                                    sign_id = '{$sign_id}',
                                    hp_name = '{$hp_name[$i]}',
                                    hp_day = '{$hp_day[$i]}',
                                    hp_date = '{$hp_date[$i]}',
                                    hp_memo = '{$hp_memo[$i]}',
                                    created_at = '{$today}'";
                
                    //echo $insert_hp.'<br>';
                    sql_query($insert_hp);
                }
                
            }
        }
    }

}else{

    if($approval_signature == '') alert('서명을 해주세요.');

    if($sign_off_category == "paid_holiday"){
        $sql_common = " mng_department = '{$mng_department}',
                        mng_grade = '{$mng_grade}',
                        sign_off_year = '{$sign_off_year}',
                        sign_off_month = '{$sign_off_month}',
                        sign_off_memo = '{$sign_off_memo}', 
                        wdate = '{$wdate}', ";
    }

    if($sign_off_category == "holiday"){
        $sql_common = " mng_department = '{$mng_department}',
                        mng_grade = '{$mng_grade}',
                        holiday_date = '{$holiday_date}', 
                        holiday_day = '{$holiday_day}',
                        holiday_memo = '{$holiday_memo}',
                        significant_memo = '{$significant_memo}',
                        wdate = '{$wdate}', ";
    }

    if($sign_off_category == "duty_report"){
        $sql_common = " mng_department = '{$mng_department}',
                        mng_grade = '{$mng_grade}',
                        duty_sdate = '{$duty_sdate}',
                        duty_edate = '{$duty_edate}', 
                        holiday_memo = '{$holiday_memo}',
                        significant_memo = '{$significant_memo}',
                        wdate = '{$wdate}', ";
    }

    if($sign_off_category == "overtime_work_request" || $sign_off_category == "overtime_work_report"){
        $sql_common = " mng_department = '{$mng_department}',
                        mng_grade = '{$mng_grade}',
                        extension_date = '{$extension_date}',
                        extension_stime = '{$extension_stime}', 
                        extension_etime = '{$extension_etime}',
                        sign_off_memo = '{$sign_off_memo}',
                        wdate = '{$wdate}', ";
    }

    $sql_chk = "";

    //2차 결재자 선택 안하면 결재체크상태로
    if($sign_off_mng_id2 == ""){
        $sql_chk .= " sign_off_status2 = '1', ";
    }

    //3차 결재자 선택 안하면 결재체크상태로
    if($sign_off_mng_id3 == ""){
        $sql_chk .= " sign_off_status3 = '1', ";
    }

    $insert_query = "INSERT INTO a_sign_off SET
                        sign_off_category = '{$sign_off_category}',
                        mng_id = '{$wid}',
                        sign_off_mng_id1 = '{$sign_off_mng_id1}',
                        sign_off_mng_id2 = '{$sign_off_mng_id2}',
                        sign_off_mng_id3 = '{$sign_off_mng_id3}',
                        {$sql_common}
                        {$sql_chk}
                        created_at = '{$today}'";

    
    sql_query($insert_query);
    $sign_id = sql_insert_id(); //팝업 idx


    if($sign_off_category == "paid_holiday"){
        if(count($hp_name) > 0){

            for($i=0;$i<count($hp_name);$i++){

                if($hp_idx[$i] != ""){

                    $del_sql = "";

                    if($holiday_del[$i]){
                        $del_sql = " ,
                                    is_del = 1,
                                    deleted_at = '{$today}' ";
                    }

                    $update_hp = "UPDATE a_holiday_person SET
                                    hp_name = '{$hp_name[$i]}',
                                    hp_day = '{$hp_day[$i]}',
                                    hp_date = '{$hp_date[$i]}',
                                    hp_memo = '{$hp_memo[$i]}'
                                    {$del_sql}
                                    WHERE hp_idx = '{$hp_idx[$i]}'";
                    sql_query($update_hp);

                }else{
                    $insert_hp = "INSERT INTO a_holiday_person SET
                                    sign_id = '{$sign_id}',
                                    hp_name = '{$hp_name[$i]}',
                                    hp_day = '{$hp_day[$i]}',
                                    hp_date = '{$hp_date[$i]}',
                                    hp_memo = '{$hp_memo[$i]}',
                                    created_at = '{$today}'";
                
                    //echo $insert_hp.'<br>';
                    sql_query($insert_hp);
                }
                
            }
        }
    }

    //1차 결재권자 푸시발송
    if($sign_off_mng_id1 != ""){
        
        $sign_off_id_info = get_member($sign_off_mng_id1); //1차 결재자 정보

        $sign_off_id = $sign_off_id_info['mb_id']; //1차 결재자 아이디

        $push_title = '[결재요청] '.$approval_name." 결재 요청이 있습니다.";
        $push_content = $wname.'님의 '.$approval_name." 결재 요청이 있습니다.";
    

        if($sign_off_id_info['mb_token'] != "" && $sign_off_id_info['noti1']){ //토큰이 있는경우 푸시 발송
            if($_SERVER['REMOTE_ADDR'] != ADMIN_IP) fcm_send($sign_off_id_info['mb_token'], $push_title, $push_content, "sign_off", "{$sign_id}", "/holiday_reqeust_info.php?mng=Y&sign_id=");
        }

        $insert_push = "INSERT INTO a_push SET
                        recv_id = '{$sign_off_id}',
                        recv_id_type = 'sm',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$wid}',
                        push_type = 'sign_off',
                        push_idx = '{$sign_id}',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }
}


//서명파일 저장된 것이 있는지
$singature_row = sql_fetch("SELECT COUNT(*) as cnt FROM a_signature WHERE mb_id = '{$wid}' and signature_data = '{$approval_signature}'");

//base64 디코드 후 저장
if($singature_row['cnt'] == 0){
    if($approval_signature != ""){
        $data_uri = $_POST['approval_signature'];
        $encoded_image = explode(",", $data_uri);
        $decoded_image = base64_decode($encoded_image[1]);
        $file_name = md5(uniqid(rand(), TRUE)).".png";
        $file_name = preg_replace("/\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $file_name);
    
        $file_path = G5_DATA_PATH.'/file/approval';
    
        @mkdir($file_path, G5_DIR_PERMISSION);
        @chmod($file_path, G5_DIR_PERMISSION);
    
        $file_name2 = $wid."_".$file_name;
    
        $tgt = $file_path.'/'.$wid."_".$file_name;
    
        //echo $tgt.'<br>';
    
        //파일저장
        file_put_contents($tgt, $decoded_image);

        //서명저장
        $signature_insert = "INSERT INTO a_signature SET
                             mb_id = '{$wid}',
                             signature_data = '{$data_uri}',
                             fil_name = '{$file_name2}',
                             created_at = '{$today}'";
        sql_query($signature_insert);
        $sg_idx = sql_insert_id(); //저장된 서명 idx
    
        $img_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_sign_off_img WHERE mng_id = '{$wid}' and sign_id = '{$sign_id}'");
    
        if($img_confirm['cnt'] > 0){
            $update_img = "UPDATE a_sign_off_img SET
                    sg_idx = '{$sg_idx}'
                    WHERE sign_id = '{$sign_id}'";
            sql_query($update_img);
    
        }else{
            $insert_img = "INSERT INTO a_sign_off_img SET
                    sg_idx = '{$sg_idx}',
                    sign_id = '{$sign_id}',
                    mng_id = '{$wid}',
                    created_at = '{$today}'";
            sql_query($insert_img);
        }
       
    }
}else{

    $singature_row2 = sql_fetch("SELECT * FROM a_signature WHERE mb_id = '{$wid}' and signature_data = '{$approval_signature}'");

    $insert_img = "INSERT INTO a_sign_off_img SET
                    sg_idx = '{$singature_row2['sg_idx']}',
                    sign_id = '{$sign_id}',
                    mng_id = '{$wid}',
                    created_at = '{$today}'";


    sql_query($insert_img);
}


//exit;

if($w == 'u'){
    // alert($approval_name.'가 수정되었습니다.');
    goto_url("/holiday_request_sample.php?mem_type=".$mem_type."&sign_id=".$sign_id);
}else{
    goto_url("/holiday_request_sample.php?mem_type=".$mem_type."&sign_id=".$sign_id);
    // alert($approval_name.'가 등록되었습니다.', './approval_form.php?' . $qstr . '&amp;w=u&amp;sign_id=' . $sign_id);
}