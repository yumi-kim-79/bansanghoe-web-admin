<?php
$sub_menu = "810200";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_senior as sn left join a_contract as ct on sn.ct_idx = ct.ct_idx left join a_building as building on ct.building_id = building.building_id ";

//and sn.sn_name != ''
$sql_search = " where (1) and sn.not_use = '0' and building.is_use = 1 ";


if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_tel':
        case 'pr_name':
            $sql_search .= " (par.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($industry_idx_sch){
    $industry_idx_sch_t = "'".implode("','", $industry_idx_sch)."'";
    $sql_search .= " and ct.industry_idx in ({$industry_idx_sch_t}) ";

    foreach($industry_idx_sch as $key => $val){
        $qstr .= '&industry_idx_sch[]='.$val;
    }
}

if($building_id_sch){
    $building_id_sch_t = "'".implode("','", $building_id_sch)."'";
    $sql_search .= " and ct.building_id in ({$building_id_sch_t}) ";

    foreach($building_id_sch as $key => $val){
        $qstr .= '&building_id_sch[]='.$val;
    }
}

if($sn_name_idx){
    $sn_name_idx_t = "'".implode("','", $sn_name_idx)."'";
    $sql_search .= " and sn.sn_name in ({$sn_name_idx_t}) ";

    foreach($sn_name_idx as $key => $val){
        $qstr .= '&sn_name_idx[]='.$val;
    }
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
    $sst = "std.st_idx";
    $sod = "desc";
}

if($order_bys == 'dday'){
    $sql_order = " order by dday asc, building.building_name asc, sn.sn_idx desc ";

    $qstr .= '&order_bys='.$order_bys;
}else{
    $sql_order = " order by building.building_name asc, dday desc, sn.sn_idx desc ";

    $qstr .= '&order_bys='.$order_bys;
}




$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
//$rows = 5;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$g5['title'] = "선임자 정보 관리";

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$sql = " select sn.*, ct.building_id, ct.company_name, ct.industry_idx, ct.industry_name, building.is_use, building.building_name, IFNULL(DATEDIFF(sn.edu_edate, CURDATE()), 0) AS dday {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 12;

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $total_count.'<br>';
    echo $sql;
}
//echo $st_status;
//echo $sub_menu;

?>
<link rel="stylesheet" href="/css/select2.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

    <label for="sfl" class="sound_only">검색대상</label>
    <div class="serach_box">
        <div class="sch_label">업종</div>
        <div class="sch_selects">
            <?php
            $industry_sql = "SELECT * FROM a_industry_list WHERE is_del = 0 and is_use = 1 ORDER BY is_fixed desc, industry_idx asc";
            $industry_res = sql_query($industry_sql);
            ?>
            <div class="multi_select_wrap">
                <select multiple class="select" id="industry_idx_sch" name="industry_idx_sch[]" multiple="multiple">
                    <option value="">업종 선택</option>
                    <?php while($industry_row = sql_fetch_array($industry_res)){ ?>
                        <option value="<?php echo $industry_row['industry_idx']; ?>"><?php echo $industry_row['industry_name']; ?></option>
                    <?php }?>
                </select>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">단지명</div>
        <div class="sch_selects">
            <div class="sch_ipt_boxs">
                <?php
                $building_sql = "SELECT * FROM a_building WHERE is_del = 0 and is_use = 1 ORDER BY building_name asc, building_id desc";
                $building_res = sql_query($building_sql);
                ?>
                <div class="multi_select_wrap">
                    <select multiple class="select" id="building_id_sch" name="building_id_sch[]" multiple="multiple">
                        <option value="">업종 선택</option>
                        <?php while($building_row = sql_fetch_array($building_res)){ ?>
                            <option value="<?php echo $building_row['building_id']; ?>"><?php echo $building_row['building_name']; ?></option>
                        <?php }?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">선임자명</div>
        <div class="sch_selects">
            <div class="multi_select_wrap">
                <?php
                $senior_sql = "SELECT * FROM a_senior WHERE not_use = 0 GROUP BY sn_name ORDER BY sn_idx desc, ct_idx desc";
                $senior_res = sql_query($senior_sql);
                ?>
                <select multiple class="select" id="sn_name_idx" name="sn_name_idx[]" multiple="multiple">
                    <option value="">선임자 선택</option>
                    <?php while($senior_row = sql_fetch_array($senior_res)){ ?>
                        <option value="<?php echo $senior_row['sn_name']; ?>"><?php echo $senior_row['sn_name']; ?></option>
                    <?php }?>
                </select>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">정렬순서</div>
        <div class="sch_selects ver_flex">
            <select name="order_bys" id="order_bys" class="bansang_sel">
                <option value="building_name" <?php echo get_selected($order_bys, "building_name"); ?>>단지명 순</option>
                <option value="dday" <?php echo get_selected($order_bys, "dday"); ?>>D-day순</option>
            </select>
            <button type="submit" class="bansang_btns ver1">검색</button>
        </div>
    </div>

</form>
<script>
    $(document).ready(function() {
        $("#industry_idx_sch").select2(
            {
                placeholder: "업종을 선택하세요", // 기본 placeholder 설정
                language: {
                    noResults: function() {
                        return "검색 결과가 없습니다."; // 원하는 문구로 변경
                    }
                }
            }
        );

        // URL 파라미터에서 선택값 가져오기
        const urlParams = new URLSearchParams(window.location.search);
        const selectedIndustries = urlParams.getAll('industry_idx_sch[]'); // 배열 파라미터 읽기

        // 선택값 적용
        if (selectedIndustries.length > 0) {
            $("#industry_idx_sch").val(selectedIndustries).trigger('change');
        }

        //단지 선택
        $("#building_id_sch").select2(
            {
                placeholder: "단지를 선택하세요", // 기본 placeholder 설정
                language: {
                    noResults: function() {
                        return "검색 결과가 없습니다."; // 원하는 문구로 변경
                    }
                }
            }
        );

        // URL 파라미터에서 선택값 가져오기
        const selectedBuildings = urlParams.getAll('building_id_sch[]'); // 배열 파라미터 읽기

        // 선택값 적용
        if (selectedBuildings.length > 0) {
            $("#building_id_sch").val(selectedBuildings).trigger('change');
        }

        //단지 선택
        $("#sn_name_idx").select2(
            {
                placeholder: "선임자를 선택하세요", // 기본 placeholder 설정
                language: {
                    noResults: function() {
                        return "검색 결과가 없습니다."; // 원하는 문구로 변경
                    }
                }
            }
        );

        // URL 파라미터에서 선택값 가져오기
        const selectedSenior = urlParams.getAll('sn_name_idx[]'); // 배열 파라미터 읽기

        // 선택값 적용
        if (selectedSenior.length > 0) {
            $("#sn_name_idx").val(selectedSenior).trigger('change');
        }
    });
</script>


<form name="fbannerlist" id="fbannerlist" action="./banner_list_update.php" onsubmit="return fbannerlist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">

    <div class="tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <!-- <th scope="col" id="mb_list_chk" >
                        <label for="chkall" class="sound_only">회원 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th> -->
                    <th>번호</th>
                    <th>단지명</th>
                    <th>업체명</th>
                    <th>업종</th>
                    <th>선임자명</th>
                    <th>연락처</th>
                    <th>선임일</th>
                    <th>선임기간</th>
                    <th>교육이수일</th>
                    <th>교육만료일</th>
                    <th>D-day</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                    $class_sql = "SELECT * FROM a_class WHERE is_del = 0 and gidx = '{$row['st_grade']}' order by is_prior asc, cl_idx asc";
                    //echo $class_sql;
                    $class_res = sql_query($class_sql);
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="checkbox" name="chk[]" value="<?php echo $row['banner_id']; ?>" id="chk_<?php echo $i ?>">
                        </td> -->
                        <td>
                            <?php
                            
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                        </td>
                        <td><?php echo $row['building_name']; ?></td>
                        <td><?php echo $row['company_name']; ?></td>
                        <td><?php echo $row['industry_name']; ?></td>
                        <td><?php echo $row['sn_name']; ?></td>
                        <td><?php echo $row['sn_hp']; ?></td>
                        <td><?php echo $row['sn_date']; ?></td>
                        <td><?php echo $row['sn_sdate'].' ~ '.$row['sn_edate']; ?></td>
                        <td><?php echo $row['edu_sdate'].' ~ '.$row['edu_edate']; ?></td>
                        <td><?php echo $row['edu_edate']; ?></td>
                        <td><?php echo $row['dday'] > 0 ? '-'.$row['dday'] : '+'.($row['dday'] * -1); ?></td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <!-- <a href="./banner_form.php?<?=$qstr;?>&amp;w=u&amp;banner_id=<? echo $row['banner_id']; ?>" class="btn btn_03">관리</a> -->
                            <button type="button" onclick="company_form_pop_open('<?php echo $row['ct_idx']; ?>');" class="btn btn_03">관리</button>
                        </td>
                    </tr>
                <?php
                }
                if ($i == 0) {
                    echo "<tr><td colspan=\"" . $colspan . "\" class=\"empty_table\">자료가 없습니다.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <!-- <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
        <?php if ($is_admin == 'super') { ?>
            <a href="./banner_form.php" id="member_add" class="btn btn_03">배너 등록</a>
        <?php } ?> -->
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<div class="cm_pop" id="contract_add_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont ver2">
        <div class="cm_pop_close_btn" onClick="popClose('contract_add_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_cont_seniors">
        </div>
    </div>
</div>

<!-- 연장하기 팝업 -->
<div class="cm_pop" id="contract_extend_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('contract_extend_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="extend_pop_wrap">
        </div>
    </div>
</div>

<script>
$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"-365d" });
});

function company_form_pop_open(idx = ''){
    $.ajax({

    url : "./contract_form_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "ct_idx":idx, "admin_level":"<?php echo $admin_level; ?>"}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        
        $(".cm_pop_cont_seniors").html(msg);

        popOpen('contract_add_pop');
    }

    });
}


//연장하기
function extend_pop_handler(idx){

$.ajax({

url : "./contract_extend_ajax.php", //ajax 통신할 파일
type : "POST", // 형식
data: { "ct_idx":idx}, //파라미터 값
success: function(msg){ //성공시 이벤트
    console.log(msg);
    
    $(".extend_pop_wrap").html(msg);

    popOpen('contract_extend_pop');
}

});
}

function company_form_pop_close(){

$(".ct_wrapper").html("");
popClose('contract_add_pop');
}



function fbannerlist_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 회원을 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    if (document.pressed == "선택승인") {
        if (!confirm("선택한 회원을 승인하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
require_once './admin.tail.php';
