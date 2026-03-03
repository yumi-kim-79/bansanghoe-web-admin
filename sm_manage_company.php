<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$building_id}'";
$building_row = sql_fetch($building_sql);

$company_sql = "SELECT ct.*, building.building_name, industry.indutry_icon, cmp.company_tel FROM a_contract as ct
                LEFT JOIN a_building as building on ct.building_id = building.building_id
                LEFT JOIN a_industry_list as industry on ct.industry_idx = industry.industry_idx
                LEFT JOIN a_manage_company as cmp on ct.company_idx = cmp.company_idx
                WHERE ct.is_del = 0 and ct.ct_status = 0 and ct.is_temp = 0 and ct.resident_release = 0 and ct.building_id = '{$building_id}' ORDER BY ct.company_recom desc, ct.company_name asc";

$company_res = sql_query($company_sql);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <p class="mng_title"><?php echo $building_row['post_name']; ?> - <?php echo $building_row['building_name'];?></p>
            </div>
        </div>
        <div class="car_content">
            <div class="inner">
                <ul class="tab_lnb">
                    <!-- 250826 수정 <li class="tab01 on" onclick="tab_handler('1', 'out')" >입주민 공개 정보</li> -->
                    <li class="tab01 on" onclick="tab_handler('1', 'out')" >관리업체정보</li>
                    <!-- <li class="tab02" onclick="tab_handler('2', 'in')">내부 공개용(입주민 공유 금지)</li> -->
                    <li class="tab02" onclick="tab_handler('2', 'in')">입주민 비공개 업체</li>
                </ul>
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
            <div class="mng_list_box ver2">
                <div class="mng_company_alert">
                <p>계약 상세 정보는 입주민에게 공개 <strong>[ 절대 금지 ]</strong></p>
                <p>금액, 계약기간 등을 안내할 경우<br />문제 발생 할 수 있으므로 상급자 확인 필요</p>
                </div>
                <div class="inner content_box_wrap">
                    <?php for($i=0;$company_row = sql_fetch_array($company_res);$i++){
                    $indutry_icon_img = $company_row['indutry_icon'] != '' ? $company_row['indutry_icon'] : 'more_icon_sm.svg';        
                    ?>
                    <a href="/sm_mng_company_info.php" class="mng_boxs">
                        <div class="mng_cate_box">
                            <div class="mng_cate_img_box">
                                <img src="/images/<?php echo $indutry_icon_img;?>" alt="소방">
                            </div>
                            <div class="mng_cate"><?php echo $company_row['industry_name']; ?></div>
                        </div>
                        <div class="mng_infos ver2">
                            <div class="mng_info_boxs ver2">
                                <div class="mng_info_tit_box ver2">
                                    <div class="mng_info_tit ver2"><?php echo $company_row['company_name']; ?></div>
                                </div>
                                <div class="mng_info_ct">
                                    <div class="mng_info_ct_text">담당자 : <?php echo $company_row['mng_name1']; ?></div>
                                    <div class="mng_info_ct_text">연락처 : <?php echo $company_row['mng_hp1']; ?></div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php }?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";

tab_handler(tabIdx, 'out');

function tab_handler(index, code){
    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    let building_id = "<?php echo $building_id; ?>";

    $.ajax({

    url : "/sm_manage_company_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":code, "building_id":building_id}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".content_box_wrap").html(msg);
    }

    });
}

</script>
<?php
include_once(G5_PATH.'/tail.php');
?>