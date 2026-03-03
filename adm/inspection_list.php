<?php
$sub_menu = "700100";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_inspection as insp
                left join a_building as building on insp.building_id = building.building_id
                left join a_building_dong as dong on insp.dong_id = dong.dong_id
                left join a_post_addr as post on building.post_id = post.post_idx
                left join a_industry_list as indus on insp.inspection_category = indus.industry_idx
                left join a_manage_company as cmp on insp.inspection_cmp = cmp.company_idx ";

$sql_search = " where (1) and insp.is_del = '0' and building.is_use = 1 ";

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
        case 'company_name':
            $sql_search .= " (cmp.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (insp.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($inspection_status){
    $sql_search .= " and insp.inspection_status = '{$inspection_status}' ";

    $qstr .= '&inspection_status='.$inspection_status;
}

if($inspection_category){
    $sql_search .= " and insp.inspection_category = '{$inspection_category}' ";

    $qstr .= '&inspection_category='.$inspection_category;
}

if($post_id2){
    $sql_search .= " and building.post_id = '{$post_id2}' ";

    $qstr .= '&post_id2='.$post_id2;
}

if($building_name2){
    $sql_search .= " and building.building_name like '%{$building_name2}%' ";

    $qstr .= '&building_name2='.$building_name2;
}

if($dong_id2){
    $sql_search .= " and insp.dong_id = '{$dong_id2}' ";

    $qstr .= '&dong_id2='.$dong_id2;
}

if($sst == 'deleted_at'){
    $sql_search2 .= " and std.is_del = 1 ";
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
    $sst = "std.st_idx";
    $sod = "desc";
}

$sql_order = " order by insp.inspection_idx desc ";

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

// 탈퇴회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and std.is_del = 1 {$sql_order} ";
//echo $sql;
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

$sql = " select count(*) as cnt {$sql_common} {$sql_search} and std.st_status = 2 {$sql_order} ";
//echo $sql;
$row = sql_fetch($sql);
$stop_count = $row['cnt'];

$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$g5['title'] = "점검일지";

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$grade_sql = "SELECT * FROM a_grade WHERE is_del = 0 ORDER BY is_prior asc, gidx asc";
$grade_res = sql_query($grade_sql);


$sql = " select insp.*, post.post_name, building.building_name, building.is_use, dong.dong_name, indus.industry_name, cmp.company_name {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 10;

$industry_sql = "SELECT * FROM a_industry_list WHERE is_del = 0 and is_use = 1 ORDER BY is_fixed desc, industry_idx asc";
$industry_res = sql_query($industry_sql);

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
        <div class="sch_label">상태</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="inspection_status" id="status1" value="" checked>
                <label for="status1">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="inspection_status" id="status2" value="N" <?php echo $inspection_status == 'N' ? 'checked' : '';?>>
                <label for="status2">승인대기</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="inspection_status" id="status3" value="Y" <?php echo $inspection_status == 'Y' ? 'checked' : '';?>>
                <label for="status3">승인</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="inspection_status" id="status4" value="R" <?php echo $inspection_status == 'R' ? 'checked' : '';?>>
                <label for="status4">재점검</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="inspection_status" id="status5" value="H" <?php echo $inspection_status == 'H' ? 'checked' : '';?>>
                <label for="status5">보류</label>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">항목</div>
        <div class="sch_selects">
            <select name="inspection_category" id="inspection_category" class="bansang_sel">
                <option value="">항목 선택</option>
                <?php while($industry_row = sql_fetch_array($industry_res)){?>
                    <option value="<?php echo $industry_row['industry_idx']; ?>" <?php echo get_selected( $industry_row['industry_idx'], $inspection_category);?>><?php echo $industry_row['industry_name']; ?></option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">지역</div>
        <div class="sch_selects">
            <?php
            $post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
            $post_res = sql_query($post_sql);
            ?>
            <select name="post_id2" id="post_id2" class="bansang_sel" onchange="post_change();">
                <option value="">지역 선택</option>
                <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                    <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($post_id2, $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                <?php }?>
            </select>
            <script>
                function post_change(){
                    $("#building_id2").val("");
                    $("#building_name2").val("");
                    $(".sch_result_box2").html("");
                }
            </script>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">단지</div>
        <div class="sch_selects">
            <div class="sch_ipt_boxs">
                <div class="sch_result_box sch_result_box2">
                </div>
                <input type="hidden" name="building_id2" id="building_id2" value="<?php echo $building_id2; ?>">
                <input type="text" name="building_name2" id="building_name2" class="bansang_ipt ver2 building_name_sch2" size="50" value="<?php echo $building_name2; ?>">
            </div>
        </div>
        <script>
            $(document).on("keyup", ".building_name_sch2", function(){
                let sch_text = this.value;
                
                console.log('keyup',sch_text);

                if(sch_text != ""){
                    let post_id = $("#post_id option:selected").val();

                    $.ajax({

                    url : "./building_mng_sch_text.php", //ajax 통신할 파일
                    type : "POST", // 형식
                    data: { "sch_category":"building_name", "sch_text":sch_text, "type":"Y", "post_id":post_id, "numbers":2}, //파라미터 값
                    success: function(msg){ //성공시 이벤트

                    
                        console.log(msg);
                        $(".sch_result_box2").html(msg); //.select_box2에 html로 나타내라..
                    }
                    })
                }else{
                    $(".sch_result_box2").html("");
                }
            
            });

            function sch_handler2(text, bid){
                $(".sch_result_box2").html("");
                $(".building_name_sch2").val(text);
                $("#building_id2").val(bid);

                $.ajax({

                url : "./building_mng_sch_ho_dong.ajax.php", //ajax 통신할 파일
                type : "POST", // 형식
                data: { "building_id":bid}, //파라미터 값
                success: function(msg){ //성공시 이벤트

                    console.log(msg);
                    $("#dong_id2").html(msg); //.select_box2에 html로 나타내라..
                }
                })
            }
        </script>
    </div>
    <!-- <div class="serach_box">
        <div class="sch_label">동</div>
        <div class="sch_selects">
            <?php
            $dong_sql = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id2}' ORDER BY dong_name asc, dong_id desc";
            $dong_res = sql_query($dong_sql);
            ?>
            <select name="dong_id2" id="dong_id2" class="bansang_sel">
                <option value="">동 선택</option>
                <?php
                while($dong_row = sql_fetch_array($dong_res)){
                ?>
                <option value="<?php echo $dong_row['dong_id'];?>" <?php echo get_selected($dong_id2, $dong_row['dong_id']); ?>><?php echo $dong_row['dong_name']; ?>동</option>
                <?php }?>
            </select>
        </div>
    </div> -->
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex" >
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="inspection_name" <?php echo get_selected($sfl, "inspection_name"); ?>>담당자</option>
                <option value="company_name" <?php echo get_selected($sfl, "company_name"); ?>>업체명</option>
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
                    <th>번호</th>
                    <th>지역</th>
                    <th>단지명</th>
                    <!-- <th>동</th> -->
                    <th>점검 항목</th>
                    <th>업체명</th>
                    <th>점검 기간</th>
                    <th>담당자</th>
                    <th>상태</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                    $month2 = str_pad($row['inspection_month'], 2, "0", STR_PAD_LEFT);
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="hidden" name="st_id[<?php echo $i ?>]" value="<?php echo $row['st_id'] ?>" id="st_id_<?php echo $i ?>">
                            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['st_name']); ?> <?php echo get_text($row['st_name']); ?>님</label>
                            <input type="checkbox" name="chk[]" value="<?php echo $i; ?>" id="chk_<?php echo $i ?>">
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
                        <!-- <td><?php echo $row['dong_name'].'동'; ?></td> -->
                        <td><?php echo $row['industry_name']; ?></td>
                        <td><?php echo $row['company_name']; ?></td>
                        <td><?php echo $row['inspection_year'].'-'.$month2; ?></td>
                        <td><?php echo $row['inspection_name']; ?></td>
                        <td>
                            <?php
                            switch($row['inspection_status']){
                                case "N":
                                    echo "승인대기";
                                    break;
                                case "Y":
                                    echo "승인";
                                    break;
                                case "R":
                                    echo "재점검";
                                    break;
                                case "H":
                                    echo "보류";
                                    break;
                            }
                             ?>
                        </td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./inspection_form.php?<?=$qstr;?>&amp;w=u&amp;inspection_idx=<? echo $row['inspection_idx']; ?>" class="btn btn_03">관리</a>
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
        <?php if ($is_admin == 'super') { ?>
            <button type="button" onclick="popOpen('inspection_qr_pop');" class="btn btn_01">QR 종합인쇄</button>
            <!-- <a href="./inspection_form.php" id="member_add" class="btn btn_03">점검일지 등록</a> -->
        <?php } ?>
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<div class="cm_pop" id="inspection_qr_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('inspection_qr_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">QR 종합 인쇄</div>
        <div class="qr_pop_cont">
            <div class="qr_sch_wrap">
                <div class="qr_select qr_form_box">
                    <?php
                    $post_sql = "SELECT * FROM a_post_addr WHERE is_del = 0 ORDER BY is_prior asc, post_idx asc";
                    $post_res = sql_query($post_sql);
                    ?>
                    <select name="post_id" id="post_id" class="bansang_sel full" onchange="post_change();">
                        <option value="">지역 선택</option>
                        <?php while($post_row = sql_fetch_array($post_res)){?>
                            <option value="<?php echo $post_row['post_idx']; ?>"><?php echo $post_row['post_name']; ?></option>
                        <?php }?>
                    </select>
                    <script>
                        function post_change(){
                            $("#building_id").val("");
                            $("#building_name").val("");
                            $(".sch_result_box1").html("");
                            $(".industry_wrap").hide();
                        }
                    </script>
                </div>
                <div class="qr_building_box qr_form_box">
                    <div class="qr_building_ipt_box">
                        <div class="sch_result_box sch_result_box1">
                            <!-- <button type="button">코스모</button> -->
                        </div>
                        <input type="hidden" name="building_id" id="building_id" value="">
                        <input type="text" name="building_name" id="building_name" class="bansang_ipt ver2 full building_name_sch" placeholder="단지검색">
                    </div>
                    <!-- <button type="button" class="sch_btn">검색</button> -->
                </div>
               
                <!-- <div class="qr_select qr_form_box">
                    <select name="dong_id" id="dong_id" class="bansang_sel full">
                        <option value="">동 선택</option>
                    </select>
                </div> -->
                <div class="qr_submit_btn_wrap mgt15">
                    <button type="button" onclick="qr_sch_hander();">검색</button>
                    <script>
                        function qr_sch_hander(){
                            let building_id = $("#building_id").val();
                            let dong_id = $("#dong_id option:selected").val();

                            if(building_id == ""){
                                alert('단지를 검색하여 선택해주세요.');
                                return false;
                            }

                            // if(dong_id == ""){
                            //     alert('동을 선택해주세요.');
                            //     return false;
                            // }

                            $(".industry_wrap").show();
                        }
                    </script>
                </div>
                <div class="industry_wrap">
                    <p>* 기본 업종 항목 + 관리자가 등록한 항목(사용) 보여집니다.</p>
                    <div class="industry_cont">
                        <div class="industry_select_box">
                            <input type="checkbox" name="industry_all" id="industry_all" value="1">
                            <label for="industry_all">전체</label>
                        </div>
                        <?php 
                        $industry_sql = "SELECT * FROM a_industry_list WHERE is_del = 0 and is_use = 1 ORDER BY is_fixed desc, industry_idx asc";
                        $industry_res = sql_query($industry_sql);

                        for($i=0;$indus_row = sql_fetch_array($industry_res);$i++){
                        ?>
                        <div class="industry_select_box">
                            <input type="checkbox" name="industry_idx" class="industry_chk" id="industry_idx<?php echo $i+1;?>" value="<?php echo $indus_row['industry_idx'];?>">
                            <label for="industry_idx<?php echo $i+1;?>"><?php echo $indus_row['industry_name'];?></label>
                        </div>
                        <?php }?>
                    </div>
                    <div class="qr_submit_btn_wrap qr_print_wrap mgt20">
                        <button type="button" onclick="print_info();">인쇄</button>
                    </div>
                </div>
            </div>
            <script>
            //전체 선택
            $("#industry_all").click(function () {
            console.log($("#industry_all").is(":checked"));
            if ($("#industry_all").is(":checked")) {
                $(".industry_chk").prop("checked", true);
            } else {
                $(".industry_chk").prop("checked", false);
            }
            $(".industry_chk").change();
            });
            $(".industry_chk").click(function () {
            var total = $(".industry_chk").length;
            var checked = $(".industry_chk:checked").length;

            if (total != checked) $("#industry_all").prop("checked", false);
            else $("#industry_all").prop("checked", true);

            });


            function print_info(){
                var lists = [];
                var building_id = $("#building_id").val();
                var dong_id = $("#dong_id option:selected").val();

                console.log(building_id, dong_id);

                $("input[name=industry_idx]:checked").each(function(i){   //jQuery로 for문 돌면서 check 된값 배열에 담는다
                    lists.push($(this).val());
                });

                if(lists == ""){
                    alert('인쇄하실 업종을 하나이상 선택해주세요.');
                    return false;
                }
                
                //console.log(lists.length);
                // if(lists.length > 12){
                //     alert('업종은 12개까지 인쇄가 가능합니다.');
                //     return false;
                // }

                if(building_id == ""){
                    alert('단지를 입력후 선택해주세요.');
                    return false;
                }

                // if(dong_id == ""){
                //     alert('동을 선택해주세요.');
                //     return false;
                // }

                //console.log(lists);
               
                let lists_split = lists.join(",");
                
                var opt = "width=810,height=1200,left=10,top=10"; 
                var url = "./inspection_print.php?building_id=" + building_id + "&dong_id=" + dong_id + "&ins_idx=" + lists_split;

                window.open(url, "win_news", opt); 

                return false; 
            }

            $(document).on("keyup", ".building_name_sch", function(){
                let sch_text = this.value;
                
                console.log('keyup',sch_text);
                $(".industry_wrap").hide();

                if(sch_text != ""){
                    //$("#building_id").val("");
                    let post_id = $("#post_id option:selected").val();
                    
                    $.ajax({

                    url : "./building_mng_sch_text.php", //ajax 통신할 파일
                    type : "POST", // 형식
                    data: { "sch_category":"building_name", "sch_text":sch_text, "type":"Y", "post_id":post_id}, //파라미터 값
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
</div>

<script>
$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

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
