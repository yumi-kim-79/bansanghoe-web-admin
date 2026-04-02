<?php
require_once './_common.php';

$today = date("Y-m-d H:i:s");

//결재서류명
$approval_name = approval_category_name($sign_off_category);

if($w == "u"){

    //수정시 정보
    $sign_info = sql_fetch("SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'");

    //2차 결재자 선택 안하면 결재체크상태로
    $sql_chk = "";
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
    
        if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
            if($sign_off_id_info['mb_token'] != "" && $sign_off_id_info['noti1']){ //토큰이 있는경우 푸시 발송
                try {
                    fcm_send($sign_off_id_info['mb_token'], $push_title, $push_content, "sign_off", "{$sign_id}", "/holiday_reqeust_info.php?mng=Y&sign_id=");
                } catch(Exception $e) {
                    // FCM 오류 무시하고 계속 진행
                }
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

    $update_query = "UPDATE a_sign_off SET
                        sign_off_mng_id1 = '{$sign_off_mng_id1}',
                        sign_off_mng_id2 = '{$sign_off_mng_id2}',
                        sign_off_mng_id3 = '{$sign_off_mng_id3}',
                        {$sql_chk}
                        sign_off_memo = '{$sign_off_memo}'
                        WHERE sign_id = '{$sign_id}'";
    sql_query($update_query);

}else{

    if($approval_signature == '') die(result_data(false, "서명을 입력해주세요.", []));

    $sql_common = " mng_department = '{$mng_department}',
                        mng_grade = '{$mng_grade}',
                        sign_off_mng_id1 = '{$sign_off_mng_id1}',
                        sign_off_mng_id2 = '{$sign_off_mng_id2}',
                        sign_off_mng_id3 = '{$sign_off_mng_id3}',
                        sign_off_memo = '{$sign_off_memo}', 
                        wdate = '{$wdate}', ";

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

    $insert_query = "INSERT INTO a_sign_off SET
                        sign_off_category = '{$sign_off_category}',
                        mng_id = '{$wid}',
                        {$sql_common}
                        {$sql_chk}
                        created_at = '{$today}'";

    sql_query($insert_query);
    $sign_id = sql_insert_id(); //팝업 idx


    //1차 결재권자 푸시발송
    if($sign_off_mng_id1 != ""){
        
        $sign_off_id_info = get_member($sign_off_mng_id1); //1차 결재자 정보

        $sign_off_id = $sign_off_id_info['mb_id']; //1차 결재자 아이디

        $push_title = '[결재요청] '.$approval_name." 결재 요청이 있습니다.";
        $push_content = $wname.'님의 '.$approval_name." 결재 요청이 있습니다.";
    
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

            if($sign_off_id_info['mb_token'] != "" && $sign_off_id_info['noti1']){ //토큰이 있는경우 푸시 발송
                try {
                    fcm_send($sign_off_id_info['mb_token'], $push_title, $push_content, "sign_off", "{$sign_id}", "/holiday_reqeust_info.php?mng=Y&sign_id=");
                } catch(Exception $e) {
                    // FCM 오류 무시하고 계속 진행
                }
            }
        }
    }
}

//파일(이미지) 첨부
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

//생성시 이미지 파일첨부 경로
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

            $upload[$i]['file'] = md5(sha1($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.replace_filename($filename);

            $dest_file = $file_path.'/'.$upload[$i]['file'];

            if($timg[0] >= 720){

                if(move_uploaded_file($tmp_file, $dest_file)){
                    resizeImage($dest_file, 720, 720);

                    $upload[$i]['image'] = @getimagesize($dest_file);
                }else{
                    die(result_data(false, $_FILES['approval_file']['error'][$i], []));
                }

            }else{
                $error_code = move_uploaded_file($tmp_file, $dest_file) or die(result_data(false, $_FILES['approval_file']['error'][$i], []));
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

if($w == "u"){
    $msg = $approval_name.'가 수정되었습니다.';
}else{
    $msg = $approval_name.'가 등록되었습니다.';
}

echo result_data(true, $msg, $sign_id);
