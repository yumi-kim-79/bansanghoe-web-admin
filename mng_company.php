<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

//print_r2($user_building);

$company_sql = "SELECT ct.*, building.building_name, industry.indutry_icon FROM a_contract as ct
                LEFT JOIN a_building as building on ct.building_id = building.building_id
                LEFT JOIN a_industry_list as industry on ct.industry_idx = industry.industry_idx
                LEFT JOIN a_manage_company as cmp on ct.company_idx = cmp.company_idx
                WHERE ct.is_del = 0 and ct.ct_status = 0 and ct.is_temp = 0 and ct.resident_release = 0 and ct.building_id = '{$user_building['building_id']}' ORDER BY ct.company_recom desc, ct.company_name asc";
// echo $company_sql;
$company_res = sql_query($company_sql);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $company_sql;
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="mng_report_box">
            <div class="inner">
                <div class="report_btn_wraps">
                    <a href="tel:119" class="report_btn ver119">119 화재신고</a>
                    <a href="tel:112" class="report_btn ver112">112 범죄신고</a>
                </div>
                <p class="report_text mgt10">화재 및 범죄신고를 제외한 긴급 신고 및 고장접수는 각각의 업체로 문의하시기
                바랍니다.</p>
            </div>
        </div>
        <!--
         fire_icons_sm 소방
         parking_tower_icons 주차타워
         mng_icons 관리소장
         elector_icon_sm 전기
         elevetor_icons 승강기
         car_lift_sm 카리프트
         clean_icons_sm 청소
         disinfection_icons_sm 소독
         repair_icon_sm 기계설비
         more_icon_sm 기타
        -->
        <div class="mng_list_box">
            <div class="inner">
                <?php for($i=0;$company_row = sql_fetch_array($company_res);$i++){
                    $indutry_icon_img = $company_row['indutry_icon'] != '' ? $company_row['indutry_icon'] : 'more_icon_sm.svg';    

                    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP && $company_row['ct_idx'] == 32){
                        // print_r2($company_row);
                    }
                ?>
                <div class="mng_boxs">
                    <div class="mng_cate_box">
                        <div class="mng_cate_img_box">
                            <img src="/images/<?php echo $indutry_icon_img;?>" alt="소방">
                        </div>
                        <div class="mng_cate"><?php echo $company_row['industry_name']; ?></div>
                    </div>
                    <div class="mng_infos">
                        <div class="mng_info_boxs">
                            <div class="mng_info_tit_box ver2">
                                <?php if($company_row['company_recom']){?>
                                <div class="mng_cm_recommend">추천</div>
                                <?php }?>
                                <div class="mng_info_tit ver2"><?php echo $company_row['company_name']; ?></div>
                            </div>
                            <div class="mng_info_ct">
                                <?php if($company_row['mng_name1'] != ""){?>
                                <div class="mng_info_ct_text">담당자 : <?php echo $company_row['mng_name1']; ?></div>
                                <?php }?>
                                <?php if($company_row['company_tel'] != ""){?>
                                <div class="mng_info_ct_text">연락처 : <?php echo $company_row['company_tel']; ?></div>
                                <?php }?>
                            </div>
                        </div>
                        <?php if($company_row['company_tel'] != ""){?>
                        <a href="tel:<?php echo $company_row['company_tel']; ?>" class="mng_info_calls">
                            <img src="/images/phone_icons.svg" alt="">
                            전화걸기
                        </a>
                        <?php }?>
                    </div>
                </div>
                <?php }?>
                <?php if($i == 0){?>
                    <div class="bill_empty">등록된 업체가 없습니다.</div>
                <?php }?>
            </div>
        </div>
        <?php
        $todays = date("Y-m-d");
            
        $bn_sql = "SELECT bn.*, files.bf_file FROM a_banner as bn
                LEFT JOIN g5_board_file as files on bn.banner_id = files.wr_id
                WHERE bn.is_del = 0 and bn.banner_area = 'company' and bn.is_view = 1 and ( bn.banner_edate = '' or (bn.banner_sdate <= '{$todays}' and bn.banner_edate >= '{$todays}')) and files.bo_table = 'banner' ORDER BY bn.is_prior asc, bn.banner_id desc";
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
<script>
let swiper2 = new Swiper(".ban_main_swp", {
    loop:true,
    slidesPerView: "auto",
    autoHeight: true,
    autoplay: {
        delay: 2500,
    },
    loop:true,
});
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>