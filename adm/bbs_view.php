<?php
require_once './_common.php';

if($bbs_idx != ''){
    $bbs_infos = sql_fetch("SELECT * FROM a_bbs WHERE bbs_idx = '{$bbs_idx}'");
    $bbs_code = $bbs_infos['bbs_code'];
}

switch($bbs_code){
    case "notice":
        $sub_menu = "920100";
    break;
    case "security":
        $sub_menu = "920200";
    break;
    case "bill":
        $sub_menu = "920300";
    break;
    case "onsite_schedule":
        $sub_menu = "920400";
    break;
    case "team_leader":
        $sub_menu = "920500";
    break;
    case "etc1":
        $sub_menu = "920600";
    break;
    case "etc2":
        $sub_menu = "920700";
    break;
    case "etc3":
        $sub_menu = "920800";
    break;
    case "etc4":
        $sub_menu = "920900";
    break;
    case "etc5":
        $sub_menu = "920910";
    break;
}

$bbs_setting = sql_fetch("SELECT bbs_title FROM a_bbs_setting WHERE bbs_code = '{$bbs_code}'");

$g5['title'] = "사내용 게시판 상세 - ".$bbs_setting['bbs_title'];

require_once './admin.head.php';


$bbs_sql = "SELECT * FROM a_bbs WHERE bbs_code = '{$bbs_code}' and bbs_idx = {$bbs_idx}";
$bbs_row = sql_fetch($bbs_sql);

//print_r2($bbs_row);

//작성자 정보
$mng_info = get_manger($bbs_row['wid']);


//이미지 첨부파일 리스트
$bbs_file_sql = "SELECT * FROM g5_board_file WHERE bo_table = 'bbs_img' and wr_id = '{$bbs_idx}' ORDER BY bf_no asc";
$bbs_file_res = sql_query($bbs_file_sql);
$bbs_file_total = sql_num_rows($bbs_file_res);

//문서 첨부파일 리스트
$bbs_pdf_sql = "SELECT * FROM g5_board_file WHERE bo_table = 'bbs_pdf' and wr_id = '{$bbs_idx}' ORDER BY bf_no asc";
$bbs_pdf_res = sql_query($bbs_pdf_sql);
$bbs_pdf_total = sql_num_rows($bbs_pdf_res);
?>
<div class="bbs_view_wrap">
    <div class="bbs_view_box">
        <div class="bbs_view_title"><?php echo $bbs_row['bbs_title']; ?></div>
        <div class="bbs_view_flex_box mgt15 mgb15">
            <div class="bbs_view_date">
                <?php echo date('Y.m.d', strtotime($bbs_row['created_at'])); ?>
            </div>
            <div class="bbs_view_writer">
                <?php echo $mng_info['md_name'].'/'.$mng_info['mg_name'].'/'.$mng_info['mng_name']; ?>
            </div>
        </div>
        <div class="bbs_view_content">
            <?php echo $bbs_row['bbs_content']; ?>
        </div>
    </div>
    <div class="bbs_view_box">
        <?php if($bbs_file_total > 0){?>
        <div class="bbs_view_img_wrap">
            <div class="bbs_view_sub_title">첨부 이미지</div>
            <div class="bbs_view_img">
                <?php for($i=0;$bbs_file_row = sql_fetch_array($bbs_file_res);$i++){?>
                    <div class="bbs_view_img_box" onclick="bigSize('/data/file/bbs_img/<?php echo $bbs_file_row['bf_file']; ?>')">
                        <img src="/data/file/bbs_img/<?php echo $bbs_file_row['bf_file']; ?>" alt="">
                    </div>
                <?php }?>
            </div>
        </div>
        <?php }?>
        <?php if($bbs_pdf_total > 0){ ?>
        <div class="bbs_view_pdf_wrap <?php echo $bbs_file_total > 0 ? 'mgt30' : '';?>">
            <div class="bbs_view_sub_title">첨부 파일</div>
            <div class="bbs_view_pdf">
                <?php for($i=0;$bbs_pdf_row = sql_fetch_array($bbs_pdf_res);$i++){
                    ?>
                    <div class="bbs_view_pdf_box">
                        <a href="/data/file/bbs_pdf/<?php echo $bbs_pdf_row['bf_file']; ?>" download>
                            <?php echo $bbs_pdf_row['bf_source']; ?>
                        </a>
                    </div>
                <?php }?>
            </div>
        </div>
        <?php }?>
    </div>
    <div class="btn_fixed_top">
        <a href="./bbs_list.php?bbs_code=<?=$bbs_code; ?>&<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <a href="./bbs_form.php?bbs_code=<?php echo $bbs_code?>&<?=$qstr;?>&amp;w=u&amp;bbs_idx=<? echo $bbs_idx; ?>" class="btn btn_03">수정</a>
        <!-- <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'> -->
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

$(".bbs_view_content img").on("click", function(){
    let img_src = $(this).attr('src');

    bigSize(img_src);
});
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

