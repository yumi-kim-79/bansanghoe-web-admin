<?php
include_once('./_common.php');

//auth_check($auth[$sub_menu], "w");

$building_bbs = "SELECT * FROM a_building_bbs WHERE bb_id = '{$bb_idx}'";
$building_bbs_row = sql_fetch($building_bbs);

$g5['title'] = $building_bbs_row['bb_title'];
include_once(G5_PATH.'/head.sub.php');


//echo $building_bbs;

//print_r2($building_bbs_row);
$bb_number = str_split($building_bbs_row['bb_number']);
$bb_number = implode(" ", $bb_number);
//print_r2($bb_number);
?>
<style>
.building_news_sample_wrap {position: relative;min-width:210mm}
.news_content {width: 100%;}
.preset_info {padding: 41mm 5mm 34mm !important;}

.building_news_sample_hd {width: 100%;padding:15px;display: flex;justify-content:flex-end;}
.building_news_sample_hd button {padding:10px 15px;border-radius:6px;border:none;background: var(--colorMain);color: #fff;font-size: 14px;}

.news_tit_box {position: absolute;top:100px;font-size: 30px;font-weight: 600;text-align: center;width: 100%;max-width: 210mm;left: 50%;transform:translateX(-50%);}

.new_info_hd {width: 100%;max-width: 210mm;left: 50%;transform:translateX(-50%);display: flex;align-items:center;justify-content:space-between;position: absolute;top:25px;padding: 0 16px;}
.news_number {font-size: 12px;width: 78px;text-align:justify;}
.news_number span {display: inline-block;width: 78px;text-align:justify;}
.news_number span:after {content:"";display:inline-block;width:100%;}
.news_number_label {position: relative;top:10px;}
.news_number span.news_number_box1 {width: 55px;}
</style>
<div class="building_news_sample_wrap">
    <div class="news_content">
        <?php echo $building_bbs_row['bb_content']; ?>
        <div class="new_info_hd">
            <p class="news_number">
                <span class="news_number_label news_number_box1">문 서 번 호</span>
                <span class="news_number_box1"><?php echo $bb_number;?></span>
            </p>
            <p class="news_number">
                <span class="news_number_label">게 시 기 한</span>
                <span><?php echo $building_bbs_row['edate'];?>까지</span>
            </p>
        </div>
        <p class="news_tit_box"><?php echo $building_bbs_row['bb_title']; ?></p>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>