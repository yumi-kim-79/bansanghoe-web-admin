<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

$sql = "SELECT * FROM a_building_bbs WHERE bb_id = '{$bb_id}'";
//echo $sql;
$row = sql_fetch($sql);

//print_r2($row);
?>
<style>
.bbs_content_box {overflow: auto;}
</style>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="bbs_wrap">
                <div class="bbs_title_box">
                    <p class="bbs_title"><?php echo $row['bb_title']; ?></p>
                    <p class="bbs_date"><?php echo date("Y.m.d", strtotime($row['created_at'])); ?></p>
                </div>
                <div class="bbs_content_box">
                    <div class="preset_info_wrap">
                    <?php echo $row['bb_content']; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>