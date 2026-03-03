<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

$complain_sql = "SELECT * FROM a_online_complain WHERE complain_idx = '{$complain_idx}'";
$complain_row = sql_fetch($complain_sql);

//print_r2($complain_row);
$complain_file = "SELECT * FROM g5_board_file WHERE bo_table = 'complain' and wr_id = '{$complain_idx}' ORDER BY bf_no asc ";
$complain_file_res = sql_query($complain_file); 

$complain_ans_file = "SELECT * FROM g5_board_file WHERE bo_table = 'complain_answer' and wr_id = '{$complain_idx}' ORDER BY bf_no asc ";
$complain_ans_file_res = sql_query($complain_ans_file); 
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="bbs_wrap">
                <?php
                $complain_status = "";
                $msgs = '';
                switch($complain_row['complain_status']){
                    case "CA":
                        $complain_status = "접수대기";
                        $msgs = "담당 부서에서 담당자를 배정 중입니다";
                        break;
                    case "CB":
                        $complain_status = "할당대기";
                        $msgs = "접수 후 담당 부서 할당 중입니다";
                        break;
                    case "CC":
                        $complain_status = "진행중";
                        $msgs = "담당자가 처리 진행 중입니다";
                        break;
                    case "CD":
                        $complain_status = "완료";
                        $msgs = "처리완료된 민원 내역입니다";
                        break;
                }
                ?>
                <div class="bbs_status_box">
                    <span><?php echo $complain_status; ?></span>
                    <p><?php echo $msgs; ?></p>
                </div>
                <div class="bbs_title_box ver2">
                    <p class="bbs_title"><?php echo $complain_row['complain_title']; ?></p>
                    <p class="bbs_date"><?php echo date("Y.m.d", strtotime($complain_row['wdate'])); ?></p>
                </div>
                <div class="bbs_content_box">
                <?php echo $complain_row['complain_content']; ?>
                </div>
                <?php if($complain_file_res){?>
                <div class="bbs_img_wrap">
                    <?php for($i=0;$complain_file_row = sql_fetch_array($complain_file_res);$i++){?>
                    <div class="bbs_img_box" onclick="bigSize('/data/file/complain/<?php echo $complain_file_row['bf_file']; ?>')">
                        <img src="/data/file/complain/<?php echo $complain_file_row['bf_file']; ?>" alt="">
                    </div>
                    <?php }?>
                </div>
                <?php }?>
            </div>
        </div>
        <?php if($complain_row['complain_answer'] != '' && $complain_row['complain_status'] == 'CD'){?>
        <div class="online_complain_answer">
            <div class="inner">
                <div class="compl_anser_tit">
                    <img src="/images/answer_icons.svg" alt=""> 답변
                </div>
                <div class="comple_answers mgt10">
                    <textarea name="comple_answer" id="comple_answers" class="bansang_ipt ver2 ta" readonly><?php echo $complain_row['complain_answer']; ?></textarea>
                </div>
                <?php if($complain_ans_file_res){?>
                <div class="bbs_img_wrap ver2">
                    <?php for($i=0;$complain_ans_file_row = sql_fetch_array($complain_ans_file_res);$i++){?>
                    <div class="bbs_img_box" onclick="bigSize('/data/file/complain_answer/<?php echo $complain_ans_file_row['bf_file']; ?>')">
                        <img src="/data/file/complain_answer/<?php echo $complain_ans_file_row['bf_file']; ?>" alt="">
                    </div>
                    <?php }?>
                </div>
                <?php }?>
            </div>
        </div>
        <?php }?>
    </div>
</div>

<div class="cm_pop" id="complain_del_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">해당 민원을 삭제하시겠습니까?</p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="popClose('complain_del_pop');">취소</button>
            <button type="button" class="cm_pop_btn ver2" onClick="complain_del();">삭제</button>
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

function complain_del(){

    let mb_id = "<?php echo $user_info['mb_id']; ?>";
    let complain_idx = "<?php echo $complain_idx; ?>";
    
    let sendData = {'mb_id':mb_id, 'complain_idx': complain_idx};

    $.ajax({
        type: "POST",
        url: "/online_complain_del.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);
                return false;
            }else{
                showToast(data.msg);
              
                setTimeout(() => {
                    location.replace('/online_complain.php?tabIdx=2');
                }, 700);
               
            }
        },
    });

}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>