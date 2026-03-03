<?php
require_once "./_common.php";

$mng_building = get_mng_building($member['mb_id']);
$mng_building_t = "'".implode("','", $mng_building)."'";

$sql_where = "";
if($code != "all"){
    $sql_where .= " and insp.inspection_status = '{$code}' ";
}

$sql_where .= " and building.building_id IN ({$mng_building_t}) ";

if($industry_idx != ""){
    $sql_where .= " and insp.inspection_category = '{$industry_idx}' ";
}

if($company_name != ""){
    $sql_where .= " and cmp.company_name like '%{$company_name}%' ";
}


if($building_name != ""){
    $sql_where .= " and building.building_name like '%{$building_name}%' ";
}


$inspection_sql = "SELECT insp.*, building.building_name, building.is_use, cmp.company_name, industry.industry_name, industry.indutry_icon FROM a_inspection as insp
                    LEFT JOIN a_manage_company as cmp on insp.inspection_cmp = cmp.company_idx
                    LEFT JOIN a_industry_list as industry on insp.inspection_category = industry.industry_idx
                    LEFT JOIN a_building as building on insp.building_id = building.building_id
                    WHERE insp.is_del = 0 and building.is_use = 1 {$sql_where} ORDER BY insp.inspection_idx desc";
// echo $inspection_sql;
$inspection_res = sql_query($inspection_sql);

for($i=0;$inspection_row = sql_fetch_array($inspection_res);$i++){

    $inspection_icon = $inspection_row['indutry_icon'] != '' ? $inspection_row['indutry_icon'] : 'more_icon_sm.svg';

    switch($inspection_row['inspection_status']){
        case "N":
            $status = "승인대기";
            break;
        case "Y":
            $status = "승인";
            break;
        case "R":
            $status = "재점검";
            break;
        case "H":
            $status = "보류";
            break;
    }
?>
<li>
    <a href="/inspection_info_sm.php?inspection_idx=<?php echo $inspection_row['inspection_idx']; ?>">
        <div class="mng_cate_box">
            <div class="mng_cate_img_box">
                <img src="/images/<?php echo $inspection_icon?>" alt="<?php echo $inspection_row['industry_name']; ?>">
            </div>
            <div class="mng_cate"><?php echo $inspection_row['industry_name']; ?></div>
        </div>
        <div class="inspection_content">
            <div class="content_box_ct1">
                <span><?php echo $status; ?></span> <?php echo date('Y-m-d', strtotime($inspection_row['created_at'])); ?>
            </div>
            <div class="content_box_ct2">
                <?php echo $inspection_row['building_name'];?>
            </div>
            <div class="content_box_ct2">
            <?php echo $inspection_row['industry_name'];?> <?php echo $inspection_row['company_name'] == '' ? '' : '-'.$inspection_row['company_name']; ;?>
            </div>
        </div>
    </a>
</li>
<?php }?>
<?php if($i==0){?>
<li class="empty_inspections">등록된 점검일지가 없습니다.</li>
<?php }?>