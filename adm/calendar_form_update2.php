<?php
require_once "./_common.php";

//캘린더 등록

//기본
$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

$calendar_info = get_calendar_category($cal_code);
$calendar_name = $calendar_info['cal_name'];
//print_r2($_POST);

if($w == "u"){

    $cal_info = "SELECT * FROM a_calendar WHERE cal_idx = '{$cal_idx}'";
    $cal_info_row = sql_fetch($cal_info);


    // echo '수정중<br>';
    // echo '---------<br>';

    //반복설정이 되어있을 때 수정하면
    if($cal_info_row['noti_repeat'] != 'N'){
        // echo '반복일정 수정중<br>';

        $cal_check = "SELECT COUNT(*) as cnt FROM a_calendar WHERE cal_date = '{$cal_date_def}' and cal_idx = '{$cal_idx}'";
        $cal_check_row = sql_fetch($cal_check);
            
        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
            echo $cal_check.'<br>';
        }


        if($cal_check_row['cnt'] > 0){ //기존 일정일 경우(반복일정 중 특정일 수정이 아닌 전체수정)

            //업데이트
            $update_query = "UPDATE a_calendar SET
                            cal_code = '{$cal_code}',
                            post_id = '{$post_id}',
                            building_id = '{$building_id}',
                            mng_department = '{$mng_department}',
                            mng_id = '{$mng_id}',
                            cal_date = '{$cal_date2}',
                            noti_repeat = '{$noti_repeat}',
                            cal_title = '{$cal_title}',
                            cal_content = '{$cal_content}',
                            updated_at = '{$today}'
                            WHERE cal_idx = '{$cal_idx}'";

            if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                echo $update_query.'33<br>';
            }else{
                sql_query($update_query);
            }
            // echo $update_query.'33<br>';
            

        }else{

            $cal_edate = date('Y-m-t',strtotime($cal_date2."-1 month")); // -1달

           

            //반복일정 마감일 설정
            $update_query = "UPDATE a_calendar SET
                                cal_edate = '{$cal_edate}'
                                WHERE cal_idx = '{$cal_idx}'";
            // echo $update_query.'<br>';
            sql_query($update_query);
    
    
            //일정추가
            $insert_query = "INSERT a_calendar SET
                                cal_code = '{$cal_code}',
                                post_id = '{$post_id}',
                                building_id = '{$building_id}',
                                mng_department = '{$mng_department}',
                                mng_id = '{$mng_id}',
                                exception_idx = '{$cal_idx}',
                                cal_date = '{$cal_date2}',
                                noti_repeat = '{$noti_repeat}',
                                cal_title = '{$cal_title}',
                                cal_content = '{$cal_content}',
                                wid = '{$wid}',
                                created_at = '{$today}'";
            // echo $insert_query.'<br>';
            if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                echo $insert_query.'33<br>';
                
            }else{
                sql_query($insert_query);
            }
            
    
            $cal_idx = sql_insert_id(); //팝업 idx
        }

   

        // echo '---------<br>';
    }else{ //반복 아닌경우 그냥 업데이트
         //업데이트
         $update_query = "UPDATE a_calendar SET
                            cal_code = '{$cal_code}',
                            post_id = '{$post_id}',
                            building_id = '{$building_id}',
                            mng_department = '{$mng_department}',
                            mng_id = '{$mng_id}',
                            cal_date = '{$cal_date2}',
                            noti_repeat = '{$noti_repeat}',
                            cal_title = '{$cal_title}',
                            cal_content = '{$cal_content}',
                            updated_at = '{$today}'
                            WHERE cal_idx = '{$cal_idx}'";
        // echo $update_query.'22<br>';
        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
            echo $update_query.'22<br>';
        }else{
            sql_query($update_query);
        }
        
    }


    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        exit;
    }
    // exit;

    //담당자 변경시 푸시
    if($mng_id != $cal_info_row['mng_id']){

        if($mng_id != '-1'){
            $mng_sql = "SELECT mng.*, mb.mb_token, mb.noti3 FROM a_mng as mng
                        LEFT JOIN g5_member as mb ON mng.mng_id = mb.mb_id
                        WHERE mng.mng_id = '{$mng_id}' ORDER BY mng.mng_idx desc";
            $mng_row = sql_fetch($mng_sql);
            
            $push_title = '['.$calendar_name.' 캘린더] 일정 담당자가 변경되었습니다.';
            $push_content = $calendar_name."캘린더의 ".$cal_title." 일정 담당자로 지정되었습습니다.";

            if($mng_row['mb_token'] != "" && $mng_row['noti3']){ //토큰이 있는경우 푸시 발송
                if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
                    try {
                        fcm_send($mng_row['mb_token'], $push_title, $push_content, 'schedule', "{$cal_idx}", "/schedule_add.php?w=i&cal_idx=");
                    } catch(Exception $e) {
                        // FCM 오류 무시하고 계속 진행
                    }
                }
            }

            $insert_push = "INSERT INTO a_push SET
                            recv_id_type = 'sm',
                            recv_id = '{$mng_row['mng_id']}',
                            push_title = '{$push_title}',
                            push_content = '{$push_content}',
                            wid = '{$member['mb_id']}',
                            push_type = 'schedule',
                            push_idx = '{$cal_idx}',
                            created_at = '{$today}'";
            sql_query($insert_push);
        }
    }

   
    

}else{

    $insert_query = "INSERT INTO a_calendar SET
                        cal_code = '{$cal_code}',
                        post_id = '{$post_id}',
                        building_id = '{$building_id}',
                        mng_department = '{$mng_department}',
                        mng_id = '{$mng_id}',
                        cal_date = '{$cal_date}',
                        noti_repeat = '{$noti_repeat}',
                        cal_title = '{$cal_title}',
                        cal_content = '{$cal_content}',
                        wid = '{$member['mb_id']}',
                        created_at = '{$today}'";
    //echo $insert_query.'<br>';

    sql_query($insert_query);
    $cal_idx = sql_insert_id(); //팝업 idx


    //푸시발송
    
    $computation_arr = ['computation', 'move_out_settlement']; //전산팀
    $one_site_arr = ['one_site', 'meter_reading']; //현장팀
    $secretary_arr = ['secretary']; //총무팀
    $all_arr = ['etc1', 'etc2', 'etc3'];

    $sql_wh = '';

    //전산팀
    if(in_array($cal_code, $computation_arr)){
        $sql_wh = " and mng.mng_department = 1 ";
    }

     //현장팀
    if(in_array($cal_code, $one_site_arr)){
        $sql_wh = " and mng.mng_department = 2 ";
    }

    //총무팀
    if(in_array($cal_code, $one_site_arr)){
        $sql_wh = " and mng.mng_department = 3 ";
    }

    //기타
    if(in_array($cal_code, $all_arr)){
        $sql_wh = "";
    }

   

    //and mng.mng_id != '{$member['mb_id']}'
    //{$sql_wh}
    $mng_sql = "SELECT mng.*, mb.mb_token, mb.noti3 FROM a_mng as mng
                LEFT JOIN g5_member as mb ON mng.mng_id = mb.mb_id
                WHERE mng.is_del = 0 ORDER BY mng.mng_idx desc";
    $mng_res = sql_query($mng_sql);

    while($mng_row = sql_fetch_array($mng_res)){

        $push_title = '['.$calendar_name.' 캘린더] 일정이 등록되었습니다.';
        $push_content = $calendar_name."캘런더에 일정이 등록되었습니다.";

        if($mng_row['mb_token'] != "" && $mng_row['noti3']){ //토큰이 있는경우 푸시 발송
            if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
                try {
                    fcm_send($mng_row['mb_token'], $push_title, $push_content, 'schedule', "{$cal_idx}", "/schedule_add.php?w=i&cal_idx=");
                } catch(Exception $e) {
                    // FCM 오류 무시하고 계속 진행
                }
            }
        }

        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'sm',
                        recv_id = '{$mng_row['mng_id']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$member['mb_id']}',
                        push_type = 'schedule',
                        push_idx = '{$cal_idx}',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }
}


if($w == 'u'){
    alert('사내용 캘린더가 수정되었습니다.', './calendar_form2.php?w=u&cal_code='.$cal_code.'&cal_idx=' . $cal_idx.'&cal_date_def='.$cal_date2);
}else{
    // alert('사내용 캘린더가 등록되었습니다.', './calendar_form.php?cal_code='.$cal_code.'&amp;' . $qstr . '&amp;w=u&amp;cal_idx=' . $cal_idx);
    // alert('사내용 캘린더가 등록되었습니다.', './calendar_list.php?cal_code='.$cal_code);
    alert('사내용 캘린더가 등록되었습니다.', './calendar_list.php?cal_code='.$cal_code.'&toYear=' . substr($cal_date,0,4) . '&toMonth=' . substr($cal_date,5,2));
}
