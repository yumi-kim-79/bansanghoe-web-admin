<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

if($w == "u"){
    $msg = "점검일지 수정이 완료되었습니다.";
}else{
    $msg = "점검일지 작성이 완료되었습니다.";
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inspection_end_box">
            <p class="login_logo"><img src="/images/bansang_logos.svg" alt="이맥스 로고"></p>
            <p class="insp_title"><?php echo $msg; ?></p>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>