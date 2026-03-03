<?php
include_once('./_common.php');
if($types == "sm") include_once(G5_PATH.'/head_sm.php');
else include_once(G5_PATH.'/head.php');

$privacy_sql = "SELECT * FROM g5_content WHERE co_id = '{$co_id}'";
$privacy_row = sql_fetch($privacy_sql);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="policy_cont parking_sc1">
            <div class="inner">
            <?php echo $privacy_row['co_content'];?>
            </div>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>