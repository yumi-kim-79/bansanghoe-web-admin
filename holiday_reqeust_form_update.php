<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");
$wdate = date("Y-m-d");

// if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
//     die(result_data(false, count($_FILES['approval_file']['name']), []));
// }

if($sign_off_mng_id1 == "") die(result_data(false, "1차 결재자를 선택해주세요.", "sign_off_mng_id1"));

if($sign_off_category == "paid_holiday"){
    if($sign_off_year == "") die(result_data(false, "휴가를 사용하실 년도를 선택하세요.", "sign_off_year"));
    if($sign_off_month == "") die(result_data(false, "휴가를 사용하실 월을 선택하세요.", "sign_off_year"));

    if($hp_name[0] == "") die(result_data(false, "연차 신청자 정보를 한명 이상 입력해주세요.", "hp_name"));

    for($i=0;$i<count($hp_name);$i++){
        if($hp_name[$i] != ""){
            if($hp_date[$i] == "") die(result_data(($i+1)."번째 연차 신청자의 사용일자를 선택해주세요.", []));
        }
    }
}

if($sign_off_category == 'duty_report'){
    if($duty_edate < $duty_sdate){
        die(result_data(false, "근무 시작일이 근무 종료일보다 이후 일수 없습니다.", []));
    }
}

function isValidTime($value) {
    // HH:MM 형식 확인 (24시간)
    return preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $value) === 1;
}

if($sign_off_category == "overtime_work_request" || $sign_off_category == "overtime_work_report"){

    if($extension_date == "") die(result_data(false, "연장 근무 일시를 선택해주세요.", []));

    if(!isValidTime($extension_stime)){
        die(result_data(false, "연장 근무 시작시간을 형식에 맞게 입력하세요. ex.12:00", []));
    }


    if(!isValidTime($extension_etime)){
        die(result_data(false, "연장 근무 종료시간을 형식에 맞게 입력하세요. ex.12:00", []));
    }

    if($extension_stime > $extension_etime){
        die(result_data(false, "연장 근무 시작 시간이 종료 시작 시간보다 이후 일 수 없습니다.", []));
    }
}

//결재서류명
$approval_name = approval_category_name($sign_off_category);

if($w == "u"){

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

    $update_query = "UPDATE a_sign_off SET
                        sign_off_mng_id1 = '{$sign_off_mng_id1}',
                        sign_off_mng_id2 = '{$sign_off_mng_id2}',
                        sign_off_mng_id3 = '{$sign_off_mng_id3}',
                        {$sql_chk}
                        {$sql_common}
                        WHERE sign_id = '{$sign_id}'";
    //die(result_data(false, $update_query, []));
    sql_query($update_query);

    $update_q = array();
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
                    //array_push($update_q, $update_hp);
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
                    //array_push($update_q, $insert_hp);

                }
                
            }

            //die(result_data(false, $update_q, $update_query));
        }
    }

    


}else{

    $page_arr = ["daily_paid", "onsite_expenses", "personal_signoff", "expenditure_plan", "builder_statement", "building_account", "mng_adjustment", "bill_payment", "household_refund"];

    if($approval_signature == "") die(result_data(false, "서명을 진행해주세요.", []));

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

    if(in_array($sign_off_category, $page_arr)){
        $sql_common = " mng_department = '{$mng_department}',
                        mng_grade = '{$mng_grade}',
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

    //die(result_data(false, $insert_query, $page_arr));
    sql_query($insert_query);
    $sign_id = sql_insert_id(); //팝업 idx
    //die(result_data(false, $insert_query, []));
    
    //연차 유급 휴가 사용 계획서
    if($sign_off_category == "paid_holiday"){
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

    //1차 결재권자 푸시발송
    if($sign_off_mng_id1 != ""){
        
        $sign_off_id_info = get_member($sign_off_mng_id1); //1차 결재자 정보

        $sign_off_id = $sign_off_id_info['mb_id']; //1차 결재자 아이디

        $push_title = '[결재요청] '.$approval_name." 결재 요청이 있습니다.";
        $push_content = $wname.'님의 '.$approval_name." 결재 요청이 있습니다.";
    

        if($sign_off_id_info['mb_token'] != ""){ //토큰이 있는경우 푸시 발송

            
            if($_SERVER['REMOTE'] != ADMIN_IP) fcm_send($sign_off_id_info['mb_token'], $push_title, $push_content, "sign_off", "{$sign_id}", "/holiday_reqeust_info.php?mng=Y&sign_id=");
            //     if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
            // }
        }

        if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
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
}

 //서명파일 저장된 것이 있는지
$singature_row = sql_fetch("SELECT COUNT(*) as cnt FROM a_signature WHERE mb_id = '{$wid}' and signature_data = '{$approval_signature}'");

if($singature_row['cnt'] == 0){
    //서명 파일
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

        //파일저장
        file_put_contents($tgt, $decoded_image);
    
        //서명저장
        $signature_insert = "INSERT INTO a_signature SET
                             mb_id = '{$wid}',
                             signature_data = '{$data_uri}',
                             fil_name = '{$file_name2}',
                             created_at = '{$today}'";
        sql_query($signature_insert);

        //신청시 사인
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


//파일업로드
//파일(이미지) 첨부
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

//문항 생성시 이미지 파일첨부 경로
$file_path = G5_DATA_PATH.'/file/signOff';

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir($file_path, G5_DIR_PERMISSION);
@chmod($file_path, G5_DIR_PERMISSION);

$upload = array();

if(isset($_FILES['approval_file']['name'])){
    for ($i=0; $i<count($_FILES['approval_file']['name']); $i++) {
        $upload[$i]['file']     = '';
        $upload[$i]['source']   = '';
        $upload[$i]['filesize'] = 0;
        $upload[$i]['image']    = array();
        $upload[$i]['image'][0] = 0;
        $upload[$i]['image'][1] = 0;
        $upload[$i]['image'][2] = 0;
        $upload[$i]['fileurl'] = '';
        $upload[$i]['thumburl'] = '';
        $upload[$i]['storage'] = '';

        //파일삭제
        if (isset($_POST['file_del'][$i]) && $_POST['file_del'][$i]) {
            $upload[$i]['del_check'] = true;

            $row = sql_fetch(" select * from {$g5['board_file_table']} where bo_table = 'signOff' and wr_id = '{$sign_id}' and bf_no = '{$i}' ");

            $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/signOff/'.str_replace('../', '', $row['bf_file']), $row);

            if( file_exists($delete_file) ){
                @unlink($delete_file);
            }
        }else{
            $upload[$i]['del_check'] = false;
        }

        $tmp_file  = $_FILES['approval_file']['tmp_name'][$i];
        $filesize  = $_FILES['approval_file']['size'][$i];
        $filename  = $_FILES['approval_file']['name'][$i];
        $filename  = get_safe_filename($filename);

        if (is_uploaded_file($tmp_file)) {
            $timg = @getimagesize($tmp_file);

            if ( preg_match("/\.({$config['cf_image_extension']})$/i", $filename) || preg_match("/\.({$config['cf_flash_extension']})$/i", $filename) ) {
                if ($timg['2'] < 1 || $timg['2'] > 18){
                    alert("등록할 수 있는 파일 유형이 아닙니다.");
                }
            }

            $upload[$i]['image'] = $timg;

            // 프로그램 원래 파일명
            $upload[$i]['source'] = $filename;
            $upload[$i]['filesize'] = $filesize;

            // 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
            $filename = preg_replace("/\.(php|pht|phtm|htm|cgi|pl|exe|jsp|asp|inc|phar)/i", "$0-x", $filename);

            shuffle($chars_array);
            $shuffle = implode('', $chars_array);

            // 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
            $upload[$i]['file'] = md5(sha1($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.replace_filename($filename);

            $dest_file = $file_path.'/'.$upload[$i]['file'];

            if($timg[0] >= 720){

                 //이미지 회전
                 if(!empty($exif['Orientation'])) {
                    if($timg[2] == 1) $cfile = imagecreatefromgif($tmp_file);
                    else if($timg[2] == 2) $cfile = imagecreatefromjpeg($tmp_file);
                    else if($timg[2] == 3) $cfile = imagecreatefrompng($tmp_file);

                    switch($exif['Orientation']) {
                        case 8:
                            $cfile = imagerotate($cfile,90,0);
                            break;
                        case 3:
                            $cfile = imagerotate($cfile,180,0);
                            break;
                        case 6:
                            $cfile = imagerotate($cfile,-90,0);
                            break;
                    }

                    if($timg[2] == 1) imagegif($cfile, $dest_file, 100);
                    else if($timg[2] == 2) imagejpeg($cfile, $dest_file, 100);
                    else if($timg[2] == 3) imagepng($cfile, $dest_file, 9);
            
                    chmod($dest_file, G5_FILE_PERMISSION);
                    imagedestroy($cfile);
    
                    resizeImage($dest_file, 720, 720);

                    $upload[$i]['image'] = @getimagesize($dest_file); //이미지 축소된 정보로 다시 저장
                }else{
                    if(move_uploaded_file($tmp_file, $dest_file)){
                        resizeImage($dest_file, 720, 720);
    
                        $upload[$i]['image'] = @getimagesize($dest_file); //이미지 축소된 정보로 다시 저장
                    }else{
                        //alert($_FILES['img_up']['error'][$i]);
                        die(result_data(false, $_FILES['approval_file']['error'][$i], []));
                    }
                }

            }else{
                $error_code = move_uploaded_file($tmp_file, $dest_file) or die(result_data(false, $_FILES['approval_file']['error'][$i], []));
            }
        }
    }
}


//die(result_data(false, $upload, []));

for ($i=0; $i<count($upload); $i++)
{
    $upload[$i]['source'] = sql_real_escape_string($upload[$i]['source']);
    $bf_width = isset($upload[$i]['image'][0]) ? (int) $upload[$i]['image'][0] : 0;
    $bf_height = isset($upload[$i]['image'][1]) ? (int) $upload[$i]['image'][1] : 0;
    $bf_type = isset($upload[$i]['image'][2]) ? (int) $upload[$i]['image'][2] : 0;

    if ($upload[$i]['del_check']){

        $del = "DELETE FROM g5_board_file WHERE bo_table = 'signOff' and bf_no = '{$i}' and wr_id = '{$sign_id}'";
        sql_query($del);

        $file_chk = "SELECT * FROM g5_board_file WHERE bo_table = 'signOff' and wr_id = '{$sign_id}' and bf_no > {$i}";
        $file_chk_res = sql_query($file_chk);
        $file_chk_total = sql_num_rows($file_chk_res);

        if($file_chk_total > 0){
            for($j=0;$file_chk_row = sql_fetch_array($file_chk_res);$j++){

                $bf_no_new = $file_chk_row['bf_no'] - 1;
                $up_bf_no = "UPDATE g5_board_file SET bf_no = '{$bf_no_new}' WHERE bo_table = 'signOff' and wr_id = '{$sign_id}' and bf_no = '{$file_chk_row['bf_no']}'";

                sql_query($up_bf_no);
            }
        }
    }

    if($upload[$i]['source'] != "blob"){
        $file_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table = 'signOff' and wr_id = '{$sign_id}' and bf_no = '{$i}'");

        if($file_confirm['cnt'] > 0){
            $sql = " UPDATE {$g5['board_file_table']}
                    SET  bf_source = '{$upload[$i]['source']}',
                         bf_file = '{$upload[$i]['file']}',
                         bf_fileurl = '{$upload[$i]['fileurl']}',
                         bf_thumburl = '{$upload[$i]['thumburl']}',
                         bf_storage = '{$upload[$i]['storage']}',
                         bf_download = 0,
                         bf_filesize = '".(int)$upload[$i]['filesize']."',
                         bf_width = '".$bf_width."',
                         bf_height = '".$bf_height."',
                         bf_type = '".$bf_type."',
                         bf_datetime = '".G5_TIME_YMDHIS."' 
                         WHERE bo_table = 'signOff' and wr_id = '{$sign_id}' and bf_no = '{$i}'";
        }else{
            $sql = " insert into {$g5['board_file_table']}
                    set bo_table = 'signOff',
                         wr_id = '{$sign_id}',
                         bf_no = '{$i}',
                         bf_source = '{$upload[$i]['source']}',
                         bf_file = '{$upload[$i]['file']}',
                         bf_fileurl = '{$upload[$i]['fileurl']}',
                         bf_thumburl = '{$upload[$i]['thumburl']}',
                         bf_storage = '{$upload[$i]['storage']}',
                         bf_download = 0,
                         bf_filesize = '".(int)$upload[$i]['filesize']."',
                         bf_width = '".$bf_width."',
                         bf_height = '".$bf_height."',
                         bf_type = '".$bf_type."',
                         bf_datetime = '".G5_TIME_YMDHIS."' ";
        }

        sql_query($sql);
    }
}

if($w == "u"){
    echo result_data(true, $approval_name.'가 수정되었습니다.', $sign_id);
}else{
    echo result_data(true, $approval_name.'가 등록되었습니다.', $sign_id);
}