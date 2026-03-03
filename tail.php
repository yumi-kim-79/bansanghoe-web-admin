<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(defined('G5_THEME_PATH')) {
    require_once(G5_THEME_PATH.'/tail.php');
    return;
}

if (G5_IS_MOBILE) {
    include_once(G5_MOBILE_PATH.'/tail.php');
    return;
}

?>
<?php if($footerType == "ver1"){
    
?>
<div id="footer">
    <ul id="bottom_tab">
        <li>
            <a href="/" class="<?php echo $tab_on1;?>">
                <img src="/images/tab_navi_<?php echo $tab_on1 ?? 'off';?>1.svg" alt="">
                <span>메인</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $user_info['mb_type'] == "OUT" ? "javascript:notOpen();" : "/bill.php"; ?>" class="<?php echo $tab_on2;?>">
                <img src="/images/tab_navi_<?php echo $tab_on2 ?? 'off';?>2.svg" alt="">
                <span>고지서</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $user_info['mb_type'] == "OUT" ? "javascript:notOpen();" : "/online_complain.php"; ?>" class="<?php echo $tab_on3;?>">
                <img src="/images/tab_navi_<?php echo $tab_on3 ?? 'off';?>3.svg" alt="">
                <span>온라인 민원</span>
            </a>
        </li>
        <li>
            <a href="/mypage.php" class="<?php echo $tab_on4;?>">
                <img src="/images/tab_navi_<?php echo $tab_on4 ?? 'off';?>4.svg" alt="">
                <span>MY</span>
            </a>
        </li>
    </ul>
</div>
<?php }?>
<?php if($footerType == "ver_sm"){?>
<div id="footer">
    <ul id="bottom_tab">
        <li>
            <a href="/sm_index.php" class="<?php echo $tab_on1;?>">
                <img src="/images/tab_navi_<?php echo $tab_on1 ?? 'off';?>1.svg" alt="">
                <span>메인</span>
            </a>
        </li>
        <li>
            <a href="/bill_sm.php" class="<?php echo $tab_on2;?>">
                <img src="/images/tab_navi_sm_<?php echo $tab_on2 ?? 'off';?>2.svg" alt="">
                <span>결재관리</span>
            </a>
        </li>
        <li>
            <a href="/building_sch.php" class="<?php echo $tab_on3;?>">
                <img src="/images/tab_navi_sm_<?php echo $tab_on3 ?? 'off';?>3.svg" alt="">
                <span>단지검색</span>
            </a>
        </li>
        <li>
            <a href="/board_list.php" class="<?php echo $tab_on31;?>">
                <img src="/images/tab_navi_sm_<?php echo $tab_on31 ?? 'off';?>4.svg" alt="">
                <span>게시판</span>
            </a>
        </li>
        <li>
            <a href="/mypage.php?types=sm" class="<?php echo $tab_on4;?>">
                <img src="/images/tab_navi_<?php echo $tab_on4 ?? 'off';?>4.svg" alt="">
                <span>MY</span>
            </a>
        </li>
    </ul>
</div>
<?php }?>
<div class="indicator">
	<p><img src="<?php echo G5_IMG_URL?>/indicator.gif" alt=""></p>
</div>



<script>
function notOpen(){
    showToast("외부인 회원은 이용할 수 없는 서비스입니다.");
}

function popClose(pop){
    bodyUnlock();
    document.getElementById(pop).style.display = "none";
}

function popOpen(pop){
    bodyLock();
    document.getElementById(pop).style.display = "block";
}
</script>

<?php
include_once(G5_PATH."/tail.sub.php");