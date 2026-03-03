<?php
require_once "./_common.php";

//die(result_data(false, $_POST, $_FILES));

$today = date("Y-m-d H:i:s");
$bbs_setting = sql_fetch("SELECT bbs_id, bbs_code, bbs_title FROM a_bbs_setting WHERE bbs_code = '{$bbs_code}'");

if($bbs_code == "") die(result_data(false, "올바른 접근이 아닙니다.", []));
if($bbs_title == "") die(result_data(false, "제목을 입력해주세요.", []));
if($bbs_content == "") die(result_data(false, "내용을 입력해주세요.", []));

if($w == "u"){

}else{

    $insert_query = "INSERT INTO a_bbs SET
                    bbs_code = '{$bbs_code}',
                    bbs_title = '{$bbs_title}',
                    bbs_content = '{$bbs_content}',
                    is_view = '1',
                    wid = '{$mb_id}',
                    created_at = '{$today}'";
    sql_query($insert_query);
    $bbs_idx = sql_insert_id(); //게시판 idx

    //작성자를 제외한 모든 매니저에게 푸시 발송
    $category_info = get_bbs_category($bbs_code);
    $category_name = $category_info['bbs_title'];

    $sql_w = '';
    if($bbs_code == 'etc5'){ //기타5일 때 최고관리자와 팀장급에게만 발송
        $sql_w = " and mng.mng_certi IN ('A', 'B') ";
    }

    //and mng.mng_id != '{$mb_id}' 
    $mng_sql = "SELECT mng.*, mb.mb_token, mb.noti2 FROM a_mng as mng
                LEFT JOIN g5_member as mb ON mng.mng_id = mb.mb_id
                WHERE mng.is_del = 0 {$sql_w} ORDER BY mng.mng_idx desc";
    $mng_res = sql_query($mng_sql);

    while($mng_row = sql_fetch_array($mng_res)){

        $push_title = '['.$category_name.' 게시판] 게시글이 등록되었습니다.';
        $push_content = $category_name." 게시판에 게시글이 등록되었습니다.";

        if($mng_row['mb_token'] != "" && $mng_row['noti2']){ //토큰이 있는경우 푸시 발송
            
            fcm_send($mng_row['mb_token'], $push_title, $push_content, 'bbs', $bbs_idx);
        }

        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'sm',
                        recv_id = '{$mng_row['mng_id']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$mb_id}',
                        push_type = 'bbs',
                        push_idx = '{$bbs_idx}',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }
}

//이미지 첨부
//파일 첨부
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

//문항 생성시 이미지 파일첨부 경로
$file_path = G5_DATA_PATH.'/file/bbs_img';

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir($file_path, G5_DIR_PERMISSION);
@chmod($file_path, G5_DIR_PERMISSION);

$upload = array();

if(isset($_FILES['img_up']['name'])){
    for ($i=0; $i<count($_FILES['img_up']['name']); $i++) {
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

        $tmp_file  = $_FILES['img_up']['tmp_name'][$i];
        $filesize  = $_FILES['img_up']['size'][$i];
        $filename  = $_FILES['img_up']['name'][$i];
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
                        die(result_data(false, $_FILES['img_up']['error'][$i], []));
                    }
                }

            }else{
                $error_code = move_uploaded_file($tmp_file, $dest_file) or die(result_data(false, $_FILES['img_up']['error'][$i], []));
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

    if($upload[$i]['source'] != "blob"){
        $file_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table = 'bbs_img' and wr_id = '{$bbs_idx}' and bf_no = '{$i}'");

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
                         WHERE bo_table = 'bbs_img' and wr_id = '{$bbs_idx}' and bf_no = '{$i}'";
        }else{
            $sql = " insert into {$g5['board_file_table']}
                    set bo_table = 'bbs_img',
                         wr_id = '{$bbs_idx}',
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

        //array_push($sibal, $sql);
        sql_query($sql);
    }
}

//pdf 파일 첨부
$file_path2 = G5_DATA_PATH.'/file/bbs_pdf';

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir($file_path2, G5_DIR_PERMISSION);
@chmod($file_path2, G5_DIR_PERMISSION);

$upload_pdf = array();

if(isset($_FILES['bf_file']['name'])){
    for ($i=0; $i<count($_FILES['bf_file']['name']); $i++) {
        $upload_pdf[$i]['file']     = '';
        $upload_pdf[$i]['source']   = '';
        $upload_pdf[$i]['filesize'] = 0;
        $upload_pdf[$i]['image']    = array();
        $upload_pdf[$i]['image'][0] = 0;
        $upload_pdf[$i]['image'][1] = 0;
        $upload_pdf[$i]['image'][2] = 0;
        $upload_pdf[$i]['fileurl'] = '';
        $upload_pdf[$i]['thumburl'] = '';
        $upload_pdf[$i]['storage'] = '';

        $tmp_file  = $_FILES['bf_file']['tmp_name'][$i];
        $filesize  = $_FILES['bf_file']['size'][$i];
        $filename  = $_FILES['bf_file']['name'][$i];
        $filename  = get_safe_filename($filename);

        if (is_uploaded_file($tmp_file)) {

            $timg = @getimagesize($tmp_file);

            if ( preg_match("/\.({$config['cf_image_extension']})$/i", $filename) || preg_match("/\.({$config['cf_flash_extension']})$/i", $filename) ) {
                if ($timg['2'] < 1 || $timg['2'] > 18){
                    die(result_data(false, "등록할 수 있는 파일 유형이 아닙니다.", []));
                }
            }

            $upload_pdf[$i]['image'] = $timg;

            // 프로그램 원래 파일명
            $upload_pdf[$i]['source'] = $filename;
            $upload_pdf[$i]['filesize'] = $filesize;

            // 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
            $filename = preg_replace("/\.(php|pht|phtm|htm|cgi|pl|exe|jsp|asp|inc|phar)/i", "$0-x", $filename);

            shuffle($chars_array);
            $shuffle = implode('', $chars_array);

            // 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
            $upload_pdf[$i]['file'] = md5(sha1($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.replace_filename($filename);

            $dest_file = $file_path2.'/'.$upload_pdf[$i]['file'];

            $error_code = move_uploaded_file($tmp_file, $dest_file) or die(result_data(false, $_FILES['bf_file']['error'][$i], []));
        }
    }
}

for ($i=0; $i<count($upload_pdf); $i++)
{
    $upload_pdf[$i]['source'] = sql_real_escape_string($upload_pdf[$i]['source']);
    $bf_width = isset($upload_pdf[$i]['image'][0]) ? (int) $upload_pdf[$i]['image'][0] : 0;
    $bf_height = isset($upload_pdf[$i]['image'][1]) ? (int) $upload_pdf[$i]['image'][1] : 0;
    $bf_type = isset($upload_pdf[$i]['image'][2]) ? (int) $upload_pdf[$i]['image'][2] : 0;

    if($upload_pdf[$i]['source'] != "blob"){
        $file_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table = 'bbs_pdf' and wr_id = '{$bbs_idx}' and bf_no = '{$i}'");
        
        if($file_confirm['cnt'] > 0){
            $sql = " UPDATE {$g5['board_file_table']}
                    SET  bf_source = '{$upload_pdf[$i]['source']}',
                         bf_file = '{$upload_pdf[$i]['file']}',
                         bf_fileurl = '{$upload_pdf[$i]['fileurl']}',
                         bf_thumburl = '{$upload_pdf[$i]['thumburl']}',
                         bf_storage = '{$upload_pdf[$i]['storage']}',
                         bf_download = 0,
                         bf_filesize = '".(int)$upload_pdf[$i]['filesize']."',
                         bf_width = '".$bf_width."',
                         bf_height = '".$bf_height."',
                         bf_type = '".$bf_type."',
                         bf_datetime = '".G5_TIME_YMDHIS."' 
                         WHERE bo_table = 'bbs_pdf' and wr_id = '{$bbs_idx}' and bf_no = '{$i}'";
        }else{
            $sql = " insert into {$g5['board_file_table']}
                    set bo_table = 'bbs_pdf',
                         wr_id = '{$bbs_idx}',
                         bf_no = '{$i}',
                         bf_source = '{$upload_pdf[$i]['source']}',
                         bf_file = '{$upload_pdf[$i]['file']}',
                         bf_fileurl = '{$upload_pdf[$i]['fileurl']}',
                         bf_thumburl = '{$upload_pdf[$i]['thumburl']}',
                         bf_storage = '{$upload_pdf[$i]['storage']}',
                         bf_download = 0,
                         bf_filesize = '".(int)$upload_pdf[$i]['filesize']."',
                         bf_width = '".$bf_width."',
                         bf_height = '".$bf_height."',
                         bf_type = '".$bf_type."',
                         bf_datetime = '".G5_TIME_YMDHIS."' ";
        }
    }

    sql_query($sql);
}

echo result_data(true, $bbs_setting['bbs_title'].' 게시글이 등록되었습니다.', $bbs_setting);