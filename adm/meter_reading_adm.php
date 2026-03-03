<?php
$sub_menu = "300500";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

// $sql_common = " from a_meter_building as mt_b
//                 left join a_building as b on mt_b.building_id = b.building_id 
//                 left join a_post_addr as post on b.post_id = post.post_idx
//                 left join a_mng_department as dept on mt_b.mr_department = dept.md_idx
//                 left join a_mng as mng on mt_b.wid = mng.mng_id ";

$sql_common = " from a_building as building 
                left join a_meter_building as mr_b on building.building_id = mr_b.building_id 
                left join a_post_addr as post on building.post_id = post.post_idx 
                left join a_mng_department as dept on mr_b.mr_department = dept.md_idx
                left join a_mng as mng on mr_b.wid = mng.mng_id ";

$sql_search = " where (1) and building.is_use = 1 ";

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
        case 'mng_name':
            $sql_search .= " (mng.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($mr_year){
    $sql_search .= " and mr_b.mr_year = '{$mr_year}' ";

    $qstr .= '&mr_year='.$mr_year;
}

if($mr_month){
    $sql_search .= " and mr_b.mr_month = '{$mr_month}' ";

    $qstr .= '&mr_month='.$mr_month;
}

if($building_name){
    $sql_search .= " and building.building_name like '%{$building_name}%' ";

    $qstr .= '&building_name='.$building_name;
}

if (!$sst) {
    $sst = "std.st_idx";
    $sod = "desc";
}

$sql_order = " order by building.building_name asc, mr_b.mr_year desc, mr_b.mr_month desc ";

//COUNT(DISTINCT building.building_id) AS cnt 
$sql = " select COUNT(*) AS cnt {$sql_common} {$sql_search} {$sql_order} ";
// echo $sql.'<br>';
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
//$rows = 5;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$g5['title'] = "검침";
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

//GROUP BY building.building_id 
$sql = " select mr_b.mr_idx, building.building_id, building.building_name, post.post_name, dept.md_name, mng.mng_name, mr_b.mr_year, mr_b.mr_month, mr_b.electro_date, mr_b.water_date {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 11;

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

//매니저 정보
$mng_info =  get_manger($member['mb_id']);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
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
        <div class="sch_label">년/월</div>
        <div class="sch_selects ver_flex">
            <select name="mr_year" id="mr_year" class="bansang_sel">
                <option value="">전체</option>
                <?php for($i=2025;$i<=2030;$i++){?>
                    <option value="<?php echo $i; ?>" <?php echo $mr_year == $i ? 'selected' : '';?>><?php echo $i; ?></option>
                <?php }?>
            </select>
            <select name="mr_month" id="mr_month" class="bansang_sel">
                <option value="">전체</option>
                <?php for($i=1;$i<=12;$i++){?>
                    <option value="<?php echo $i; ?>" <?php echo $mr_month  == $i ? 'selected' : '';?>><?php echo $i.'월'; ?></option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">단지</div>
        <div class="sch_selects">
            <div class="sch_ipt_boxs">
                <div class="sch_result_box sch_result_box1">
                </div>
                <input type="hidden" name="building_id" id="building_id" value="<?php echo $building_id; ?>">
                <input type="text" name="building_name" id="building_name" class="bansang_ipt ver2 building_name_sch" size="50" value="<?php echo $building_name; ?>">
            </div>
            <script>
                $(document).on("keyup", ".building_name_sch", function(){
                    let sch_text = this.value;
                    
                    console.log('keyup',sch_text);

                    if(sch_text != ""){

                        $.ajax({

                        url : "./building_mng_sch_text.php", //ajax 통신할 파일
                        type : "POST", // 형식
                        data: { "sch_category":"building_name", "sch_text":sch_text, "type":"Y"}, //파라미터 값
                        success: function(msg){ //성공시 이벤트

                        
                            console.log(msg);
                            $(".sch_result_box1").html(msg); //.select_box2에 html로 나타내라..
                        }
                        })
                    }else{
                        $(".sch_result_box1").html("");
                    }
                
                });

                function sch_handler(text, bid){
                    $(".sch_result_box1").html("");
                    $(".building_name_sch").val(text);
                    $("#building_id").val(bid);

                    $.ajax({

                    url : "./building_mng_sch_ho_dong.ajax.php", //ajax 통신할 파일
                    type : "POST", // 형식
                    data: { "building_id":bid}, //파라미터 값
                    success: function(msg){ //성공시 이벤트

                        console.log(msg);
                        $("#dong_id").html(msg); //.select_box2에 html로 나타내라..
                    }
                    })
                }
            </script>
        </div>
    </div>
    <!-- <div class="serach_box">
        <div class="sch_label">동/호수</div>
        <div class="sch_selects ver_flex building_dong_ho">
            <?php
            $dong_sql = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' ORDER BY dong_name asc, dong_id desc";
            $dong_res = sql_query($dong_sql);
            ?>
            <select name="dong_id" id="dong_id" class="bansang_sel" onchange="dong_change();">
                <option value="">동 선택</option>
                <?php
                while($dong_row = sql_fetch_array($dong_res)){
                ?>
                <option value="<?php echo $dong_row['dong_id'];?>" <?php echo get_selected($dong_row['dong_id'], $dong_id); ?>><?php echo $dong_row['dong_name']; ?></option>
                <?php }?>
            </select>
        </div>
    </div> -->
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="mng_name" <?php echo get_selected($sfl, "mng_name"); ?>>작성자</option>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx"  class="bansang_ipt ver2" size="50">
            <button type="submit" class="bansang_btns ver1">검색</button>
        </div>
    </div>

</form>

<!-- <div class="local_desc01 local_desc">
    <p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
    </p>
</div> -->

<?php if($total_count > 0){?>
<div class="excel_download_wrap">
    <button type="button" onclick="excelUploadPopOpen();" class="btn btn_04 mgr10">엑셀 업로드</button>
    <button type="button" onclick="excelDownloadHandler();" disabled class="btn btn_02 excel_down_btn">검침 엑셀 다운로드</button>
</div>
<?php }?>
<form name="fstudentlist" id="fstudentlist" action="./student_list_update.php" onsubmit="return fstudentlist_submit(this);" method="post">
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
                    <th></th>
                    <th>번호</th>
                    <th>지역</th>
                    <th>단지명</th>
                    <th>년도</th>
                    <th>월</th>
                    <th>부서</th>
                    <th>작성자</th>
                    <th>전기 검침날짜</th>
                    <th>수도 검침날짜</th>
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
                            <input type="hidden" name="st_id[<?php echo $i ?>]" value="<?php echo $row['st_id'] ?>" id="st_id_<?php echo $i ?>">
                            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['st_name']); ?> <?php echo get_text($row['st_name']); ?>님</label>
                            <input type="checkbox" name="chk[]" value="<?php echo $i; ?>" id="chk_<?php echo $i ?>">
                        </td> -->
                        <td>
                            <div class="building_radio_box">
                                <input type="checkbox" name="building_id_chk" class="building_id_chk" data-alias="building_id_chk" data-mridx="<?php echo $row['mr_idx'];?>" value="<?php echo $row['building_id']; ?>">
                            </div>
                        </td>
                        <td>
                            <?php
                            
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                        </td>
                        <td><?php echo $row['post_name']; ?></td>
                        <td><?php echo $row['building_name']; ?></td>
                        <td><?php echo $row['mr_year']; ?></td>
                        <td><?php echo $row['mr_month']; ?></td>
                        <td><?php echo $row['md_name']; ?></td>
                        <td><?php echo $row['mng_name']; ?></td>
                        <td><?php echo $row['electro_date']; ?></td>
                        <td><?php echo $row['water_date']; ?></td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <?php if($row['mr_idx'] != ''){?>
                            <a href="./meter_reading_form.php?<?=$qstr;?>&amp;w=u&amp;mr_idx=<? echo $row['mr_idx']; ?>" class="btn btn_03">관리</a>
                            <?php }?>
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

    <!-- <div class="btn_fixed_top">
        <?php if ($is_admin == 'super') { ?>
            <a href="./meter_reading_form.php" id="member_add" class="btn btn_03">검침 추가</a>
        <?php } ?>
    </div> -->


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<!-- 검침 엑셀 업로드 팝업 -->
<div class="cm_pop" id="mr_excel_type_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('mr_excel_type_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">엑셀 업로드</div>
        <div class="excel_upload_btn_wrap">
            <button type="button" onclick="excelUploadHandler('electro');" class="btn btn_04">전기 엑셀 업로드</button>
            <button type="button" onclick="excelUploadHandler('water');" class="btn btn_04">수도 엑셀 업로드</button>
        </div>
    </div>
</div>

<script>
$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

function excelDownloadHandler(){

    var selectedBuildingId = $('input[name="building_id_chk"]:checked').val();	
    var mridxValue = $('input[name="building_id_chk"]:checked').data('mridx');	

    console.log('mridxValue', mridxValue);

    location.href = "./meter_reading_adm_excel2.php?building_id=" + selectedBuildingId + "&mr_idx=" + mridxValue;
}

$(":checkbox[name='building_id_chk']").on("click", function(){
    if($(this).prop("checked") == true){

//        console.log(this);

        let classnames = $(this).data("alias");
        console.log('classnames', classnames);

        $("." + classnames).prop("checked", false);
        $(this).prop("checked", true);


        let mridx = $(this).data('mridx');

        if(mridx == ""){
            $(".excel_down_btn").attr("disabled", true);
            $(".excel_down_btn").addClass("btn_02");
            $(".excel_down_btn").removeClass("btn_04");
        }else{
            $(".excel_down_btn").attr("disabled", false);
            $(".excel_down_btn").addClass("btn_04");
            $(".excel_down_btn").removeClass("btn_02");
        }
    }else{
        $(".excel_down_btn").attr("disabled", true);
        $(".excel_down_btn").addClass("btn_02");
        $(".excel_down_btn").removeClass("btn_04");
    }
})

function excelUploadPopOpen(){

    //선택한 단지
    var selectedBuildingId = $('input[name="building_id_chk"]:checked').val();	

    if(!selectedBuildingId){
        alert("엑셀 업로드 하실 단지를 선택해주세요.");
        return false;
    } 

    $.ajax({

    url : "./meter_reading_building_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "building_id":selectedBuildingId}, //파라미터 값
    success: function(msg){ //성공시 이벤트
       
        $(".cm_pop_title").html(msg); 
        popOpen('mr_excel_type_pop');
    }

    });
    
}

function excelUploadHandler(type){

    popClose('mr_excel_type_pop');

    var selectedBuildingId = $('input[name="building_id_chk"]:checked').val();

    if(!selectedBuildingId){
        
        alert("엑셀 업로드 하실 단지를 선택해주세요.");
        return false;
    }

    let mryear = "<?php echo date("Y"); ?>"; //현재 연도
    let mrmonth = "<?php echo date("n"); ?>"; //현재 월
    let mrDepart = "<?php echo $mng_info['mng_department']; ?>"; //부서 인덱스
    let mrid = "<?php echo $member['mb_id']; ?>";

    let url = "./meter_reading_excel.php?building_id=" + selectedBuildingId + "&type=" + type + "&mr_department=" + mrDepart + "&mr_id=" + mrid;

    var opt = "width=600,height=450,left=10,top=10"; 

    window.open(url, "win_excel", opt); 

    return false; 
}

function fstudentlist_submit(f) {
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
