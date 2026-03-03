<?php
require_once "./_common.php";

$start_date = date('Y-m-01');
$end_date = date('Y-m-t');

$sql_release = " and ct.ct_sdate <= '{$end_date}' and ct.ct_edate >= '{$start_date}' ";

if($code == 'out'){
    $sql_release .= " and ct.resident_release = 0 ";
}else{
    $sql_release .= " and ct.resident_release = 1 ";
}

$company_sql = "SELECT ct.*, building.building_name, industry.indutry_icon, mc.transaction_status FROM a_contract as ct
                LEFT JOIN a_building as building on ct.building_id = building.building_id
                LEFT JOIN a_industry_list as industry on ct.industry_idx = industry.industry_idx
                LEFT JOIN a_manage_company as mc on mc.company_idx = ct.company_idx
                WHERE ct.is_del = 0 and ct.is_temp = 0 and ct.building_id = '{$building_id}' and mc.transaction_status = 'Y' {$sql_release} ORDER BY ct.company_recom desc, ct.company_name asc";
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