<?php
$sub_menu = "700200";
require_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = "누락업체 조회";
require_once './admin.head.php';

// 기본값: 현재 년월
$sch_year = $sch_year ?: date("Y");
$sch_month = $sch_month ?: date("n");

// 지역 목록
$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

// 검색 실행
$results = [];
$total_missing = 0;
$total_buildings = 0;

if($_SERVER['REQUEST_METHOD'] == 'GET'){

    // 단지 필터
    $building_where = " AND b.is_use = 1 AND b.is_del = 0 ";
    if($post_id){
        $building_where .= " AND b.post_id = '{$post_id}' ";
    }
    if($building_name){
        $building_where .= " AND b.building_name LIKE '%{$building_name}%' ";
    }

    // 계약이 있는 단지 조회 (is_temp=0: 정식계약)
    $building_sql = "SELECT DISTINCT b.building_id, b.building_name, p.post_name
                     FROM a_contract AS ct
                     LEFT JOIN a_building AS b ON ct.building_id = b.building_id
                     LEFT JOIN a_post_addr AS p ON b.post_id = p.post_idx
                     WHERE ct.ct_status = 0 {$building_where}
                     ORDER BY p.post_name ASC, b.building_name ASC";
    $building_res = sql_query($building_sql);

    while($brow = sql_fetch_array($building_res)){
        $bid = $brow['building_id'];

        // 해당 단지의 계약 업종 목록
        $contract_sql = "SELECT ct.industry_idx, ind.industry_name, ct.company_idx, mc.company_name
                         FROM a_contract AS ct
                         LEFT JOIN a_industry_list AS ind ON ct.industry_idx = ind.industry_idx
                         LEFT JOIN a_manage_company AS mc ON ct.company_idx = mc.company_idx
                         WHERE ct.building_id = '{$bid}' AND ct.ct_status = 0
                         ORDER BY ind.industry_name ASC";
        $contract_res = sql_query($contract_sql);

        $industries = [];
        $missing_count = 0;

        while($crow = sql_fetch_array($contract_res)){
            $iidx = $crow['industry_idx'];

            // 해당 단지/업종/년월의 점검일지 조회
            $insp_sql = "SELECT inspection_status FROM a_inspection
                         WHERE building_id = '{$bid}'
                         AND inspection_category = '{$iidx}'
                         AND inspection_year = '{$sch_year}'
                         AND inspection_month = '{$sch_month}'
                         AND is_del = 0
                         ORDER BY inspection_idx DESC LIMIT 1";
            $insp_row = sql_fetch($insp_sql);

            if(!$insp_row || $insp_row['inspection_status'] == ''){
                $status = 'missing';
                $missing_count++;
            } else if($insp_row['inspection_status'] == 'R'){
                $status = 'rejected';
                $missing_count++;
            } else {
                $status = 'submitted'; // N(대기) or Y(승인) or H(보류)
            }

            $industries[] = [
                'industry_idx' => $iidx,
                'industry_name' => $crow['industry_name'],
                'company_name' => $crow['company_name'],
                'status' => $status,
            ];
        }

        if(count($industries) > 0){
            $total_buildings++;
            $total_missing += $missing_count;

            $results[] = [
                'building_id' => $bid,
                'building_name' => $brow['building_name'],
                'post_name' => $brow['post_name'],
                'industries' => $industries,
                'missing_count' => $missing_count,
                'total_count' => count($industries),
            ];
        }
    }
}
?>

<style>
.missing_summary {padding:10px 0;font-size:14px;color:#333;font-weight:600;margin-bottom:10px;}
.missing_summary span {color:#e74c3c;font-weight:700;}

.building_card {background:#fff;border:1px solid #d6dce7;border-radius:8px;margin-bottom:16px;overflow:hidden;}
.building_card_header {display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f4f6f9;border-bottom:1px solid #d6dce7;}
.building_card_title {font-size:14px;font-weight:700;color:#333;}
.building_card_title .post_label {font-size:12px;color:#888;font-weight:400;margin-right:6px;}
.building_card_count {font-size:13px;color:#666;}
.building_card_count .count_red {color:#e74c3c;font-weight:700;}

.industry_grid {display:flex;flex-wrap:wrap;padding:12px;gap:8px;}
.industry_item {
    flex:0 0 calc(20% - 8px);
    min-width:100px;
    text-align:center;
    padding:10px 6px;
    border-radius:6px;
    border:1px solid #e4e4e4;
    font-size:12px;
}
.industry_item .ind_name {font-weight:600;color:#333;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.industry_item .ind_company {font-size:11px;color:#999;margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.industry_item .ind_status {font-size:12px;font-weight:600;}

.industry_item.st_submitted {background:#e8f5e9;border-color:#4caf50;}
.industry_item.st_submitted .ind_status {color:#2e7d32;}
.industry_item.st_missing {background:#fff3e0;border-color:#ff9800;}
.industry_item.st_missing .ind_status {color:#e65100;}
.industry_item.st_rejected {background:#fce4ec;border-color:#e91e63;}
.industry_item.st_rejected .ind_status {color:#c62828;}

.no_missing_msg {text-align:center;padding:40px;color:#999;font-size:14px;}
</style>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <div class="serach_box">
        <div class="sch_label">지역</div>
        <div class="sch_selects ver_flex">
            <select name="post_id" class="bansang_sel">
                <option value="">전체</option>
                <?php for($i=0;$pr=sql_fetch_array($post_res);$i++){?>
                <option value="<?php echo $pr['post_idx'];?>" <?php echo get_selected($post_id, $pr['post_idx']);?>><?php echo $pr['post_name'];?></option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">단지</div>
        <div class="sch_selects">
            <input type="text" name="building_name" class="bansang_ipt ver2" value="<?php echo $building_name;?>" placeholder="단지명 입력" size="30">
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">년월</div>
        <div class="sch_selects ver_flex">
            <select name="sch_year" class="bansang_sel">
                <?php for($y=date("Y")-1;$y<=date("Y")+1;$y++){?>
                <option value="<?php echo $y;?>" <?php echo get_selected($sch_year, $y);?>><?php echo $y;?>년</option>
                <?php }?>
            </select>
            <select name="sch_month" class="bansang_sel">
                <?php for($m=1;$m<=12;$m++){?>
                <option value="<?php echo $m;?>" <?php echo get_selected($sch_month, $m);?>><?php echo $m;?>월</option>
                <?php }?>
            </select>
            <button type="submit" class="bansang_btns ver1">검색</button>
        </div>
    </div>
</form>

<div class="missing_summary">
    <?php echo $sch_year;?>년 <?php echo $sch_month;?>월 조회 결과:
    전체 <?php echo $total_buildings;?>개 단지 /
    <span>누락 <?php echo $total_missing;?>건</span>
</div>

<?php
// 누락 있는 단지 먼저, 그 다음 완료 단지
usort($results, function($a, $b){
    if($a['missing_count'] == 0 && $b['missing_count'] > 0) return 1;
    if($a['missing_count'] > 0 && $b['missing_count'] == 0) return -1;
    return $b['missing_count'] - $a['missing_count'];
});

foreach($results as $r){
    $status_labels = ['submitted'=>'✅제출', 'missing'=>'❌미제출', 'rejected'=>'🔄반려'];
?>
<div class="building_card">
    <div class="building_card_header">
        <div class="building_card_title">
            <span class="post_label"><?php echo $r['post_name'];?></span>
            <?php echo $r['building_name'];?>
        </div>
        <div class="building_card_count">
            <?php if($r['missing_count'] > 0){?>
            <span class="count_red">누락 <?php echo $r['missing_count'];?>건</span> / 전체 <?php echo $r['total_count'];?>건
            <?php }else{?>
            ✅ 전체 제출 (<?php echo $r['total_count'];?>건)
            <?php }?>
        </div>
    </div>
    <div class="industry_grid">
        <?php foreach($r['industries'] as $ind){?>
        <div class="industry_item st_<?php echo $ind['status'];?>">
            <div class="ind_name" title="<?php echo $ind['industry_name'];?>"><?php echo $ind['industry_name'];?></div>
            <div class="ind_company" title="<?php echo $ind['company_name'];?>"><?php echo $ind['company_name'] ?: '-';?></div>
            <div class="ind_status"><?php echo $status_labels[$ind['status']];?></div>
        </div>
        <?php }?>
    </div>
</div>
<?php }?>

<?php if(count($results) == 0){?>
<div class="no_missing_msg">계약된 단지가 없거나 검색 결과가 없습니다.</div>
<?php }?>

<?php
require_once './admin.tail.php';
