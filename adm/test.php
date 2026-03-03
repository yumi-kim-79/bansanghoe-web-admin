<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.sub.php');

$contract_industry_sql = "SELECT ct.industry_idx, ind.industry_name FROM a_contract as ct
                          LEFT JOIN a_industry_list as ind on ct.industry_idx = ind.industry_idx
                          WHERE ct.is_del = 0 GROUP BY ct.industry_idx ORDER BY ct.ct_idx asc";
$contract_industry_res = sql_query($contract_industry_sql);
?>
<link rel="stylesheet" href="/css/select2.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- 복수선택-->
<select multiple class="select" id="noodles_multi" name="noodles_multi" style="width:335px; height:70px;" multiple="multiple">
	<option value="">선 택</option>
    <?php while($contract_industry_row = sql_fetch_array($contract_industry_res)){ ?>
    	<option value="<?php echo $contract_industry_row['industry_idx']; ?>"><?php echo $contract_industry_row['industry_name']; ?></option>
    <?php }?>
</select>
<script>
$(document).ready(function() {
	$("#noodles_multi").select2(
        {
            placeholder: "옵션을 선택하세요", // 기본 placeholder 설정
            language: {
                noResults: function() {
                    return "검색 결과가 없습니다."; // 원하는 문구로 변경
                }
            }
        }
    );
});	

$('#noodles_multi').on('change', function() {
    let selectedValues = $(this).val(); // 선택된 모든 값 가져오기 (배열 형태)
    console.log('선택된 값 목록:', selectedValues);
    //sendAjaxRequest(selectedValues);
});
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>