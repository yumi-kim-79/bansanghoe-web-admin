<?php
include_once('./_common.php');

if($types == "sm"){
    include_once(G5_PATH.'/head_sm.php');
}else{
    include_once(G5_PATH.'/head.php');


    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // print_r2($user_building);
    }

    //관리단인지
    $mng_team_row = sql_fetch("SELECT COUNT(*) as cnt FROM a_mng_team WHERE mb_id = '{$user_info['mb_id']}' and is_del = 0 and ho_id = '{$user_building['ho_id']}'");
    $is_mng_team = $mng_team_row['cnt'] > 0 ? 1 : 0;

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        echo "SELECT COUNT(*) as cnt FROM a_mng_team WHERE mb_id = '{$user_info['mb_id']}' and is_del = 0 and ho_id = '{$user_building['ho_id']}'";
    }
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
           
            <ul class="mypage_menu_list">
                <li>
                    <a href="/my_info.php?types=<?php echo $types;?>">
                        내정보
                    </a>
                </li>
                <?php if($types != "sm" && $is_mng_team){?>
                <li>
                    <a href="expense_report_adm.php">
                        품의서 [관리단 결재]
                    </a>
                </li>
                <?php }?>
                <li>
                    <a href="/notification_setting.php?types=<?php echo $types;?>">
                        알림설정
                    </a> 
                </li>
                <li>
                    <a href="/policy.php?co_id=<?php echo $types == 'sm' ? 'provision_sm' : 'provision'; ?>&types=<?php echo $types;?>">
                        이용약관
                    </a>
                </li>
                <li>
                    <a href="/policy.php?co_id=<?php echo $types == 'sm' ? 'privacy_sm' : 'privacy'; ?>&types=<?php echo $types;?>">
                        개인정보 처리방침
                    </a>
                </li>
                <?php if($types != "sm"){?>
                <li>
                    <a href="/app_info.php?types=<?php echo $types;?>">
                        앱정보
                    </a>
                </li>
                <?php }?>
            </ul>
            <div class="mypage_lnb">
                <ul>
                    <li><button type="button" onClick="popOpen('logout_pop');">로그아웃</button></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="cm_pop" id="logout_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">로그아웃 하시겠어요?</p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="popClose('logout_pop');">취소</button>
            <?php if($types == "sm"){?>
            <a href="<?php echo G5_BBS_URL?>/logout.sm.php" class="cm_pop_btn ver2">로그아웃</a>
            <?php }else{ ?>
            <a href="<?php echo G5_BBS_URL?>/logout.user.php" class="cm_pop_btn ver2">로그아웃</a>
            <?php }?>
		</div>
	</div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>