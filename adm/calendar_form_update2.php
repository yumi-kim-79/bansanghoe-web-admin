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
    // 반복 시리즈 occurrence 편집 판정 (B-2)
    //  - 부모(MONTH/YEAR) 직접 편집           → parent_idx = 자기 cal_idx
    //  - 그달 예외(noti_repeat='N' + exception_idx 채워짐) 편집 → parent_idx = exception_idx
    //  ※ empty()는 NULL/''/'0'/0 을 모두 빈값으로 판정 (목록 쿼리 exception_idx 필터와 동일 취지)
    $series_parent_idx = '';
    if($cal_info_row['noti_repeat'] != 'N'){
        $series_parent_idx = $cal_info_row['cal_idx'];
    }elseif(!empty($cal_info_row['exception_idx'])){
        $series_parent_idx = $cal_info_row['exception_idx'];
    }

    if($series_parent_idx != ''){ //반복 시리즈 occurrence 수정 (그달 예외 보존 + 부모 내용 전파)

        // 부모 시리즈 조회 (시작일/noti_repeat 보존 확인용)
        $parent_row = sql_fetch("SELECT * FROM a_calendar WHERE cal_idx = '{$series_parent_idx}'");

        $occ_date = $cal_date_def; // 편집 대상 occurrence 날짜

        // 부모 내용 전파 UPDATE: cal_title/cal_content/담당자/updated_at 만.
        //  ★ cal_date / noti_repeat / cal_edate / exception_idx / wid / created_at 은 SET 에서 제외(보존)
        //    (시리즈 정체성 필드 building_id/cal_code/post_id 도 내용 수정 경로에서는 건드리지 않음)
        $parent_update = "UPDATE a_calendar SET
                        mng_department = '{$mng_department}',
                        mng_id = '{$mng_id}',
                        cal_title = '{$cal_title}',
                        cal_content = '{$cal_content}',
                        updated_at = '{$today}'
                        WHERE cal_idx = '{$series_parent_idx}'";

        if($occ_date == $parent_row['cal_date']){
            // (시작월 자체 편집) 예외 불필요 — 부모만 직접 UPDATE
            if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                echo $parent_update.'33<br>';
            }else{
                sql_query($parent_update);
            }

            $cal_idx = $series_parent_idx;

        }else{
            // 그 달 예외 upsert + 부모 내용 전파

            // ① 기존 그달 예외 확인
            $exception_check = sql_fetch("SELECT cal_idx, COUNT(*) as cnt FROM a_calendar WHERE exception_idx = '{$series_parent_idx}' and cal_date = '{$occ_date}' and is_del = 0");

            if($exception_check['cnt'] > 0){
                // 그달 예외 UPDATE (cal_date=occ_date / noti_repeat='N' 보존, 내용·담당자만 갱신)
                $exc_query = "UPDATE a_calendar SET
                                mng_department = '{$mng_department}',
                                mng_id = '{$mng_id}',
                                cal_title = '{$cal_title}',
                                cal_content = '{$cal_content}',
                                updated_at = '{$today}'
                                WHERE cal_idx = '{$exception_check['cal_idx']}'";
                $exc_is_insert = false;
            }else{
                // 그달 예외 신규 INSERT (noti_repeat='N' 유지 — 펼침 로직 호환)
                $exc_query = "INSERT a_calendar SET
                                    cal_code = '{$cal_code}',
                                    post_id = '{$post_id}',
                                    building_id = '{$building_id}',
                                    mng_department = '{$mng_department}',
                                    mng_id = '{$mng_id}',
                                    exception_idx = '{$series_parent_idx}',
                                    cal_date = '{$occ_date}',
                                    noti_repeat = 'N',
                                    cal_title = '{$cal_title}',
                                    cal_content = '{$cal_content}',
                                    wid = '{$wid}',
                                    created_at = '{$today}'";
                $exc_is_insert = true;
            }

            // 예외 upsert + 부모 UPDATE 를 한 가드 덩어리로
            //  관리자 IP면 둘 다 echo만(부분 실행 방지), 그 외에는 둘 다 실행. sql_insert_id()는 실행 분기에서만.
            if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                echo $exc_query.'33<br>';
                echo $parent_update.'33<br>';
            }else{
                sql_query($exc_query);
                $cal_idx = $exc_is_insert ? sql_insert_id() : $exception_check['cal_idx'];
                sql_query($parent_update);
            }
        }

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
                        try { fcm_send($mng_row['mb_token'], $push_title, $push_content, 'schedule', "{$cal_idx}", "/schedule_add.php?w=i&cal_idx="); } catch(Exception $e) {}
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
                    try { fcm_send($mng_row['mb_token'], $push_title, $push_content, 'schedule', "{$cal_idx}", "/schedule_add.php?w=i&cal_idx="); } catch(Exception $e) {}
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
