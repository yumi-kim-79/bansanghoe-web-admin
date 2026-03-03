<?php
$sub_menu = '300600';
include_once('./_common.php');

//auth_check($auth[$sub_menu], "w");

$g5['title'] = '엑셀파일로 고지서 업로드';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>
            엑셀파일을 이용하여 고지서를 등록합니다.<br>
            형식은 <strong>고지서용 엑셀파일</strong>을 다운로드하여 정보를 입력하시면 됩니다.<br>
            수정 완료 후 엑셀파일을 업로드하시면 일괄등록됩니다.<br>
            <!-- 엑셀파일을 저장하실 때는 <strong>Excel 97 - 2003 통합문서 (*.xls)</strong> 로 저장하셔야 합니다. -->
        </p>

        <!-- <p>
            <a href="<?php echo G5_URL; ?>/adm/bill_excel_download.php?bill_id=<?php echo $bill_id; ?>">고지서용 엑셀파일 다운로드</a>
        </p> -->
    </div>
    
    <?php
    $action_url = "./bill_excel_upload_update.php";
    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // $action_url = "./bill_excel_upload_update2.php";
    }
    ?>
    <form name="fmemberexcel" method="post" action="<?php echo $action_url; ?>" enctype="MULTIPART/FORM-DATA" autocomplete="off">
    <input type="hidden" name="building_id" value="<?php echo $building_id; ?>">
    <input type="hidden" name="excel_type" value="<?php echo $excel_type; ?>">
    <div id="excelfile_upload">
        <label for="excelfile">파일선택</label>
        <input type="file" name="excelfile" id="excelfile">
    </div>

    <div class="btn_confirm01 btn_confirm" style="text-align:center">
        <input type="submit" value="엑셀파일 등록" class="btn_submit btn_04">
		<button type="button" onclick="window.close();">닫기</button>
    </div>

    </form>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>