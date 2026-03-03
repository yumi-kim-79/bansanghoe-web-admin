<?php
$sub_menu = "810100";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_manage_company as company left join a_industry_list as industry on industry.industry_idx = company.company_industry ";

$sql_search = " where (1) and company.is_del = '0' ";

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
    $industry_idx_sch_t = implode(',', $industry_idx_sch);
    $sql_search .= " and company.company_industry in ({$industry_idx_sch_t}) ";

    foreach($industry_idx_sch as $key => $val){
        $qstr .= '&industry_idx_sch[]='.$val;
    }
}

if($company_idx_sch){
    $company_idx_sch_t = implode(',', $company_idx_sch);
    $sql_search .= " and company.company_idx in ({$company_idx_sch_t}) ";

    foreach($company_idx_sch as $key => $val){
        $qstr .= '&company_idx_sch[]='.$val;
    }
}

if($transaction_status == 'Y'){
    $sql_search .= " and company.transaction_status IN ('Y', 'N') ";

    $qstr .= '&transaction_status=Y';
}else{
    $sql_search .= " and company.transaction_status IN ('Y') ";

    $qstr .= '';
}

if($industry_idx){
    $sql_search .= " and company.company_industry = '{$industry_idx}' ";

    $qstr .= '&industry_idx='.$industry_idx;
}

if($company_idx){
    $sql_search .= " and company.company_idx = '{$company_idx}' ";

    $qstr .= '&company_idx='.$company_idx;
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

$sql_order = " order by company.company_idx desc ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
//echo $sql.'<br>';
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
//$rows = 1;
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

$g5['title'] = "업체관리";

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$grade_sql = "SELECT * FROM a_grade WHERE is_del = 0 ORDER BY is_prior asc, gidx asc";
$grade_res = sql_query($grade_sql);


$sql = " select company.*, industry.industry_name {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 12;

$industry_sql = "SELECT * FROM a_industry_list WHERE is_del = 0 and is_use = 1 ORDER BY is_fixed desc, industry_idx asc";
$industry_res = sql_query($industry_sql);


$indus_where = '';
if($industry_idx != ''){
    $indus_where = "and company_industry = '{$industry_idx}'";
}

$cmp_sql = "SELECT * FROM a_manage_company WHERE is_del = 0 {$indus_where} ORDER BY company_name asc, company_idx asc";
$cmp_res = sql_query($cmp_sql);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;
}
//echo $st_status;
//echo $sub_menu;

?>
<link rel="stylesheet" href="/css/select2.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    .tbl_head01 tbody tr:nth-child(even) {background: #fff;}
    .tbl_head01 tbody tr.transaction_not {background: #eee;}
</style>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

    <label for="sfl" class="sound_only">검색대상</label>
    <div class="serach_box">
        <div class="sch_label">거래중지 여부</div>
        <div class="sch_selects">
            <input type="checkbox" name="transaction_status" id="transaction_status" value="Y" <?php echo $transaction_status == 'Y' ? 'checked' : ''; ?> style="margin-right:5px;">
            <label for="transaction_status">거래 중지 포함</label>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">업종</div>
        <div class="sch_selects">
            <?php
            $contract_industry_sql = "SELECT ct.industry_idx, ind.industry_name FROM a_contract as ct
            LEFT JOIN a_industry_list as ind on ct.industry_idx = ind.industry_idx
            WHERE ct.is_del = 0 GROUP BY ct.industry_idx ORDER BY ct.ct_idx asc";
            $contract_industry_res = sql_query($contract_industry_sql);
            ?>
            <div class="multi_select_wrap">
                <select multiple class="select" id="industry_idx_sch" name="industry_idx_sch[]" style="min-width:670px;" multiple="multiple">
                    <option value="">업종 선택</option>
                    <?php while($industry_row = sql_fetch_array($industry_res)){ ?>
                        <option value="<?php echo $industry_row['industry_idx']; ?>"><?php echo $industry_row['industry_name']; ?></option>
                    <?php }?>
                </select>
            </div>
        </div>
    </div>
    <?php
    $mng_company_sql = "SELECT * FROM a_manage_company WHERE transaction_status = 'Y' ORDER BY company_name asc, company_idx desc";
    $mng_company_res = sql_query($mng_company_sql);
    ?>
    <div class="serach_box">
        <div class="sch_label">업체</div>
        <div class="sch_selects ver_flex">
            <div class="multi_select_wrap">
                <select multiple class="select" id="company_idx_sch" name="company_idx_sch[]" style="min-width:335px;" multiple="multiple">
                    <option value="">선택</option>
                    <?php while($mng_company_row = sql_fetch_array($mng_company_res)){ ?>
                        <option value="<?php echo $mng_company_row['company_idx']; ?>"><?php echo $mng_company_row['company_name']; ?></option>
                    <?php }?>
                </select>
                
            </div>
            <button type="submit" style="margin-left:10px;" class="bansang_btns ver1">검색</button>
            <!-- <select name="company_idx" id="company_idx" class="bansang_sel">
                <option value="">항목 선택</option>
            </select> -->
        </div>
    </div>
    <!-- <div class="serach_box">
        <div class="sch_label">업체</div>
        <div class="sch_selects ver_flex">
            <input type="text" name="company_name" id="company_name" class="bansang_ipt ver2" value="<?php echo $company_name; ?>" size="50" style="margin-right:10px;">
           
            <button type="submit" style="margin-left:10px;" class="bansang_btns ver1">검색</button>
        </div>
    </div> -->
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

        $("#company_idx_sch").select2(
            {
                placeholder: "업체를 선택하세요", // 기본 placeholder 설정
                language: {
                    noResults: function() {
                        return "검색 결과가 없습니다."; // 원하는 문구로 변경
                    }
                }
            }
        );

        const urlParams2 = new URLSearchParams(window.location.search);
        const selectedCompany = urlParams2.getAll('company_idx_sch[]'); // 배열 파라미터 읽기

        // 선택값 적용
        if (selectedCompany.length > 0) {
            $("#company_idx_sch").val(selectedCompany).trigger('change');
        }
    });
</script>

<?php if($total_count > 0 && $admin_level < 4){?>
<div class="excel_download_wrap">
    <a href="./company_list_excel.php?<?php echo $qstr;?>" class="btn btn_04">업체 엑셀 다운로드</a>
</div>
<?php }?>
<form name="fcompanystatus" id="fcompanystatus" action="./company_list_status.php" onsubmit="return fcompanystatus_submit(this);" method="post">
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
                    <th>업종</th>
                    <th>업체명</th>
                    <th>사업자 등록번호</th>
                    <th>담당자</th>
                    <th>대표번호</th>
                    <th>연락처</th>
                    <th>입금은행</th>
                    <th>계좌번호</th>
                    <th>예금주</th>
                    <th>상태</th>
                    <th>상태변경</th>
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

                    <tr class="<?php echo $bg; ?> <?php echo $row['transaction_status'] == 'Y' ? '' : 'transaction_not';?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="checkbox" name="chk[]" value="<?php echo $row['company_idx']; ?>" id="chk_<?php echo $i ?>" <?php echo $row['transaction_status'] == 'Y' ? '' : 'disabled';?>>
                        </td> -->
                        <td><?php echo $row['industry_name']; ?></td>
                        <td><?php echo $row['company_name']; ?></td>
                        <td><?php echo $row['company_number']; ?></td>
                        <td><?php echo $row['company_mng_name']; ?></td>
                        <td><?php echo $row['company_tel']; ?></td>
                        <td><?php echo $row['company_mng_tel']; ?></td>
                        <td><?php echo $row['company_bank_name']; ?></td>
                        <td><?php echo $row['company_account_number']; ?></td>
                        <td><?php echo $row['company_account_name']; ?></td>
                        <td><?php echo $row['transaction_status'] == 'Y' ? '거래활성화' : '거래중지'; ?></td>
                        <td>
                            <?php if($row['transaction_status'] == 'Y'){?>
                            <button type="button" onclick="transaction_change_stop('<?php echo $row['company_name']; ?>', '<?php echo $row['company_idx']; ?>')" class="btn btn_01">거래중지</button>
                            <?php }else{ ?>
                            <button type="button" onclick="transaction_change('<?php echo $row['company_name']; ?>', '<?php echo $row['company_idx']; ?>')" class="btn btn_03">거래활성화</button>
                            <?php }?>
                        </td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <button type="button" onclick="company_info('<?php echo $row['company_idx']; ?>');" class="btn btn_03">관리</button>
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

    <?php if($admin_level < 4){?>
    <div class="btn_fixed_top">
        <button type="button" onclick="industry_pop_list();" class="btn btn_01">업종 관리</button>
        <button type="button" onclick="company_info();" class="btn btn_03">업체 추가</button>
        <!-- <input type="submit" name="act_button" value="거래중지" onclick="document.pressed=this.value" class="btn btn_02"> -->
        <!-- <?php if ($is_admin == 'super') { ?>
            <a href="./inspection_form.php" id="member_add" class="btn btn_03">점검일지 등록</a>
        <?php } ?> -->
    </div>
    <?php }?>

</form>

<div class="cm_pop" id="company_industry_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('company_industry_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">업종 관리</div>
        <div class="industry_form_wrap">
            <div class="industry_ipt_box mgt20">
                <input type="text" name="industry_name_add" id="industry_name_add" class="bansang_ipt full ver2" placeholder="업종명을 입력하세요." value="">
                <p>추가 할 업종이 이미 있는지 확인 후 업종 추가 하시기 바랍니다.</p>
                <p>같은 업종이여도 띄어쓰기, 문자 등으로 인해 다른업종으로 인식되는점 참고 바랍니다.<br>
                ex) “전기”, “전 기” 는 다른 업종으로 인식됩니다.</p>
                <div class="industry_add_btn_wrap mgt20">
                    <button type="button" onclick="industry_add();" class="industry_add_btn">추가</button>
                </div>
            </div>
        </div>
        <?php 
        $industry_fix = "SELECT * FROM a_industry_list WHERE is_fixed = 1 ORDER BY industry_idx asc";
        $industry_res = sql_query($industry_fix);
        ?>
        <div class="industry_fix_list mgt15">
            <?php for($i=0;$industry_row = sql_fetch_array($industry_res);$i++){?>
                <div class="industry_fix_box">
                    <input type="text" name="industry_fix_name[]" class="bansang_ipt ver2 full" readonly value="<?php echo $industry_row['industry_name']; ?>">
                </div>
            <?php }?>
        </div>
        <?php 
        $industry_nfix = "SELECT * FROM a_industry_list WHERE is_fixed = 0 and is_del = 0 ORDER BY industry_idx asc";
        //echo $industry_nfix;
        $industry_nres = sql_query($industry_nfix);
        ?>
        <form name="findustrylist" id="findustrylist" action="./company_industry_update.php" onsubmit="return findustrylist_submit(this);" method="post">
            <div class="industry_add_list">
                <?php for($i=0;$industry_nrow = sql_fetch_array($industry_nres);$i++){?>
                    <input type="hidden" name="industry_idx[]" value="<?php echo $industry_nrow['industry_idx']; ?>">
                    <div class="indutry_name_box ver2">
                        <input type="text" name="industry_name[]" class="bansang_ipt ver2" value="<?php echo $industry_nrow['industry_name']; ?>">
                        <div class="industry_use_status_box ver2 industry_use_status_box<?php echo $i + 1;?>">
                            <div class="indus_use_radio_box">
                                <input type="radio" name="is_use<?php echo $i + 1;?>" id="is_use<?php echo $i + 1;?>1" class="is_use1" value="1" <?php echo $industry_nrow['is_use'] == '1' ? 'checked' : ''; ?>>
                                <label class="is_use_label1" for="is_use<?php echo $i + 1;?>1">사용</label>
                            </div>
                            <div class="indus_use_radio_box">
                                <input type="radio" name="is_use<?php echo $i + 1;?>" id="is_use<?php echo $i + 1;?>2" class="is_use2" value="0" <?php echo $industry_nrow['is_use'] == '0' ? 'checked' : ''; ?>>
                                <label class="is_use_label2" for="is_use<?php echo $i + 1;?>2">미사용</label>
                            </div>
                            <!-- <div class="indus_del_box">
                                <button type="button" onclick="industry_cancel(this, ${indutry_box_length});" class="btn btn_03">취소</button>
                            </div> -->
                        </div>
                    </div>
                <?php }?>
            </div>
            <div class="industry_add_text mgt10">
                <p>사용/미사용 변경 후 저장 해야 상태값 저장 됩니다.</p>
                <p>등록한 업종은 삭제 불가능 합니다.</p>
            </div>
            <div class="industry_submit_btn_wrap mgt20">
                <button type="submit" class="btn btn_03 btn_submit">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
function industry_pop_list(){
    $.ajax({

    url : "./company_list_pop_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { }, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".industry_add_list").html(msg); 
        popOpen('company_industry_pop');
    }

    });
}
</script>

<div class="cm_pop" id="company_add_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('company_add_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">업체 추가</div>
        <form name="fcompany" id="fcompany" action="./company_list_update.php" onsubmit="return fcompany_submit(this);" method="post" autocomplete="off">
            <div class="company_add_form">
                <ul>
                    <li>
                        <p>사업자번호</p>
                        <div class="ipt_box">
                            <input type="text" name="company_number" class="bansang_ipt ver2 full" value="" placeholder="사업자 번호를 입력해주세요.">
                        </div>
                    </li>
                    <li>
                        <p>업체명 <span>*</span></p>
                        <div class="ipt_box ipt_flex">
                            <input type="hidden" name="company_name_chk" id="company_name_chk" value="N">
                            <input type="text" name="company_name" id="company_name" class="bansang_ipt ver2 ver3 full" value="" placeholder="업체명을 입력해주세요." required>
                            <button type="button" onclick="companyNameCheckHandler();" class="certify_btn">중복확인</button>

                            <script> 
                                function companyNameCheckHandler(){
                                    let company_name = $("#company_name").val();

                                    let sendData = {'company_name': company_name};

                                    $.ajax({
                                        type: "POST",
                                        url: "./company_list_name_check.php",
                                        data: sendData,
                                        cache: false,
                                        async: false,
                                        dataType: "json",
                                        success: function(data) {
                                            console.log('data:::', data);

                                            if(data.result == false) { 
                                                alert(data.msg);
                                                //$(".btn_submit").attr('disabled', false);
                                                if(data.data != ""){
                                                    $("#" + data.data).focus();
                                                }
                                                return false;
                                            }else{
                                                alert(data.msg);
                                                
                                                $(".certify_btn").text("확인완료");
                                                $(".certify_btn").attr({"disabled": true});
                                                $(".certify_btn").addClass('ver2');
                                                $("#company_name_chk").val("Y");
                                                $("#company_name").attr({"readonly": true});
                                            }
                                        },
                                    });
                                }
                                
                            </script>
                        </div>
                    </li>
                    <li>
                        <p>업종 <span>*</span></p>
                        <div class="ipt_box">
                        <?php 
                        $industry_sql = "SELECT * FROM a_industry_list WHERE is_use = 1 ORDER BY industry_idx asc";
                        //echo $industry_sql;
                        $industry_res2 = sql_query($industry_sql);
                        ?>
                            <select name="company_industry" id="company_industry" class="bansang_sel full" required>
                                <option value="">선택하세요.</option>
                                <?php while($industry_row2 = sql_fetch_array($industry_res2)){?>
                                    <option value="<?php echo $industry_row2['industry_idx']; ?>"><?php echo $industry_row2['industry_name']; ?></option>
                                <?php }?>
                            </select>
                        </div>
                    </li>
                    <li>
                        <p>대표번호</p>
                        <div class="ipt_box">
                            <input type="text" name="company_tel" class="bansang_ipt ver2 full" value="" placeholder="대표번호를 입력해주세요.">
                        </div>
                    </li>
                    <li>
                        <p>담당자</p>
                        <div class="ipt_box">
                            <input type="text" name="company_mng_name" class="bansang_ipt ver2 full" value="" placeholder="담당자를 입력해주세요.">
                        </div>
                    </li>
                    <li>
                        <p>담당자 연락처</p>
                        <div class="ipt_box">
                            <input type="text" name="company_mng_tel" class="bansang_ipt ver2 full" value="" placeholder="담당자 연락처를 입력해주세요.">
                        </div>
                    </li>
                    <li>
                        <p>은행명</p>
                        <div class="ipt_box">
                            <input type="text" name="company_bank_name" class="bansang_ipt ver2 full" value="" placeholder="은행명을 입력해주세요.">
                        </div>
                    </li>
                    <li>
                        <p>계좌번호</p>
                        <div class="ipt_box">
                            <input type="text" name="company_account_number" class="bansang_ipt ver2 full" value="" placeholder="계좌번호를 입력해주세요.">
                        </div>
                    </li>
                    <li>
                        <p>예금주</p>
                        <div class="ipt_box">
                            <input type="text" name="company_account_name" class="bansang_ipt ver2 full" value="" placeholder="예금주를 입력해주세요.">
                        </div>
                    </li>
                    <li>
                        <p>비고</p>
                        <div class="ipt_box">
                            <textarea name="company_memo" id="company_memo" class="bansang_ipt ta ver2 full"></textarea>
                        </div>
                    </li>
                </ul>
                <?php if($admin_level < 4){?>
                <div class="company_add_btn_wrap mgt20">
                    <button type="button" class="btn_cancel" onClick="popClose('company_add_pop');">취소</button>
                    <button type="submit" class="btn btn_03 btn_submit">저장</button>
                </div>
                <?php }?>
            </div>
        </form>
    </div>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>

document.getElementById("fcompany").addEventListener("keydown", function(event) {
    // console.log('keydown event', event);
  if (event.key === "Enter" && event.target.tagName !== "TEXTAREA") {
    event.preventDefault(); // 기본 동작(submit) 막기
  }
});

let add_industry_data = [];
function industry_add(){
    
   let industry_name_add = $("#industry_name_add").val();

   if(add_industry_data.includes(industry_name_add)){
        alert('이미 추가된 업종입니다.');
        return false;
   }

   let sendData = {'industry_name_add': industry_name_add};

   let indutry_box_length = $(".indutry_name_box").length + 1;
   let industry_html = `<div class="indutry_name_box">
                <input type="text" name="industry_name[]" class="bansang_ipt ver2" value="${industry_name_add}">
                <div class="industry_use_status_box industry_use_status_box${indutry_box_length}">
                    <div class="indus_use_radio_box">
                        <input type="radio" name="is_use${indutry_box_length}" id="is_use${indutry_box_length}1" class="is_use1" value="1" checked>
                        <label class="is_use_label1" for="is_use${indutry_box_length}1">사용</label>
                    </div>
                    <div class="indus_use_radio_box">
                        <input type="radio" name="is_use${indutry_box_length}" id="is_use${indutry_box_length}2" class="is_use2" value="0">
                        <label class="is_use_label2" for="is_use${indutry_box_length}2">미사용</label>
                    </div>
                    <div class="indus_del_box">
                        <button type="button" onclick="industry_cancel(this, ${indutry_box_length});" class="btn btn_03">취소</button>
                    </div>
                </div>
            </div>`;
    
    //console.log('add_industry_data', add_industry_data);

    $.ajax({
        type: "POST",
        url: "./company_list_industry_add_ajax.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                alert(data.msg);
                //$(".btn_submit").attr('disabled', false);
                return false;
            }else{
                //alert(data.msg);
                $("#industry_name_add").val("");

                $(".industry_add_list").append(industry_html);

                $(".industry_add_list").addClass("ver2");

                add_industry_data[indutry_box_length - 1] = industry_name_add;

                add_industry_data = add_industry_data;
            }
        },
    });

    console.log('add_industry_data2', add_industry_data);
}

function industry_cancel(ele, idx){
    ele.closest('.indutry_name_box').remove();

    updateAnswers();
}

function updateAnswers(){
    const industry = document.querySelectorAll('.industry_use_status_box');

    //console.log('industry', industry);
    industry.forEach((item, index) => {
        //console.log(index, `is_use` + (index + 1) + '' + '1');

        const inputField = item.querySelector('.is_use1');
        const newId = `is_use` + (index + 1) + '' + '1';
        inputField.id = newId;
        inputField.name = `is_use${index+1}`;

        const inputField2 = item.querySelector('.is_use2');
        const newId2 = `is_use` + (index + 1) + '' + '2';
        inputField2.id = newId2;
        inputField2.name = `is_use${index+1}`;

        const label = item.querySelector('.is_use_label1');
        if (label) {
            label.setAttribute('for', newId);
        }

        const label2 = item.querySelector('.is_use_label2');
        if (label2) {
            label2.setAttribute('for', newId2);
        }
    });
}

$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

function company_info(idx = ''){

    console.log(idx);
    $.ajax({

    url : "./company_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "company_idx":idx, "admin_level":"<?php echo $admin_level; ?>"}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        //console.log(msg);
        
        $(".company_add_form").html(msg);

        popOpen('company_add_pop');
    }

    });

}

//거래활성화
function transaction_change(cname, cidx){
    if (!confirm(cname + " 업체를 거래활성화 상태로 변경 하시겠습니까?")) {
        return false;
    }

    let sendData = {'cname': cname, 'cidx':cidx};

    $.ajax({
        type: "POST",
        url: "./company_list_transaction_change.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                alert(data.msg);
               
                return false;
            }else{
                alert(data.msg);

                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        },
    });
}

function transaction_change_stop(cname, cidx){
    if (!confirm(cname + " 업체를 거래중지 상태로 변경 하시겠습니까?")) {
        return false;
    }

    let sendData = {'cname': cname, 'cidx':cidx};

    $.ajax({
        type: "POST",
        url: "./company_list_transaction_stop_change.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                alert(data.msg);
            
                return false;
            }else{
                alert(data.msg);

                setTimeout(() => {
                    window.location.reload();
                }, 200);
            }
        },
    });
}

function fcompanystatus_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if (document.pressed == "거래중지") {
        if (!confirm("선택한 업체를 거래중지 하시겠습니까?")) {
            return false;
        }
    }
    
    return true;
}

function findustrylist_submit(){

    let indutry_name_box_cnt = $(".indutry_name_box").length;

    if(indutry_name_box_cnt == 0){
        alert('업종을 하나 이상 추가해주세요.');
        return false;
    }

    return true;
}

function fcompany_submit(f){

    if(f.company_name_chk.value == "N"){
        alert("업체명 중복확인을 진행해주세요.");
        return false;
    }

    return true;
}
</script>

<?php
require_once './admin.tail.php';
