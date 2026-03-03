<?php
$sub_menu = '300600';
include_once('./_common.php');

//auth_check($auth[$sub_menu], "w");

$g5['title'] = '입주자 정보 엑셀 업로드';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>
            엑셀파일을 이용하여 입주자 정보를 등록합니다.<br>
            형식은 <strong>입주자 정보 업로드 샘플 엑셀파일</strong>을 다운로드하여 정보를 입력하시면 됩니다.<br>
            수정 완료 후 엑셀파일을 업로드하시면 등록됩니다.<br>
        </p>
        <p>호수가 비어있는 경우에 입력이 실패하며 동일한 호수가 등록된 경우 모든 정보가 덮어쓰기 됩니다.</p>
        <p>
            <a href="<?php echo G5_URL; ?>/adm/dong_mng_add_excel_download.php?dong_id=<?php echo $dong_id; ?>">입주자 정보 업로드 샘플 엑셀파일 다운로드</a>
        </p>
    </div>

    <?php
    $action_url = "./dong_mng_add_excel_update.php";

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
      
        $action_url = "./dong_mng_add_excel_update2.php";
    }
    ?>
    
    <form name="fmemberexcel" method="post" action="<?php echo $action_url; ?>" enctype="MULTIPART/FORM-DATA" autocomplete="off">
    <input type="hidden" name="dong_id" id="dong_id" value="<?php echo $dong_id; ?>">
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