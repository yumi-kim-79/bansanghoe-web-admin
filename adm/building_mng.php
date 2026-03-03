<?php
$sub_menu = $_GET['type'] == "Y" ? "200200" : "200300";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_building as building left join a_post_addr as post on building.post_id = post.post_idx ";


if($_GET['type'] == 'Y'){
    $sql_search = " where (1) and building.is_del = '0' and building.is_use = 1 ";
    //$sql_search2 = " and building.is_use = 1 ";
    
}else{
    $sql_search = " where (1) and building.is_del = '0' and building.is_use = 0 ";
    //$sql_search2 = " and building.is_use = 0 ";
}

$qstr .= "&type=".$type;

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
            $sql_search .= " (building.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($post_id){
    $sql_search .= " and building.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
    $sst = "building_id";
    $sod = "desc";
}

if($_GET['type'] == 'Y'){
    $sql_order = " order by building.building_id desc ";
}else{
    $sql_order = " order by building.building_id desc ";
}

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
//echo $sql.'<br>';
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$sql_use = " select count(*) as cnt {$sql_common} where (1) and building.is_del = '0' and building.is_use = 1 {$sql_order} ";
$row_use = sql_fetch($sql_use);
$total_count_use = $row_use['cnt'];

$sql_no = " select count(*) as cnt {$sql_common} where (1) and building.is_del = '0' and building.is_use = 0 {$sql_order} ";
$row_no = sql_fetch($sql_no);
$total_count_no = $row_no['cnt'];

$rows = $config['cf_page_rows'];
// $rows = 2;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 탈퇴회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and building.is_use = 1 {$sql_order} ";
//echo $sql;
$row = sql_fetch($sql);
$use_count = $row['cnt'];

$sql = " select count(*) as cnt {$sql_common} {$sql_search} and building.is_use = 0 {$sql_order} ";
//echo $sql;
$row = sql_fetch($sql);
$not_count = $row['cnt'];

$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$g5['title'] = $_GET['type'] == 'Y' ? '단지 관리' : '해지 단지관리';
require_once './admin.head.php';

$sql = " select building.*, post.post_name {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

$colspan = 6;

$mb_ids = $member['mb_id'];
$mng_infos = get_manger($mb_ids);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql.'<br>';
    echo $sql_no.'<br>';
}
?>
<div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt">총 운영 단지 </span><span class="ov_num"> <?php echo number_format($total_count_use) ?>건 </span></span>
    <span class="btn_ov01"><span class="ov_txt">총 해지 단지 </span><span class="ov_num"> <?php echo number_format($total_count_no) ?>건 </span></span>
</div>


<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <label for="sfl" class="sound_only">검색대상</label>
    <div class="serach_box">
        <div class="sch_label">지역</div>
        <div class="sch_selects">
            <select name="post_id" id="post_id" class="bansang_sel">
                <option value="">지역 선택</option>
                <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                    <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($post_id, $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="building_name" <?php echo get_selected($sfl, "building_name"); ?>>단지명</option>
                <option value="building_addr" <?php echo get_selected($sfl, "building_addr"); ?>>주소</option>
            </select>
            <div class="sch_ipt_boxs">
                <div class="sch_result_box sch_result_box1">
                </div>
                <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="stx" value="<?php echo $stx ?>" id="stx"  class="bansang_ipt ver2 building_name_sch" size="50">
            </div>
            <button type="submit" class="bansang_btns ver1">검색</button>
            <!-- <input type="submit" class="btn_submit" value="검색"> -->
        </div>
    </div>

</form>
<script>
    $(document).on("keyup", ".building_name_sch", function(){
        let sch_text = this.value;
        
        console.log('keyup',sch_text);

        if(sch_text != ""){
            let sch_category = $("#sfl option:selected").val();
            let type = "<?php echo $type; ?>";
            let post_id = $("#post_id option:selected").val();

            console.log('building_name', sch_category);
            $.ajax({

            url : "./building_mng_sch_text.php", //ajax 통신할 파일
            type : "POST", // 형식
            data: { "sch_category":sch_category, "sch_text":sch_text, "type":type, "post_id":post_id}, //파라미터 값
            success: function(msg){ //성공시 이벤트

             
                console.log(msg);
                $(".sch_result_box1").html(msg); //.select_box2에 html로 나타내라..
            }
            })
        }else{
            $(".sch_result_box1").html("");
        }
      
    });

    function sch_handler(text){
        $(".sch_result_box1").html("");
        $(".building_name_sch").val(text);
    }
</script>

<?php if($total_count > 0){?>
<div class="excel_download_wrap">
    <a href="./building_mng_excel.php?<?php echo $qstr;?>" class="btn btn_04"><?php echo $type == 'Y' ? '' : '해지 ';?>단지 엑셀 다운로드</a>
</div>
<?php }?>
<form name="fbuildinglist" id="fbuildinglist" action="./building_list_update.php" onsubmit="return fbuildinglist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="type" value="<?php echo $type; ?>">

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
                    <th>지역</th>
                    <th>단지명</th>
                    <th>주소</th>
                    <th>운영여부</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="checkbox" name="chk[]" value="<?php echo $row
                            ['building_id']; ?>" id="chk_<?php echo $i ?>">
                        </td> -->
                        <td>
                        <?php
                            
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                        </td>
                        <td><?php echo $row['post_name']; ?></td>
                        <td><?php echo $row['building_name']; ?></td>
                        <td><?php echo $row['building_addr']; ?></td>
                        <td><?php echo $row['is_use'] == '1' ? '정상' : '해지'; ?></td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./building_mng_add.php?w=<?php echo $row['is_use'] ? 'u' : 'a';?>&amp;<?=$qstr;?>&amp;building_id=<?php echo $row['building_id']; ?>" class="btn btn_03">관리</a>
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
        <!-- <?php if (($member['mb_level'] == 10 || $mng_infos['mng_certi'] == 'A' || $mng_infos['mng_certi'] == 'B') && $type == "Y") { ?>
        <a href="./building_mng_add.php?type=<?php echo $type;?>" id="member_add" class="btn btn_03">단지 등록</a>
        <?php } ?> -->
        <a href="./building_mng_add.php?type=<?php echo $type;?>" id="member_add" class="btn btn_03">단지 등록</a>
    </div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
    function fbuildinglist_submit(f) {
        if (!is_checked("chk[]")) {
            alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
            return false;
        }

        if (document.pressed == "선택삭제") {
            if (!confirm("선택한 단지를 정말 해지하시겠습니까?")) {
                return false;
            }
        }

        // if (document.pressed == "선택승인") {
        //     if (!confirm("선택한 회원을 승인하시겠습니까?")) {
        //         return false;
        //     }
        // }

        return true;
    }
</script>

<?php
require_once './admin.tail.php';
