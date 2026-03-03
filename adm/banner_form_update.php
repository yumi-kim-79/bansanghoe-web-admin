<?php
$sub_menu = "910100";
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($w == "u"){

    $querys_banner_date = "";
    if($banner_sdate != ""){
        $querys_banner_date .= " banner_sdate = '{$banner_sdate}', ";
    }else{
        $querys_banner_date .= " banner_sdate = '', ";
    }

    if($banner_edate != ""){
        $querys_banner_date .= " banner_edate = '{$banner_edate}', ";
    }else{
        $querys_banner_date .= " banner_edate = '', ";
    }


    $update_query = "UPDATE a_banner SET
                    banner_area = '{$banner_area}',
                    banner_name = '{$banner_name}',
                    {$querys_banner_date}
                    is_prior = '{$is_prior}',
                    is_view = '{$is_view}',
                    burl_use = '{$burl_use}',
                    burl = '{$burl}',
                    banner_content = '{$banner_content}'
                    WHERE banner_id = '{$banner_id}'";

    sql_query($update_query);
    
}else{
 
    if($_FILES['banner_file']['name'][0] == ""){
        alert('광고배너 이미지를 첨부해주세요.');
    }

    $querys_banner_date = "";
    if($banner_sdate != ""){
        $querys_banner_date .= " banner_sdate = '{$banner_sdate}', ";
    }

    if($banner_edate != ""){
        $querys_banner_date .= " banner_edate = '{$banner_edate}', ";
    }
    
    $insert_query = "INSERT INTO a_banner SET
                    banner_area = '{$banner_area}',
                    banner_name = '{$banner_name}',
                    {$querys_banner_date}
                    is_prior = '{$is_prior}',
                    is_view = '{$is_view}',
                    burl_use = '{$burl_use}',
                    burl = '{$burl}',
                    banner_content = '{$banner_content}',
                    wid = '{$member['mb_id']}',
                    created_at = '{$today}'";
        
    //echo $insert_query.'<br>';
    //exit;
    sql_query($insert_query);
    $banner_id = sql_insert_id(); //팝업 idx

}


//파일 첨부
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

//문항 생성시 이미지 파일첨부 경로
$file_path = G5_DATA_PATH.'/file/banner';

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir($file_path, G5_DIR_PERMISSION);
@chmod($file_path, G5_DIR_PERMISSION);

$upload = array();

if(isset($_FILES['banner_file']['name'])){
    for ($i=0; $i<count($_FILES['banner_file']['name']); $i++) {
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

        $tmp_file  = $_FILES['banner_file']['tmp_name'][$i];
        $filesize  = $_FILES['banner_file']['size'][$i];
        $filename  = $_FILES['banner_file']['name'][$i];
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

                if(move_uploaded_file($tmp_file, $dest_file)){
                    resizeImage($dest_file, 720, 720);

                    $upload[$i]['image'] = @getimagesize($dest_file); //이미지 축소된 정보로 다시 저장
                }else{
                    alert($_FILES['banner_file']['error'][$i]);
                }

            }else{
                $error_code = move_uploaded_file($tmp_file, $dest_file) or alert($_FILES['banner_file']['error'][$i]);
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

    if($upload[$i]['source'] != ""){
        $file_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table = 'banner' and wr_id = '{$banner_id}' and bf_no = '{$i}'");

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
                         WHERE bo_table = 'banner' and wr_id = '{$banner_id}' and bf_no = '{$i}'";
        }else{
            $sql = " insert into {$g5['board_file_table']}
                    set bo_table = 'banner',
                         wr_id = '{$banner_id}',
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
    alert('배너가 수정되었습니다.');
}else{
    alert('배너가 등록되었습니다.', './banner_form.php?' . $qstr . '&amp;w=u&amp;banner_id=' . $banner_id);
}