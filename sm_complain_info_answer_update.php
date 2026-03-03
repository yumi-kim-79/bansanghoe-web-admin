<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

$msg = "민원 답변이 등록되었습니다.";
$sql_status = "";
if($complain_status == "CD"){ //완료시 완료시간 표시

    //완료 처리시에는 처리결과 입력
    if($complain_answer == "") die(result_data(false, "처리결과를 입력해주세요.", []));

    $sql_status = " ,
                    complain_status = '{$complain_status}',
                    edate = '{$today}' ";

    $msg = "민원이 완료처리 되었습니다.";
}

//답변 추가
$update_complain = "UPDATE a_online_complain SET
                    complain_answer = '{$complain_answer}',
                    complain_memo = '{$complain_memo}'
                    {$sql_status}
                    WHERE complain_idx = '{$complain_idx}'";
sql_query($update_complain);

//작성자 정보가 있고 완료처리인 경우
if($complain_id != "" && $complain_status == "CD"){
    //민원 작성자에게 푸시 발송
    $mem_sql = "SELECT * FROM a_member WHERE mb_id = '{$complain_id}'";
    $mem_row = sql_fetch($mem_sql);

    $push_title = '[민원 처리 완료] 민원 처리가 완료되었습니다.';
    $push_content = '민원 처리가 완료되었습니다. 확인 부탁드립니다.';

    if($mem_row['mb_token'] != "" && $mem_row['noti3']){ //토큰이 있는경우 푸시 발송
           
        fcm_send($mem_row['mb_token'], $push_title, $push_content, 'complain_end', "{$complain_idx}", "/online_complain_info.php?complain_status=CD&complain_idx=");
    }

    $insert_push = "INSERT INTO a_push SET
                    recv_id_type = 'user',
                    recv_id = '{$complain_id}',
                    push_title = '{$push_title}',
                    push_content = '{$push_content}',
                    wid = '{$wid}',
                    push_type = 'complain_end',
                    push_idx = '{$complain_idx}',
                    created_at = '{$today}'";
    sql_query($insert_push);
}

//민원 파일(이미지) 첨부
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

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

                    if($timg[2] == 1) imagegif($cfile, $dest_file2, 100);
                    else if($timg[2] == 2) imagejpeg($cfile, $dest_file2, 100);
                    else if($timg[2] == 3) imagepng($cfile, $dest_file2, 9);
            
                    chmod($dest_file2, G5_FILE_PERMISSION);
                    imagedestroy($cfile);
    
                    resizeImage($dest_file2, 720, 720);

                    $upload[$i]['image'] = @getimagesize($dest_file2); //이미지 축소된 정보로 다시 저장
                }else{

                    $error_code = move_uploaded_file($tmp_file, $dest_file2) or die(result_data(false, $_FILES['answer_file']['error'][$i], []));

                    // if(move_uploaded_file($tmp_file, $dest_file2)){
                    //     resizeImage($dest_file2, 720, 720);
    
                    //     $upload_a[$i]['image'] = @getimagesize($dest_file2); //이미지 축소된 정보로 다시 저장
                    // }else{
                    //     //alert($_FILES['img_up']['error'][$i]);
                    //     die(result_data(false, $_FILES['answer_file']['error'][$i], []));
                    // }
                }

                

            }else{
                $error_code = move_uploaded_file($tmp_file, $dest_file2) or die(result_data(false, $_FILES['answer_file']['error'][$i], []));
            }
        }
    }
}

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

                    if($timg[2] == 1) imagegif($cfile, $dest_file3, 100);
                    else if($timg[2] == 2) imagejpeg($cfile, $dest_file3, 100);
                    else if($timg[2] == 3) imagepng($cfile, $dest_file3, 9);
            
                    chmod($dest_file3, G5_FILE_PERMISSION);
                    imagedestroy($cfile);
    
                    resizeImage($dest_file3, 720, 720);

                    $upload[$i]['image'] = @getimagesize($dest_file3); //이미지 축소된 정보로 다시 저장
                }else{
                    
                    $error_code = move_uploaded_file($tmp_file, $dest_file3) or die(result_data(false, $_FILES['answer_add_file']['error'][$i], []));
                }

            }else{
                $error_code = move_uploaded_file($tmp_file, $dest_file3) or die(result_data(false, $_FILES['answer_add_file']['error'][$i], []));
            }
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

echo result_data(true, $msg, $complain_status);
//die(result_data(false, $upload_add, $_FILES));