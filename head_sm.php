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
$is_webview = ($chk_app == 'Y') || 
              (strpos($_SERVER['HTTP_USER_AGENT'], 'wv') !== false);
if(preg_match($mobile_agent, $_SERVER['HTTP_USER_AGENT']) || $is_webview){
	//echo "Mobile";
    
}else{

    if($_SERVER['REMOTE_ADDR'] != "59.16.155.80" && $_SERVER['REMOTE_ADDR'] != "221.154.172.192"){
        goto_url("/adm");
    }
}

//매니저 계정 로그인이 아닐 때
if(!$is_member){

    // if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
    //     $sql2 = " select count(*) cnt from g5_member as mem 
    //               left join a_mng on mng mem.mb_id = mng.mng_id
    //               where mem.mb_token = '{$app_token}' and mem.mb_auto = 1 and mng.mng_status = 1 ";
    //     echo $sql2.'<br>';
    // }
    
    //앱에서 접속했을 때
    if($chk_app == 'Y' && $app_token){

        $sql2 = " select count(*) cnt from g5_member as mem 
                  left join a_mng on mng mem.mb_id = mng.mng_id
                  where mem.mb_token = '{$app_token}' and mem.mb_auto = 1 and mng.mng_status = 1 ";
        $row2 = sql_fetch($sql2);
        
        //로그인 기록이 있는 경우 다시 로그인
        if($row2['cnt'] > 0){
            $sql = " select * from g5_member where mb_token = '{$app_token}' ";
            $sm_mb = sql_fetch($sql);
         
            // 회원아이디 세션 생성
            set_session('ss_mb_id', $sm_mb['mb_id']);
            // FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함
            generate_mb_key($sm_mb);

        }
    }
    
}

//echo $pages;
if($pages != "login_sm.php" && $pages != "find_info.php" && $pages != "find_id.php" && $pages != "find_pw.php" && $pages != "find_pw_res.php" && $pages != "register.php" && $pages != "find_id_res.php" && $pages != "sm_login_agree.php" && $pages != "inspection_form.php" && $pages != "inspection_end.php"){
    if(!$is_member){
        goto_url("/bbs/login_sm.php?chk_app=".$chk_app.'&app_token='.$app_token);
    }
}


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
                    $push_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_push WHERE recv_id = '{$member['mb_id']}' and is_view = '0'");

                    // print_r2($push_cnt);
                    ?>
                    <a href="/notification_list.php?types=sm">
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

            if($basename == "schedule_add.php" || $basename == "schedule_add2.php"){
                $onClick = "location.replace('/sm_index.php?tabIdx=1&tabCode=schedule');";
            }

            //민원상세에서 홈화면에서 돌아갈 때 민원리스트로 돌아가기
            if($basename == "sm_complain_info.php"){
                $onClick = "location.replace('/sm_index.php?tabIdx=3&tabCode=complain');";
            }
            
            if($basename == "sm_move.php"){
                $onClick = "location.replace('/sm_index.php?tabIdx=2&tabCode=inout');";
            }

            //결재관리
            // if($basename == "holiday_reqeust_info.php"){
            //     $onClick = "location.replace('/holiday_reqeust.php?types=".$types."&tabIdx=".$tabIdx."&tabCode=".$tabCode."');";
            // }
            ?>
            <?php if($basename != 'inspection_form.php' && $basename != 'board_info.php'){?>
            <button type="button" class="hd_btn back_btn" onClick="<?php echo $onClick?>">
				<img src="<?php echo G5_IMG_URL?>/icon_hd_back.svg" alt="">
			</button>
            <?php }?>
            <?php 
            if($basename == 'board_info.php'){
            ?>
            <button type="button" class="hd_btn back_btn" onclick="replaceto();">
				<img src="<?php echo G5_IMG_URL?>/icon_hd_back.svg" alt="">
			</button>
            <script>
                function replaceto(){
                    location.replace("/board_list.php?tabCode=<?php echo $tabCode;?>&tabIdx=<?php echo $tabIdx;?>");
                }
            </script>
            <?php }?>
            <p class="hd_title"><?php echo $headerTitle?></p>
            <!-- 게시판 -->
            <?php if($basename == 'sm_complain_info.php'){
                //mng_change_memo
                $complain_info = sql_fetch("SELECT * FROM a_online_complain WHERE complain_idx = '{$complain_idx}'");

                //print_r2($complain_info);
                
                ?>
                <?php if($complain_info['mng_change_memo'] != ""){?>
                    <ul class="hd_lnb hd_lnb2 <?php echo $complain_info['complain_status'] == "CD" ? "ver2" : "";?>">
                        <li class="sm_hd_lnb">
                            <a href="javascript:popOpen('department_ch_memo_pop');">
                                <img src="/images/setting_icons.svg" alt="">변경사유
                            </a>
                        </li>
                    </ul>
                <?php }?>
            <?php }?>
            <?php if(($basename == "schedule_add.php" || $basename == "schedule_add2.php") && $w == 'i'){
                $cal_sql = "SELECT cal.*, building.building_name FROM a_calendar as cal
                LEFT JOIN a_building as building ON cal.building_id = building.building_id
                WHERE cal.cal_idx = '{$cal_idx}'";
                $cal_row = sql_fetch($cal_sql);

                //$cal_row['wid'] == $member['mb_id'] &&
                if(!$cal_row['is_process']){
                    // 반복일정은 schedule_add2.php로, 일반일정은 schedule_add.php로 수정 이동
                    $edit_page = $basename == "schedule_add2.php" ? "schedule_add2.php" : "schedule_add.php";
                ?>
                <button type="button" class="hd_btn home_btn"><img src="/images/head_option_icons.svg" alt=""></button>
                <div class="tooltip_box">
                    <a href="/<?php echo $edit_page; ?>?w=u&cal_idx=<?php echo $cal_idx;?>&cal_code=<?php echo $cal_code; ?><?php echo $basename == 'schedule_add2.php' && $cal_date ? '&cal_date='.$cal_date : ''; ?>" class="tooltip_btn">수정하기</a>
                    <button type="button" onclick="popOpen('schedule_del_pop')" class="tooltip_btn">삭제하기</button>
                </div>
            <?php }?>
            <?php }?>
            <?php if(($basename == "online_complain_info.php" && $cstatus != 'CD') || $basename == "sm_complain_info.php" || ($basename == "holiday_reqeust_info.php" && $mng == "" || ($basename == "expense_report_info.php" && $types == "sm"))){
                ?>
                <!-- 결재등록시 -->
                <?php
                if($basename == "holiday_reqeust_info.php" && $mng == ""){
                    $sign_info = sql_fetch("SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'");
               
                ?>
                    <?php if($sign_info['sign_status'] == 'N'){?>
                    <button type="button" class="hd_btn home_btn"><img src="/images/head_option_icons.svg" alt=""></button>
                    <?php }?>
                <?php }?>

                <!-- 품의서 -->
                <?php
                if($basename == "expense_report_info.php" && $types == "sm"){
                     $expense_info = sql_fetch("SELECT * FROM a_expense_report WHERE ex_id = '{$ex_id}'");
                ?>
                    <?php if($expense_info['ex_status'] == 'N'){?>
                    <button type="button" class="hd_btn home_btn"><img src="/images/head_option_icons.svg" alt=""></button>
                    <?php }?>
                <?php }?>

                <!-- 민원 -->
                <?php
                if($basename == "online_complain_info.php" && $cstatus != 'CD'){
                ?>
                    <button type="button" class="hd_btn home_btn"><img src="/images/head_option_icons.svg" alt=""></button>
                <?php }?>
                <?php
                //&& $complain_status != 'CB' && $complain_status != 'CD'
                if($basename == "sm_complain_info.php" && $complain_status == 'CC'){
                ?>
                    <button type="button" class="hd_btn home_btn"><img src="/images/head_option_icons.svg" alt=""></button>
                <?php }?>
              
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
                    <a href="/holiday_reqeust_form.php?w=u&types=<?php echo $types;?>&sign_id=<?php echo $sign_info['sign_id']; ?>" class="tooltip_btn">수정하기</a>
                    <button type="button" onclick="popOpen('approval_del_pop')"  class="tooltip_btn">삭제하기</button>
                </div>
                <?php }else if($basename == "expense_report_info.php" && $types == "sm"){
                    ?>
                <div class="tooltip_box">
                    <a href="/expense_report_form.php?building_id=<?php echo $building_id; ?>&amp;w=u&amp;ex_id=<?php echo $ex_id; ?>" class="tooltip_btn">수정하기</a>
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
