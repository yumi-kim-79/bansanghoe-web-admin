<?php
include_once('./_common.php');

if($types == 'sm'){
    include_once(G5_PATH.'/head_sm.php');
}else{
    include_once(G5_PATH.'/head.php');
}

if($types == 'sm'){
    $push_list = "SELECT push.*, push_sc.screen, push_sc.screen_sm FROM a_push as push
                  LEFT JOIN a_push_screen as push_sc ON push.push_type = push_sc.push_type
                  WHERE push.recv_id = '{$member['mb_id']}' and push.is_del = 0 ORDER BY push.created_at desc, push.push_id DESC";
    // echo $push_list.'<br>';
    $push_res = sql_query($push_list);
}else{
    $push_list = "SELECT push.*, push_sc.screen FROM a_push as push
                  LEFT JOIN a_push_screen as push_sc ON push.push_type = push_sc.push_type
                  WHERE push.recv_id = '{$user_info['mb_id']}' and push.is_del = 0 ORDER BY push.push_id DESC";
    // echo $push_list;
    $push_res = sql_query($push_list);
}

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // print_r2($user_building);
    // echo $push_list.'<br>';
} 
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="content_box_wrap ver3">
                <?php for($i=0;$row=sql_fetch_array($push_res);$i++){

                    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                        // print_r2($row);
                    }

                    $screen = $row['screen_sm'] != "" ? $row['screen_sm'] : $row['screen'];

                    if($row['push_type'] == 'bill'){
                        $bill_info = sql_fetch("SELECT * FROM a_bill WHERE bill_id = '{$row['push_idx']}' ");
                        // $screen = '/bill.php?bill_ids='.$row['push_idx'];
                    }

                    if($row['push_type'] == 'car'){
                        // 차량 푸시: push_idx = ho_id로 저장됨
                        $push_idxs = $row['push_idx'];
                        $screen = '/household_mng_info.php?ho_id=';
                    }else if($row['push_type'] == 'calendar' || $row['push_type'] == 'schedule'){

                        $cal_info = sql_fetch("SELECT * FROM a_calendar WHERE cal_idx = '{$row['push_idx']}' ");

                        $date_parts = date('Y-m', strtotime($row['created_at']));
                        $dayday = date('d', strtotime($cal_info['cal_date']));
                        $date_parts2 = $date_parts.'-'.$dayday;

                        $screen = '/schedule_add2.php?w=u&cal_idx='.$row['push_idx'].'&cal_code='.$cal_info['cal_code'].'&cal_date_def='.$date_parts2;
                    }else{
                        $push_idxs = $row['push_idx'];
                    }

                    $href = $screen != "" ? "javascript:notification_view_ajax('".$row['push_id']."', '".$screen."', '".$push_idxs."', 'content_notibox".$i."', '".$row['push_type']."')" : "javascript:;";
                    
                ?>
                <a href="<?php echo $href; ?>" class="content_box content_notibox<?php echo $i;?> ver2 <?php echo $row['is_view'] ? 'noti_off' : 'noti_on'; ?>">
                    <div class="content_box_icons">
                        <img src="/images/notification_icons.svg" alt="">
                    </div>
                    <div class="content_box_ct ver3">
                        <div class="content_box_ct1">
                            <?php echo date("Y.m.d", strtotime($row['created_at'])); ?>
                        </div>
                        <div class="content_box_ct2">
                            <?php echo nl2br($row['push_content']); ?>
                        </div>
                    </div>
                </a>
                <?php }?>
                <?php if($i==0){?>
                    <div class="complain_empty">등록된 알림이 없습니다.</div>
                <?php }?>
            </div>
        </div>
    </div>
</div>
<script>
$(".tab_lnb li").on("click", function(){
    $(".tab_lnb li").removeClass("on");
    $(this).addClass("on");
})

function notification_view_ajax(push_id, screen, push_idx, obj, push_type){

    $("." + obj).removeClass("noti_on");
    $("." + obj).addClass("noti_off");

    let id = "<?php echo $types == 'sm' ? $member['mb_id'] : $user_info['mb_id']; ?>";
    let sendData = {'mb_id': id, 'push_id':push_id};

    $.ajax({
        type: "POST",
        url: "/notification_view_ajax.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                return false;
            }else{
                setTimeout(() => {
                    if(push_type == 'calendar' || push_type == 'schedule'){
                        location.href = screen;
                    }else{
                        location.href = screen + push_idx;
                    }
                }, 200);
            }
        },
    });
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>
