<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$g5_debug['php']['begin_time'] = $begin_time = get_microtime();

$files = glob(G5_ADMIN_PATH . '/css/admin_extend_*');
if (is_array($files)) {
    foreach ((array) $files as $k => $css_file) {

        $fileinfo = pathinfo($css_file);
        $ext = $fileinfo['extension'];

        if ($ext !== 'css') {
            continue;
        }

        $css_file = str_replace(G5_ADMIN_PATH, G5_ADMIN_URL, $css_file);
        add_stylesheet('<link rel="stylesheet" href="' . $css_file . '">', $k);
    }
}

require_once G5_PATH . '/head.sub.php';

function print_menu1($key, $no = '')
{
    global $menu;

    $str = print_menu2($key, $no);

    return $str;
}

function print_menu2($key, $no = '')
{
    global $menu, $auth_menu, $is_admin, $auth, $g5, $sub_menu;

    $str = "<ul>";
    for ($i = 1; $i < count($menu[$key]); $i++) {
        if (!isset($menu[$key][$i])) {
            continue;
        }

        if ($is_admin != 'super' && (!array_key_exists($menu[$key][$i][0], $auth) || !strstr($auth[$menu[$key][$i][0]], 'r'))) {
            continue;
        }

        $gnb_grp_div = $gnb_grp_style = '';

        if (isset($menu[$key][$i][4])) {
            if (($menu[$key][$i][4] == 1 && $gnb_grp_style == false) || ($menu[$key][$i][4] != 1 && $gnb_grp_style == true)) {
                $gnb_grp_div = 'gnb_grp_div';
            }

            if ($menu[$key][$i][4] == 1) {
                $gnb_grp_style = 'gnb_grp_style';
            }
        }

        $current_class = '';

        if ($menu[$key][$i][0] == $sub_menu) {
            $current_class = ' on';
        }

        $str .= '<li data-menu="' . $menu[$key][$i][0] . '"><a href="' . $menu[$key][$i][2] . '" class="gnb_2da ' . $gnb_grp_style . ' ' . $gnb_grp_div . $current_class . '">' . $menu[$key][$i][1] . '</a></li>';

        $auth_menu[$menu[$key][$i][0]] = $menu[$key][$i][1];
    }
    $str .= "</ul>";

    return $str;
}

$adm_menu_cookie = array(
    'container' => '',
    'gnb'       => '',
    'btn_gnb'   => '',
);

if (!empty($_COOKIE['g5_admin_btn_gnb'])) {
    $adm_menu_cookie['container'] = 'container-small';
    $adm_menu_cookie['gnb'] = 'gnb_small';
    $adm_menu_cookie['btn_gnb'] = 'btn_gnb_open';
}
?>

<script>
    var g5_admin_csrf_token_key = "<?php echo (function_exists('admin_csrf_token_key')) ? admin_csrf_token_key() : ''; ?>";
    var tempX = 0;
    var tempY = 0;

    function imageview(id, w, h) {

        menu(id);

        var el_id = document.getElementById(id);

        //submenu = eval(name+".style");
        submenu = el_id.style;
        submenu.left = tempX - (w + 11);
        submenu.top = tempY - (h / 2);

        selectBoxVisible();

        if (el_id.style.display != 'none')
            selectBoxHidden(id);
    }




</script>

<div id="to_content"><a href="#container">본문 바로가기</a></div>

<header id="hd">
    <h1><?php echo $config['cf_title'] ?></h1>
    <div id="hd_top">
        <button type="button" id="btn_gnb" class="btn_gnb_close <?php echo $adm_menu_cookie['btn_gnb']; ?>">메뉴</button>
        <!-- <?php echo correct_goto_url(G5_ADMIN_URL); ?> -->
        <div id="logo"><img src="<?php echo G5_ADMIN_URL ?>/img/logo.png" alt="<?php echo get_text($config['cf_title']); ?> 관리자"></div>

        <div id="tnb">
            <div class="notice_box">
                <a href="javascript:notiPopOpen();">
                    <img src="/images/admin_bell.svg" alt="">
                    <span class="noti_cnt"></span>
                </a>
                <script>
                    function notiPopOpen(){
                        notilist();
                        popOpen('admin_noti_pop');
                    }

                    $(document).ready(function() {
                        admin_noti_check();
                        setInterval(() => {
                            admin_noti_check();
                        }, 1000);
                    });

                    function admin_noti_check(){
                        let sendData = {'mb_id': '<?php echo $member['mb_id']?>'};

                        $.ajax({
                            type: "POST",
                            url: "./admin_noti_check.php",
                            data: sendData,
                            cache: false,
                            async: false,
                            dataType: "json",
                            success: function(data) {
                                //console.log('data:::', data);

                                if(data.result == false) { 
                                   $(".noti_cnt").hide();
                                    return false;
                                }else{
                                    $(".noti_cnt").show();
                                    $(".noti_cnt").text(data.data);
                                }
                            },
                        });
                    }
                </script>
            </div>
            <ul>
                <!-- <?php if (defined('G5_USE_SHOP') && G5_USE_SHOP) { ?>
                    <li class="tnb_li"><a href="<?php echo G5_SHOP_URL ?>/" class="tnb_shop" target="_blank" title="쇼핑몰 바로가기">쇼핑몰 바로가기</a></li>
                <?php } ?> -->
                <!-- <li class="tnb_li"><a href="<?php echo G5_URL ?>/" class="tnb_community" target="_blank" title="커뮤니티 바로가기">커뮤니티 바로가기</a></li> -->
                <!-- <li class="tnb_li"><a href="<?php echo G5_ADMIN_URL ?>/service.php" class="tnb_service">부가서비스</a></li> -->
                <li class="tnb_li"><button type="button" class="tnb_mb_btn"><?php echo $member['mb_level'] == '9' ? $member['mb_nick'].'님' : '관리자'?><span class="./img/btn_gnb.png">메뉴열기</span></button>
                    <ul class="tnb_mb_area">
                        <!-- <li><a href="<?php echo G5_ADMIN_URL ?>/member_form.php?w=u&amp;mb_id=<?php echo $member['mb_id'] ?>">관리자정보</a></li> -->
                        <li id="tnb_logout"><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="admin_nav">
        <?php 
        $menu_chk = $member['mb_level'] == 10 ? 'A' : '';

        if($member['mb_level'] == 9){
            $mng_info_set = get_manger($member['mb_id']);

            $menu_chk = $mng_info_set['mng_certi'];
        }

        switch($menu_chk){
            case "A":
                $admin_level = 1;
                break;
            case "B":
                $admin_level = 2;
                break;
            case "C":
                $admin_level = 3;
                break;
            case "D":
                $admin_level = 4;
                break;
        }
        ?>
      <ul>
        
        <li>
          <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "200" ? "active" : ""; ?>">단지관리</button>
          <div class="admin_sub_menu">
            <!-- <a href="./member_list.php" class="<?php echo $sub_menu == "200100" ? "active" : "";?>">관리자계정</a> -->
            <a href="./building_mng.php?type=Y" class="<?php echo $sub_menu == "200200" ? "active" : "";?>">단지관리</a>
            <a href="./building_mng.php?type=N" class="<?php echo $sub_menu == "200300" ? "active" : "";?>">해지 단지관리</a>
            <a href="./dong_mng.php" class="<?php echo $sub_menu == "200400" ? "active" : "";?>">동/호수 관리</a>
            <a href="./building_news_info.php" class="<?php echo $sub_menu == "200500" ? "active" : "";?>">안내문</a>
            <a href="./building_news_public.php?type=progress" class="<?php echo $sub_menu == "200600" ? "active" : "";?>">공문(관공서)</a>
            <a href="./building_news_public_bf.php" class="<?php echo $sub_menu == "200700" ? "active" : "";?>">공문(이전자료)</a>
            <a href="./building_news_event.php" class="<?php echo $sub_menu == "200800" ? "active" : "";?>">이벤트</a>
          </div>
        </li>
      
        <li>
          <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "300" ? "active" : ""; ?>">세대관리</button>
          <div class="admin_sub_menu">
            <a href="./house_hold_list.php" class="<?php echo $sub_menu == "300100" ? "active" : "";?>">세대관리</a>
            <a href="./household_member_list.php" class="<?php echo $sub_menu == "300110" ? "active" : "";?>">세대 구성원</a>
            <a href="./manage_list.php" class="<?php echo $sub_menu == "300200" ? "active" : "";?>">관리단</a>
            <a href="./car_list.php" class="<?php echo $sub_menu == "300300" ? "active" : "";?>">차량관리</a>
            <a href="./visit_car_list.php" class="<?php echo $sub_menu == "300400" ? "active" : "";?>">방문차량 관리</a>
            <a href="./meter_reading_adm.php" class="<?php echo $sub_menu == "300500" ? "active" : "";?>">검침</a>
            <a href="./bill_list.php" class="<?php echo $sub_menu == "300600" ? "active" : "";?>">고지서 관리</a>
            <a href="./move_request_list.php" class="<?php echo $sub_menu == "300700" ? "active" : "";?>">입주민 전출 신청 관리</a>
          </div>
        </li>
        <li>
            <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "400" ? "active" : ""; ?>">품의서</button>
            <div class="admin_sub_menu">
                <a href="./expense_list.php" class="<?php echo $sub_menu == "400100" ? "active" : "";?>">품의서 </a>
            </div>
        </li>
        <li>
            <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "500" ? "active" : ""; ?>">민원</button>
            <div class="admin_sub_menu">
                <a href="./complain_list.php?type=progress" class="<?php echo $sub_menu == "500100" ? "active" : "";?>">민원</a>
                <a href="./complain_list_bf.php" class="<?php echo $sub_menu == "500200" ? "active" : "";?>">민원(이전자료)</a>
            </div>
        </li>
        <li>
            <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "600" ? "active" : ""; ?>">온라인 투표</button>
            <div class="admin_sub_menu">
                <a href="./online_vote_list.php" class="<?php echo $sub_menu == "600100" ? "active" : "";?>">온라인 투표</a>
            </div>
        </li>
       
        <li>
         
            <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "700" ? "active" : ""; ?>">점검 일지</button>
            <div class="admin_sub_menu">
                <a href="./inspection_list.php" class="<?php echo $sub_menu == "700100" ? "active" : "";?>">점검 일지</a>
            </div>
        </li>
        <li>
            <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "800" ? "active" : ""; ?>">결재관리</button>
            <div class="admin_sub_menu">
                <a href="./approval_list.php" class="<?php echo $sub_menu == "800100" ? "active" : "";?>">결재관리</a>
                
                <a href="./approval_document_list.php" class="<?php echo $sub_menu == "800200" ? "active" : "";?>">결재서류함</a>
                <!-- <?php if($admin_level < 4){?>
                <?php }?> -->
            </div>
        </li>
        <?php 
        
        if($admin_level <= 4){?>
        <li>
            <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "810" ? "active" : ""; ?>">용역업체</button>
            <div class="admin_sub_menu">
                <a href="./contract_list_new.php" class="<?php echo $sub_menu == "810110" ? "active" : "";?>">용역업체</a>
                <a href="./company_list.php?transaction_status=Y" class="<?php echo $sub_menu == "810100" ? "active" : "";?>">업체관리</a>
                <a href="./senior_list.php" class="<?php echo $sub_menu == "810200" ? "active" : "";?>">선임자 정보 관리</a>
                <a href="./contract_date_list.php" class="<?php echo $sub_menu == "810300" ? "active" : "";?>">계약 기간 관리</a>
            </div>
        </li>
        <?php }?>
        <?php if($admin_level == 1){?>
        <li>
            <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "900" ? "active" : ""; ?>">담당자 관리</button>
            <div class="admin_sub_menu">
                <a href="./sm_manager.php" class="<?php echo $sub_menu == "900100" ? "active" : "";?>">담당자 관리</a>
            </div>
        </li>
        <?php }?>
        <?php if($admin_level < 4){?>
        <li>
            <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "910" ? "active" : ""; ?>">광고배너 관리</button>
            <div class="admin_sub_menu">
                <a href="./banner_list.php" class="<?php echo $sub_menu == "910100" ? "active" : "";?>">광고배너 관리</a>
            </div>
        </li>
        <?php }?>
        <li>
            <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "920" ? "active" : ""; ?>">사내용 게시판</button>
            <?php
            //게시판 리스트 가져오기
            $bbs_query = "SELECT * FROM a_bbs_setting WHERE is_view = 1 ORDER BY bbs_id asc";
            $bbs_res = sql_query($bbs_query);
            ?>
            <div class="admin_sub_menu">
                <?php for($i=0;$bbs_row = sql_fetch_array($bbs_res);$i++){?>
                    <a href="./bbs_list.php?bbs_code=<?php echo $bbs_row['bbs_code']; ?>" class="<?php echo $sub_menu == $bbs_row['bbs_menu'] ? "active" : "";?>"><?php echo $bbs_row['bbs_title']; ?> 게시판</a>
                <?php }?>
                <a href="./bbs_setting.php" class="<?php echo $sub_menu == "920920" ? "active" : "";?>">게시판 관리 </a>
            </div>
        </li>
        <li>
            <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "930" ? "active" : ""; ?>">사내용 캘린더</button>
            <?php
                //캘린더 리스트 가져오기
                $cal_query = "SELECT * FROM a_calendar_setting WHERE is_view = 1 ORDER BY is_prior asc, cal_id asc";
                $cal_res = sql_query($cal_query);
            ?>
            <div class="admin_sub_menu">
                <?php for($i=0;$cal_row = sql_fetch_array($cal_res);$i++){?>
                    <a href="./calendar_list.php?cal_code=<?php echo $cal_row['cal_code']; ?>" class="<?php echo $sub_menu == $cal_row['bbs_menu'] ? "active" : "";?>"><?php echo $cal_row['cal_name']; ?> 캘린더</a>
                <?php }?>
                <a href="./calendar_setting.php" class="<?php echo $sub_menu == "930910" ? "active" : "";?>">캘린더 관리</a>
            </div>
        </li>
        <?php if($admin_level < 3){?>
        <li>
          <button type="button" class="<?php echo substr($sub_menu, 0, 3) == "100" ? "active" : ""; ?>">설정</button>
          <div class="admin_sub_menu">
            <?php if($member['mb_level'] == 10){?>
            <a href="./member_form.php?w=u&mb_id=<?php echo $member['mb_id']; ?>" class="<?php echo $sub_menu == "100450" ? "active" : "";?>">최고괸리자 정보수정</a>
            <?php }?>
            <a href="./popup_list.php" class="<?php echo $sub_menu == "100100" ? "active" : "";?>">팝업관리</a>
            <a href="./push_list.php" class="<?php echo $sub_menu == "100200" ? "active" : "";?>">푸시알림 관리</a>
            <a href="./faq_list.php" class="<?php echo $sub_menu == "100300" ? "active" : "";?>">FAQ 관리</a>
            <a href="./contentlist.php" class="<?php echo $sub_menu == "100400" ? "active" : "";?>">약관 관리</a>
            <a href="./config_form.php" class="<?php echo $sub_menu == "100500" ? "active" : "";?>">기본설정</a>
          </div>
        </li>
        <?php }?>
      </ul>
    </div>
    <script>
      $(".admin_nav ul li > button").on("click", function(){
        $(this).next(".admin_sub_menu").slideToggle(300);
      })
    </script>
    <!-- <nav id="gnb" class="gnb_large <?php echo $adm_menu_cookie['gnb']; ?>">
        <h2>관리자 주메뉴</h2>
        <ul class="gnb_ul">
            <?php
            $jj = 1;
            foreach ($amenu as $key => $value) {
                $href1 = $href2 = '';

                if (isset($menu['menu' . $key][0][2]) && $menu['menu' . $key][0][2]) {
                    $href1 = '<a href="' . $menu['menu' . $key][0][2] . '" class="gnb_1da">';
                    $href2 = '</a>';
                } else {
                    continue;
                }

                $current_class = "";
                if (isset($sub_menu) && (substr($sub_menu, 0, 3) == substr($menu['menu' . $key][0][0], 0, 3))) {
                    $current_class = " on";
                }

                $button_title = $menu['menu' . $key][0][1];
            ?>
                <li class="gnb_li<?php echo $current_class; ?>">
                    <button type="button" class="btn_op menu-<?php echo $key; ?> menu-order-<?php echo $jj; ?>" title="<?php echo $button_title; ?>"><?php echo $button_title; ?></button>
                    <div class="gnb_oparea_wr">
                        <div class="gnb_oparea">
                            <h3><?php echo $menu['menu' . $key][0][1]; ?></h3>
                            <?php echo print_menu1('menu' . $key, 1); ?>
                        </div>
                    </div>
                </li>
            <?php
                $jj++;
            }     //end foreach
            ?>
        </ul>
    </nav> -->

</header>
<script>
    jQuery(function($) {

        var menu_cookie_key = 'g5_admin_btn_gnb';

        $(".tnb_mb_btn").click(function() {
            $(".tnb_mb_area").toggle();
        });

        $("#btn_gnb").click(function() {

            var $this = $(this);

            try {
                if (!$this.hasClass("btn_gnb_open")) {
                    set_cookie(menu_cookie_key, 1, 60 * 60 * 24 * 365);
                } else {
                    delete_cookie(menu_cookie_key);
                }
            } catch (err) {}

            $("#container").toggleClass("container-small");
            $("#gnb").toggleClass("gnb_small");
            $this.toggleClass("btn_gnb_open");

        });

        $(".gnb_ul li .btn_op").click(function() {
            $(this).parent().addClass("on").siblings().removeClass("on");
        });

    });
</script>
<!-- 알림 팝업 -->
<div class="cm_pop" id="admin_noti_pop" >
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('admin_noti_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_cont_wrapper">
            <div class="cm_pop_title">
                알림
            </div>
            <div class="admin_noti_view_btn_wrap">
                <button type="button" onclick="noti_all_check_handler();" class="view_btn btn btn_03">모두 읽음 처리</button>
            </div>
            <div class="admin_noti_list">
            </div>
         
            <script>

                function noti_all_check_handler(){

                    if (!confirm("모두 읽음 처리 하시겠습니까?")) {
                        return false;
                    }

                    let sendData = {'mb_id': '<?php echo $member['mb_id']?>'};

                    $.ajax({
                        type: "POST",
                        url: "./admin_noti_check_handler.php",
                        data: sendData,
                        cache: false,
                        async: false,
                        dataType: "json",
                        success: function(data) {
                            //console.log('data:::', data);

                            if(data.result == false) { 
                                alert(data.msg);
                                return false;
                            }else{
                                admin_noti_check();
                                notilist();
                                alert(data.msg);
                                popClose('admin_noti_pop');
                            }
                        },
                    });
                }

                $(document).on('click', '.pg_page_noti', function() {
                    var page = $(this).data('page');
                    var cls = '.pg_page' + page;

                    $(this).addClass('on').siblings().removeClass('on');
                    
                    // 예시: ajax 호출
                    $.ajax({
                        url: './admin.head.noti_page.php',
                        type: 'GET',
                        data: { page: page },
                        success: function(response) {
                            // console.log(response);

                            $(".admin_noti_list").html(response);
                        }
                    });
                });

                notilist();
                function notilist(page = 1){
                    // 예시: ajax 호출
                    $.ajax({
                        url: './admin.head.noti_page.php',
                        type: 'GET',
                        data: { page: page },
                        success: function(response) {
                            // console.log('notilist');

                            $(".admin_noti_list").html(response);
                        }
                    });
                }
            </script>
        </div>
    </div>
</div>

<div id="wrapper">

    <div id="container" class="<?php echo $adm_menu_cookie['container']; ?>">

        <h1 id="container_title"><?php echo $g5['title'] ?></h1>
        <div class="container_wr">