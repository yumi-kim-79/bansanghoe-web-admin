<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

run_event('pre_head');

$basename=basename($_SERVER["PHP_SELF"]);
include_once(G5_PATH."/head.tit.php");
include_once(G5_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
?>

<header class="header">
	<div class="inner">
        <p class="hd_logo">
            
            <img src="<?php echo G5_IMG_URL?>/logo.png" alt="이맥스 로고">
            <span>반상회</span>
                <!-- <a href="<?php echo G5_URL?>">
            </a> -->
        </p>
	</div>
</header>
<div class="header_back_box"></div>