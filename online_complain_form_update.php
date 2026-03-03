<?php
require_once "./_common.php";

if($complain_title == "") die(result_data(false, "민원 제목을 입력해주세요.", []));
if($complain_content == "") die(result_data(false, "민원 내용을 입력해주세요.", []));

$today = date("Y-m-d H:i:s");
$today_at = date("Y-m-d");
$ip_info = $_SERVER['REMOTE_ADDR'];

$users = get_user($mb_id);

if($w == "u"){

    $update_query = "UPDATE a_online_complain SET
                        complain_title = '{$complain_title}',
                        complain_content = '{$complain_content}'
                        WHERE complain_idx = {$complain_idx}";

    sql_query($update_query);

}else{

    $sql_ho = "SELECT * FROM a_building_ho WHERE ho_tenant_hp = '{$users['mb_hp']}'";
    $row_ho = sql_fetch($sql_ho);

    $complain_sql = "INSERT INTO a_online_complain SET
                        complain_type = 'user',
                        post_id = '{$post_id}',
                        building_id = '{$building_id}',
                        dong_id = '{$dong_id}',
                        ho_id = '{$ho_id}',
                        complain_name = '{$users['mb_name']}',
                        complain_hp = '{$users['mb_hp']}',
                        complain_id = '{$mb_id}',
                        wname = '{$users['mb_name']}',
                        complain_status = 'CB',
                        complain_title = '{$complain_title}',
                        complain_content = '{$complain_content}',
                        complain_ip = '{$ip_info}',
                        wdate = '{$today_at}',
                        created_at = '{$today}'";
    sql_query($complain_sql);
    $complain_idx = sql_insert_id(); //팝업 idx


    if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
        $wheres = " and mng.mng_id = 'mng1' ";
    }

    //sm 매니저 전직원에게 푸시 발송
    if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
        $mng_sql = "SELECT mng.*, mb.mb_token, mb.noti1, mb.noti2, mb.noti3, mb.noti4, mb.noti5, mb.noti6 FROM a_mng as mng
                    LEFT JOIN g5_member as mb ON mng.mng_id = mb.mb_id
                    WHERE mng.is_del = 0 {$wheres} ORDER BY mng.mng_idx desc";
        $mng_res = sql_query($mng_sql);
        
        while($mng_row = sql_fetch_array($mng_res)){

            $push_title = '[민원신청] '.$complain_title." 민원신청이 있습니다.";
            $push_content = $users['mb_name'].'님의 '.$complain_title." 민원신청이 있습니다.";

            if($mng_row['mb_token'] != "" && $mng_row['noti6']){ //토큰이 있는경우 푸시 발송
            
                fcm_send($mng_row['mb_token'], $push_title, $push_content, 'complain', "{$complain_idx}", "/sm_complain_info.php?complain_status=CD&complain_idx=");
            }

            
            $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'sm',
                        recv_id = '{$mng_row['mng_id']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$users['mb_id']}',
                        push_type = 'complain',
                        push_idx = '{$complain_idx}',
                        created_at = '{$today}'";
            sql_query($insert_push);
        }
    }
}


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

            if($timg[0] >= 720){

                // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) die(result_data(false, $exif['Orientation'] ? '1' : '0', $exif));

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

                    if($timg[2] == 1) imagegif($cfile, $dest_file);
                    else if($timg[2] == 2) imagejpeg($cfile, $dest_file, 100);
                    else if($timg[2] == 3) imagepng($cfile, $dest_file, 9);
            
                    chmod($dest_file, G5_FILE_PERMISSION);
                    imagedestroy($cfile);
    
                    resizeImage($dest_file, 720, 720);

                    $upload[$i]['image'] = @getimagesize($dest_file); //이미지 축소된 정보로 다시 저장
                }else{
                    $error_code = move_uploaded_file($tmp_file, $dest_file) or die(result_data(false, $_FILES['complain_file']['error'][$i], []));
                }

            }else{
                $error_code = move_uploaded_file($tmp_file, $dest_file) or die(result_data(false, $_FILES['complain_file']['error'][$i], []));
            }
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

if($w == "u"){
    echo result_data(true, "온라인민원이 수정되었습니다.", []);
}else{
    echo result_data(true, "온라인민원이 접수되었습니다.", []);
}