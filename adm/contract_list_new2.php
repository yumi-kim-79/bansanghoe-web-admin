<?php

$sub_menu = "810110";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_contract as contract left join a_building as building on contract.building_id = building.building_id ";

$sql_search = " where (1) and contract.is_del = '0' ";

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

$sql_order = " order by contract.is_temp desc, contract.company_name asc, contract.ct_idx desc ";

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

$g5['title'] = "용역업체";

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$sql = " select contract.*, building.building_name {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 12;


$year = $select_year ? $select_year : date("Y");
$bfYear = 2017;
$afYear = $year + 10;

$nowMonth = $select_month ? $select_month : date("n");
$bfMonth =  $nowMonth - 1;
$afMonth =  $nowMonth + 1;

$prevYear        = ( $nowMonth == 1 )? ( $year - 1 ) : $year;
$prevMonth        = ( $nowMonth == 1 )? 12 : ( $nowMonth - 1 );
$nextYear        = ( $nowMonth == 12 )? ( $year + 1 ) : $year;
$nextMonth        = ( $nowMonth == 12 )? 1 : ( $nowMonth + 1 );


$sql_bf = " select contract.*, building.building_name {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows}";
$result_bf = sql_query($sql_bf);

$sql_now = " select contract.*, building.building_name {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows}";
$result_now = sql_query($sql_now);

$sql_af = " select contract.*, building.building_name {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows}";
$result_af = sql_query($sql_af);

$sql_where = " where (1) and contract.is_del = '0' ";

if($transaction_status){
    $sql_where .= " ";
}else{
    $sql_where .= " and contract.ct_status = '0' ";
}

if($industry_idx_sch){
    $industry_idx_sch_t = "'".implode("','", $industry_idx_sch)."'";
    $sql_where .= " and contract.industry_idx IN ({$industry_idx_sch_t}) ";
}

if($company_idx_sch){
    $company_idx_sch_t = "'".implode("','", $company_idx_sch)."'";
    $sql_where .= " and contract.company_idx IN ({$company_idx_sch_t}) ";
}

if($building_id_sch){
    $building_id_sch_t = "'".implode("','", $building_id_sch)."'";
    $sql_where .= " and contract.building_id IN ({$building_id_sch_t}) ";
}

if($ptIdxValue){

    $sql_where .= " and company_bill.payment_type = '{$ptIdxValue}' ";
}

if($paymentStatusSch){
    $sql_where .= " and IFNULL(payment_list.payment_status, 1) = '{$paymentStatusSch}' ";
}

if($billStatusSch){
    $sql_where .= " and IFNULL(bill_list.bill_statusm, 1) = '{$billStatusSch}' ";
}

if($btIdxSch){
    $sql_where .= " and bill_list.bill_type = '{$btIdxSch}' ";
}


$sql_ctct = "select contract.*, building.building_name, manage_company.transaction_status, company_bill.payment_type, IFNULL(payment_list.payment_status, 1) as ps, IFNULL(bill_list.bill_statusm, 1) as bs, bill_list.bill_type from a_contract as contract 
        left join a_building as building on contract.building_id = building.building_id 
        left join a_manage_company as manage_company on contract.company_idx = manage_company.company_idx
        left join a_contract_company_bill as company_bill on contract.ct_idx = company_bill.ct_idx
        left join a_payment_list as payment_list on contract.ct_idx = payment_list.ct_idx
        left join a_company_bill_list as bill_list on contract.ct_idx = bill_list.ct_idx
        {$sql_where} GROUP BY ct_idx
        order by contract.is_temp desc, contract.company_name asc, contract.ct_idx desc";
$res_ctct = sql_query($sql_ctct);

$total_ctct = sql_num_rows($res_ctct);


if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $sql_where.'<br>';
    echo $sql_ctct.'<br>';
    // echo $sql_now.'<br>';
}
//echo $st_status;
//echo $sub_menu;


?>
<!-- <div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt">총 단지 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
    <a href="?sst=deleted_at&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01" data-tooltip-text="탈퇴된 순으로 정렬합니다.&#xa;전체 데이터를 출력합니다."> <span class="ov_txt">운영 </span><span class="ov_num"><?php echo number_format($leave_count) ?>건</span></a>
    <span class="btn_ov01"><span class="ov_txt">해지 </span><span class="ov_num"> <?php echo number_format($stop_count) ?>건 </span></span>
</div> -->
<link rel="stylesheet" href="/css/select2.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="contract_top_wrap">
    <div class="contract_sch_box">
        <form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
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
                    <select multiple class="select" id="industry_idx_sch" name="industry_idx_sch[]" style="min-width:335px;" multiple="multiple">
                        <option value="">선택</option>
                        <?php while($contract_industry_row = sql_fetch_array($contract_industry_res)){ ?>
                            <option value="<?php echo $contract_industry_row['industry_idx']; ?>"><?php echo $contract_industry_row['industry_name']; ?></option>
                        <?php }?>
                    </select>
                </div>
            </div>
        </div>

        <!-- 업체 -->
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
                <!-- <select name="company_idx" id="company_idx" class="bansang_sel">
                    <option value="">항목 선택</option>
                </select> -->
            </div>
        </div>
        <!-- 단지 -->
        <?php
        $building_sql = "SELECT * FROM a_building WHERE is_del = 0 ORDER BY building_name asc, building_id desc";
        $building_res = sql_query($building_sql);
        ?>
        <div class="serach_box">
            <div class="sch_label">단지</div>
            <div class="sch_selects">
                <div class="multi_select_wrap">
                    <select multiple class="select" id="building_id_sch" name="building_id_sch[]" style="min-width:335px;" multiple="multiple">
                        <option value="">선택</option>
                        <?php while($building_sel_row = sql_fetch_array($building_res)){ ?>
                            <option value="<?php echo $building_sel_row['building_id']; ?>"><?php echo $building_sel_row['building_name']; ?></option>
                        <?php }?>
                    </select>
                </div>
                <!-- <input type="text" name="building_name" value="<?php echo $stx ?>" id="building_name"  class="bansang_ipt ver2" size="50"> -->
            </div>
        </div>
        <div class="serach_box">
            <div class="sch_label">날짜</div>
            <div class="sch_selects ver_flex">
                <select name="select_year" id="select_year" class="bansang_sel">
                    <option value="">년 선택</option>
                    <?php for($i=$bfYear;$i<=$afYear;$i++){?>
                        <option value="<?php echo $i; ?>" <?php echo get_selected($year, $i); ?>><?php echo $i; ?>년</option>
                    <?php }?>
                </select>
                <select name="select_month" id="select_month" class="bansang_sel">
                    <option value="">월 선택</option>
                    <?php for($i=1;$i<=12;$i++){?>
                        <option value="<?php echo $i; ?>" <?php echo get_selected($nowMonth, $i); ?>><?php echo $i; ?>월</option>
                    <?php }?>
                </select>
            </div>
        </div>
        <div class="serach_box">
            <div class="sch_label">지급여부</div>
            <div class="sch_selects ver_flex">
                <select name="payment_status_sch" id="payment_status_sch" class="bansang_sel">
                    <option value="">전체</option>
                    <option value="1" <?php echo $payment_status_sch == '1' ? 'selected' : ''; ?>>미지급</option>
                    <option value="2" <?php echo $payment_status_sch == '2' ? 'selected' : ''; ?>>지급</option>
                    <option value="3" <?php echo $payment_status_sch == '3' ? 'selected' : ''; ?>>서비스</option>
                    <option value="4" <?php echo $payment_status_sch == '4' ? 'selected' : ''; ?>>특이사항</option>
                </select>
            </div>
            <div class="sch_label">지급방식</div>
            <div class="sch_selects ver_flex">
                <?php
                $payment_type_sql = "SELECT * FROM a_payment_type WHERE is_del = 0 and is_use = 1 ORDER BY is_fixed desc, pt_idx asc";
                // echo $payment_type_sql;
                $payment_type_res = sql_query($payment_type_sql);
                ?>
                <select name="pt_idx_sch" id="pt_idx_sch" class="bansang_sel">
                    <option value="">전체</option>
                    <?php while($payment_type_row = sql_fetch_array($payment_type_res)){?>
                        <option value="<?php echo $payment_type_row['pt_idx']; ?>" <?php echo get_selected($pt_idx_sch, $payment_type_row['pt_idx'])?>><?php echo $payment_type_row['pt_name']; ?></option>
                    <?php }?>
                </select>
            </div>
        </div>
        <div class="serach_box">
            <div class="sch_label">계산서</div>
            <div class="sch_selects ver_flex">
               
                <select name="bill_status_sch" id="bill_status_sch" class="bansang_sel">
                    <option value="">전체</option>
                    <option value="1" <?php echo get_selected($bill_status_sch, '1'); ?>>발행전</option>
                    <option value="2" <?php echo get_selected($bill_status_sch, '2'); ?>>발행</option>
                    <option value="3" <?php echo get_selected($bill_status_sch, '3'); ?>>특이사항</option>
                </select>
            </div>
            <div class="sch_label">계산서 종류</div>
            <div class="sch_selects ver_flex">
                <?php
                $sql_bill_type = "SELECT * FROM a_company_bill_type WHERE is_del = 0 and is_use = 1 ORDER BY is_fixed desc, bt_idx asc";
                $bill_type_res = sql_query($sql_bill_type);
                ?>
                <select name="bt_idx_sch" id="bt_idx_sch" class="bansang_sel">
                    <option value="">전체</option>
                    <?php while($bill_type_row = sql_fetch_array($bill_type_res)){?>
                        <option value="<?php echo $bill_type_row['bt_idx'];?>" <?php echo get_selected($bt_idx_sch, $bill_type_row['bt_idx']); ?>><?php echo $bill_type_row['bill_name']; ?></option>
                    <?php }?>
                </select>
            </div>
            <div class="sch_selects ver_flex">
                <div class="sch_radios">
                    <input type="checkbox" name="transaction_status" id="transaction_status" value="1" <?php echo $transaction_status ? 'checked' : ''; ?>>
                    <label for="transaction_status">해지 포함</label>
                </div>
                <button type="submit" style="margin-left:20px;" class="bansang_btns ver1">검색</button>
            </div>
        </div>
        </form>
    </div>
    <div class="contract_btn_wrap">
        <div class="contract_btn_wrapper">
            <div class="ct_btn_box">
                <button type="button" class="ctn_btn ver2" onclick="contract_pt_pop_ajax();">지급 방식 관리</button>
                <button type="button" class="ctn_btn ver1" onclick="senior_move();">선임자 정보 관리</button>
            </div>
            <div class="ct_btn_box">
                <button type="button" class="ctn_btn ver2" onclick="contract_bt_pop_ajax();">계산서 종류 관리</button>
                <button type="button" class="ctn_btn ver1" onclick="contract_date_move();">계약기간 관리</button>
            </div>
            <div class="ct_btn_box">
                <button type="button" class="ctn_btn ver3" onclick="company_move();">업체 관리</button>
                <button type="button" class="ctn_btn ver3" onclick="company_form_pop_open();">계약 추가</button>
                <button type="button" onclick="ct_excel_download();" class="ctn_btn ver4">엑셀 다운로드</button>
            </div>
        </div>
        <div class="progress_btn_wrap">
            <button type="button" onclick="contract_payment_prg_pop_ajax()">지급처리</button>
            <button type="button" onclick="contract_bill_prg_pop_ajax();">세금계산서</button>
        </div>
    </div>
</div>
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

    const selectedCompany = urlParams.getAll('company_idx_sch[]'); // 배열 파라미터 읽기
    // 선택값 적용
    if (selectedCompany.length > 0) {
        $("#company_idx_sch").val(selectedCompany).trigger('change');
    }

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
});	
</script>

<!-- <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?> -->
<!-- <div class="local_desc01 local_desc">
    <p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
    </p>
</div> -->
<style>
    .empty_table td {padding:20px 0;border-bottom: 1px solid #ccc;}
</style>
<div class="table_wrapper">
    <div class="table_date table-header">
        <button type="button" id="prevMonth">이전</button>
        <div id="currentMonth" class="date_box"><?php echo $year; ?>년 <?php echo sprintf('%02d', $nowMonth); ?>월</div>
        <button type="button" id="nextMonth">다음</button>
    </div>

 
    <div class="table_view_all_wrap">
        <div class="table_color_box">
            <div class="temp_box">
                <span></span>
                임시저장
            </div>
            <div class="temp_box temp_box2">
                <span></span>
                계약해지
            </div>
        </div>
        <button type="button" id="viewAll">전체보기</button>
    </div>

    <div class="table-wrapper">
        <div class="left-table-container">
            <table class="fixed-table">
                <!-- 헤더 -->
                <thead>
                    <tr>
                        <th>업종</th>
                        <th>업체명</th>
                        <th>현장명</th>
                    </tr>
                </thead>
                <tbody id="fixedTableBody">
                    <!-- AJAX로 데이터 동기 삽입 -->
                </tbody>
            </table>
        </div>
        <!-- 오른쪽 가로 스크롤 테이블 (월별 데이터) -->
        <div class="right-table-container">
            <div class="table-scroll-x">
                <table class="scroll-table">
                    <thead id="scrollTableHead">
                    <!-- 월별 헤더 (3월 계산서 / 3월 지급일 ...) -->
                    </thead>
                    <tbody id="scrollTableBody">
                    <!-- AJAX로 데이터 동기 삽입 -->
                    </tbody>
                </table>
            </div>
        </div>
       
    </div>
    <table class="empty_table" style="display:none;">
        <tr>
            <td colspan='12'>등록된 계약 업체가 없습니다.</td>
        </tr>
    </table>
</div>

<script>
let currentYear = $("#select_year option").val() || "<?php echo $year; ?>";
let currentMonth = $("#select_month option").val() || "<?php echo $nowMonth; ?>";
let viewAll = false;

function updateHeader(year, month, viewAll) {
  const scrollHead = document.getElementById("scrollTableHead");
  let headerHtml = '<tr>';

  if (viewAll) {
    // — 전체보기 모드: 1월부터 12월까지
    for (let m = 1; m <= 12; m++) {
      headerHtml += `<th>${m}월</th><th>계산서</th><th>지급일</th>`;
    }
  } else {
    // — 일반 모드: 기준월 기준으로 −1, 0, +1
    for (let offset = -1; offset <= 1; offset++) {
      const date = new Date(year, month - 1 + offset);
      const m = date.getMonth() + 1; // 1~12
      headerHtml += `<th>${m}월</th><th>계산서</th><th>지급일</th>`;
    }
  }

  headerHtml += '</tr>';
  scrollHead.innerHTML = headerHtml;

  document.getElementById("currentMonth").textContent = viewAll
    ? `전체보기 (${year}년)`
    : `${year}년 ${month}월`;
}

function syncRowHeights() {
  const fixedRows = document.querySelectorAll("#fixedTableBody tr");
  const scrollRows = document.querySelectorAll("#scrollTableBody tr");

  const rowCount = Math.min(fixedRows.length, scrollRows.length);

  for (let i = 0; i < rowCount; i++) {
    const fixedRow = fixedRows[i];
    const scrollRow = scrollRows[i];

    // reset height first
    fixedRow.style.height = "auto";
    scrollRow.style.height = "auto";

    // get max height
    const fixedHeight = fixedRow.offsetHeight;
    const scrollHeight = scrollRow.offsetHeight;
    const maxHeight = Math.max(fixedHeight, scrollHeight);

    fixedRow.style.height = maxHeight + "px";
    scrollRow.style.height = maxHeight + "px";
  }
}

function loadTableData() {
  updateHeader(currentYear, currentMonth, viewAll); // 오른쪽 헤더 생성

      const xhr = new XMLHttpRequest();
    xhr.open("POST", "load_table_data2.php");

    // FormData 사용
    const formData = new FormData();

    // Select2에서 선택된 값들 가져오기
    const industryIdxschselectedValues = $("#industry_idx_sch").val() || [];
    const companyIdxschselectedValues = $("#company_idx_sch").val() || [];
    const buildingIdschselectedValues = $("#building_id_sch").val() || [];

    console.log('companyIdxschselectedValues', companyIdxschselectedValues);

    console.log('Select2 initialized?', $("#company_idx_sch").hasClass('select2-hidden-accessible'));
    console.log('Select element exists?', $("#industry_idx_sch").length);
    console.log('Select2 data:', $("#company_idx_sch").select2('data'));

    // 기본 파라미터
    formData.append('year', currentYear);
    formData.append('month', currentMonth);
    formData.append('viewAll', viewAll ? 1 : 0);

    // 배열 파라미터 추가
    industryIdxschselectedValues.forEach(val => {
        if (val && val !== '') {
            formData.append('industry_idx_sch[]', val);
        }
    });

    companyIdxschselectedValues.forEach(val => {
        if (val && val !== '') {
            formData.append('company_idx_sch[]', val);
        }
    });

    buildingIdschselectedValues.forEach(val => {
        if (val && val !== '') {
            formData.append('building_id_sch[]', val);
        }
    });

     //해지포함
    const transactionStatusValue = "<?php echo $transaction_status; ?>";

    //지급방식
    const ptIdxValue = "<?php echo $pt_idx_sch; ?>";

    const paymentStatusSch = "<?php echo $payment_status_sch; ?>";

    const billStatusSch = "<?php echo $bill_status_sch; ?>";

    const btIdxSch = "<?php echo $bt_idx_sch; ?>";

    // 기타 파라미터
    formData.append('transactionStatusValue', transactionStatusValue);
    formData.append('ptIdxValue', ptIdxValue);
    formData.append('paymentStatusSch', paymentStatusSch);
    formData.append('billStatusSch', billStatusSch);
    formData.append('btIdxSch', btIdxSch);

    xhr.onload = function () {
        console.log('xhr', xhr);
        if (xhr.status === 200) {
            $(".empty_table").hide();
            const [fixedHtml, scrollHtml] = xhr.responseText.split("<!-- SPLIT -->");
            document.getElementById("fixedTableBody").innerHTML = fixedHtml;
            document.getElementById("scrollTableBody").innerHTML = scrollHtml;
            syncRowHeights();
        } else {
            console.log('Error');
            $("#fixedTableBody").empty();
            $("#scrollTableBody").empty();
            $(".empty_table").show();
        }
        loadSumData();
    };

    xhr.send(formData);

  
}

const loadSumData = () => {
  const xhr2 = new XMLHttpRequest();
  xhr2.open("POST", "load_sum_data.php");
  xhr2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

  //검색필터
  // 업종 셀렉트 값들을 가져오기
  const industryIdxschSelect = document.getElementById("industry_idx_sch");
  const industryIdxschselectedValues = Array.from(industryIdxschSelect.selectedOptions).map(option => option.value);
  const industryfilterParams = industryIdxschselectedValues.map(val => `industry_idx_sch[]=${encodeURIComponent(val)}`).join("&");

  // 업체 셀렉트 값들을 가져오기
  const companyIdxschSelect = document.getElementById("company_idx_sch");
  const companyIdxschselectedValues = Array.from(companyIdxschSelect.selectedOptions).map(option => option.value);
  const companyfilterParams = companyIdxschselectedValues.map(val => `company_idx_sch[]=${encodeURIComponent(val)}`).join("&");

  // 단지 셀렉트 값들을 가져오기
  const buildingIdschSelect = document.getElementById("building_id_sch");
  const buildingIdschselectedValues = Array.from(buildingIdschSelect.selectedOptions).map(option => option.value);
  const buildingfilterParams = buildingIdschselectedValues.map(val => `building_id_sch[]=${encodeURIComponent(val)}`).join("&");

  //해지포함
  const transactionStatusValue = "<?php echo $transaction_status; ?>";

  //지급방식
  const ptIdxValue = "<?php echo $pt_idx_sch; ?>";

  const paymentStatusSch = "<?php echo $payment_status_sch; ?>";

  const billStatusSch = "<?php echo $bill_status_sch; ?>";

  const btIdxSch = "<?php echo $bt_idx_sch; ?>";

  const baseParams = `year=${currentYear}&month=${currentMonth}&viewAll=${viewAll ? 1 : 0}`;
  const fullParams = `${baseParams}&${industryfilterParams}&${companyfilterParams}&${buildingfilterParams}&transactionStatusValue=${transactionStatusValue}&ptIdxValue=${ptIdxValue}&paymentStatusSch=${paymentStatusSch}&billStatusSch=${billStatusSch}&btIdxSch=${btIdxSch}`;

  xhr2.onload = function () {

    console.log('xhr2', xhr2);
    if (xhr2.status === 200) {
      //$(".table_sums").hide();
     
      document.getElementById("table_sums").innerHTML = xhr2.responseText;

      syncRowHeights(); // 동기화 호출
    }else{
        //$(".empty_table").show();
    }
  };
  xhr2.send(fullParams);
}

function adjustMonth(currentYear, currentMonth, direction) {
  // currentMonth는 1~12로 받음
  const date = new Date(currentYear, currentMonth - 1 + direction); // 내부에서는 0~11
  const newYear = date.getFullYear();
  const newMonth = date.getMonth() + 1; // 다시 1~12로 변환

  return { year: newYear, month: newMonth };
}

document.getElementById("prevMonth").onclick = () => {
  const result = adjustMonth(currentYear, currentMonth, -1);
  currentYear = result.year;
  currentMonth = result.month;
  viewAll = false;
  loadTableData();
};

document.getElementById("nextMonth").onclick = () => {
const result = adjustMonth(currentYear, currentMonth, 1);
  currentYear = result.year;
  currentMonth = result.month;
  viewAll = false;
  loadTableData();
};

document.getElementById("viewAll").onclick = () => {
  viewAll = !viewAll;
  loadTableData();
};

setTimeout(() => {
    loadTableData();
}, 300);



function ct_excel_download(){
    console.log(currentYear, currentMonth, viewAll);

    const ptIdxValue = "<?php echo $pt_idx_sch; ?>";

    const paymentStatusSch = "<?php echo $payment_status_sch; ?>";

    const billStatusSch = "<?php echo $bill_status_sch; ?>";

    const btIdxSch = "<?php echo $bt_idx_sch; ?>";

    let params = new URLSearchParams(window.location.search);
    params = params + "&year=" + currentYear + "&month=" + currentMonth + "&viewAll=" + (viewAll ? 1 : 0) + '&ptIdxValue=' + ptIdxValue + '&paymentStatusSch=' + paymentStatusSch + '&billStatusSch=' + billStatusSch + '&btIdxSch=' + btIdxSch;

    // console.log(params.toString());

    window.location.href = `./contract_list_excel_download.php?${params.toString()}`;
}
</script>

<?php
$ct_sum = sql_fetch("SELECT COUNT(*) as cnt FROM a_contract WHERE is_temp = 0 and ct_status = 0");
?>
<div class="table_sum_wrapper">
    <table id='table_sums'>
        <tr>
            <th>개수</th>
            <td><?php echo $total_ctct; ?></td>
            <th>합계금액</th>
            <td>0</td>
        </tr>
    </table>
</div>


<script>

//get_contract_list();
function get_contract_list(year = "<?php echo date("Y"); ?>", month =  "<?php echo date("n"); ?>"){

    $.ajax({

    url : "./contract_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "year":year, "month":month}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".table_wrap").html(msg);
    }

    });
}
   

</script>


<!-- 계약추가 팝업 -->
<div class="cm_pop" id="contract_add_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont ver2">
        <div class="cm_pop_close_btn" onClick="company_form_pop_close();">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_cont_wrapper ct_wrapper">
        </div>
    </div>
</div>


<script>
//지급처리
function contract_payment_prg_pop_ajax(){

    //날짜
    const year = document.getElementById("select_year").value;
    const month = document.getElementById("select_month").value;

    const industryIdxschSelect = document.getElementById("industry_idx_sch");
    const industryIdxschselectedValues = Array.from(industryIdxschSelect.selectedOptions).map(option => option.value);
    const industryfilterParams = industryIdxschselectedValues.map(val => `industry_idx_sch[]=${encodeURIComponent(val)}`).join("&");

    // 업체 셀렉트 값들을 가져오기
    const companyIdxschSelect = document.getElementById("company_idx_sch");
    const companyIdxschselectedValues = Array.from(companyIdxschSelect.selectedOptions).map(option => option.value);
    const companyfilterParams = companyIdxschselectedValues.map(val => `company_idx_sch[]=${encodeURIComponent(val)}`).join("&");

    // 단지 셀렉트 값들을 가져오기
    const buildingIdschSelect = document.getElementById("building_id_sch");
    const buildingIdschselectedValues = Array.from(buildingIdschSelect.selectedOptions).map(option => option.value);
    const buildingfilterParams = buildingIdschselectedValues.map(val => `building_id_sch[]=${encodeURIComponent(val)}`).join("&");


    let ct_idx_arr = $("#ct_idx_arr").val();

      //해지포함
    const transactionStatusValue = "<?php echo $transaction_status; ?>";

    $.ajax({

    url : "./contract_list_payment_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: {'year':currentYear, 'month':currentMonth, 'company_idx':companyIdxschselectedValues, 'ct_idx_arr':ct_idx_arr, 'transactionStatusValue':transactionStatusValue}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        // console.log(msg);
        $(".cm_pop_prg_cont_wrapper").html(msg); 
        popOpen('contract_payment_prg_pop');
    }

    });
}

//세금 계산서
function contract_bill_prg_pop_ajax(){

//날짜
const year = document.getElementById("select_year").value;
const month = document.getElementById("select_month").value;

const industryIdxschSelect = document.getElementById("industry_idx_sch");
const industryIdxschselectedValues = Array.from(industryIdxschSelect.selectedOptions).map(option => option.value);
const industryfilterParams = industryIdxschselectedValues.map(val => `industry_idx_sch[]=${encodeURIComponent(val)}`).join("&");

// 업체 셀렉트 값들을 가져오기
const companyIdxschSelect = document.getElementById("company_idx_sch");
const companyIdxschselectedValues = Array.from(companyIdxschSelect.selectedOptions).map(option => option.value);
const companyfilterParams = companyIdxschselectedValues.map(val => `company_idx_sch[]=${encodeURIComponent(val)}`).join("&");

// 단지 셀렉트 값들을 가져오기
const buildingIdschSelect = document.getElementById("building_id_sch");
const buildingIdschselectedValues = Array.from(buildingIdschSelect.selectedOptions).map(option => option.value);
const buildingfilterParams = buildingIdschselectedValues.map(val => `building_id_sch[]=${encodeURIComponent(val)}`).join("&");


let ct_idx_arr = $("#ct_idx_arr").val();

  //해지포함
  const transactionStatusValue = "<?php echo $transaction_status; ?>";

$.ajax({

url : "./contract_list_bill_ajax.php", //ajax 통신할 파일
type : "POST", // 형식
data: {'year':currentYear, 'month':currentMonth, 'company_idx':companyIdxschselectedValues, 'ct_idx_arr':ct_idx_arr, 'transactionStatusValue':transactionStatusValue}, //파라미터 값
success: function(msg){ //성공시 이벤트
    // console.log(msg);
    $(".cm_pop_bill_cont_wrapper").html(msg); 
    popOpen('contract_bill_prg_pop');
}

});
}
</script>

<!-- 지급처리 팝업 -->
<div class="cm_pop" id="contract_payment_prg_pop" >
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont ver_pop ver2">
        <div class="cm_pop_close_btn" onClick="payment_pop_close_handler();">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_prg_cont_wrapper">
        </div>
    </div>
</div>

<!-- 개별 지급처리, 세금계산서 처리 -->
<div class="cm_pop" id="contract_personal_prg_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont ver3">
        <div class="cm_pop_close_btn" onClick="popClose('contract_personal_prg_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="contract_personal_pop_cont">
        </div>
    </div>
</div>


<!-- 세금계산서 처리 팝업 -->
<div class="cm_pop" id="contract_bill_prg_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont ver_pop ver2">
        <div class="cm_pop_close_btn" onClick="bill_pop_close_handler();">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        
        <div class="cm_pop_bill_cont_wrapper">
        </div>
    </div>
</div>

<script>
function contract_pt_pop_ajax(){

    $.ajax({

    url : "./contract_list_pt_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: {}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        // console.log(msg);
        $(".payment_type_add_list").html(msg); 
        popOpen('contract_payment_pop');
    }

    });
}

</script>
<!-- 지급방식팝업 -->
<div class="cm_pop" id="contract_payment_pop" >
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('contract_payment_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">지급방식 관리</div>
        <div class="industry_form_wrap">
            <div class="industry_ipt_box mgt20">
                <input type="text" name="pt_name_add" id="pt_name_add" class="bansang_ipt full ver2" placeholder="지급방식을 입력하세요." value="">
                <div class="industry_add_btn_wrap mgt20">
                    <button type="button" onclick="industry_add();" class="industry_add_btn">추가</button>
                </div>
            </div>
        </div>
        <?php 
        $payment_fix = "SELECT * FROM a_payment_type WHERE is_fixed = 1 ORDER BY pt_idx asc";
        $payment_res = sql_query($payment_fix);
        ?>
        <div class="industry_fix_list mgt15">
            <?php for($i=0;$payment_row = sql_fetch_array($payment_res);$i++){?>
                <div class="industry_fix_box">
                    <input type="text" name="pt_fix_name[]" class="bansang_ipt ver2 full" readonly value="<?php echo $payment_row['pt_name']; ?>">
                </div>
            <?php }?>
        </div>
        <?php 
        $payment_nfix = "SELECT * FROM a_payment_type WHERE is_fixed = 0 and is_del = 0 ORDER BY pt_idx asc";
        //echo $industry_nfix;
        $payment_nres = sql_query($payment_nfix);
        ?>
        <form name="findustrylist" id="findustrylist" action="./contract_payment_update.php" onsubmit="return findustrylist_submit(this);" method="post">
            <div class="industry_add_list payment_type_add_list">
                <?php for($i=0;$payment_nrow = sql_fetch_array($payment_nres);$i++){?>
                    <input type="hidden" name="pt_idx[]" value="<?php echo $payment_nrow['pt_idx']; ?>">
                    <div class="indutry_name_box payment_type_name_box ver2">
                        <input type="text" name="pt_name[]" class="bansang_ipt ver2" value="<?php echo $payment_nrow['pt_name']; ?>">
                        <div class="industry_use_status_box payment_type_use_status_box ver2 industry_use_status_box<?php echo $i + 1;?>">
                            <div class="indus_use_radio_box">
                                <input type="radio" name="is_use<?php echo $i + 1;?>" id="is_use<?php echo $i + 1;?>1" class="is_use1" value="1" <?php echo $payment_nrow['is_use'] == '1' ? 'checked' : ''; ?>>
                                <label class="is_use_label1" for="is_use<?php echo $i + 1;?>1">사용</label>
                            </div>
                            <div class="indus_use_radio_box">
                                <input type="radio" name="is_use<?php echo $i + 1;?>" id="is_use<?php echo $i + 1;?>2" class="is_use2" value="0" <?php echo $payment_nrow['is_use'] == '0' ? 'checked' : ''; ?>>
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
                <p>등록한 지급방식은 삭제 불가능 합니다.</p>
            </div>
            <div class="industry_submit_btn_wrap mgt20">
                <button type="submit" class="btn btn_03 btn_submit">저장</button>
            </div>
        </form>
    </div>
</div>

<!-- 계산서 종류 팝업 -->
<script>
function contract_bt_pop_ajax(){

    $.ajax({

    url : "./contract_list_bt_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: {}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        // console.log(msg);
        $(".bill_type_add_list").html(msg); 
        popOpen('contract_bill_type_pop');
    }

    });
}

</script>
<div class="cm_pop" id="contract_bill_type_pop" >
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('contract_bill_type_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">계산서 종류 관리</div>
        <div class="industry_form_wrap">
            <div class="industry_ipt_box mgt20">
                <input type="text" name="bill_name_add" id="bill_name_add" class="bansang_ipt full ver2" placeholder="계산서 종류를 입력하세요." value="">
                <div class="industry_add_btn_wrap mgt20">
                    <button type="button" onclick="bill_type_add2();" class="industry_add_btn">추가</button>
                </div>
            </div>
        </div>
        <?php 
        $bill_type_fix = "SELECT * FROM a_company_bill_type WHERE is_fixed = 1 ORDER BY bt_idx asc";
        $bill_type_fres = sql_query($bill_type_fix);
        ?>
        <div class="industry_fix_list mgt15">
            <?php for($i=0;$bill_type_frow = sql_fetch_array($bill_type_fres);$i++){?>
                <div class="industry_fix_box">
                    <input type="text" name="bill_fix_name[]" class="bansang_ipt ver2 full" readonly value="<?php echo $bill_type_frow['bill_name']; ?>">
                </div>
            <?php }?>
        </div>
        <?php 
        $bill_type_nfix = "SELECT * FROM a_company_bill_type WHERE is_fixed = 0 and is_del = 0 ORDER BY bt_idx asc";
        //echo $industry_nfix;
        $bill_type_nfres = sql_query($bill_type_nfix);
        ?>
        <form name="fbilltypelist" id="fbilltypelist" action="./contract_bill_type_update.php" onsubmit="return fbilltypelist_submit(this);" method="post">
            <div class="industry_add_list bill_type_add_list">
                <?php for($i=0;$bill_type_nfrow = sql_fetch_array($bill_type_nfres);$i++){?>
                    <input type="hidden" name="bt_idx[]" value="<?php echo $bill_type_nfrow['bt_idx']; ?>">
                    <div class="indutry_name_box bill_type_name_box ver2">
                        <input type="text" name="bill_name[]" class="bansang_ipt ver2" value="<?php echo $bill_type_nfrow['bill_name']; ?>">
                        <div class="industry_use_status_box bill_type_use_status_box ver2 industry_use_status_box<?php echo $i + 1;?>">
                            <div class="indus_use_radio_box">
                                <input type="radio" name="b_is_use<?php echo $i + 1;?>" id="b_is_use<?php echo $i + 1;?>1" class="b_is_use1" value="1" <?php echo $bill_type_nfrow['is_use'] == '1' ? 'checked' : ''; ?>>
                                <label class="b_is_use_label1" for="b_is_use<?php echo $i + 1;?>1">사용</label>
                            </div>
                            <div class="indus_use_radio_box">
                                <input type="radio" name="b_is_use<?php echo $i + 1;?>" id="b_is_use<?php echo $i + 1;?>2" class="b_is_use2" value="0" <?php echo $bill_type_nfrow['is_use'] == '0' ? 'checked' : ''; ?>>
                                <label class="b_is_use_label2" for="b_is_use<?php echo $i + 1;?>2">미사용</label>
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
                <p>등록한 계산서 종류는 삭제 불가능 합니다.</p>
            </div>
            <div class="industry_submit_btn_wrap mgt20">
                <button type="submit" class="btn btn_03 btn_submit">저장</button>
            </div>
        </form>
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
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});

let add_industry_data = [];
function industry_add(){
    
   let industry_name_add = $("#pt_name_add").val();

   if(add_industry_data.includes(industry_name_add)){
        alert('이미 추가된 지급방식입니다.');
        return false;
   }

   let sendData = {'industry_name_add': industry_name_add};

   let indutry_box_length = $(".payment_type_name_box").length + 1;
   let industry_html = `<div class="indutry_name_box payment_type_name_box">
                <input type="text" name="pt_name[]" class="bansang_ipt ver2" value="${industry_name_add}">
                <div class="industry_use_status_box payment_type_use_status_box industry_use_status_box${indutry_box_length}">
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
        url: "./contract_list_payment_add_ajax.php",
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
                $("#pt_name_add").val("");

                $(".payment_type_add_list").append(industry_html);

                $(".payment_type_add_list").addClass("ver2");

                add_industry_data[indutry_box_length - 1] = industry_name_add;

                add_industry_data = add_industry_data;
            }
        },
    });

    console.log('add_industry_data2', add_industry_data);
}

//저장 전 지급방식 엘리먼트 삭제
function industry_cancel(ele, idx){
    ele.closest('.indutry_name_box').remove();

    updateAnswers();
}

//엘리먼트 삭제시 is_use name 값 변경
function updateAnswers(){
    const industry = document.querySelectorAll('.payment_type_use_status_box');

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


let add_bill_type_data = [];
//계산서 종류 추가
function bill_type_add2(){
    let industry_name_add = $("#bill_name_add").val();

   if(add_bill_type_data.includes(industry_name_add)){
        alert('이미 추가된 계산서 종류입니다.');
        return false;
   }

   console.log(industry_name_add);
   let sendData = {'industry_name_add': industry_name_add};

   let indutry_box_length = $(".bill_type_name_box").length + 1;
   let industry_html = `<div class="indutry_name_box bill_type_name_box">
                <input type="text" name="bill_name[]" class="bansang_ipt ver2" value="${industry_name_add}">
                <div class="industry_use_status_box bill_type_use_status_box industry_use_status_box${indutry_box_length}">
                    <div class="indus_use_radio_box">
                        <input type="radio" name="b_is_use${indutry_box_length}" id="b_is_use${indutry_box_length}1" class="b_is_use1" value="1" checked>
                        <label class="b_is_use_label1" for="b_is_use${indutry_box_length}1">사용</label>
                    </div>
                    <div class="indus_use_radio_box">
                        <input type="radio" name="b_is_use${indutry_box_length}" id="b_is_use${indutry_box_length}2" class="b_is_use2" value="0">
                        <label class="b_is_use_label2" for="b_is_use${indutry_box_length}2">미사용</label>
                    </div>
                    <div class="indus_del_box">
                        <button type="button" onclick="bill_type_cancel(this, ${indutry_box_length});" class="btn btn_03">취소</button>
                    </div>
                </div>
            </div>`;
    
    console.log('industry_html', industry_html);
//     //console.log('add_industry_data', add_industry_data);

    $.ajax({
        type: "POST",
        url: "./contract_list_bii_type_add_ajax.php",
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
                $("#bill_name_add").val("");

                $(".bill_type_add_list").append(industry_html);

                $(".bill_type_add_list").addClass("ver2");

                add_bill_type_data[indutry_box_length - 1] = industry_name_add;

                add_bill_type_data = add_bill_type_data;
            }
        },
    });
}

//저장 전 지급방식 엘리먼트 삭제
function bill_type_cancel(ele, idx){
    ele.closest('.bill_type_name_box').remove();

    updateAnswers2();
}

//엘리먼트 삭제시 is_use name 값 변경
function updateAnswers2(){
    const industry = document.querySelectorAll('.bill_type_use_status_box');

    //console.log('industry', industry);
    industry.forEach((item, index) => {
        //console.log(index, `is_use` + (index + 1) + '' + '1');

        const inputField = item.querySelector('.b_is_use1');
        const newId = `b_is_use` + (index + 1) + '' + '1';
        inputField.id = newId;
        inputField.name = `b_is_use${index+1}`;

        const inputField2 = item.querySelector('.b_is_use2');
        const newId2 = `b_is_use` + (index + 1) + '' + '2';
        inputField2.id = newId2;
        inputField2.name = `b_is_use${index+1}`;

        const label = item.querySelector('.b_is_use_label1');
        if (label) {
            label.setAttribute('for', newId);
        }

        const label2 = item.querySelector('.b_is_use_label2');
        if (label2) {
            label2.setAttribute('for', newId2);
        }
    });
}

function findustrylist_submit(){

    let indutry_name_box_cnt = $(".payment_type_name_box").length;

    if(indutry_name_box_cnt == 0){
        alert('지급방식을 하나 이상 추가해주세요.');
        return false;
    }

    return true;
}

function fbilltypelist_submit(){
    let bill_type_name_box_cnt = $(".bill_type_name_box").length;

    if(bill_type_name_box_cnt == 0){
        alert('계산서 종류를 하나 이상 추가해주세요.');
        return false;
    }

    return true;
}

function senior_move(){
    location.href = "./senior_list.php";
}

function contract_date_move(){
    location.href = "./contract_date_list.php";
}

function company_move(){
    
    location.href = "./company_list.php";
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

function checkValidDate(value) {
	var result = true;
	try {
	    var date = value.split("-");
	    var y = parseInt(date[0], 10),
	        m = parseInt(date[1], 10),
	        d = parseInt(date[2], 10);
	    
	    var dateRegex = /^(?=\d)(?:(?:31(?!.(?:0?[2469]|11))|(?:30|29)(?!.0?2)|29(?=.0?2.(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(?:\x20|$))|(?:2[0-8]|1\d|0?[1-9]))([-.\/])(?:1[012]|0?[1-9])\1(?:1[6-9]|[2-9]\d)?\d\d(?:(?=\x20\d)\x20|$))?(((0?[1-9]|1[012])(:[0-5]\d){0,2}(\x20[AP]M))|([01]\d|2[0-3])(:[0-5]\d){1,2})?$/;
	    result = dateRegex.test(d+'-'+m+'-'+y);
	} catch (err) {
		result = false;
	}    
    return result;
}

function contract_personal_pop_open(ct_idx, idx, year, month, sdate, edate){
    $.ajax({

    url : "./contract_personal_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: {"ct_idx":ct_idx, "company_idx":idx, 'year':year, 'month':month, 'sdate':sdate, 'edate':edate}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        
        $(".contract_personal_pop_cont").html(msg);

        popOpen('contract_personal_prg_pop');
    }

    });
}

function company_form_pop_open(idx = '', sdate = '', edate = ''){
    $.ajax({

    url : "./contract_form_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "ct_idx":idx, "sdate":sdate, "edate":edate}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        
        $(".ct_wrapper").html(msg);

        popOpen('contract_add_pop');
    }

    });
}

function company_form_pop_close(){

    $(".ct_wrapper").html("");
    popClose('contract_add_pop');
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

function extend_history_del(idx){

    if (!confirm("계약내역을 삭제 하시겠습니까?")) {
        return false;
    }

    let sendData = {'cth_idx': idx};

    $.ajax({
        type: "POST",
        url: "./contract_expend_del_update.php",
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
                }, 700);
               
            }
        },
    });
}



function payment_prg_pop_open(){

    $(".payment_date_box_wrap1").css('display','flex');

}

//세금계산서 리스트 전체선택
$("#bill_chk_all").click(function() {
	if($("#bill_chk_all").is(":checked")){
		$(".bill_chk").prop("checked", true);
	}else{
		$(".bill_chk").prop("checked", false);
	}
	$(".bill_chk").change();
});
$(".bill_chk").click(function() {
	var total = $(".bill_chk").length;
	var checked = $(".bill_chk:checked").length;

	if(total != checked) $("#bill_chk_all").prop("checked", false);
	else $("#bill_chk_all").prop("checked", true); 
});

function bill_prg_pop_open(){

$(".payment_date_box_wrap2").css('display','flex');

}

//지급처리 반영
function payment_prg_handler(){
    var chk_arr = [];

    $("input[name=payment_chk]:checked").each(function(){
        var chk = $(this).val();

        chk_arr.push(chk);
    });

    if(chk_arr == ""){
        alert("지급처리 하실 업체를 하나이상 선택해주세요.");
        return false;
    }

    var payment_date = $("#payment_date").val();

    if(payment_date == ''){
        alert('지급 날짜를 입력해주세요.');
    }

    if(!checkValidDate(payment_date)){
        alert("지급 날짜를 형식에 맞게 입력해주세요.");
        return false;
    }

    console.log('count', chk_arr.length);
    //$(".payment_date_chk_all").text("");
    for(i=0;i<chk_arr.length;i++){
        console.log('chk::', chk_arr[i]);
        $(".payment_date_ipt" + chk_arr[i]).val(payment_date);
        $(".payment_date_chk" + chk_arr[i]).text(payment_date);
    }

    $("#payment_date").val("");
}

function payment_save(){
    var formData = $("#fpayment").serialize();

    console.log('fpayment::',formData);

    $.ajax({
        type: "POST",
        url: "./contract_payment_ajax.php",
        data: formData,
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

                $("#payment_date").val("");
                $(".payment_chk").prop("checked", false);

                $("#submit_end").val("Y");

                setTimeout(() => {
                    window.location.reload();
                }, 300);
                //$("#id_chk").val(1);
            }
        },
        error: function(e){
            console.log(e);
        }
    });

}


//세금계산서 처리
function bills_prg_handler(){
    var chk_arr = [];

    $("input[name=bill_chk]:checked").each(function(){
        var chk = $(this).val();

        chk_arr.push(chk);
    });

    if(chk_arr == ""){
        alert("세금계산서 처리 하실 업체를 하나이상 선택해주세요.");
        return false;
    }

    var bill_dates = $("#bill_dates").val();

    if(bill_dates == ''){
        alert('세금계산서 처리 날짜를 입력해주세요.');
    }


    if(!checkValidDate(bill_dates)){
        alert("세금계산서 날짜를 형식에 맞게 입력해주세요.");
        return false;
    }

    console.log('count', chk_arr.length);
    //$(".payment_date_chk_all").text("");
    for(i=0;i<chk_arr.length;i++){
        //console.log('chk::', chk_arr[i]);
        $(".bill_date_ipt" + chk_arr[i]).val(bill_dates);
        $(".bill_date_chk" + chk_arr[i]).text(bill_dates);
    }

    $("#bill_dates").val("");
}


function bill_save(){
    var formData = $("#fbilldate").serialize();

    console.log('fbilldate::',formData);

    $.ajax({
        type: "POST",
        url: "./contract_bill_ajax.php",
        data: formData,
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

                $("#bill_dates").val("");
                $(".bill_chk").prop("checked", false);

                $("#bill_submit_end").val("Y");
                //$("#id_chk").val(1);
            }
        },
        error: function(e){
            console.log(e);
        }
    });

}

function payment_pop_close_handler(){
    let submit_end = $("#submit_end").val();

    if(submit_end == ""){
        if (!confirm("최종 저장하지 않고 닫기시 입력한 내용이 반영되지 않습니다.\n해당 창을 닫으시겠습니까?")) {
            return false;
        }
    }

    popClose('contract_payment_prg_pop');
}

function bill_pop_close_handler(){
    let bill_submit_end = $("#bill_submit_end").val();

    if(bill_submit_end == ""){
        if (!confirm("최종 저장하지 않고 닫기시 입력한 내용이 반영되지 않습니다.\n해당 창을 닫으시겠습니까?")) {
            return false;
        }
    }

    popClose('contract_bill_prg_pop');
}

function fextend_submit(f){

    if(!checkValidDate(f.extend_sdate.value)){
        alert("연장 시작일을 날짜 형식에 맞게 입력해주세요.\n(YYYY-MM-DD 형식)");
        return false;
    }

    if(!checkValidDate(f.extend_edate.value)){
        alert("연장 종료일을 날짜 형식에 맞게 입력해주세요.\n(YYYY-MM-DD 형식)");
        return false;
    }

    if(f.last_date.value > f.extend_sdate.value){
        alert('연장 시작일은 최근 계약 종료일보다 이후로 선택해주세요.');
        return false;
    }

    if(f.last_date.value > f.extend_edate.value){
        alert('연장 종료일이 최근 계약 종료일보다 이후로 선택해주세요.');
        return false;
    }

    if(f.extend_sdate.value > f.extend_edate.value){
        alert('연장 시작일이 종료일보다 이후일 수 없습니다.');
        return false;
    }

    return true;
}

//계약 추가
function fcontract_submit(f){

    //단지명은 반드시 선택으로만 입력
    if(f.building_id.value == ""){
        alert('단지명을 입력 후 선택해주세요.');
        return false;
    }

    if(!checkValidDate(f.ct_sdate.value)){
        alert("계약 시작일을 날짜 형식에 맞게 입력해주세요.\n(YYYY-MM-DD 형식)");
        return false;
    }

    if(!checkValidDate(f.ct_edate.value)){
        alert("계약 종료일을 날짜 형식에 맞게 입력해주세요.\n(YYYY-MM-DD 형식)");
        return false;
    }

     //계약종료일보다 시작일이 이후로 선택되었을 때
    if(f.ct_sdate.value > f.ct_edate.value){
        alert('계약 시작일이 종료일보다 이후일 수 없습니다.');
        return false;
    }

    if(f.sn_sdate.value != ""){
        if(!checkValidDate(f.sn_sdate.value)){
            alert("선임기간 시작일을 날짜 형식에 맞게 입력해주세요.\n(YYYY-MM-DD 형식)");
            return false;
        }
    }

    if(f.sn_edate.value != ""){
        if(!checkValidDate(f.sn_edate.value)){
            alert("선임기간 종료일을 날짜 형식에 맞게 입력해주세요.\n(YYYY-MM-DD 형식)");
            return false;
        }
    }

    //선임기간 종료일보다 시작일이 이후로 선택되었을 때
    if(f.sn_sdate.value != "" && f.sn_edate.value != ""){
        if(f.sn_sdate.value > f.sn_edate.value){
            alert('선임기간 시작일이 종료일보다 이후일 수 없습니다.');
            return false;
        }
    }

    if(f.edu_sdate.value != ""){
        if(!checkValidDate(f.edu_sdate.value)){
            alert("교육 이수일 날짜 형식에 맞게 입력해주세요.\n(YYYY-MM-DD 형식)");
            return false;
        }
    }

    if(f.edu_edate.value != ""){
        if(!checkValidDate(f.edu_edate.value)){
            alert("교육 만료일 날짜 형식에 맞게 입력해주세요.\n(YYYY-MM-DD 형식)");
            return false;
        }
    }

    //교육 만료일보다 이수일이 이후로 선택됐을 때
    if(f.edu_sdate.value != "" && f.edu_edate.value != ""){
        if(f.edu_sdate.value > f.edu_edate.value){
            alert('교육 이수일이 만료일보다 이후일 수 없습니다.');
            return false;
        }
    }

    if(f.ch_date_year2.value != "" && f.ch_date_month2.value != ""){
        if(f.ct_price_or.value == f.ct_price.value){
            alert('비용을 변경해주세요.');
            return false;
        }
    }

    return true;
}
</script>

<?php
require_once './admin.tail.php';
