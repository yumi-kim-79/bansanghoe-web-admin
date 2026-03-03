<?php
require_once './_common.php';

$today = date("Y-m-d H:i:s");

//die(result_data(false, "", $_POST));
if($post_id == "") die(result_data(false, "지역을 선택해주세요.", 'post_id'));
if($building_id == "") die(result_data(false, "단지를 선택해주세요.", 'building_id'));
if($dong_id == "") die(result_data(false, "동을 선택해주세요.", 'dong_id'));
if($ex_department == "") die(result_data(false, "부서를 선택해주세요.", 'ex_department'));
if($ex_name == "") die(result_data(false, "작성자를 입력해주세요.", 'ex_name'));
if($ex_grade == "") die(result_data(false, "직급을 입력해주세요.", 'ex_grade'));
if($ex_title == "") die(result_data(false, "품의서 제목을 입력해주세요.", 'ex_title'));
if($ex_approver1 == "") die(result_data(false, "첫번째 관리단 결재자를 선택해주세요.", 'ex_approver1'));
// if($ex_approver2 == "") die(result_data(false, "관리단 결재자를 선택해주세요.", 'ex_approver2'));
// if($ex_approver3 == "") die(result_data(false, "관리단 결재자를 선택해주세요.", 'ex_approver3'));

//중간결재자 패싱 후 최종결재자 선택할 때 막기
if($ex_approver3 != "" && $ex_approver2 == "") die(result_data(false, "중간 결재자를 선택 후 최종 결재자를 선택하세요.", 'ex_approver2'));
// die(result_data(false, $_POST, $_FILES));

if($w == "u"){


    //수정
    $update_query = "UPDATE a_expense_report SET
                        ex_department = '{$ex_department}',
                        ex_name = '{$ex_name}',
                        ex_grade = '{$ex_grade}',
                        ex_title = '{$ex_title}',
                        ex_approver1 = '{$ex_approver1}',
                        ex_approver2 = '{$ex_approver2}',
                        ex_approver3 = '{$ex_approver3}',
                        ex_content = '{$ex_content}'
                        WHERE ex_id = '{$ex_id}'";

    sql_query($update_query);
    //die(result_data(false, $update_query, []));

}else{

    $sql_chk = "";

    //2차 결재자 선택 안하면 결재체크상태로
    if($ex_approver2 == ""){
        $sql_chk .= " ex_apprval2_chk = 1, ";
    }

    //3차 결재자 선택 안하면 결재체크상태로
    if($ex_approver3 == ""){
        $sql_chk .= " ex_apprval3_chk = 1, ";
    }

    $insert_query = "INSERT INTO a_expense_report SET
                        post_id = '{$post_id}',
                        building_id = '{$building_id}',
                        dong_id = '{$dong_id}',
                        ex_department = '{$ex_department}',
                        ex_name = '{$ex_name}',
                        ex_grade = '{$ex_grade}',
                        ex_title = '{$ex_title}',
                        ex_approver1 = '{$ex_approver1}',
                        ex_approver2 = '{$ex_approver2}',
                        ex_approver3 = '{$ex_approver3}',
                        {$sql_chk}
                        ex_content = '{$ex_content}',
                        created_at = '{$today}',
                        wid = '{$member['mb_id']}'";
    
    sql_query($insert_query);
    $ex_id = sql_insert_id(); //팝업 idx


    //1차 결재자 푸시발송
    if($ex_approver1 != ""){
        //1차 결재자 정보 가져옴
        $ex_approver1_info = get_user($ex_approver1);

       
        $ex_department_name = get_department_name($ex_department);  //부서정보
        $ex_grade_name = get_mng_grade_name($ex_grade); //직급정보

        $push_title = "[품의서] 품의서가 등록되었습니다.";
        $push_content = "제목 : {$ex_title}\n작성자 : {$ex_name}\n부서 : {$ex_department_name}\n직급 : {$ex_grade}\n\n결재를 진행해주세요.";

        //푸시발송
        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'user',
                        recv_id = '{$ex_approver1}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$member['mb_id']}',
                        push_type = 'expense',
                        push_idx = '{$ex_id}',
                        created_at = '{$today}'";
        sql_query($insert_push);


        if($ex_approver1_info['mb_token'] != "" && $ex_approver1_info['noti6']){ //토큰이 있는경우 푸시 발송
            fcm_send($ex_approver1_info['mb_token'], $push_title, $push_content, 'expense', "{$ex_id}", "/expense_report_adm_info.php?ex_id=");
        }
        
    }
}


//품의서 파일(이미지) 첨부
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

//파일첨부 경로
$file_path = G5_DATA_PATH.'/file/expense';

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir($file_path, G5_DIR_PERMISSION);
@chmod($file_path, G5_DIR_PERMISSION);

$upload = array();

if(isset($_FILES['expense_file']['name'])){
    for ($i=0; $i<count($_FILES['expense_file']['name']); $i++) {
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
        if (isset($_POST['ex_file_del'][$i]) && $_POST['ex_file_del'][$i]) {
            $upload[$i]['del_check'] = true;

            $row = sql_fetch(" select * from {$g5['board_file_table']} where bo_table = 'expense' and wr_id = '{$ex_id}' and bf_no = '{$i}' ");

            $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/expense/'.str_replace('../', '', $row['bf_file']), $row);

            if( file_exists($delete_file) ){
                @unlink($delete_file);
            }
        }else{
            $upload[$i]['del_check'] = false;
        }

        $tmp_file  = $_FILES['expense_file']['tmp_name'][$i];
        $filesize  = $_FILES['expense_file']['size'][$i];
        $filename  = $_FILES['expense_file']['name'][$i];
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

            $exif = @exif_read_data($tmp_file);

            if($timg[0] >= 720){

                if(!empty($exif['Orientation'])) {

                    $upload[$i]['exif'] = 'Y';

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
                    $upload[$i]['exif'] = 'N';
                    if(move_uploaded_file($tmp_file, $dest_file)){
                        resizeImage($dest_file, 720, 720);
    
                        $upload[$i]['image'] = @getimagesize($dest_file); //이미지 축소된 정보로 다시 저장
                    }else{
                        //alert($_FILES['img_up']['error'][$i]);
                        die(result_data(false, $_FILES['expense_file']['error'][$i], []));
                    }
                }

            }else{
                $error_code = move_uploaded_file($tmp_file, $dest_file) or die(result_data(false, $_FILES['expense_file']['error'][$i], []));
            }
        }
    }
}


// die(result_data(false, '', $upload));


for ($i=0; $i<count($upload); $i++)
{
    $upload[$i]['source'] = sql_real_escape_string($upload[$i]['source']);
    $bf_width = isset($upload[$i]['image'][0]) ? (int) $upload[$i]['image'][0] : 0;
    $bf_height = isset($upload[$i]['image'][1]) ? (int) $upload[$i]['image'][1] : 0;
    $bf_type = isset($upload[$i]['image'][2]) ? (int) $upload[$i]['image'][2] : 0;

    if ($upload[$i]['del_check']){

        $del = "DELETE FROM g5_board_file WHERE bo_table = 'expense' and bf_no = '{$i}' and wr_id = '{$ex_id}'";
        sql_query($del);

        // $file_chk = "SELECT * FROM g5_board_file WHERE bo_table = 'expense' and wr_id = '{$ex_id}' and bf_no > {$i}";
        // $file_chk_res = sql_query($file_chk);
        // $file_chk_total = sql_num_rows($file_chk_res);

        // if($file_chk_total > 0){
        //     for($j=0;$file_chk_row = sql_fetch_array($file_chk_res);$j++){

        //         $bf_no_new = $file_chk_row['bf_no'] - 1;
        //         $up_bf_no = "UPDATE g5_board_file SET bf_no = '{$bf_no_new}' WHERE bo_table = 'expense' and wr_id = '{$ex_id}' and bf_no = '{$file_chk_row['bf_no']}'";

        //         sql_query($up_bf_no);
        //     }
        // }
    }

    if($upload[$i]['source'] != "blob"){
        $file_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table = 'expense' and wr_id = '{$ex_id}' and bf_no = '{$i}'");

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
                         WHERE bo_table = 'expense' and wr_id = '{$ex_id}' and bf_no = '{$i}'";
        }else{
            $sql = " insert into {$g5['board_file_table']}
                    set bo_table = 'expense',
                         wr_id = '{$ex_id}',
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

//bf_no 재정렬

if($w == 'u'){
    $sql_select = "SELECT bf_no FROM g5_board_file WHERE bo_table = 'expense' AND wr_id = '{$ex_id}' ORDER BY bf_no ASC";
    $result = sql_query($sql_select);

    $index = 0;

    $del_arr = array();
    while ($row = sql_fetch_array($result)) {
        $bf_no = $row['bf_no'];

        $bf_no_up = "UPDATE g5_board_file SET bf_no = $index WHERE bo_table = 'expense' AND wr_id = '{$ex_id}' and bf_no = $bf_no";
        sql_query($bf_no_up);
        //array_push($del_arr, $bf_no_up);
        //sql_query("UPDATE g5_board_file SET bf_no = $index WHERE bf_no = $bf_no");
        $index++;
    }
}


//die(result_data(false, $insert_query, $del_arr));

if($w == "u"){
    $msg = '품의서가 수정되었습니다.';
}else{
    $msg = '품의서가 등록되었습니다.';
}

echo result_data(true, $msg, $ex_id);