<?php
include_once("_common.php");
include_once(G5_PATH."/_head.php");

if($types == 'sm'){
    $member_info = sql_fetch("SELECT * FROM a_mng WHERE mng_hp = '{$hp}' and is_del = 0");

    // echo "SELECT * FROM a_mng WHERE mng_hp = '{$hp}' and is_del = 0";

    $id = $member_info['mng_id'];
}else{
    $member_info = sql_fetch("SELECT * FROM a_member WHERE mb_hp = '{$hp}' and is_del = 0");

    $id = $member_info['mb_id'];
}
?>
<form name="find_frm" id="find_frm" method="post" autocomplete="off">
	<div class="find_id_view sub_box">
		<div class="inner">
			<p class="regi_title">아이디 찾기</p>
            <p class="find_id_value"><?php echo $types == 'sm' ? $member_info['mng_id'] : $member_info['mb_hp']; ?></p>
			<p class="find_id_desc">
                고객님의 정보와 일치하는 아이디는 위와 같습니다.
			</p>
		</div>
	</div>

	<div class="fix_btn_back_box ver2"></div>
	<div class="fix_btn_box ver2">
        <a href="<?php echo G5_URL?>/find_pw.php?id=<?php echo $id; ?>&types=<?php echo $types;?>" class="fix_btn ver2"><?php echo $title?> 비밀번호 찾기</a>
		<a href="<?php echo G5_BBS_URL?>/login.php" class="fix_btn on">로그인하기</a>
	</div>
</form>
<?php
include_once(G5_PATH."/_tail.php");
?>