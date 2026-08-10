<?php
require_once "./_common.php";

$start_date = date('Y-m-01');
$end_date = date('Y-m-t');

// ★관리자에서 등록한 계약은 앱에도 모두 보여야 한다(2026-08 요청).
//   예전에는 아래 두 조건 때문에 관리자 웹에는 있는데 앱에서만 조용히 사라지는 업체가 있었다.
//   ① 계약기간이 당월과 겹칠 것
//      → 계약 종료일이 지났어도 [계약해지] 처리 전이면 여전히 관리 대상이다.
//        노출 중단은 아래 루프의 **계약해지(ct_status) 판정 한 곳**으로만 결정한다.
//   ② mc.transaction_status = 'Y'
//      → a_manage_company를 LEFT JOIN 해놓고 WHERE에서 값을 비교해 사실상 INNER JOIN이었다.
//        업체관리에 연결된 업체 정보가 없거나(company_idx 불일치·업체 삭제) 거래상태가 'N'이면
//        계약이 멀쩡해도 행 자체가 빠졌다. 업체 마스터 상태로 계약 노출을 막지 않는다.
//   유지하는 조건: is_del(삭제) / is_temp(임시저장=미완성) / resident_release(공개·비공개 탭 분기)
$sql_release = "";

if($code == 'out'){
    $sql_release .= " and ct.resident_release = 0 ";
}else{
    $sql_release .= " and ct.resident_release = 1 ";
}

$company_sql = "SELECT ct.*, building.building_name, industry.indutry_icon, mc.transaction_status FROM a_contract as ct
                LEFT JOIN a_building as building on ct.building_id = building.building_id
                LEFT JOIN a_industry_list as industry on ct.industry_idx = industry.industry_idx
                LEFT JOIN a_manage_company as mc on mc.company_idx = ct.company_idx
                WHERE ct.is_del = 0 and ct.is_temp = 0 and ct.building_id = '{$building_id}' {$sql_release} ORDER BY ct.company_recom desc, ct.company_name asc";
// echo $company_sql;
if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $company_sql;
}
$company_res = sql_query($company_sql);
?>
<!-- <?php echo $code != 'out' ? '/sm_mng_company_info.php?ct_idx='.$company_row['ct_idx'] : 'javascript:;'?> -->
<?php for($i=0;$company_row2 = sql_fetch_array($company_res);$i++){
    
    $indutry_icon_img = $company_row2['indutry_icon'] != '' ? $company_row2['indutry_icon'] : 'more_icon_sm.svg';  

    //계약해지된 업체는 계약해지 일자 이후부터 노출 안함
    if($company_row2['ct_status'] == '1'){
        $not_company_date = $company_row2['ct_status_year'].'-'.$company_row2['ct_status_month'].'-01';
        $not_company_date = date('Y-m-t', strtotime($not_company_date));
        // echo $not_company_date;

        if($start_date > $not_company_date){
            continue;
        }
    }
   
    ?>
<a href="<?php echo '/sm_mng_company_info.php?ct_idx='.$company_row2['ct_idx']; ?>" class="mng_boxs <?php echo $code; ?>">
    <div class="mng_cate_box">
        <div class="mng_cate_img_box">
            <img src="/images/<?php echo $indutry_icon_img;?>" alt="소방">
        </div>
        <div class="mng_cate"><?php echo $company_row2['industry_name']; ?></div>
    </div>
    <div class="mng_infos ver2">
        <div class="mng_info_boxs ver2">
            <?php if($company_row2['resident_release']){?>
                <div class="resident_rel">입주민 비공개</div>
            <?php }?>
            <div class="mng_info_tit_box ver2">
                <div class="mng_info_tit ver2"><?php echo $company_row2['company_name']; ?></div>
            </div>
            <div class="mng_info_ct">
                <?php if($company_row2['mng_name1'] != ''){?>
                <div class="mng_info_ct_text">담당자 : <?php echo $company_row2['mng_name1']; ?></div>
                <?php }?>
                <?php if($company_row2['company_tel'] != ''){?>
                <div class="mng_info_ct_text">연락처 : <?php echo $company_row2['company_tel']; ?></div>
                <?php }?>
            </div>
        </div>
    </div>
</a>
<?php }?>
<?php if($i==0){?>
<div class="faq_empty_box">
    등록된 관리 업체가 없습니다.
</div>
<?php }?>