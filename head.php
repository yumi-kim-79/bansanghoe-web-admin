<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

run_event('pre_head');

if(defined('G5_THEME_PATH')) {
    require_once(G5_THEME_PATH.'/head.php');
    return;
}

if (G5_IS_MOBILE) {
    include_once(G5_MOBILE_PATH.'/head.php');
    return;
}

$basename=basename($_SERVER["PHP_SELF"]);
include_once(G5_PATH."/head.tit.php");
include_once(G5_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');

$mobile_agent = "/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS)/";
if(preg_match($mobile_agent, $_SERVER['HTTP_USER_AGENT'])){
    //echo "Mobile";

}else if($chk_app == 'Y'){
    // 앱에서 푸시로 진입한 경우 → 모바일로 인정, 통과

}else{

    if($_SERVER['REMOTE_ADDR'] != "59.16.155.80" && $_SERVER['REMOTE_ADDR'] != "221.154.172.192"){
        goto_url("/adm");
    }
}

//회원아닐 때 로그인 풀렸으면 로그인 세션 유지
if(!$is_users){
    
    //앱에서 접속했을 때
    if($chk_app == 'Y' && $app_token){

        $sql2 = " select count(*) cnt from a_member where mb_token = '{$app_token}' and is_del = 0 and mb_auto = 1 ";
        $row2 = sql_fetch($sql2);

        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
            $sql2 = " select count(*) cnt from a_member where mb_token = '{$app_token}' and is_del = 0 and mb_auto = 1 ";
            echo $sql2.'<br>';
            // exit;
        }
        
        //로그인 기록이 있는 경우 다시 로그인
        if($row2['cnt'] > 0){
            $sql = " select * from a_member where mb_token = '{$app_token}' ";
            $user_mb = sql_fetch($sql);

            $sql_ho_hd = "SELECT * FROM a_building_ho WHERE ho_status = 'Y' and ho_tenant_hp = '{$user_mb['mb_hp']}' ORDER BY ho_id asc ";
            $row_ho_hd = sql_fetch($sql_ho_hd);
            
            session_start_samesite();

            $_SESSION['users']['id'] = $user_mb['mb_id'];
            $_SESSION['users']['ho_id'] = $row_ho_hd['ho_id'];

            goto_url('/');
        }
    }
    
}

//echo $pages;
if($pages != "login.php" && $pages != "find_info.php" && $pages != "find_id.php" && $pages != "find_pw.php" && $pages != "find_pw_res.php" && $pages != "register.php" && $pages != "find_id_res.php" && $pages != "sm_index.php" && $pages != "inspection_form.php" && $pages != "inspection_end.php"){
    if(!$is_users){
        goto_url("/bbs/login.php?chk_app=".$chk_app.'&app_token='.$app_token);
    }
}

$ho_tenant_at_de = $user_building['ho_tenant_at'];
// print_r2($user_building);

?>

<header class="header <?php echo $headerType?> <?php echo $headerbg; ?>">
	<div class="inner">
        <?php if($headerType == "ver1"){?>
            <?php if($basename != "index.php"){?>
            <p class="hd_title ver2"><?php echo $headerTitle?></p>
            <?php }else{ ?>
            <p class="hd_logo">
				
				<img src="<?php echo G5_IMG_URL?>/logo.png" alt="이맥스 로고">
                <span>반상회</span>
					<!-- <a href="<?php echo G5_URL?>">
				</a> -->
			</p>
            <?php }?>
            <ul class="hd_lnb">
                <?php if($basename == 'sm_index.php'){?>
                    <li class="sm_hd_lnb">
                        <a href="/meter_reading.php">
                            <img src="/images/setting_icons.svg" alt="">검침
                        </a>
                    </li>
                    <li class="sm_hd_lnb">
                        <a href="/inspection_log_list.php">
                            <img src="/images/setting_icons.svg" alt="">점검일지
                        </a>
                    </li>
                <?php }?>
                <li>
                    <?php
                    $push_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_push WHERE recv_id = '{$user_info['mb_id']}' and is_view = '0' and is_del = 0");

                    // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo "SELECT COUNT(*) as cnt FROM a_push WHERE recv_id = '{$user_info['mb_id']}' and is_view = '0' and is_del = 0";
                    ?>
                    <a href="/notification_list.php?types=user">
                        <img src="<?php echo G5_IMG_URL?>/bell_icons.svg" alt="">
                        <?php if($push_cnt['cnt'] > 0){?>
                        <span class="notis"></span>
                        <?php }?>
                    </a>
                </li>
            </ul>
        <?php }else if($headerType == "ver2"){
            $onClick = "historyBack();";
            $onClick2 = "toolTipShow();";
            ?>
            <?php if($basename != 'inspection_form.php' && $basename != 'inspection_end.php'){?>
            <button type="button" class="hd_btn back_btn" onClick="<?php echo $onClick?>">
				<img src="<?php echo G5_IMG_URL?>/icon_hd_back.svg" alt="">
			</button>
            <?php }?>
            <p class="hd_title"><?php echo $headerTitle?></p>
            <?php if($basename == 'sm_complain_info.php'){?>
            <ul class="hd_lnb hd_lnb2">
                <li class="sm_hd_lnb">
                    <a href="javascript:popOpen('department_ch_memo_pop');">
                        <img src="/images/setting_icons.svg" alt="">변경사유
                    </a>
                </li>
            </ul>
            <?php }?>
            <?php if(($basename == "online_complain_info.php" && $cstatus == 'CB') || $basename == "sm_complain_info.php" || ($basename == "holiday_reqeust_info.php" && $mng == "" || ($basename == "expense_report_info.php" && $types == "sm"))){?>
            <button type="button" class="hd_btn home_btn"><img src="/images/head_option_icons.svg" alt=""></button>
                <?php if($basename == "online_complain_info.php"){?>
                <div class="tooltip_box">
                    <a href="/online_complain_form.php?w=u&complain_idx=<?php echo $complain_idx; ?>&cstatus=<?php echo $cstatus; ?>" class="tooltip_btn">수정하기</a>
                    <button type="button" onclick="popOpen('complain_del_pop')" class="tooltip_btn">삭제하기</button>
                </div>
                <?php }else if($basename == "sm_complain_info.php"){?>
                <div class="tooltip_box">
                    <button type="button" onclick="popOpen('department_ch_pop')" class="tooltip_btn">부서변경</button>
                </div>
                <?php }else if($basename == "holiday_reqeust_info.php"){?>
                <div class="tooltip_box">
                    <a href="/holiday_reqeust_form.php?w=u&types=<?php echo $types;?>" class="tooltip_btn">수정하기</a>
                    <button type="button" onclick="popOpen('approval_del_pop')"  class="tooltip_btn">삭제하기</button>
                </div>
                <?php }else if($basename == "expense_report_info.php" && $types == "sm"){
                    ?>
                <div class="tooltip_box">
                    <a href="/expense_report_form.php?w=u&ex_id=<?php echo $ex_id; ?>" class="tooltip_btn">수정하기</a>
                    <button type="button" onclick="popOpen('report_del_pop')"  class="tooltip_btn">삭제하기</button>
                </div>
                <?php }?>
            
            <?php }?>
        <?php }?>
	</div>
</header>
<div class="header_back_box"></div>


<!-- <?php if($basename != "index.php" && $basename != "bill.php" && $basename != 'online_complain.php' && $basename != 'mypage.php'){?>
<div id="sub_div">
<?php }?> -->

<script>
//웹뷰로 포스트메시지 보내기
const sendMessage = (_type, data) => {
    if(window.ReactNativeWebView) {
        window.ReactNativeWebView.postMessage(JSON.stringify({'mode':_type,'data':data}));
    } else {
        console.log(_type,data);
    }
}

const varUA = navigator.userAgent.toLowerCase();
let is_users = "<?php echo $is_users; ?>";
let is_sm = "<?php echo $is_member; ?>";

if(varUA.indexOf('android') > -1) { //android
    document.addEventListener("message", function(msg) {
        let data =  JSON.parse(msg.data);
    
        if(data.isapp == 'Y'){ 
            //isapp이 Y일 때
		   if(is_users){
				token_save("<?php echo $user_info['mb_id']; ?>", data.istoken, data.isapp, "user");
		   }

		   if(is_sm){
				token_save("<?php echo $member['mb_id']; ?>", data.istoken, data.isapp, "sm");
		   }
        }
    });
}else if(varUA.indexOf("iphone") > -1 || varUA.indexOf("ipad") > -1 || 
varUA.indexOf("ipod") > -1 ){ //ios
    window.addEventListener("message", function(msg){
        let data =  JSON.parse(msg.data);
        if(data.isapp == 'Y'){ 
            //isapp이 Y일 때
            //alert(data.istoken); 
            if(is_users){
		 		token_save("<?php echo $user_info['mb_id']; ?>", data.istoken, data.isapp, "user");
		   }

		   if(is_sm){
		 		token_save("<?php echo $member['mb_id']; ?>", data.istoken, data.isapp, "sm");
		   }
        }
    });
}

function token_save(id, apptoken, app, type){

	$.ajax({
        url : "/app_token.php", //ajax 통신할 파일
        type : "POST", // 형식
        data: { "id":id, "token":apptoken, "app":app, "type":type}, //파라미터 값
        success: function(msg){ //성공시 이벤트
            sendMessage('success', {"content":msg});
            // $(".select_box2").html(msg); //.select_box2에 html로 나타내라..
        },
        error:function(e){
            sendMessage('error', {"content":e});
        }

    });
}
</script>
