<?php
include_once('./_common.php');
if($types == "sm"){
    include_once(G5_PATH.'/head_sm.php');
}else{
    include_once(G5_PATH.'/head.php');
}

$sql = "SELECT * FROM a_online_vote WHERE vt_id = '{$vt_id}'";
$row = sql_fetch($sql);

$sql_q = "SELECT * FROM a_online_vote_question WHERE vt_id = '{$vt_id}' and is_del = 0 ORDER BY vtq_id asc";
$res_q = sql_query($sql_q);
//print_r2($row);
$vt_status = "";
if($row['vt_period_type'] == 'personnel'){
    $sql_building_ho_number = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho WHERE ho_status = 'Y' and dong_id = '{$user_building['dong_id']}'");
    //echo $sql_building_ho_number['cnt'].'<br>';

    $vote_number = sql_fetch("SELECT COUNT(*) as cnt FROM a_online_vote_result WHERE vt_id = '{$vt_id}'");
    //echo $vote_number['cnt'];

    if($sql_building_ho_number['cnt'] == $vote_number['cnt']){
        $vt_status = "end";
    }
}

$sql_result = "SELECT *, COUNT(*) as cnt FROM a_online_vote_result WHERE mb_id = '{$user_info['mb_id']}' and vt_id = '{$vt_id}'";
$row_result = sql_fetch($sql_result);


if($vt_id != ''){
    //종료상태라면
    $vote = $row['vt_status'] == '2' ? 'end' : 'prg';
}

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $sql_result;
?>
<form name="fvote" id="fvote" method="post" autocomplete="off">
<input type="hidden" name="ho_id" value="<?php echo $user_building['ho_id']; ?>">
<input type="hidden" name="mb_id" value="<?php echo $user_info['mb_id']; ?>">
<input type="hidden" name="vt_id" value="<?php echo $vt_id; ?>">
<input type="hidden" name="w" value="<?php echo $row_result['cnt'] > 0 ? "u" : "";?>">
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="bbs_wrap">
                <div class="bbs_title_box">
                    <p class="bbs_title"><?php echo $row['vt_title']; ?></p>
                    <p class="bbs_date">
                        <?php
                        if($row['vt_period_type'] == 'period'){
                        ?>
                        투표 기간 : <?php echo date("Y.m.d", strtotime($row['vt_sdate'])) ?> ~ <?php echo date("Y.m.d", strtotime($row['vt_edate'])) ?>
                        <?php }else{ 
                            $gigan_t = "";
                            switch($row['vt_period_type']){
                                case "personnel";
                                    $gigan_t = "인원수 마감";
                                    break;
                                case "period_not":
                                    $gigan_t = "무기한";
                                    break;
                            }    
                        ?>
                        투표 기간 : <?php echo $gigan_t; ?>
                        <?php }?>
                    </p>
                </div>
                <div class="bbs_content_box bbs_content_bbs_ct">
                    <?php if($vt_status == 'end'){?>
                    <div class="bbs_vote_notice">
                        <div class="bbs_vote_notice_inner">
                        인원수 마감
                        </div>
                    </div>
                    <?php }?>
                    <?php echo $row['vt_content']; ?>
                </div>
                
                <input type="hidden" id="q_cnt" value="1"> <!--  질문 개수 -->
                <input type="hidden" id="choice_cnt" value="<?php echo $row_result['cnt']; ?>"> <!-- 선택한 개수 -->
                <div class="bbs_vote_wrap">
                    <ul class="vote_list">
                        <?php for($i=0;$row_q = sql_fetch_array($res_q);$i++){
                            $total_vote = sql_fetch("SELECT COUNT(*) as cnt FROM a_online_vote_result WHERE vtq_id = '{$row_q['vtq_id']}'");

                            $total_vote_cnt = str_pad($total_vote['cnt'], 2, "0", STR_PAD_LEFT);
                            //echo $total_vote;
                            $no_dis = $types != 'sm' ? '' : 'disabled';
                        ?>
                        <li 
                        onClick="<?php echo $vote == 'end' && $types == 'sm' ? "linkMove('".$vt_id."', '".$row_q['vtq_id']."')" : ""; ?>"
                        >
                            <input type="radio" name="vtq_id" id="vote<?php echo $i+1; ?>" class="vote_rd" value="<?php echo $row_q['vtq_id']; ?>" onChange="valiAns();" <?php echo $vote == "end" ? "disabled" : "";?> <?php echo $row_result['vtq_id'] == $row_q['vtq_id'] ? "checked" : "";?> <?php echo $no_dis; ?>>
                            <label for="vote<?php echo $i+1; ?>">
                                <i><img src="/images/radio_chk_icon.svg" alt=""></i>
                                <?php echo $row_q['vtq_name']; ?>
                                <?php if($vote == 'end' && $types == 'sm'){?>
                                <span>
                                    <?php echo $total_vote_cnt; ?>표
                                    <u class="list_icons">
                                        <u class="list_icons_bar list_icons_bar1"></u>
                                        <u class="list_icons_bar list_icons_bar2"></u>
                                        <u class="list_icons_bar list_icons_bar3"></u>
                                    </u>
                                </span>
                                <?php }?>
                            </label>
                        </li>
                        <?php }?>
                        
                        <!-- <li onClick="<?php echo $vote == 'end' ? "linkMove('1')" : ""; ?>">
                            <input type="radio" name="vote_val" id="vote2" class="vote_rd" value="2" onChange="valiAns();" <?php echo $vote == "end" ? "disabled" : "";?>>
                            <label for="vote2">
                                <i><img src="/images/radio_chk_icon.svg" alt=""></i>
                                2번 선택지
                                <?php if($vote == 'end'){?>
                                <span>
                                    00표
                                    <u class="list_icons">
                                        <u class="list_icons_bar list_icons_bar1"></u>
                                        <u class="list_icons_bar list_icons_bar2"></u>
                                        <u class="list_icons_bar list_icons_bar3"></u>
                                    </u>
                                </span>
                                <?php }?>
                            </label>
                        </li> -->
                    </ul>
                    <?php if($vote == 'end'){

                        $sql_vqq = "SELECT * FROM a_online_vote_question WHERE vt_id = '{$vt_id}'";
                        $res_vqq = sql_query($sql_vqq);
                       
                        $vote_title = array();
                        $vote_cnt = array();
                        foreach($res_vqq as $idx => $row_vqq){

                            $sql_qr = "SELECT COUNT(*) as cnt FROM a_online_vote_result WHERE vt_id = '{$vt_id}' and vtq_id = '{$row_vqq['vtq_id']}'";
                            $row_qr = sql_fetch($sql_qr);

                            array_push($vote_title, $row_vqq['vtq_name']);
                            array_push($vote_cnt, $row_qr['cnt']);
                        }
                           
                        $maxCnt = max($vote_cnt);

                        $maxIdx = array_keys($vote_cnt, $maxCnt);
                    ?>
                    <?php foreach($maxIdx as $idx => $maxRow){?>
                        <div class="vote_result mgt20">투표결과 : <?php echo $vote_title[$maxRow]; ?>  - <?php echo $vote_cnt[$maxRow].'표';?></div>
                    <?php }?>
                    <?php }?>
                </div>
            </div>
            <?php if($vote == 'prg' && $types != 'sm'){?>
            <div class="fix_btn_back_box"></div>
            <div class="fix_btn_box">
                <button type="button" class="fix_btn <?php echo $row_result ? "on" : "";?>" id="fix_btn" onClick="checkTest();">투표하기</button>
            </div>
            <?php }?>
        </div>
    </div>
</div>
</form>
<script>
function valiAns(){
    const questionBoxes = document.querySelectorAll('.bbs_vote_wrap');
    let isValid = true;
    let firstUnansweredQuestion = null;
    let choiceCnt = 0;

    questionBoxes.forEach((questionBox) => {
        const radioInputs = questionBox.querySelectorAll('input[type="radio"]');
        const checkboxInputs = questionBox.querySelectorAll('input[type="checkbox"]');
        //const textInputs = questionBox.querySelectorAll('input[type="checkbox"]');
        
        let isQuestionAnswered = false;
        
        if (radioInputs.length > 0) {
            // 라디오버튼 문제 검증
            isQuestionAnswered = Array.from(radioInputs).some(input => input.checked);
        } else if (checkboxInputs.length > 0) {
            // 체크박스 문제 검증
            isQuestionAnswered = Array.from(checkboxInputs).some(input => input.checked);
        }

        console.log(isQuestionAnswered);
        
        if (!isQuestionAnswered) {
            isValid = false;
            // 첫 번째 미답변 문제 저장
            if (!firstUnansweredQuestion) {
                firstUnansweredQuestion = questionBox;
            }
            // 미답변 문제 표시
            //questionBox.querySelector('.class_v_q').style.color = 'red';
        } else {
            choiceCnt++;
            // 정상 답변 처리된 문제는 색상 원복
            //questionBox.querySelector('.class_v_q').style.color = '';
        }
    });

    document.getElementById('choice_cnt').value = choiceCnt;

    console.log('isValid', isValid);        

    if (!isValid) {			
        document.getElementById("fix_btn").classList.remove("on");
        //document.getElementById("fix_btn").setAttribute('disabled', true);
        // 첫 번째 미답변 문제로 스크롤
        if (firstUnansweredQuestion) {
            firstUnansweredQuestion.scrollIntoView({ behavior: 'smooth' });
        }
        return false;
    }else{
        document.getElementById("fix_btn").classList.add("on");
        //document.getElementById("fix_btn").setAttribute('disabled', false);
    }
}

function checkTest(){
    let questionCnt = parseInt(document.getElementById('q_cnt').value);
    let choiceCnt = parseInt(document.getElementById('choice_cnt').value);

    if(questionCnt !== choiceCnt){
        showToast("투표를 진행해 주세요.");
        return false;
    }

    //showToast('투표완료!');

    var formData = $("#fvote").serialize();
    
    $.ajax({
        cache : false,
        url : "/online_vote_info_update.php", // 요기에
        type : 'POST', 
        data : formData, 
        dataType: "json",
        success : function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                showToast(data.msg);
            }else{
                showToast(data.msg);

                setTimeout(() => {
                    
                    location.replace("/online_vote.php?tabIdx=" + data.data);
                    
                }, 500);
            }
        }, // success 
    }); // $.ajax */
}


function linkMove(vt_id, idx){
    location.href = "online_vote_result.php?vt_id=" + vt_id + "&vtq_id=" + idx;
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>