<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$bbs_sql = "SELECT * FROM a_bbs WHERE bbs_idx = '{$bbs_idx}'";
$bbs_row = sql_fetch($bbs_sql);

$bbs_setting = sql_fetch("SELECT * FROM a_bbs_setting WHERE bbs_code = '{$bbs_row['bbs_code']}'");

//파일리스트
$bbs_file_sql = "SELECT * FROM g5_board_file WHERE bo_table = 'bbs_img' and wr_id = '{$bbs_idx}' ORDER BY bf_no asc";
$bbs_file_res = sql_query($bbs_file_sql);

$bbs_pdf_sql = "SELECT * FROM g5_board_file WHERE bo_table = 'bbs_pdf' and wr_id = '{$bbs_idx}' ORDER BY bf_no asc";

if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
// echo $bbs_file_sql;
}
$bbs_pdf_res = sql_query($bbs_pdf_sql);
$bbs_pdf_total = sql_num_rows($bbs_pdf_res);

$cate = sql_fetch("SELECT * FROM a_bbs_setting WHERE bbs_code = '{$bbs_row['bbs_code']}'");
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="bbs_wrap">
                <div class="bbs_title_box">
                    <p class="bbs_title"><?php echo $bbs_setting['bbs_title']; ?></p>
                    <p class="bbs_title mgt10"><?php echo $bbs_row['bbs_title']; ?></p>
                    <div class="bbs_info_box">
                        <p class="bbs_date"><?php echo date("Y.m.d", strtotime($bbs_row['created_at'])); ?></p>
                        <p class="bbs_date">
                            <?php if($bbs_row['wid'] == "admin"){?>
                                <?php echo "신반상회"; ?>
                            <?php }else{ 
                                //현장팀/팀장/홍길동
                                $bbs_mng = get_manger($bbs_row['wid']);
                                //print_r2($bbs_mng);
                            ?>
                            <?php echo $bbs_mng['md_name'].'/'.$bbs_mng['mg_name'].'/'.$bbs_mng['mng_name'];?>
                            <?php }?>
                        </p>
                    </div>
                </div>
                <div class="bbs_content_box bbs_content_bbs_ct">
                <?php echo $bbs_row['bbs_content']; ?>
                </div>
                <?php if($bbs_file_res){?>
                <div class="bbs_img_wrap">
                    <?php for($i=0;$bbs_file_row = sql_fetch_array($bbs_file_res);$i++){?>
                    <div class="bbs_img_box" onclick="imgZoom('/data/file/bbs_img/<?php echo $bbs_file_row['bf_file'];?>')">
                        <img src="/data/file/bbs_img/<?php echo $bbs_file_row['bf_file'];?>" alt="">
                    </div>
                    <?php }?>
                </div>
                <?php }?>
                <?php if($bbs_pdf_total > 0){?>
                <div class="bbs_file_wrap mgt20">
                    <p class="regi_list_title">첨부 자료</p>
                    <div class="file_down_box_wrap">
                        <?php for($i=0;$bbs_pdf_row = sql_fetch_array($bbs_pdf_res);$i++){?>
                        <a href="javascript:buidling_file_download('/data/file/bbs_pdf/<?php echo $bbs_pdf_row['bf_file']; ?>', '<?php echo $bbs_pdf_row['bf_source']; ?>');">
                            <div class="file_tit"><?php echo $bbs_pdf_row['bf_source']; ?></div>
                        </a>
                        <?php }?>
                    </div>
                </div>
                <?php }?>
            </div>
        </div>
    </div>
</div>
<div id="big_size_pop">
    <div class="od_cancel_inner"></div>
	<button type="button" class="big_size_pop_x" onclick="bigSizeOff();">
		<span></span>
		<span></span>
	</button>
	<div class="od_cancel_cont">
		<img src="" id="big_img" alt="확대 보기">
	</div>
</div>
<script>
function buidling_file_download(url, name){
    console.log('url', url);

    sendMessage('building_file', {"url":url, "name":name});
}

function imgZoom(imgPath){
    sendMessage('imgZoom', {"content":imgPath});
}

$(".bbs_content_box img").click(function(){
    var imgPath = $(this).attr("src");

    const base = "https://smtm2017.com";
    const result = imgPath.replace(base, "");

    sendMessage('imgZoom', {"content":result});
});

function bigSize(url){
	const windowHeight = window.innerHeight;
	$("#big_size_pop .od_cancel_cont").css("height", `${windowHeight}px`);
	$("#big_img").attr("src", url);
	$("#big_size_pop").show();
}

function bigSizeOff(){
	$("#big_size_pop").hide();
	$("#big_img").attr("src", "");
}

const homebtn = document.querySelector('.home_btn');
const tooltipBox = document.querySelector('.tooltip_btn');
homebtn.addEventListener('click', () => {
  const dropdown = document.querySelector('.tooltip_box');
  dropdown.style.display = 'block';
});

homebtn.addEventListener('blur', () => {
  const dropdown = document.querySelector('.tooltip_box');

  setTimeout(() => {
    dropdown.style.display = '';
  }, 200);
});

</script>
<?php
include_once(G5_PATH.'/tail.php');
?>