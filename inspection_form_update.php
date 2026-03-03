<?php
require_once "./_common.php";

function isValidPhoneNumber($phone) {
    return preg_match('/^010-\d{4}-\d{4}$/', $phone);
}

if($inspection_category == "") die(result_data(false, "업종이 선택되지 않았습니다. 다시 시도해주세요.", []));
if($inspection_cmp == "") die(result_data(false, "업체가 선택되지 않았습니다. 다시 시도해주세요.", []));
if($building_id == "") die(result_data(false, "단지가 선택되지 않았습니다. 다시 시도해주세요.", []));
if($inspection_name == "") die(result_data(false, "작성자를 입력해주세요.", []));
if($inspection_hp == "") die(result_data(false, "연락처를 입력해주세요.", []));
if (!isValidPhoneNumber($inspection_hp)) die(result_data(false, "연락처를 올바른 형식으로 입력해주세요. ex)010-1111-1111", "visit_hp"));
if($inspection_year == "") die(result_data(false, "점검 연도가 설정되지 않았습니다. 다시 시도해주세요.", []));
if($inspection_month == "") die(result_data(false, "점검 연도가 설정되지 않았습니다. 다시 시도해주세요.", []));
if($inspection_title == "") die(result_data(false, "제목을 입력해주세요.", []));

$today = date("Y-m-d H:i:s");

if($w == "u"){

    if($inspection_idx == "") die(result_data(false, "잘못된 접근입니다. 다시 시도해주세요.", []));

    $update_query = "UPDATE a_inspection SET
                        inspection_name = '{$inspection_name}',
                        inspection_hp = '{$inspection_hp}',
                        inspection_status = 'N',
                        inspection_title = '{$inspection_title}',
                        inspection_memo = '{$inspection_memo}'
                        WHERE inspection_idx = '{$inspection_idx}'";
   // die(result_data(false, $update_query, []));
    sql_query($update_query);

}else{

    // dong_id = '{$dong_id}',
    $insert_query = "INSERT INTO a_inspection SET
                        building_id = '{$building_id}',
                        inspection_category = '{$inspection_category}',
                        inspection_cmp = '{$inspection_cmp}',
                        inspection_name = '{$inspection_name}',
                        inspection_hp = '{$inspection_hp}',
                        inspection_year = '{$inspection_year}',
                        inspection_month = '{$inspection_month}',
                        inspection_status = 'N',
                        inspection_title = '{$inspection_title}',
                        inspection_memo = '{$inspection_memo}',
                        created_at = '{$today}'";
    sql_query($insert_query);
    $inspection_idx = sql_insert_id(); //점검일지 idx


    //푸시발송 단지 관리자에게
    $building_info = get_builiding_info($building_id); //단지 정보
    $building_name = $building_info['building_name']; //단지명

    $industry_info = get_industry_info($inspection_category); //업종 정보
    $industry_name = $industry_info['industry_name']; //업종명

    $mng_builindg_sql = "SELECT mng.*, mb.mb_token FROM a_mng_building as mng
                        LEFT JOIN g5_member as mb ON mng.mb_id = mb.mb_id
                        WHERE mng.building_id = '{$building_id}' and mng.is_del = 0 GROUP BY mng.mb_id ORDER BY mng.mng_id desc";
    $mng_building_res = sql_query($mng_builindg_sql);

    while($mng_row = sql_fetch_array($mng_building_res)){
        $push_title = '[점검일지] '.$building_name." ".$industry_name." 점검일지가 작성되었습니다.";
        $push_content = $inspection_name.'님의 '.$building_name." ".$industry_name." 점검일지가 작성되었습니다.";

        if($mng_row['mb_token'] != ""){ //토큰이 있는경우 푸시 발송
            fcm_send($mng_row['mb_token'], $push_title, $push_content, 'inspection', $inspection_idx);
        }

        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'sm',
                        recv_id = '{$mng_row['mb_id']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        push_type = 'inspection',
                        push_idx = '{$inspection_idx}',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }
}

//점검일지 파일(이미지) 첨부
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

//점검일지 이미지 파일첨부 경로
$file_path = G5_DATA_PATH.'/file/inspection';

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir($file_path, G5_DIR_PERMISSION);
@chmod($file_path, G5_DIR_PERMISSION);

$upload = array();

if(isset($_FILES['inspection_file']['name'])){
    for ($i=0; $i<count($_FILES['inspection_file']['name']); $i++) {
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

        if (isset($_POST['inspection_file_del'][$i]) && $_POST['inspection_file_del'][$i]) {
            $upload[$i]['del_check'] = true;

            $row = sql_fetch(" select * from {$g5['board_file_table']} where bo_table = 'inspection' and wr_id = '{$inspection_idx}' and bf_no = '{$i}' ");

            $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/inspection/'.str_replace('../', '', $row['bf_file']), $row);

            if( file_exists($delete_file) ){
                @unlink($delete_file);
            }
        }else{
            $upload[$i]['del_check'] = false;
        }

        $tmp_file  = $_FILES['inspection_file']['tmp_name'][$i];
        $filesize  = $_FILES['inspection_file']['size'][$i];
        $filename  = $_FILES['inspection_file']['name'][$i];
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
                    // if(move_uploaded_file($tmp_file, $dest_file)){
                    //     resizeImage($dest_file, 720, 720);
    
                    //     $upload[$i]['image'] = @getimagesize($dest_file); //이미지 축소된 정보로 다시 저장
                    // }else{
                    //     //alert($_FILES['img_up']['error'][$i]);
                    //     die(result_data(false, $_FILES['inspection_file']['error'][$i], []));
                    // }
                    $error_code = move_uploaded_file($tmp_file, $dest_file) or die(result_data(false, $_FILES['inspection_file']['error'][$i], []));
                }

            }else{
                $error_code = move_uploaded_file($tmp_file, $dest_file) or die(result_data(false, $_FILES['inspection_file']['error'][$i], []));
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

        $del = "DELETE FROM g5_board_file WHERE bo_table = 'inspection' and bf_no = '{$i}' and wr_id = '{$inspection_idx}'";
        sql_query($del);

        $file_chk = "SELECT * FROM g5_board_file WHERE bo_table = 'inspection' and wr_id = '{$inspection_idx}' and bf_no > {$i}";
        $file_chk_res = sql_query($file_chk);
        $file_chk_total = sql_num_rows($file_chk_res);

        if($file_chk_total > 0){
            for($j=0;$file_chk_row = sql_fetch_array($file_chk_res);$j++){

                $bf_no_new = $file_chk_row['bf_no'] - 1;
                $up_bf_no = "UPDATE g5_board_file SET bf_no = '{$bf_no_new}' WHERE bo_table = 'inspection' and wr_id = '{$inspection_idx}' and bf_no = '{$file_chk_row['bf_no']}'";

                sql_query($up_bf_no);
            }
        }
    }

    if($upload[$i]['source'] != "blob"){
        $file_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table = 'inspection' and wr_id = '{$inspection_idx}' and bf_no = '{$i}'");

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
                         WHERE bo_table = 'inspection' and wr_id = '{$inspection_idx}' and bf_no = '{$i}'";
        }else{
            $sql = " insert into {$g5['board_file_table']}
                    set bo_table = 'inspection',
                         wr_id = '{$inspection_idx}',
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
    echo result_data(true, "점검일지 수정이 완료되었습니다.", []);
}else{
    echo result_data(true, "점검일지 작성이 완료되었습니다.", []);
}