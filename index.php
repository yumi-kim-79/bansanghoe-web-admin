<?php
include_once('./_common.php');

define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(defined('G5_THEME_PATH')) {
    require_once(G5_THEME_PATH.'/index.php');
    return;
}

if (G5_IS_MOBILE) {
    include_once(G5_MOBILE_PATH.'/index.php');
    return;
}

include_once(G5_PATH.'/head.php');

$year = date("y");
$month = date("m");

$yearBill = date("Y");
$monthZero = date("n");

//이사/전출 요청 확인
$move_cnt = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_move_request WHERE mb_id = '{$user_info['mb_id']}' and ho_id = '{$user_building['ho_id']}' and is_del = 0");

$mv_param = "";
if($move_cnt['cnt'] > 0){
    $mv_param = "?w=u&mv_idx=".$move_cnt['mv_idx'];
}

//echo $ho_tenant_at_de;

$building_info = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_building_info WHERE building_id = '{$user_building['building_id']}'");


// $bill_first_row = "SELECT * FROM a_bill WHERE is_del = 0 and is_submit = 'Y' and building_id = '{$user_building['building_id']}' and created_at >= '{$ho_tenant_at_de}' ORDER BY bill_month desc limit 0, 1";
$bill_first_row = "SELECT * FROM a_bill WHERE is_del = 0 and is_submit = 'Y' and building_id = '{$user_building['building_id']}' and STR_TO_DATE(CONCAT(bill_year, '-', LPAD(bill_month, 2, '0'), '-01'), '%Y-%m-%d') >= '{$ho_tenant_at_de}' ORDER BY bill_month + 1 desc limit 0, 1";

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) {
// echo $bill_first_row.'<br>';
}
$bill_first_rows = sql_fetch($bill_first_row);

//고지서 정보있는지 확인
$bill_info = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_bill WHERE is_submit = 'Y' and building_id = '{$user_building['building_id']}' and bill_year = '{$bill_first_rows['bill_year']}' and bill_month = '{$bill_first_rows['bill_month']}'");

// print_r2($bill_info);
if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) {
    // echo $bill_first_row.'<br>';
    // echo "SELECT *, COUNT(*) as cnt FROM a_bill WHERE is_submit = 'Y' and building_id = '{$user_building['building_id']}' and bill_year = '{$bill_first_rows['bill_year']}' and bill_month = '{$bill_first_rows['bill_month']}'";
}
// $bill_info = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_bill WHERE building_id = '{$user_building['building_id']}' and bill_year = '{$yearBill}' and bill_month = '{$monthZero}' and is_submit = 'Y'");


if($bill_info['cnt'] > 0){
    $bill_items_ho = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$user_building['dong_name']}동' and bi_name = '동호'";

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // echo $bill_items_ho;
        // print_r2($user_building);
        // echo $_SESSION['users']['ho_id'];
    }

    $bill_item_ho_row = sql_fetch($bill_items_ho);

    $bi_option = explode("|", $bill_item_ho_row['bi_option']);

    $bi_opt_new_arr = array();
    foreach($bi_option as $key => $row){
        $opt_re = preg_replace('/[^0-9bB\-\|]/u', '', $row);
        array_push($bi_opt_new_arr, $opt_re);
    }

    $bi_opt_new_arr = implode("|", $bi_opt_new_arr);
    $bi_option = explode("|", $bi_opt_new_arr);
    
    $ho_indx = array_search($user_building['ho_name'], $bi_option);
  
    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // echo $ho_indx;

        // print_r2($bi_option);
    }

   
    $bill_items_price = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$user_building['dong_name']}동' and bi_name = '납기내금액'";

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // echo $bill_items_price;
    }


    $bill_items_price_row = sql_fetch($bill_items_price);

    $bill_price_option = explode("|", $bill_items_price_row['bi_option']);
    //echo $bill_items_price;

    $bill_price = $bill_price_option[$ho_indx];
    
}else{
    $bill_price = "-";
}

$ho_cnts = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho WHERE ho_tenant_id = '{$user_info['mb_id']}' and ho_status = 'Y'");

//외부인 관리단
$mng_building_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_mng_team WHERE mb_id = '{$user_info['mb_id']}' and is_del = 0 ORDER BY mt_id desc");

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo "SELECT COUNT(*) as cnt FROM a_building_ho WHERE ho_tenant_id = '{$user_info['mb_id']}' and ho_status = 'Y'"."<br>";
}

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $chk_app.'<br>';
    // echo $app_token.'<br>';
}
// print_r2($user_info);
?>
<div id="wrappers" class="bgs">
    <div class="wrap_container">
        <div class="inner ver2">
            <section class="sc_boxs idx_sc1">
                <div class="sc_boxs2">
                    <?php if($user_info['mb_type'] == "IN"){?>
                    <div class="sc_boxs2_tit">
                        <p class="idx_sc_title1"><?php echo $user_building['building_name']; ?></p>
                    
                        <p class="idx_sc_title2 mgt10"><?php echo $user_building['dong_name']; ?>동 <?php echo $user_building['ho_name']; ?>호</p>
                  
                    </div>
                    <?php }else{ ?>
                        <div class="sc_boxs2_tit">
                            <p class="idx_sc_title1"><?php echo $user_building['building_name'] ." - 외부인 ".$user_building['mt_name']." ".$user_building['gr_name']; ?></p>
                        </div>
                    <?php }?>
                    <?php if($user_info['mb_type'] == "IN" && $ho_cnts['cnt'] >= 2){?>
                    <div class="sc_box2_btn">
                        <button type="button" onclick="popOpen('ho_change_pop')">변경</button>
                    </div>
                    <?php }?>
                    <?php if($user_info['mb_type'] == "OUT" && $mng_building_cnt['cnt'] >= 2){?>
                    <div class="sc_box2_btn">
                        <button type="button" onclick="popOpen('mng_building_change_pop')">변경</button>
                    </div>
                    <?php }?>
                </div>
                <?php if($user_info['mb_type'] == "IN"){?>
                <div class="mng_price_info_box mgt20">
                    <p class="mng_price_title"><?php echo $bill_info['bill_year']; ?>년 <?php echo $bill_info['bill_month']; ?>월 관리비 요금</p>
                    <p class="mng_prices"><?php echo $bill_price; ?><span> 원</span></p>
                </div>
                <p class="idx_sc_title3 mgt10">납부 내역은 온라인 민원을 통해 문의하시면 상세히 답변 드리겠습니다.</p>
                <?php }?>
            </section>
            <section class="idx_sc2">
                <p class="idx_title">바로가기 메뉴</p>
                <div class="idx_sc2_box_wrap mgt10">
                    <a href="<?php echo $user_info['mb_type'] == "OUT" ? "javascript:notOpen();" : "/bill.php"; ?>" class="idx_sc2_box">
                        <div class="idx_sc2_img_box">
                            <img src="/images/<?php echo $user_info['mb_type'] == "OUT" ? "main_icon1_off" : "main_icon1";?>.svg" alt="관리비 내역">
                        </div>
                        <div class="idx_sc2_tit_box <?php echo $user_info['mb_type'] == "OUT" ? "off" : "";?>">관리비 내역</div>
                    </a>
                    <a href="/building_news.php" class="idx_sc2_box">
                        <div class="idx_sc2_img_box">
                            <img src="/images/main_icon2.svg" alt="단지소식">
                        </div>
                        <div class="idx_sc2_tit_box">단지소식</div>
                    </a>
                    <a href="<?php echo $user_info['mb_type'] == "OUT" ? "javascript:notOpen();" : "/online_vote.php"; ?>" class="idx_sc2_box">
                        <div class="idx_sc2_img_box">
                            <img src="/images/<?php echo $user_info['mb_type'] == "OUT" ? "main_icon3_off" : "main_icon3";?>.svg" alt="온라인 투표">
                        </div>
                        <div class="idx_sc2_tit_box <?php echo $user_info['mb_type'] == "OUT" ? "off" : "";?>">온라인 투표</div>
                    </a>
                    <a href="/expense_report.php" class="idx_sc2_box">
                        <div class="idx_sc2_img_box">
                            <img src="/images/main_icon4.svg" alt="품의서">
                        </div>
                        <div class="idx_sc2_tit_box">품의서</div>
                    </a>
                    <a href="<?php echo $user_info['mb_type'] == "OUT" ? "javascript:notOpen();" : "/move_request.php".$mv_param; ?>" class="idx_sc2_box">
                        <div class="idx_sc2_img_box">
                            <img src="/images/<?php echo $user_info['mb_type'] == "OUT" ? "main_icon5_off" : "main_icon5";?>.svg" alt="이사/전출">
                        </div>
                        <div class="idx_sc2_tit_box <?php echo $user_info['mb_type'] == "OUT" ? "off" : "";?>">이사/전출</div>
                    </a>
                    <a href="<?php echo $user_info['mb_type'] == "OUT" ? "javascript:notOpen();" : "/parking_manage.php"; ?>" class="idx_sc2_box">
                        <div class="idx_sc2_img_box">
                            <img src="/images/<?php echo $user_info['mb_type'] == "OUT" ? "main_icon6_off" : "main_icon6";?>.svg" alt="주차관리">
                        </div>
                        <div class="idx_sc2_tit_box <?php echo $user_info['mb_type'] == "OUT" ? "off" : "";?>">주차관리</div>
                    </a>
                    <a href="/inspection_lists.php" class="idx_sc2_box">
                        <div class="idx_sc2_img_box">
                            <img src="/images/main_icon7.svg" alt="점검일지">
                        </div>
                        <div class="idx_sc2_tit_box">점검일지</div>
                    </a>
                    <a href="/mng_company.php" class="idx_sc2_box">
                        <div class="idx_sc2_img_box">
                            <img src="/images/main_icon8.svg" alt="관리업체">
                        </div>
                        <div class="idx_sc2_tit_box">관리업체</div>
                    </a>
                    <a href="/mng_policy.php" class="idx_sc2_box">
                        <div class="idx_sc2_img_box">
                            <img src="/images/main_icon9.svg" alt="관리규약">
                        </div>
                        <div class="idx_sc2_tit_box">관리규약</div>
                    </a>
                </div>
            </section>
            <section class="idx_sc3 mgt30">
                <p class="idx_title">단지정보</p>
                <div class="idx_sc2_box_wrap ver2 mgt10">
                    <div class="building_info">
                        <div class="building_info_box">
                            <div class="building_info_label">건물명</div>
                            <div class="building_info_cont"><?php echo $building_info['building_info_name']; ?></div>
                        </div>
                        <div class="building_info_box">
                            <div class="building_info_label">용도</div>
                            <div class="building_info_cont"><?php echo $building_info['building_info_type']; ?></div>
                        </div>
                        <div class="building_info_box">
                            <div class="building_info_label">법정동 주소</div>
                            <div class="building_info_cont"><?php echo $building_info['building_info_addr1']; ?></div>
                        </div>
                        <div class="building_info_box">
                            <div class="building_info_label">도로명 주소</div>
                            <div class="building_info_cont"><?php echo $building_info['building_info_addr2']; ?></div>
                        </div>
                        <div class="building_info_box">
                            <div class="building_info_label">연면적(㎡)</div>
                            <div class="building_info_cont"><?php echo number_format($building_info['building_info_size'], 1); ?></div>
                        </div>
                        <div class="building_info_box">
                            <div class="building_info_label">사용승인일</div>
                            <div class="building_info_cont"><?php echo $building_info['building_info_use_date']; ?></div>
                        </div>
                        <div class="building_info_box">
                            <div class="building_info_label">층수(지상/지하)</div>
                            <div class="building_info_cont"><?php echo $building_info['building_info_floor_up']; ?></div>
                        </div>
                        <div class="building_info_box">
                            <div class="building_info_label">승강기(승용/비상)</div>
                            <div class="building_info_cont"><?php echo $building_info['building_info_elevation']; ?></div>
                        </div>
                        <div class="building_info_box">
                            <div class="building_info_label">주차대수(옥내/옥외)</div>
                            <div class="building_info_cont"><?php echo $building_info['building_info_parking1']; ?></div>
                        </div>
                        <div class="building_info_box">
                            <div class="building_info_label">구조</div>
                            <div class="building_info_cont"><?php echo $building_info['building_info_structure']; ?></div>
                        </div>
                        <div class="building_info_box">
                            <div class="building_info_label">기계식주차(옥내/옥외)</div>
                            <div class="building_info_cont"><?php echo $building_info['building_info_parking2']; ?></div>
                        </div>
                        <div class="building_info_box">
                            <div class="building_info_label">호수(호)</div>
                            <div class="building_info_cont"><?php echo $building_info['building_info_ho']; ?></div>
                        </div>
                    </div>
                </div>
            </section>
           
        </div>
         <!-- 배너 -->
         <?php
        $todays = date("Y-m-d");

        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) {
        //     $sqlsss = "";
        // }else{
        //     $sqlsss = "and bn.is_view = 1 and ( bn.banner_edate = '' or (bn.banner_sdate <= '{$todays}' and bn.banner_edate >= '{$todays}')) "; 
        // }

        $sqlsss = "and bn.is_view = 1 and ( bn.banner_edate = '' or (bn.banner_sdate <= '{$todays}' and bn.banner_edate >= '{$todays}')) ";
            
        $bn_sql = "SELECT bn.*, files.bf_file FROM a_banner as bn
                LEFT JOIN g5_board_file as files on bn.banner_id = files.wr_id
                WHERE bn.is_del = 0 and bn.banner_area = 'main' {$sqlsss}  and files.bo_table = 'banner' ORDER BY bn.is_prior asc, bn.banner_id desc";
        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) {
            // echo $bn_sql;
        }
        $bn_res = sql_query($bn_sql);
        $bn_total = sql_num_rows($bn_res);
        ?>
        <?php if($bn_total > 0){?>
        <div class="main_banner_wrap">
            <div class="swiper ban_main_swp">
                <div class="swiper-wrapper">
                    <?php for($i=0;$bn_row = sql_fetch_array($bn_res);$i++){
                    
                    $burl = $bn_row['burl_use'] ? $bn_row['burl'] : 'javascript:;';
                    $target = $bn_row['burl_use'] ? 'target="_blank"' : '';
                    ?>
                    <div class="swiper-slide">
                        <div>
                            <a href="<?php echo $burl; ?>" <?php echo $target; ?>>
                                <img src="/data/file/banner/<?php echo $bn_row['bf_file'];?>" alt="">
                            </a>
                        </div>
                    </div>
                    <?php }?>
                </div>
               
            </div>
        </div>
        <?php }?>
    </div>
</div>


<div class="cm_pop" id="ho_change_pop">
    <div class="cm_pop_back" onclick="popClose('ho_change_pop')"></div>
    <div class="cm_pop_cont ver_noborder">
        <div class="ho_select_box_wrap">
        <?php
        $ho_sqls = "SELECT ho.*, building.building_name, building.is_use, dong.dong_name FROM a_building_ho as ho
                    LEFT JOIN a_building as building on building.building_id = ho.building_id
                    LEFT JOIN a_building_dong as dong on dong.dong_id = ho.dong_id
                    WHERE ho.ho_tenant_id = '{$_SESSION['users']['id']}' and ho.is_del = 0 and building.is_use = 1 and ho.ho_status = 'Y' ORDER BY (ho.ho_name REGEXP '^[0-9]+$') ASC, CAST(ho.ho_name AS UNSIGNED), ho.ho_name ASC, ho.ho_id desc";
                    
                    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                        // echo $ho_sqls; 
                    }
        // echo $ho_sqls;
        $ho_res = sql_query($ho_sqls);

        while($ho_row = sql_fetch_array($ho_res)){
        
        ?>
        <button type="button" onclick="ho_selects('<?php echo $ho_row['ho_id']; ?>');" class="ho_select_box <?php echo $_SESSION['users']['ho_id'] == $ho_row['ho_id'] ? 'on' : '';?>" <?php echo $_SESSION['users']['ho_id'] == $ho_row['ho_id'] ? 'disabled' : '';?>>
            <?php echo $ho_row['building_name'].' '.$ho_row['dong_name'].'동 '.$ho_row['ho_name'].'호'; ?>
        </button>
        <?php }?>
        </div>

        <script>
        function ho_selects(ho_id){
          
            let mb_id = "<?php echo $_SESSION['users']['id'];?>";

            let sendData = {'mb_id': mb_id, "ho_id": ho_id};

            $.ajax({
                type: "POST",
                url: "./ho_select.php",
                data: sendData,
                cache: false,
                async: false,
                dataType: "json",
                success: function(data) {
                    console.log('data:::', data);

                    if(data.result == false) { 
                        showToast(data.msg);
                        //$(".btn_submit").attr('disabled', false);
                        return false;
                    }else{
                        showToast(data.msg);

                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                },
            });
        }
        </script>
    </div>
</div>


<!-- 외부인 관리단 단지 -->
<div class="cm_pop" id="mng_building_change_pop">
    <div class="cm_pop_back" onclick="popClose('mng_building_change_pop')"></div>
    <div class="cm_pop_cont ver_noborder">
        <div class="ho_select_box_wrap">
            <?php
            //외부인 관리단 단지 리스트
            $mng_building_list = "SELECT mt.*, mtg.gr_name, b.building_name, b.is_use  FROM a_mng_team as mt
                                    LEFT JOIN a_mng_team_grade as mtg on mtg.gr_id = mt.mt_grade
                                    LEFT JOIN a_building as b on mt.build_id = b.building_id
                                    WHERE mt.mb_id = '{$user_info['mb_id']}' and mt.is_del = 0 and b.is_use = 1 ORDER BY mt.mt_id desc";
            // echo $mng_building_list;
            $mng_building_res = sql_query($mng_building_list);

            while($mng_building_row = sql_fetch_array($mng_building_res)){
            ?>
            <button type="button" onclick="mng_building_selects('<?php echo $mng_building_row['mt_id']; ?>');" class="ho_select_box <?php echo $_SESSION['users']['mng_building'] == $mng_building_row['mt_id'] ? 'on' : '';?>" <?php echo $_SESSION['users']['mng_building'] == $mng_building_row['mt_id'] ? 'disabled' : '';?>>
                <?php echo $mng_building_row['building_name']." - 외부인 ".$mng_building_row['mt_name']." ".$mng_building_row['gr_name']; ?>
            </button>
            <?php }?>
        </div>
        <script>
        function mng_building_selects(mt_id){
          
            let mb_id = "<?php echo $_SESSION['users']['id'];?>";

            let sendData = {'mb_id': mb_id, "mt_id": mt_id};

            $.ajax({
                type: "POST",
                url: "./mng_build_select.php",
                data: sendData,
                cache: false,
                async: false,
                dataType: "json",
                success: function(data) {
                    console.log('data:::', data);

                    if(data.result == false) { 
                        showToast(data.msg);

                        if(data.data == 'reload'){
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        }
                        //$(".btn_submit").attr('disabled', false);
                        return false;
                    }else{
                        showToast(data.msg);

                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                },
            });
        }
        </script>
    </div>
</div>

<?php

$pop_list = "SELECT pop.*, files.bf_file FROM a_popup as pop
                LEFT JOIN g5_board_file as files on pop.pop_id = files.wr_id
                WHERE pop.is_del = 0 and pop.is_view = 1 and pop.pop_app = 'user' and (pop_edate = '' or (pop_sdate <= '{$todays}' and pop_edate >= '{$todays}') ) and files.bo_table = 'popup' ORDER BY pop.is_prior asc, pop.pop_id desc";
$pop_res = sql_query($pop_list);
$pop_total = sql_num_rows($pop_res);
?>

<?php 
// $styles = $user_info['mb_id'] == 'bansang_mb_7' ? 'display:block;' : '';
if($pop_total > 0){
?>
<!-- style="display:block;" -->
<div class="cm_pop" id="banner_pop" >
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont ver_banner">
        <div class="bn_pop_cont">
            <!-- <?php echo $banner_list;?> -->
            <div class="swiper ban_swp">
                <div class="swiper-wrapper">
                    <?php for($i=0;$file_row = sql_fetch_array($pop_res);$i++){?>
                    <div class="swiper-slide">
                        <div>
                            <img src="/data/file/popup/<?php echo $file_row['bf_file'];?>" alt="">
                        </div>
                    </div>
                    <?php }?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
        <div class="bn_pop_btn_wrap">
            <button type="button" onclick="hideForOneDay();">오늘 하루 안보기</button>
            <button type="button" onclick="closePopup();">닫기</button>
        </div>
	</div>
</div>
<?php }?>
<script>
let swiper = new Swiper(".ban_swp", {
    slidesPerView: "auto",
    pagination: {
        el: '.swiper-pagination',
        type: 'fraction',
    },
    autoHeight: true,
    autoplay: {
        delay: 2500,
    },
    loop:true,
});

let swiper2 = new Swiper(".ban_main_swp", {
    slidesPerView: "auto",
    autoHeight: true,
    autoplay: {
        delay: 2500,
    },
    loop:true,
});



function setCookie(name, value, days) {
    const date = new Date();
    date.setDate(date.getDate() + days);
    document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/';
}

function getCookie(name) {
    const value = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
    return value ? value[2] : null;
}

// 팝업 닫기 (쿠키 설정 없음)
function closePopup() {
    document.getElementById('banner_pop').style.display = 'none';
}

// 하루 동안 보지 않기 (쿠키 설정)
function hideForOneDay() {
    setCookie('hidePopup', 'yes', 1);
    closePopup();
}

window.onload = function () {
    if (getCookie('hidePopup') !== 'yes') {
        document.getElementById('banner_pop').style.display = 'block';
    }
}

$(".idx_sc2_box").on("mouseenter", function(){
    $(".idx_sc2_box").removeClass("active");
    $(this).addClass("active");
});

$(".idx_sc2_box").on("mouseleave", function(){
    $(".idx_sc2_box").removeClass("active");
});
</script>
<?php
include_once(G5_PATH.'/tail.php');
