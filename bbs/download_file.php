<?php
include_once('./_common.php');

// clean the output buffer
ob_end_clean();

$no = isset($_REQUEST['no']) ? (int) $_REQUEST['no'] : 0;

$sql = " select * from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$no' ";
$file = sql_fetch($sql);
if (!$file['bf_file'])
    alert_close('파일 정보가 존재하지 않습니다.');

$filepath = G5_DATA_PATH.'/file/'.$bo_table.'/'.$file['bf_file'];
$filepath = addslashes($filepath);
$file_exist_check = (!is_file($filepath) || !file_exists($filepath)) ? false : true;

if ( false === run_replace('download_file_exist_check', $file_exist_check, $file) ){
    alert('파일이 존재하지 않습니다.');
}

//파일명에 한글이 있는 경우
/*
if(preg_match("/[\xA1-\xFE][\xA1-\xFE]/", $file['bf_source'])){
    // 2015.09.02 날짜의 파이어폭스에서 인코딩된 문자 그대로 출력되는 문제가 발생됨, 2018.12.11 날짜의 파이어폭스에서는 해당 현상이 없으므로 해당 코드를 사용 안합니다.
    $original = iconv('utf-8', 'euc-kr', $file['bf_source']); // SIR 잉끼님 제안코드
} else {
    $original = urlencode($file['bf_source']);
}
*/

//$original = urlencode($file['bf_source']);
$original = rawurlencode($file['bf_source']);

@include_once($board_skin_path.'/download.tail.skin.php');

run_event('download_file_header', $file, $file_exist_check);

if(preg_match("/msie/i", $_SERVER['HTTP_USER_AGENT']) && preg_match("/5\.5/", $_SERVER['HTTP_USER_AGENT'])) {
    header("content-type: doesn/matter");
    header("content-length: ".filesize($filepath));
    header("content-disposition: attachment; filename=\"$original\"");
    header("content-transfer-encoding: binary");
} else if (preg_match("/Firefox/i", $_SERVER['HTTP_USER_AGENT'])){
    header("content-type: file/unknown");
    header("content-length: ".filesize($filepath));
    //header("content-disposition: attachment; filename=\"".basename($file['bf_source'])."\"");
    header("content-disposition: attachment; filename=\"".$original."\"");
    header("content-description: php generated data");
} else {
    header("content-type: file/unknown");
    header("content-length: ".filesize($filepath));
    header("content-disposition: attachment; filename=\"$original\"");
    header("content-description: php generated data");
}
header("pragma: no-cache");
header("expires: 0");
flush();

$fp = fopen($filepath, 'rb');

// 4.00 대체
// 서버부하를 줄이려면 print 나 echo 또는 while 문을 이용한 방법보다는 이방법이...
//if (!fpassthru($fp)) {
//    fclose($fp);
//}

$download_rate = 10;

while(!feof($fp)) {
    //echo fread($fp, 100*1024);
    /*
    echo fread($fp, 100*1024);
    flush();
    */

    print fread($fp, round($download_rate * 1024));
    flush();
    usleep(1000);
}
fclose ($fp);
flush();