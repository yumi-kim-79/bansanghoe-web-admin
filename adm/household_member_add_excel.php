<?php
$sub_menu = '300110';
include_once('./_common.php');

//auth_check($auth[$sub_menu], "w");

$g5['title'] = '세대 구성원 정보 엑셀 업로드';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>
            엑셀파일을 이용하여 세대 구성원 정보를 등록합니다.<br>
            형식은 <strong>세대 구성원 정보 업로드 샘플 엑셀파일</strong>을 다운로드하여 정보를 입력하시면 됩니다.<br>
            수정 완료 후 엑셀파일을 업로드하시면 등록됩니다.<br>
        </p>

        <p>
            <a href="<?php echo G5_URL; ?>/adm/household_member_add_excel_sample.php?ho_id=<?php echo $ho_id; ?>">세대 구성원 정보 업로드 샘플 엑셀파일 다운로드</a>
        </p>
    </div>
    
    <form name="fmemberexcel" method="post" action="./household_member_add_excel_update.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">
    <input type="hidden" name="ho_id" id="ho_id" value="<?php echo $ho_id; ?>">
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