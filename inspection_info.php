<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

$inspection_sql = "SELECT insp.*, indus.industry_name, indus.indutry_icon FROM a_inspection as insp
                   LEFT JOIN a_industry_list as indus on insp.inspection_category = indus.industry_idx
                   WHERE insp.is_del = 0 and insp.inspection_idx = {$inspection_idx} ";
$row = sql_fetch($inspection_sql);
//echo $inspection_sql;

$inspection_file = "SELECT * FROM g5_board_file WHERE bo_table = 'inspection' and wr_id = {$inspection_idx} ORDER BY bf_no asc";
$files_res = sql_query($inspection_file);

$approval_at = $row['approval_at'] != '' ? date("Y.m.d", strtotime($row['approval_at'])) : date("Y.m.d", strtotime($row['created_at']));
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="bbs_wrap">
                <div class="bbs_title_box">
                    <p class="bbs_title"><?php echo $row['inspection_title']; ?></p>
                    <p class="bbs_date"><?php echo $approval_at; ?></p>
                </div>
                <div class="bbs_content_box inspection_content_box">
                    <div class="swiper insp_swp">
                        <div class="swiper-wrapper">
                            <?php for($i=0;$file_row = sql_fetch_array($files_res);$i++){?>
                            <div class="swiper-slide">
                                <div onclick="imgZoom('/data/file/inspection/<?php echo $file_row['bf_file'];?>')">
                                    <img src="/data/file/inspection/<?php echo $file_row['bf_file'];?>" alt="">
                                </div>
                            </div>
                            <?php }?>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
                <script>
                    function imgZoom(imgPath){
                        sendMessage('imgZoom', {"content":imgPath});
                    }
                </script>
                <div class="bbs_memos mgt30">
                    <p class="memo_label">특이사항</p>
                    <textarea name="inspection_memo" id="inspenction_memo" class="bansang_ipt ver2 ta" readonly><?php echo $row['inspection_memo']; ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
let swiper = new Swiper(".insp_swp", {
    slidesPerView: "auto",
    pagination: {
        el: '.swiper-pagination',
        type: 'fraction',
    },
    autoHeight: true,
});
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>