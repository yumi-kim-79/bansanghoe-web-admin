<?php
$sub_menu = '300500';
include_once('./_common.php');

//auth_check($auth[$sub_menu], "w");
$type_t = $type == 'electro' ? '전기' : '수도';

$g5['title'] = $type_t.' 엑셀 업로드';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>
            엑셀파일을 이용하여 전기 검침 값을 등록합니다.<br>
            형식은 <strong>샘플 엑셀파일</strong>을 다운로드하여 정보를 입력하시면 됩니다.<br>
            수정 완료 후 엑셀파일을 업로드하시면 등록됩니다.<br>
        </p>

        <p>
            <a href="<?php echo G5_URL; ?>/adm/meter_reading_excel_download.php?building_id=<?php echo $building_id; ?>&type=<?php echo $type;?>"><?php echo $type_t; ?> 검침 샘플 엑셀파일 다운로드</a>
        </p>
    </div>
    
    <form name="fmemberexcel" method="post" action="./meter_reading_excel_upload.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">
    <input type="hidden" name="building_id" id="building_id" value="<?php echo $building_id; ?>">
    <input type="hidden" name="type" id="type" value="<?php echo $type; ?>">
    <input type="hidden" name="mr_year" id="mr_year" value="<?php echo $mr_year; ?>">
    <input type="hidden" name="mr_month" id="mr_month" value="<?php echo $mr_month; ?>">
    <input type="hidden" name="mr_department" id="mr_department" value="<?php echo $mr_department; ?>">
    <input type="hidden" name="mr_id" id="mr_id" value="<?php echo $mr_id; ?>">
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