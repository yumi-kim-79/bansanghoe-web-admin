<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

if($post_id == "") die(result_data(false, "지역을 선택해주세요.", []));
if($building_id == "") die(result_data(false, "단지명을 입력해주세요.", []));
if($dong_id == "") die(result_data(false, "동을 선택해주세요.", []));
if($ho_id == "") die(result_data(false, "호수를 선택해주세요.", []));

// if($complain_name == "") die(result_data(false, "민원인을 입력해주세요.", []));
if($complain_hp == "") die(result_data(false, "민원인 연락처를 입력해주세요.", []));
if($complain_title == "") die(result_data(false, "민원제목을 입력해주세요.", []));
if($complain_content == "") die(result_data(false, "민원내용을 입력해주세요.", []));


//die(result_data(false, $_POST, $_FILES));

if($w == "u"){

    $complain_sql = "SELECT * FROM a_online_complain WHERE complain_idx = '{$complain_idx}'";
    $complain_row = sql_fetch($complain_sql);

    if($complain_row['mng_id'] != ""){
        if($complain_row['mng_id'] != $mng_id){
            if($mng_change_memo == '') die(result_data(false, "담당자를 변경하신 경우 변경 사유를 입력해주세요.", []));
        }
    }
    

    
    $end_sql = "";
    if($complain_status == "CD"){
        $end_sql = " edate = '{$today}', ";
    }else{
        $end_sql = " edate = NULL, ";
    }

    //관리자 접수 민원일 때만 지역, 단지, 동, 호수 수정
    $sql_addr = "";
    if($complain_type == "admin"){
        $sql_addr = "post_id = '{$post_id}',
                    building_id = '{$building_id}',
                    dong_id = '{$dong_id}',
                    ho_id = '{$ho_id}',";
    }

    $update_query = "UPDATE a_online_complain SET
                        {$sql_addr}
                        complain_name = '{$complain_name}',
                        complain_hp = '{$complain_hp}',
                        mng_department = '{$mng_department}',
                        mng_id = '{$mng_id}',
                        mng_change_memo = '{$mng_change_memo}',
                        complain_status = '{$complain_status}',
                        complain_title = '{$complain_title}',
                        complain_content = '{$complain_content}',
                        complain_answer = '{$complain_answer}',
                        complain_memo = '{$complain_memo}',
                        {$end_sql}
                        wdate = '{$wdate}'
                        WHERE complain_idx = {$complain_idx}";
    

    // die(result_data(false, $update_query, [$_POST]));
    sql_query($update_query);

    //die(result_data(false, $complain_type, []));

   

    // 민원 완료되었고 원래 상태값은 민원 완료가 아니었을 떄 푸시발송
    if($complain_status == "CD" && $complain_row['complain_status'] != 'CD' && $complain_type == "user"){
        $mem_info = get_user($complain_id);

        $push_title = "[민원처리 완료]";
        $push_content = "등록하신 민원 처리가 완료되었습니다.";

        //noti3 민원 알림 수신여부 1 수신 0 안함
        if($mem_info['mb_token'] != "" && $mem_info['noti3']){ //토큰이 있는경우 푸시 발송
            
            if($_SERVER['REMOTE_ADDR'] != ADMIN_IP) fcm_send($mem_info['mb_token'], $push_title, $push_content, 'complain', "{$complain_idx}", "/online_complain_info.php?cstatus=CD&complain_idx=");
           
        }

        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'user',
                        recv_id = '{$mem_info['mb_id']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$mng_id}',
                        push_type = 'complain',
                        push_idx = '{$complain_idx}',
                        created_at = '{$today}'";
        //echo $insert_push;  
        sql_query($insert_push);
       
    }

}else{

    //작성자 이름
    $mbs = get_member($mb_id);

    $insert_query = "INSERT INTO a_online_complain SET
                        complain_type = 'admin',
                        post_id = '{$post_id}',
                        building_id = '{$building_id}',
                        dong_id = '{$dong_id}',
                        ho_id = '{$ho_id}',
                        complain_name = '{$complain_name}',
                        complain_hp = '{$complain_hp}',
                        complain_id = '{$mb_id}',
                        wname = '{$mbs['mb_name']}',
                        mng_department = '{$mng_department}',
                        mng_id = '{$mng_id}',
                        complain_status = '{$complain_status}',
                        complain_title = '{$complain_title}',
                        complain_content = '{$complain_content}',
                        complain_memo = '{$complain_memo}',
                        complain_ip = '{$ip_info}',
                        wdate = '{$wdate}',
                        created_at = '{$today}'";
    // die(result_data(false, $insert_query, []));
    sql_query($insert_query);
    $complain_idx = sql_insert_id(); //팝업 idx


    //sm 매니저 전직원에게 푸시 발송
    $mng_sql = "SELECT mng.*, mb.mb_token, mb.noti1, mb.noti2, mb.noti3, mb.noti4, mb.noti5, mb.noti6 FROM a_mng as mng
                LEFT JOIN g5_member as mb ON mng.mng_id = mb.mb_id
                WHERE mng.is_del = 0 ORDER BY mng.mng_idx desc";
    $mng_res = sql_query($mng_sql);

    while($mng_row = sql_fetch_array($mng_res)){
        $push_title = '[민원신청] '.$complain_title." 민원신청이 있습니다.";
        $push_content = $complain_name.'님의 '.$complain_title." 민원신청이 있습니다.";

        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'sm',
                        recv_id = '{$mng_row['mng_id']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$mb_id}',
                        push_type = 'complain',
                        push_idx = '{$complain_idx}',
                        created_at = '{$today}'";
        sql_query($insert_push);

        if($mng_row['mb_token'] != "" && $mng_row['noti6']){ //토큰이 있는경우 푸시 발송
           
            if($_SERVER['REMOTE_ADDR'] != ADMIN_IP) fcm_send($mng_row['mb_token'], $push_title, $push_content, 'complain', "{$complain_idx}", "/sm_complain_info.php?complain_status=CD&complain_idx=");
        }
    }

}

//exit;

//민원 파일(이미지) 첨부
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

//문항 생성시 이미지 파일첨부 경로
$file_path = G5_DATA_PATH.'/file/complain';

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir($file_path, G5_DIR_PERMISSION);
@chmod($file_path, G5_DIR_PERMISSION);

$upload = array();

if(isset($_FILES['complain_file']['name'])){
    for ($i=0; $i<count($_FILES['complain_file']['name']); $i++) {
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
        if (isset($_POST['complain_file_del'][$i]) && $_POST['complain_file_del'][$i]) {
            $upload[$i]['del_check'] = true;

            $row = sql_fetch(" select * from {$g5['board_file_table']} where bo_table = 'complain' and wr_id = '{$complain_idx}' and bf_no = '{$i}' ");

            $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/complain/'.str_replace('../', '', $row['bf_file']), $row);

            if( file_exists($delete_file) ){
                @unlink($delete_file);
            }
        }else{
            $upload[$i]['del_check'] = false;
        }

        $tmp_file  = $_FILES['complain_file']['tmp_name'][$i];
        $filesize  = $_FILES['complain_file']['size'][$i];
        $filename  = $_FILES['complain_file']['name'][$i];
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

            // if($timg[0] >= 720){

            //     if(move_uploaded_file($tmp_file, $dest_file)){
            //         resizeImage($dest_file, 720, 720);

            //         $upload[$i]['image'] = @getimagesize($dest_file); //이미지 축소된 정보로 다시 저장
            //     }else{
            //         //alert($_FILES['img_up']['error'][$i]);
            //         die(result_data(false, $_FILES['complain_file']['error'][$i], []));
            //     }

            // }else{
            //     $error_code = move_uploaded_file($tmp_file, $dest_file) or die(result_data(false, $_FILES['complain_file']['error'][$i], []));
            // }
            $error_code = move_uploaded_file($tmp_file, $dest_file) or die(result_data(false, $_FILES['complain_file']['error'][$i], []));
        }
    }
}

for ($i=0; $i<count($upload); $i++)
{
    $upload[$i]['source'] = sql_real_escape_string($upload[$i]['source']);
    $bf_width = isset($upload[$i]['image'][0]) ? (int) $upload[$i]['image'][0] : 0;
    $bf_height = isset($upload[$i]['image'][1]) ? (int) $upload[$i]['image'][1] : 0;
    $bf_type = isset($upload[$i]['image'][2]) ? (int) $upload[$i]['image'][2] : 0;

    if ($upload[$i]['del_check']){

        $del = "DELETE FROM g5_board_file WHERE bo_table = 'complain' and bf_no = '{$i}' and wr_id = '{$complain_idx}'";
        sql_query($del);

        $file_chk = "SELECT * FROM g5_board_file WHERE bo_table = 'complain' and wr_id = '{$complain_idx}' and bf_no > {$i}";
        $file_chk_res = sql_query($file_chk);
        $file_chk_total = sql_num_rows($file_chk_res);

        if($file_chk_total > 0){
            for($j=0;$file_chk_row = sql_fetch_array($file_chk_res);$j++){

                $bf_no_new = $file_chk_row['bf_no'] - 1;
                $up_bf_no = "UPDATE g5_board_file SET bf_no = '{$bf_no_new}' WHERE bo_table = 'complain' and wr_id = '{$complain_idx}' and bf_no = '{$file_chk_row['bf_no']}'";

                sql_query($up_bf_no);
            }
        }
    }

    if($upload[$i]['source'] != "blob"){
        $file_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table = 'complain' and wr_id = '{$complain_idx}' and bf_no = '{$i}'");

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
                         WHERE bo_table = 'complain' and wr_id = '{$complain_idx}' and bf_no = '{$i}'";
        }else{
            $sql = " insert into {$g5['board_file_table']}
                    set bo_table = 'complain',
                         wr_id = '{$complain_idx}',
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


//답변 파일첨부 경로
$file_path2 = G5_DATA_PATH.'/file/complain_answer';

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir($file_path2, G5_DIR_PERMISSION);
@chmod($file_path2, G5_DIR_PERMISSION);

$upload_a = array();

if(isset($_FILES['answer_file']['name'])){
    for ($i=0; $i<count($_FILES['answer_file']['name']); $i++) {
        $upload_a[$i]['file']     = '';
        $upload_a[$i]['source']   = '';
        $upload_a[$i]['filesize'] = 0;
        $upload_a[$i]['image']    = array();
        $upload_a[$i]['image'][0] = 0;
        $upload_a[$i]['image'][1] = 0;
        $upload_a[$i]['image'][2] = 0;
        $upload_a[$i]['fileurl'] = '';
        $upload_a[$i]['thumburl'] = '';
        $upload_a[$i]['storage'] = '';

        //파일삭제
        if (isset($_POST['complain_answer_file_del'][$i]) && $_POST['complain_answer_file_del'][$i]) {
            $upload_a[$i]['del_check'] = true;

            $row = sql_fetch(" select * from {$g5['board_file_table']} where bo_table = 'complain_answer' and wr_id = '{$complain_idx}' and bf_no = '{$i}' ");

            $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/complain_answer/'.str_replace('../', '', $row['bf_file']), $row);

            if( file_exists($delete_file) ){
                @unlink($delete_file);
            }
        }else{
            $upload_a[$i]['del_check'] = false;
        }

        $tmp_file  = $_FILES['answer_file']['tmp_name'][$i];
        $filesize  = $_FILES['answer_file']['size'][$i];
        $filename  = $_FILES['answer_file']['name'][$i];
        $filename  = get_safe_filename($filename);

        if (is_uploaded_file($tmp_file)) {
            $timg = @getimagesize($tmp_file);

            if ( preg_match("/\.({$config['cf_image_extension']})$/i", $filename) || preg_match("/\.({$config['cf_flash_extension']})$/i", $filename) ) {
                if ($timg['2'] < 1 || $timg['2'] > 18){
                    alert("등록할 수 있는 파일 유형이 아닙니다.");
                }
            }

            $upload_a[$i]['image'] = $timg;

            // 프로그램 원래 파일명
            $upload_a[$i]['source'] = $filename;
            $upload_a[$i]['filesize'] = $filesize;

            // 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
            $filename = preg_replace("/\.(php|pht|phtm|htm|cgi|pl|exe|jsp|asp|inc|phar)/i", "$0-x", $filename);

            shuffle($chars_array);
            $shuffle = implode('', $chars_array);

            // 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
            $upload_a[$i]['file'] = md5(sha1($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.replace_filename($filename);

            $dest_file2 = $file_path2.'/'.$upload_a[$i]['file'];

            // if($timg[0] >= 720){

            //     if(move_uploaded_file($tmp_file, $dest_file2)){
            //         resizeImage($dest_file2, 720, 720);

            //         $upload_a[$i]['image'] = @getimagesize($dest_file2); //이미지 축소된 정보로 다시 저장
            //     }else{
            //         //alert($_FILES['img_up']['error'][$i]);
            //         die(result_data(false, $_FILES['answer_file']['error'][$i], []));
            //     }

            // }else{
            //     $error_code = move_uploaded_file($tmp_file, $dest_file2) or die(result_data(false, $_FILES['answer_file']['error'][$i], []));
            // }
            $error_code = move_uploaded_file($tmp_file, $dest_file2) or die(result_data(false, $_FILES['answer_file']['error'][$i], []));
        }
    }
}

//die(result_data(false, $dest_file2, $upload_a));

for ($i=0; $i<count($upload_a); $i++)
{
    $upload_a[$i]['source'] = sql_real_escape_string($upload_a[$i]['source']);
    $bf_width = isset($upload_a[$i]['image'][0]) ? (int) $upload_a[$i]['image'][0] : 0;
    $bf_height = isset($upload_a[$i]['image'][1]) ? (int) $upload_a[$i]['image'][1] : 0;
    $bf_type = isset($upload_a[$i]['image'][2]) ? (int) $upload_a[$i]['image'][2] : 0;

    if ($upload_a[$i]['del_check']){

        $del = "DELETE FROM g5_board_file WHERE bo_table = 'complain_answer' and bf_no = '{$i}' and wr_id = '{$complain_idx}'";
        sql_query($del);

        $file_chk = "SELECT * FROM g5_board_file WHERE bo_table = 'complain_answer' and wr_id = '{$complain_idx}' and bf_no > {$i}";
        $file_chk_res = sql_query($file_chk);
        $file_chk_total = sql_num_rows($file_chk_res);

        if($file_chk_total > 0){
            for($j=0;$file_chk_row = sql_fetch_array($file_chk_res);$j++){

                $bf_no_new = $file_chk_row['bf_no'] - 1;
                $up_bf_no = "UPDATE g5_board_file SET bf_no = '{$bf_no_new}' WHERE bo_table = 'complain_answer' and wr_id = '{$complain_idx}' and bf_no = '{$file_chk_row['bf_no']}'";

                sql_query($up_bf_no);
            }
        }
    }

    if($upload_a[$i]['source'] != "blob"){
        $file_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table = 'complain_answer' and wr_id = '{$complain_idx}' and bf_no = '{$i}'");

        if($file_confirm['cnt'] > 0){
            $sql = " UPDATE {$g5['board_file_table']}
                    SET  bf_source = '{$upload_a[$i]['source']}',
                         bf_file = '{$upload_a[$i]['file']}',
                         bf_fileurl = '{$upload_a[$i]['fileurl']}',
                         bf_thumburl = '{$upload_a[$i]['thumburl']}',
                         bf_storage = '{$upload_a[$i]['storage']}',
                         bf_download = 0,
                         bf_filesize = '".(int)$upload_a[$i]['filesize']."',
                         bf_width = '".$bf_width."',
                         bf_height = '".$bf_height."',
                         bf_type = '".$bf_type."',
                         bf_datetime = '".G5_TIME_YMDHIS."' 
                         WHERE bo_table = 'complain_answer' and wr_id = '{$complain_idx}' and bf_no = '{$i}'";
        }else{
            $sql = " insert into {$g5['board_file_table']}
                    set bo_table = 'complain_answer',
                         wr_id = '{$complain_idx}',
                         bf_no = '{$i}',
                         bf_source = '{$upload_a[$i]['source']}',
                         bf_file = '{$upload_a[$i]['file']}',
                         bf_fileurl = '{$upload_a[$i]['fileurl']}',
                         bf_thumburl = '{$upload_a[$i]['thumburl']}',
                         bf_storage = '{$upload_a[$i]['storage']}',
                         bf_download = 0,
                         bf_filesize = '".(int)$upload_a[$i]['filesize']."',
                         bf_width = '".$bf_width."',
                         bf_height = '".$bf_height."',
                         bf_type = '".$bf_type."',
                         bf_datetime = '".G5_TIME_YMDHIS."' ";
        }

        sql_query($sql);
    }
}

//추가내용 파일첨부 경로
$file_path3 = G5_DATA_PATH.'/file/complain_add';

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir($file_path3, G5_DIR_PERMISSION);
@chmod($file_path3, G5_DIR_PERMISSION);

$upload_add = array();

if(isset($_FILES['answer_add_file']['name'])){
    for ($i=0; $i<count($_FILES['answer_add_file']['name']); $i++) {
        $upload_add[$i]['file']     = '';
        $upload_add[$i]['source']   = '';
        $upload_add[$i]['filesize'] = 0;
        $upload_add[$i]['image']    = array();
        $upload_add[$i]['image'][0] = 0;
        $upload_add[$i]['image'][1] = 0;
        $upload_add[$i]['image'][2] = 0;
        $upload_add[$i]['fileurl'] = '';
        $upload_add[$i]['thumburl'] = '';
        $upload_add[$i]['storage'] = '';

        //파일삭제
        if (isset($_POST['complain_add_file_del'][$i]) && $_POST['complain_add_file_del'][$i]) {
            $upload_add[$i]['del_check'] = true;

            $row = sql_fetch(" select * from {$g5['board_file_table']} where bo_table = 'complain_add' and wr_id = '{$complain_idx}' and bf_no = '{$i}' ");

            $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/complain_add/'.str_replace('../', '', $row['bf_file']), $row);

            if( file_exists($delete_file) ){
                @unlink($delete_file);
            }
        }else{
            $upload_add[$i]['del_check'] = false;
        }

        $tmp_file  = $_FILES['answer_add_file']['tmp_name'][$i];
        $filesize  = $_FILES['answer_add_file']['size'][$i];
        $filename  = $_FILES['answer_add_file']['name'][$i];
        $filename  = get_safe_filename($filename);

        if (is_uploaded_file($tmp_file)) {
            $timg = @getimagesize($tmp_file);

            if ( preg_match("/\.({$config['cf_image_extension']})$/i", $filename) || preg_match("/\.({$config['cf_flash_extension']})$/i", $filename) ) {
                if ($timg['2'] < 1 || $timg['2'] > 18){
                    alert("등록할 수 있는 파일 유형이 아닙니다.");
                }
            }

            $upload_add[$i]['image'] = $timg;

            // 프로그램 원래 파일명
            $upload_add[$i]['source'] = $filename;
            $upload_add[$i]['filesize'] = $filesize;

            // 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
            $filename = preg_replace("/\.(php|pht|phtm|htm|cgi|pl|exe|jsp|asp|inc|phar)/i", "$0-x", $filename);

            shuffle($chars_array);
            $shuffle = implode('', $chars_array);

            // 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
            $upload_add[$i]['file'] = md5(sha1($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.replace_filename($filename);

            $dest_file3 = $file_path3.'/'.$upload_add[$i]['file'];

            // if($timg[0] >= 720){

            //     if(move_uploaded_file($tmp_file, $dest_file3)){
            //         resizeImage($dest_file3, 720, 720);

            //         $upload_add[$i]['image'] = @getimagesize($dest_file3); //이미지 축소된 정보로 다시 저장
            //     }else{
            //         //alert($_FILES['img_up']['error'][$i]);
            //         die(result_data(false, $_FILES['answer_add_file']['error'][$i], []));
            //     }

            // }else{
            //     $error_code = move_uploaded_file($tmp_file, $dest_file3) or die(result_data(false, $_FILES['answer_add_file']['error'][$i], []));
            // }
            $error_code = move_uploaded_file($tmp_file, $dest_file3) or die(result_data(false, $_FILES['answer_add_file']['error'][$i], []));
        }
    }
}

for ($i=0; $i<count($upload_add); $i++)
{
    $upload_add[$i]['source'] = sql_real_escape_string($upload_add[$i]['source']);
    $bf_width = isset($upload_add[$i]['image'][0]) ? (int) $upload_add[$i]['image'][0] : 0;
    $bf_height = isset($upload_add[$i]['image'][1]) ? (int) $upload_add[$i]['image'][1] : 0;
    $bf_type = isset($upload_add[$i]['image'][2]) ? (int) $upload_add[$i]['image'][2] : 0;

    if ($upload_add[$i]['del_check']){

        $del = "DELETE FROM g5_board_file WHERE bo_table = 'complain_add' and bf_no = '{$i}' and wr_id = '{$complain_idx}'";
        sql_query($del);

        $file_chk = "SELECT * FROM g5_board_file WHERE bo_table = 'complain_add' and wr_id = '{$complain_idx}' and bf_no > {$i}";
        $file_chk_res = sql_query($file_chk);
        $file_chk_total = sql_num_rows($file_chk_res);

        if($file_chk_total > 0){
            for($j=0;$file_chk_row = sql_fetch_array($file_chk_res);$j++){

                $bf_no_new = $file_chk_row['bf_no'] - 1;
                $up_bf_no = "UPDATE g5_board_file SET bf_no = '{$bf_no_new}' WHERE bo_table = 'complain_add' and wr_id = '{$complain_idx}' and bf_no = '{$file_chk_row['bf_no']}'";

                sql_query($up_bf_no);
            }
        }

    }

    if($upload_add[$i]['source'] != "blob"){
        $file_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table = 'complain_add' and wr_id = '{$complain_idx}' and bf_no = '{$i}'");

        if($file_confirm['cnt'] > 0){
            $sql = " UPDATE {$g5['board_file_table']}
                    SET  bf_source = '{$upload_add[$i]['source']}',
                         bf_file = '{$upload_add[$i]['file']}',
                         bf_fileurl = '{$upload_add[$i]['fileurl']}',
                         bf_thumburl = '{$upload_add[$i]['thumburl']}',
                         bf_storage = '{$upload_add[$i]['storage']}',
                         bf_download = 0,
                         bf_filesize = '".(int)$upload_add[$i]['filesize']."',
                         bf_width = '".$bf_width."',
                         bf_height = '".$bf_height."',
                         bf_type = '".$bf_type."',
                         bf_datetime = '".G5_TIME_YMDHIS."' 
                         WHERE bo_table = 'complain_add' and wr_id = '{$complain_idx}' and bf_no = '{$i}'";
        }else{
            $sql = " insert into {$g5['board_file_table']}
                    set bo_table = 'complain_add',
                         wr_id = '{$complain_idx}',
                         bf_no = '{$i}',
                         bf_source = '{$upload_add[$i]['source']}',
                         bf_file = '{$upload_add[$i]['file']}',
                         bf_fileurl = '{$upload_add[$i]['fileurl']}',
                         bf_thumburl = '{$upload_add[$i]['thumburl']}',
                         bf_storage = '{$upload_add[$i]['storage']}',
                         bf_download = 0,
                         bf_filesize = '".(int)$upload_add[$i]['filesize']."',
                         bf_width = '".$bf_width."',
                         bf_height = '".$bf_height."',
                         bf_type = '".$bf_type."',
                         bf_datetime = '".G5_TIME_YMDHIS."' ";
        }

        sql_query($sql);
    }
}

if($w == "u"){
    echo result_data(true, "민원 수정이 완료되었습니다.", $complain_idx);
}else{
    echo result_data(true, "민원 등록이 완료되었습니다.", $complain_idx);
}
?>