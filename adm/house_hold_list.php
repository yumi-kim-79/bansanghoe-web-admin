<?php
$sub_menu = "300100";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_building_ho as ho 
                left join a_building_dong as dong on ho.dong_id = dong.dong_id
                left join a_building as building on ho.building_id = building.building_id 
                left join a_post_addr as post on ho.post_id = post.post_idx ";

$sql_search = " where (1) and ho.is_del = '0' ";

// 1차 검색: 단지명
$stx_building_ids = [];
$stx_buildings = [];
if ($stx) {
    // 매칭된 단지 목록 조회
    $stx_building_sql = "SELECT building.*, post.post_idx as stx_post_id FROM a_building as building LEFT JOIN a_post_addr as post ON building.post_id = post.post_idx WHERE building.building_name like '%{$stx}%' and building.is_del = 0 ORDER BY building.building_name asc";
    $stx_building_res = sql_query($stx_building_sql);
    while($stx_b = sql_fetch_array($stx_building_res)){
        $stx_building_ids[] = $stx_b['building_id'];
        $stx_buildings[] = $stx_b;
    }

    // 단일 매칭: post_id/building_id 자동 적용 (수동 선택 없을 때)
    if(count($stx_buildings) == 1 && !$post_id && !$building_id){
        $post_id = $stx_buildings[0]['stx_post_id'];
        $building_id = $stx_buildings[0]['building_id'];
    }

    // 여러 매칭: building_id IN 조건으로 필터
    if(count($stx_buildings) > 1 && !$building_id){
        $stx_bids_filter = implode(',', array_map('intval', $stx_building_ids));
        $sql_search .= " and ho.building_id IN ({$stx_bids_filter}) ";
    } else if(count($stx_buildings) == 0){
        // 매칭 없음: building_name LIKE로 폴백
        $sql_search .= " and (building.building_name like '%{$stx}%') ";
    }
}

// 2차 검색: 소유자명/연락처/입주자명/연락처/호수/차량번호 통합
if ($stx2) {
    $sql_search .= " and (
        ho.ho_owner like '%{$stx2}%'
        OR ho.ho_owner_hp like '%{$stx2}%'
        OR ho.ho_tenant like '%{$stx2}%'
        OR ho.ho_tenant_hp like '%{$stx2}%'
        OR ho.ho_name like '%{$stx2}%'
        OR EXISTS (SELECT 1 FROM a_building_car as car WHERE car.ho_id = ho.ho_id AND car.is_del = 0 AND car.car_name like '%{$stx2}%')
    ) ";

    $qstr .= '&stx2='.$stx2;
}

if($ho_tenant_at){
    $sql_search .= " and ho.ho_tenant_at = '{$ho_tenant_at}' ";

    $qstr .= '&ho_tenant_at='.$ho_tenant_at;
}

if($ho_status){
    $sql_search .= " and ho.ho_status = '{$ho_status}' ";

    $qstr .= '&ho_status='.$ho_status;
}

if($post_id){
    $sql_search .= " and ho.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;

    $sql_building = "SELECT * FROM a_building WHERE post_id = '{$post_id}' and is_del = 0 and is_use = 1 ORDER BY building_name asc, building_id desc";
    $res_building = sql_query($sql_building);
}

if($building_id){
    $sql_search .= " and ho.building_id = '{$building_id}' ";

    $qstr .= '&building_id='.$building_id;

    $sql_dong = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' and is_del = 0";
    $res_dong = sql_query($sql_dong);
}

if($dong_id){
    $sql_search .= " and ho.dong_id = '{$dong_id}' ";

    $qstr .= '&dong_id='.$dong_id;
}

// 1차 검색 결과 단지의 동 목록 (building_id 미선택 시)
if(!$building_id && count($stx_building_ids) > 0){
    $stx_bids = implode(',', array_map('intval', $stx_building_ids));
    $sql_dong_stx = "SELECT DISTINCT dong.dong_id, dong.dong_name FROM a_building_dong as dong WHERE dong.building_id IN ({$stx_bids}) and dong.is_del = 0 ORDER BY dong.dong_name + 0 asc";
    $res_dong_stx = sql_query($sql_dong_stx);
}

if($sst == 'deleted_at'){
    $sql_search2 .= " and std.is_del = 1 ";
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
    $sst = "ho_id";
    $sod = "desc";
}

// $sql_order = " order by building.building_name asc, dong.dong_name + 0 asc, ho.ho_name + 0 asc, ho.ho_id desc ";
$sql_order = " order by building.building_name asc, dong.dong_name + 0 asc, (ho.ho_name REGEXP '^[0-9]+$') ASC,  CAST(ho.ho_name AS UNSIGNED), ho.ho_name ASC, ho.ho_id desc ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
//echo $sql.'<br>';
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
//$rows = 5;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$g5['title'] = "세대관리";
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>
<style>
/* 라벨 너비 통일 (상태/입주일/지역/검색 세로 정렬) */
#fsearch .serach_box .sch_label {
    width: 70px;
    min-width: 70px;
    max-width: 70px;
    flex-shrink: 0;
    box-sizing: border-box;
}
/* 2차 검색 구분선 */
.sch_divider {
    width: 1px;
    height: 20px;
    background: #ccc;
    margin: 0 5px;
    flex-shrink: 0;
}
#stx2 {
    width: 300px;
}
/* 세대관리 테이블 컴팩트 스타일 */
.tbl_head01 thead th {
    padding: 5px 4px;
}
.tbl_head01 tbody td {
    padding: 3px 4px;
    line-height: 1.3em;
}
.tbl_head01 tbody tr {
    height: auto;
}
.list_car_item, .list_hh_item {
    font-size: 12px;
    padding: 1px 0;
    white-space: nowrap;
}
.list_car_item + .list_car_item,
.list_hh_item + .list_hh_item {
    border-top: 1px dotted #ddd;
}
.td_mng_s .btn {
    padding: 3px 8px;
}
</style>
<?php


$sql = " select ho.*, building.building_name, building.is_use, dong.dong_name, post.post_name {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 16;

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);


if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $sql;
}
//echo $st_status;
//echo $sub_menu;

?>
<!-- <div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt">총 단지 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
    <a href="?sst=deleted_at&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01" data-tooltip-text="탈퇴된 순으로 정렬합니다.&#xa;전체 데이터를 출력합니다."> <span class="ov_txt">운영 </span><span class="ov_num"><?php echo number_format($leave_count) ?>건</span></a>
    <span class="btn_ov01"><span class="ov_txt">해지 </span><span class="ov_num"> <?php echo number_format($stop_count) ?>건 </span></span>
</div> -->


<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

    <label for="sfl" class="sound_only">검색대상</label>
   
    <div class="serach_box">
        <div class="sch_label">상태</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="ho_status" id="status1" value="" <?php echo $ho_status == "" ? "checked" : "";?>>
                <label for="status1">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="ho_status" id="status2" value="Y" <?php echo $ho_status == "Y" ? "checked" : "";?>>
                <label for="status2">입주</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="ho_status" id="status3" value="N" <?php echo $ho_status == "N" ? "checked" : "";?>>
                <label for="status3">퇴실</label>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">입주일</div>
        <div class="sch_selects">
            <input type="text" name="ho_tenant_at" class="bansang_ipt ver2 ipt_date" id="dates" value="<?php echo $ho_tenant_at; ?>">
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">지역</div>
        <div class="sch_selects ver_flex">
            <?php
            // 1차 검색 결과가 1개 단지면 지역 자동 선택
            $auto_post_id = $post_id;
            if(!$post_id && count($stx_buildings) == 1) $auto_post_id = $stx_buildings[0]['stx_post_id'];
            ?>
            <select name="post_id" id="post_id" class="bansang_sel" onchange="post_change();">
                <option value="">지역 선택</option>
                <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                    <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($auto_post_id, $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                <?php }?>
            </select>
            <select name="building_id" id="building_id" class="bansang_sel" onchange="building_change();">
                <option value="">단지 선택</option>
                <?php if($res_building){ while($row_building = sql_fetch_array($res_building)){ ?>
                <option value="<?php echo $row_building['building_id']?>" <?php echo get_selected($building_id, $row_building['building_id']); ?>><?php echo $row_building['building_name'];?></option>
                <?php }} else if(!$building_id && count($stx_buildings) > 0){ foreach($stx_buildings as $stx_brow){ ?>
                <option value="<?php echo $stx_brow['building_id']?>" <?php echo count($stx_buildings) == 1 ? 'selected' : ''; ?>><?php echo $stx_brow['building_name']; echo $stx_brow['is_use'] == 0 ? ' (해지)' : ''; ?></option>
                <?php }} ?>
            </select>
            <select name="dong_id" id="dong_id" class="bansang_sel">
                <option value="">동 선택</option>
                <?php if($res_dong){ while($row_dong = sql_fetch_array($res_dong)){ ?>
                <option value="<?php echo $row_dong['dong_id']?>" <?php echo get_selected($dong_id, $row_dong['dong_id']); ?>><?php echo $row_dong['dong_name'];?>동</option>
                <?php }} else if($res_dong_stx){ while($row_dong = sql_fetch_array($res_dong_stx)){ ?>
                <option value="<?php echo $row_dong['dong_id']?>" <?php echo get_selected($dong_id, $row_dong['dong_id']); ?>><?php echo $row_dong['dong_name'];?>동</option>
                <?php }} ?>
            </select>
        </div>
        <script>
        function post_change(){
            var postSelect = document.getElementById("post_id");
            var postValue = postSelect.options[postSelect.selectedIndex].value;

            console.log('postValue', postValue);

            $.ajax({

            url : "./post_building_ajax.php", //ajax 통신할 파일
            type : "POST", // 형식
            data: { "post_id":postValue}, //파라미터 값
            success: function(msg){ //성공시 이벤트

                //console.log(msg);
                $("#building_id").html(msg);
            }

            });
        }

        function building_change(){
            var buildingSelect = document.getElementById("building_id");
            var buildingValue = buildingSelect.options[buildingSelect.selectedIndex].value;

            console.log('buildingValue', buildingValue);

            $.ajax({

            url : "./building_dong_ajax.php", //ajax 통신할 파일
            type : "POST", // 형식
            data: { "building_id":buildingValue}, //파라미터 값
            success: function(msg){ //성공시 이벤트

                //console.log(msg);
                $("#dong_id").html(msg);
            }

            });
        }
    </script>
    </div>
    <input type="hidden" name="sfl" value="building_name">
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <div class="sch_ipt_boxs">
                <div class="sch_result_box sch_result_box1"></div>
                <label for="stx" class="sound_only">단지명 검색</label>
                <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="bansang_ipt ver2 building_name_sch" placeholder="단지명 검색" autocomplete="off">
            </div>
            <button type="submit" class="bansang_btns ver1">검색</button>
            <div class="sch_divider"></div>
            <label for="stx2" class="sound_only">상세 검색</label>
            <input type="text" name="stx2" value="<?php echo $stx2 ?>" id="stx2" class="bansang_ipt ver2" placeholder="소유자/입주자/연락처/호수/차량번호">
            <button type="submit" class="bansang_btns ver1">검색</button>
        </div>
    </div>

</form>
<script>
    // 1차 검색: 단지명 자동완성
    $(document).on("keyup", ".building_name_sch", function(){
        let sch_text = this.value;

        if(sch_text != ""){
            $.ajax({
                url : "./house_hold_list_sch_text.php",
                type : "POST",
                data: { "sch_category":"building_name", "sch_text":sch_text, "type":"Y"},
                success: function(msg){
                    $(".sch_result_box1").html(msg);
                }
            });
        }else{
            $(".sch_result_box1").html("");
        }
    });

    // 자동완성 항목 선택 시 단지명 확정 + 동 목록 업데이트
    function sch_handler(text, bid){
        $(".sch_result_box1").html("");
        $(".building_name_sch").val(text);

        // 동 드롭다운 업데이트
        if(bid){
            $.ajax({
                url : "./building_dong_ajax.php",
                type : "POST",
                data: { "building_id": bid },
                success: function(msg){
                    $("#dong_id").html(msg);
                }
            });
        }
    }

    // 검색창 외부 클릭 시 자동완성 닫기
    $(document).on("click", function(e){
        if(!$(e.target).closest(".sch_ipt_boxs").length){
            $(".sch_result_box1").html("");
        }
    });
</script>

<!-- <div class="local_desc01 local_desc">
    <p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
    </p>
</div> -->

<div class="excel_download_wrap">
    <a href="./house_hold_list_excel.php?<?php echo $qstr;?>" class="btn btn_04">세대 엑셀 다운로드</a>
</div>
<form name="fhouseholdlist" id="fhouseholdlist" action="./house_hold_list_update.php" onsubmit="return fhouseholdlist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="stx2" value="<?php echo $stx2 ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">

    <div class="tbl_head01 tbl_head03 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th scope="col" id="mb_list_chk" >
                        <label for="chkall" class="sound_only">회원 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th>번호</th>
                    <th>지역</th>
                    <th>단지명</th>
                    <th>동</th>
                    <th>호수</th>
                    <th>면적(㎡)</th>
                    <th>소유자</th>
                    <th>소유자 연락처</th>
                    <th>입주자</th>
                    <th>입주자 연락처</th>
                    <th>입주일</th>
                    <th>등록차량</th>
                    <th>세대구성원</th>
                    <th>상태</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                    
                    //차량 목록
                    $car_list_res = sql_query("SELECT car_type, car_name FROM a_building_car WHERE ho_id = '{$row['ho_id']}' and is_del = 0 and car_name != '' ORDER BY car_id asc");
                    $car_list = [];
                    while($car_item = sql_fetch_array($car_list_res)) $car_list[] = $car_item;

                    //세대구성원 목록
                    $hh_list_res = sql_query("SELECT hh_relationship, hh_name FROM a_building_household WHERE ho_id = '{$row['ho_id']}' and is_del = 0 and hh_name != '' ORDER BY hh_id asc");
                    $hh_list = [];
                    while($hh_item = sql_fetch_array($hh_list_res)) $hh_list[] = $hh_item;
                ?>

                    <tr class="<?php echo $row['ho_status'] == 'N' ? 'status_n' : ''; ?>">
                        <td headers="mb_list_chk" class="td_chk" >
                            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['ho_name']); ?> <?php echo get_text($row['ho_name']); ?>님</label>
                            <input type="checkbox" name="chk[]" value="<?php echo $row['ho_id']; ?>" id="chk_<?php echo $i ?>">
                        </td>
                        <td>
                            <?php
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                        </td>
                        <td><?php echo $row['post_name']; ?></td>
                        <td><?php echo $row['building_name']; echo $row['is_use'] == 0 ? '<span style="color:#e74c3c;"> (해지)</span>' : ''; ?></td>
                        <td><?php echo $row['dong_name']; ?></td>
                        <td><?php echo $row['ho_name']; ?></td>
                        <td><?php echo $row['ho_size'] ? number_format($row['ho_size'], 4) : '-'; ?></td>
                        <td><?php echo $row['ho_owner']; ?></td>
                        <td><?php echo $row['ho_owner_hp']; ?></td>
                        <td><?php echo $row['ho_status'] == 'N' ? '-' : $row['ho_tenant']; ?></td>
                        <td><?php echo $row['ho_status'] == 'N' ? '-' : $row['ho_tenant_hp']; ?></td>
                        <td><?php echo $row['ho_status'] == 'N' ? '-' : $row['ho_tenant_at']; ?></td>
                        <td>
                            <?php if($row['ho_status'] == 'N'){ echo '-'; }else{ ?>
                            <?php if(count($car_list) > 0){ foreach($car_list as $car_item){ ?>
                                <div class="list_car_item"><?php echo $car_item['car_type'] ? $car_item['car_type'].' ' : ''; echo $car_item['car_name']; ?></div>
                            <?php } }else{ echo '-'; } ?>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if($row['ho_status'] == 'N'){ echo '-'; }else{ ?>
                            <?php if(count($hh_list) > 0){ foreach($hh_list as $hh_item){ ?>
                                <div class="list_hh_item"><?php echo $hh_item['hh_relationship'] ? '['.$hh_item['hh_relationship'].'] ' : ''; echo $hh_item['hh_name']; ?></div>
                            <?php } }else{ echo '-'; } ?>
                            <?php } ?>
                        </td>
                        <td><?php echo $row['ho_status'] == 'Y' ? '입주' : '퇴실'; ?></td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./house_hold_form.php?<?=$qstr;?>&amp;w=<?php echo $row['ho_status'] == 'Y' ? 'u' : 'a';?>&amp;ho_id=<? echo $row['ho_id']; ?>" class="btn btn_03">관리</a>
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
        <!-- <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02"> -->
        <input type="submit" name="act_button" value="퇴실" onclick="document.pressed=this.value" class="btn btn_02">
        <!-- <?php if ($is_admin == 'super') { ?>
            <a href="./house_hold_form.php" id="member_add" class="btn btn_03">세대관리 등록</a>
        <?php } ?> -->

    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});

function fhouseholdlist_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 처리 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if (document.pressed == "퇴실") {
        if (!confirm("선택한 세대를 정말 퇴실 처리하시겠습니까?")) {
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
