<?php
require_once './_common.php';

$today = date("Y-m-d H:i:s");
$ondayAfter = date("Y-m-d H:i:s", strtotime("+1 day"));

?>
<input type="hidden" name="rv_time" id="rv_time" value="<?php echo $ondayAfter; ?>">
<div class="cm_rv_time">예상 예약 발행 시간 : <?php echo $ondayAfter; ?></div>
<p>* 예약 발행 후 예약 발행 설정 시점으로  24시간 후 앱 발송 됩니다.</p>
<p>* 24시간 이내 예약 발행 취소 가능하며, 24시간 이내 강제 발송 가능합니다.</p>