<?php
require_once './_common.php';

$today = date("Y-m-d");

$last_edate = sql_fetch("SELECT * FROM a_contract_history WHERE ct_idx = '{$ct_idx}' and is_del = 0 ORDER BY cth_idx desc limit 0, 1");

$last_edate2 = date('Y-m-d',strtotime($last_edate['ct_edate']."+1 day"));

$sql = "SELECT * FROM a_contract_history WHERE ct_idx = '{$ct_idx}' and is_del = 0 ORDER BY cth_idx asc ";
$res = sql_query($sql);
?>
<div class="cm_pop_title">연장하기</div>
<form name="fextend" id="fextend" action="./contract_extend_update.php" onsubmit="return fextend_submit(this);" method="post">
    <input type="hidden" name="ct_idx" value="<?php echo $ct_idx; ?>">
    <input type="hidden" name="last_date" value="<?php echo $last_edate2; ?>">
    <div class="extend_pop_form_box_wrap">
        <div class="extend_pop_form_box">
            <div class="extend_pop_box_left">연장 시작일</div>
            <div class="extend_pop_box_right">
                <input type="text" name="extend_sdate" class="bansang_ipt ver2 ipt_date full" value="" required>
            </div>
        </div>
        <div class="extend_pop_form_box">
            <div class="extend_pop_box_left">연장 종료일</div>
            <div class="extend_pop_box_right">
                <input type="text" name="extend_edate" class="bansang_ipt ver2 ipt_date full" value="" required>
            </div>
        </div>
        <div class="extend_pop_form_box">
            <div class="extend_pop_box_left">연장 금액</div>
            <div class="extend_pop_box_right">
                <input type="number" name="extend_price" class="bansang_ipt ver2 full" value="" min="0" required>
            </div>
        </div>
    </div>
    <div class="ep_extract_history_wrap">
        <div class="extract_history_table_label">- 계약 이력</div>
        <table class="extract_history_table">
            <thead>
                <tr>
                    <th>계약일</th>
                    <th>계약 시작일</th>
                    <th>계약 종료일</th>
                    <th>계약 금액</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php for($i=0;$row = sql_fetch_array($res);$i++){?>
                    <tr>
                        <td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
                        <td><?php echo $row['ct_sdate']; ?></td>
                        <td><?php echo $row['ct_edate']; ?></td>
                        <td><?php echo number_format($row['ct_price']); ?></td>
                        <td>
                            <?php if($i != 0 && $row['ct_sdate'] > $today){?>
                                <button type="button" onclick="extend_history_del('<?php echo $row['cth_idx']; ?>')" class="del_history">삭제</button>
                            <?php }?>
                        </td>
                    </tr>
                <?php }?>
            </tbody>
        </table>
        <div class="extend_btn_wrap">
            <button type="submit" class="expend_submit">연장하기</button>
            <button type="button" onclick="popClose('contract_extend_pop');">닫기</button>
        </div>
    </div>
</form>
<script>
$(function(){
    //maxDate: "+365d",
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", minDate:"<?php echo $last_edate2; ?>" });
});

document.querySelectorAll('.ipt_date').forEach(function(input) {
    input.setAttribute('maxlength', '10');
});

document.querySelectorAll('.ipt_date').forEach(function (input) {
    input.addEventListener('input', function () {
        let val = this.value.replace(/\D/g, '').substring(0, 8);
        if (val.length >= 5) {
            val = val.replace(/(\d{4})(\d{2})(\d{0,2})/, '$1-$2-$3');
        } else if (val.length >= 3) {
            val = val.replace(/(\d{4})(\d{0,2})/, '$1-$2');
        }
        this.value = val;
    });
});
</script>