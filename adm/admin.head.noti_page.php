<?php
require_once './_common.php';

$noti_page = $page;

$sql_push = " SELECT count(*) as cnt FROM a_push WHERE recv_id_type = 'sm' and recv_id = '{$member['mb_id']}' ORDER BY push_id desc ";
// echo $sql_push.'<br>';
$row_pushs = sql_fetch($sql_push);
$noti_total_count = $row_pushs['cnt'];

// echo $noti_total_count;

//$noti_rows = 1;
$noti_rows = $config['cf_page_rows'];
//$rows = 5;
$noti_total_page  = ceil($noti_total_count / $noti_rows);  // 전체 페이지 계산
if ($noti_page < 1) {
    $noti_page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$noti_from_record = ($noti_page - 1) * $noti_rows; // 시작 열을 구함

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // $sql_admin = " and push.push_id = 146436 ";
}

$sql_admin = '';

$push_sql = "SELECT push.*, push_sc.screen_adm FROM a_push as push
             LEFT JOIN a_push_screen as push_sc ON push.push_type = push_sc.push_type
             WHERE push.recv_id_type = 'sm' and push.is_del = 0 and push.recv_id = '{$member['mb_id']}' {$sql_admin} ORDER BY push.created_at desc, push.push_id desc limit {$noti_from_record}, {$noti_rows}";
// echo $push_sql;
if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // print_r2($push_row);
    // echo $push_sql;
}
$push_res = sql_query($push_sql);
$push_total = sql_num_rows($push_res);
?>
<?php foreach($push_res as $idx => $push_row){
// print_r2($push_row);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // print_r2($push_row);
    // echo $push_sql;
}

if($push_row['push_type'] == 'schedule' || $push_row['push_type'] == 'calendar'){

    $cal_info = sql_fetch("SELECT * FROM a_calendar WHERE cal_idx = '{$push_row['push_idx']}' ");

    $date_parts = date('Y-m', strtotime($push_row['created_at']));

    $dayday = date('d', strtotime($cal_info['cal_date']));

    $date_parts2 = $date_parts.'-'.$dayday;

    $screen = '/adm/calendar_form2.php?w=u&cal_idx='.$push_row['push_idx'].'&cal_code='.$cal_info['cal_code'].'&cal_date_def='.$date_parts2;

    // /adm/calendar_form2.php?w=u&cal_code=schedule&cal_idx=565&cal_date_def=2025-12-10
   
}else{
    $screen = $push_row['screen_adm'];
}

$push_idx = $push_row['push_idx'];
// && $push_idx != "0"

$href = $screen != ""  ? "javascript:notification_view_ajax('".$push_row['push_id']."', '".$screen."', '".$push_row['push_idx']."', 'admin_noti_box".$push_row['push_id']."', '".$push_row['push_type']."')" : "javascript:;";
?>
<div class="admin_noti_list_item">
    <a href="<?php echo $href; ?>" class="admin_noti_box<?php echo $push_row['push_id']; ?> <?php echo $push_row['is_view'] ? 'off' : ''; ?>">
        <div class="admin_noti_list_item_title">
            <div class="item_title"><?php echo $push_row['push_title']; ?></div>
            <div class="item_date"><?php echo date("Y.m.d", strtotime($push_row['created_at']));  ?></div>
        </div>
        <div class="admin_noti_list_item_cont">
        <?php echo $push_row['push_content']; ?>
        </div>
    </a>
</div>
<?php }?>
<script>
function notification_view_ajax(push_id, screen, push_idx, ele, push_type = ''){

    // console.log(push_id, screen, push_idx);

    let id = "<?php echo $member['mb_id']; ?>";
    let sendData = {'mb_id': id, 'push_id':push_id};

    let page = "<?php echo $noti_page; ?>";

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
                // showToast(data.msg);
                //$(".btn_submit").attr('disabled', false);
            
                return false;
            }else{
                // showToast(data.msg);
                

                if(push_idx != "0"){
                    notilist();

                    setTimeout(() => {
                        if(push_type == 'schedule' || push_type == 'calendar'){
                            location.href = screen;
                        }else{
                            location.href = screen + push_idx;
                        }
                    }, 200);
                }else{

                    $("." + ele).removeClass("on");
                    $("." + ele).addClass("off");
                }
            }
        },
    });
}
</script>
<?php echo get_paging_ajax(5, $noti_page, $noti_total_page); ?>
<?php if($push_total == 0){?>
<div class="preset_empty">등록된 알림이 없습니다.</div>
<?php }?>