<?php
$sub_menu = "200200";
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

$ip_info = $_SERVER['REMOTE_ADDR'];

// print_r2($_POST);

if($w == "u"){

    $use_sql = "";
    if($is_use == 0){
        $use_sql = " is_use = 0, 
                     not_use_at = '{$today}', ";
    }

    $update_query = "UPDATE a_building SET
                    building_name = '{$building_name}',
                    building_size = '{$building_size}',
                    post_id = '{$post_id}',
                    building_addr_zip = '{$building_addr_zip}',
                    building_addr = '{$building_addr}',
                    building_addr2 = '{$building_addr2}',
                    building_addr3 = '{$building_addr3}',
                    building_addr_jibeon = '{$building_addr_jibeon}',
                    {$use_sql}
                    building_bill_account = '{$building_bill_account}',
                    building_bill_account_bank = '{$building_bill_account_bank}',
                    building_bill_account_name = '{$building_bill_account_name}',
                    building_bill_notice = '{$building_bill_notice}',
                    open_password = '{$open_password}',
                    cctv_password = '{$cctv_password}',
                    building_owner = '{$building_owner}',
                    building_estate = '{$building_estate}',
                    building_company = '{$building_company}',
                    building_bigo = '{$building_bigo}',
                    building_memo = '{$building_memo}',
                    building_policy = '{$building_policy}',
                    ip_info = '{$ip_info}'
                    WHERE building_id = '{$building_id}'";

    //  echo $update_query.'<br>';

    // print_r2($_POST);
    // exit;
    sql_query($update_query);

    $del_sql = "";
    for($i=0;$i<count($dong_name);$i++){

        if($dong_del[$i]){
            $del_sql = " ,
                        is_del = 1,
                        deleted_at = '{$today}' ";
        }

        if($dong_id[$i] != ''){
            $update_dong = "UPDATE a_building_dong SET
                                dong_name = '{$dong_name[$i]}'
                                {$del_sql}
                                WHERE dong_id = '{$dong_id[$i]}'";
            // echo $update_dong.'<br>';
            sql_query($update_dong);
        }else{
            $insert_dong = "INSERT INTO a_building_dong SET
                        building_id = '{$building_id}',
                        dong_name = '{$dong_name[$i]}',
                        created_at = '{$today}',
                        ip_info = '{$ip_info}'";
            sql_query($insert_dong);
        }
        
    }

    
    
}else if($w == 'a'){

    //해지에서 다시 등록할때
    $use_sql = "";
    if($is_use == 1){
        $use_sql = " is_use = 1, 
                     not_use_at = '', ";
    }

    $update_query = "UPDATE a_building SET
                    building_name = '{$building_name}',
                    building_size = '{$building_size}',
                    post_id = '{$post_id}',
                    building_addr_zip = '{$building_addr_zip}',
                    building_addr = '{$building_addr}',
                    building_addr2 = '{$building_addr2}',
                    building_addr3 = '{$building_addr3}',
                    building_addr_jibeon = '{$building_addr_jibeon}',
                    {$use_sql}
                    building_bill_account = '{$building_bill_account}',
                    building_bill_account_bank = '{$building_bill_account_bank}',
                    building_bill_account_name = '{$building_bill_account_name}',
                    building_bill_notice = '{$building_bill_notice}',
                    open_password = '{$open_password}',
                    cctv_password = '{$cctv_password}',
                    building_owner = '{$building_owner}',
                    building_estate = '{$building_estate}',
                    building_company = '{$building_company}',
                    building_bigo = '{$building_bigo}',
                    building_memo = '{$building_memo}',
                    building_policy = '{$building_policy}',
                    ip_info = '{$ip_info}'
                    WHERE building_id = '{$building_id}'";

    //  echo $update_query.'<br>';

    // print_r2($_POST);
    // exit;
    sql_query($update_query);

    $del_sql = "";
    for($i=0;$i<count($dong_name);$i++){

        if($dong_del[$i]){
            $del_sql = " ,
                        is_del = 1,
                        deleted_at = '{$today}' ";
        }

        if($dong_id[$i] != ''){
            $update_dong = "UPDATE a_building_dong SET
                                dong_name = '{$dong_name[$i]}'
                                {$del_sql}
                                WHERE dong_id = '{$dong_id[$i]}'";
            // echo $update_dong.'<br>';
            sql_query($update_dong);
        }else{
            $insert_dong = "INSERT INTO a_building_dong SET
                        building_id = '{$building_id}',
                        dong_name = '{$dong_name[$i]}',
                        created_at = '{$today}',
                        ip_info = '{$ip_info}'";
            sql_query($insert_dong);
        }
        
    }
    

}else{
    
    
    $insert_query = "INSERT INTO a_building SET
                    building_name = '{$building_name}',
                    building_size = '{$building_size}',
                    post_id = '{$post_id}',
                    building_addr_zip = '{$building_addr_zip}',
                    building_addr = '{$building_addr}',
                    building_addr2 = '{$building_addr2}',
                    building_addr3 = '{$building_addr3}',
                    building_addr_jibeon = '{$building_addr_jibeon}',
                    is_use = '1',
                    building_bill_account = '{$building_bill_account}',
                    building_bill_account_bank = '{$building_bill_account_bank}',
                    building_bill_account_name = '{$building_bill_account_name}',
                    building_bill_notice = '{$building_bill_notice}',
                    open_password = '{$open_password}',
                    cctv_password = '{$cctv_password}',
                    building_owner = '{$building_owner}',
                    building_estate = '{$building_estate}',
                    building_company = '{$building_company}',
                    building_bigo = '{$building_bigo}',
                    building_memo = '{$building_memo}',
                    building_policy = '{$building_policy}',
                    ip_info = '{$ip_info}',
                    wid = '{$member['mb_id']}',
                    created_at = '{$today}'";
        
    // echo $insert_query.'<br>';
    // exit;
    sql_query($insert_query);
    $building_id = sql_insert_id(); //팝업 idx


    //단지 동 추가
    if($dong_name != ""){
        for($i=0;$i<count($dong_name);$i++){

            $insert_dong = "INSERT INTO a_building_dong SET
                                building_id = '{$building_id}',
                                dong_name = '{$dong_name[$i]}',
                                created_at = '{$today}',
                                ip_info = '{$ip_info}'";
            sql_query($insert_dong);
        }
    }

}

if($building_info_addr1 != ""){
    $info_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_info WHERE building_id = '{$building_id}'");

    if($info_confirm['cnt'] > 0){
        $update_info = "UPDATE a_building_info SET
                         building_info_name = '{$building_info_name}',
                         building_info_type = '{$building_info_type}',
                         building_info_addr1 = '{$building_info_addr1}',
                         building_info_addr2 = '{$building_info_addr2}',
                         building_info_size = '{$building_info_size}',
                         building_info_use_date = '{$building_info_use_date}',
                         building_info_floor_up = '{$building_info_floor_up}',
                         building_info_elevation = '{$building_info_elevation}',
                         building_info_parking1 = '{$building_info_parking1}',
                         building_info_parking2 = '{$building_info_parking2}',
                         building_info_structure = '{$building_info_structure}',
                         building_info_ho = '{$building_info_ho}'
                         WHERE building_id = '{$building_id}'";

        // echo $update_info.'<br>';

        // exit;

        sql_query($update_info);
    }else{
        $insert_info = "INSERT INTO a_building_info SET
                         building_id = '{$building_id}',
                         building_info_name = '{$building_info_name}',
                         building_info_type = '{$building_info_type}',
                         building_info_addr1 = '{$building_info_addr1}',
                         building_info_addr2 = '{$building_info_addr2}',
                         building_info_size = '{$building_info_size}',
                         building_info_use_date = '{$building_info_use_date}',
                         building_info_floor_up = '{$building_info_floor_up}',
                         building_info_elevation = '{$building_info_elevation}',
                         building_info_parking1 = '{$building_info_parking1}',
                         building_info_parking2 = '{$building_info_parking2}',
                         building_info_structure = '{$building_info_structure}',
                         building_info_ho = '{$building_info_ho}',
                         created_at = '{$today}',
                         ip_info = '{$ip_info}'";
        //echo $insert_info.'<br>';

        sql_query($insert_info);
    }

}

//파일 첨부
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

//문항 생성시 이미지 파일첨부 경로
$file_path = G5_DATA_PATH.'/file/building';

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir($file_path, G5_DIR_PERMISSION);
@chmod($file_path, G5_DIR_PERMISSION);

$upload = array();

if(isset($_FILES['bf_file']['name'])){
    for ($i=0; $i<count($_FILES['bf_file']['name']); $i++) {
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
        if (isset($_POST['pdf_file_del'][$i]) && $_POST['pdf_file_del'][$i]) {
            $upload[$i]['del_check'] = true;

            $row = sql_fetch(" select * from {$g5['board_file_table']} where bo_table = 'building' and wr_id = '{$building_id}' and bf_no = '{$i}' ");

            $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/building/'.str_replace('../', '', $row['bf_file']), $row);

            if( file_exists($delete_file) ){
                @unlink($delete_file);
            }
        }else{
            $upload[$i]['del_check'] = false;
        }

        $tmp_file  = $_FILES['bf_file']['tmp_name'][$i];
        $filesize  = $_FILES['bf_file']['size'][$i];
        $filename  = $_FILES['bf_file']['name'][$i];
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

            move_uploaded_file($tmp_file, $dest_file) or alert($_FILES['bf_file']['error'][$i]);
        }
    }
}


// if($_SERVER['REMOTE_ADDR'] == '211.226.242.248'){
//     // print_r2($_FILES);
//     // print_r2($upload);



//     for ($i=0; $i<count($upload); $i++)
//     {
//         if ($upload[$i]['del_check']){
//             $del = "DELETE FROM g5_board_file WHERE bo_table = 'building' and bf_no = '{$i}' and wr_id = '{$building_id}'";
//             echo $del.'<br>';
//         }
//     }
//     exit;
// }


for ($i=0; $i<count($upload); $i++)
{
    $upload[$i]['source'] = sql_real_escape_string($upload[$i]['source']);
    $bf_width = isset($upload[$i]['image'][0]) ? (int) $upload[$i]['image'][0] : 0;
    $bf_height = isset($upload[$i]['image'][1]) ? (int) $upload[$i]['image'][1] : 0;
    $bf_type = isset($upload[$i]['image'][2]) ? (int) $upload[$i]['image'][2] : 0;

    if ($upload[$i]['del_check']){
        $del = "DELETE FROM g5_board_file WHERE bo_table = 'building' and bf_no = '{$i}' and wr_id = '{$building_id}'";
        sql_query($del);

        $file_chk = "SELECT * FROM g5_board_file WHERE bo_table = 'building' and wr_id = '{$building_id}' and bf_no > {$i}";
        $file_chk_res = sql_query($file_chk);
        $file_chk_total = sql_num_rows($file_chk_res);

        // if($file_chk_total > 0){
        //     for($j=0;$file_chk_row = sql_fetch_array($file_chk_res);$j++){

        //         $bf_no_new = $file_chk_row['bf_no'] - 1;
        //         $up_bf_no = "UPDATE g5_board_file SET bf_no = '{$bf_no_new}' WHERE bo_table = 'building' and wr_id = '{$building_id}' and bf_no = '{$file_chk_row['bf_no']}'";

        //         sql_query($up_bf_no);
        //     }
        // }
    }

    if($upload[$i]['source'] != ""){
        $file_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table = 'building' and wr_id = '{$building_id}' and bf_no = '{$i}'");

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
                         WHERE bo_table = 'building' and wr_id = '{$building_id}' and bf_no = '{$i}'";
        }else{
            $sql = " insert into {$g5['board_file_table']}
                    set bo_table = 'building',
                         wr_id = '{$building_id}',
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

if($w == 'u'){
    if($is_use == 0){
        alert('단지가 해지되었습니다.', '/adm/building_mng.php?type=N');
    }else{
        alert('단지가 수정되었습니다.');
    }
}else if($w == 'a'){
    if($is_use == 0){
        alert('단지가 수정되었습니다.', './building_mng_add.php?' . $qstr . '&amp;w=a&amp;type='.$type.'&amp;building_id=' . $building_id);
    }else{
        alert('단지가 수정되었습니다.', './building_mng_add.php?' . $qstr . '&amp;w=u&amp;type=Y&amp;building_id=' . $building_id);
    }
}else{
    alert('단지가 등록되었습니다.', './building_mng_add.php?' . $qstr . '&amp;w=u&amp;type='.$type.'&amp;building_id=' . $building_id);
}