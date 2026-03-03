<?php
include_once('./_common.php');
if($type == "sm"){
    include_once(G5_PATH.'/head_sm.php');
}else{
    include_once(G5_PATH.'/head.php');
}


$sql = "SELECT * FROM a_building_bbs WHERE bb_id = '{$bb_id}'";
//echo $sql;
$row = sql_fetch($sql);

// print_r2($row);

// if($row['bbs_type'] == 'infomation'){

   
// }

if($row['is_submit'] == 'R'){
    alert("회수된 안내문입니다.");
}

if($row['is_del']){
    alert('삭제된 문서입니다.');
}

//이미지로 저장된 안내문 파일
$sql_img = "SELECT * FROM a_building_bbs_img WHERE bb_id = '{$bb_id}'";
$row_img = sql_fetch($sql_img);

//print_r2($row_img);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="bbs_wrap">
                <div class="bbs_title_box">
                    <p class="bbs_title"><?php echo $row['bb_title']; ?></p>
                    <p class="bbs_date"><?php echo date("Y.m.d", strtotime($row['created_at'])); ?></p>
                </div>
                <!-- <p class="bbs_content_box_tit">오른쪽으로 드래그하여 확인하세요.</p> -->
                <div class="bbs_content_box mgt20">
                    <div class="bbs_content_box_inner">
                    <?php if($row_img){?>
                        <div onclick="imgZoom('/data/building/<?php echo $row_img['img_name']; ?>')">
                            <img src="/data/building/<?php echo $row_img['img_name']; ?>" alt="">
                        </div>
                    <?php }?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function imgZoom(imgPath){
    sendMessage('imgZoom', {"content":imgPath});
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>