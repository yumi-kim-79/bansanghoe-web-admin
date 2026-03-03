<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

$tt;

switch($types){
    case "public":
        $tt = "공문";
    break;
    case "event":
        $tt = "이벤트";
    break;
    case "info":
        $tt = "안내문";
    break;
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="bbs_wrap">
                <div class="bbs_title_box">
                    <p class="bbs_title"><?php echo $tt; ?> 제목입니다.</p>
                    <p class="bbs_date">2024.10.08</p>
                </div>
                <div class="bbs_content_box">
                <?php echo $tt; ?> 내용입니다. 
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>