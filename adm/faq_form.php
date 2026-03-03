<?php
$sub_menu = "100300";
require_once './_common.php';

$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '등록';
}

$g5['title'] .= "FAQ ". $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;


$sql = "SELECT * FROM a_faq
        WHERE faq_id = {$faq_id}";
$row = sql_fetch($sql);

$faq_cate_sql = "SELECT * FROM a_faq_category ORDER BY fc_idx desc";
$faq_cate_res = sql_query($faq_cate_sql);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;

    //print_r2($row);
}

$disabled = $member['mb_level'] == '9' ? 'disabled' : '';

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js

?>

<form name="f_faq" id="f_faq" action="./faq_form_update.php" onsubmit="return ffaq_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="faq_id" value="<?php echo $row['faq_id']; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">faq 정보</h2>
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th>구분</th>
                    <td>
                        <select name="category" id="category" class="bansang_sel">
                            <option value="">선택</option>
                            <?php for($i=0;$faq_cate_row = sql_fetch_array($faq_cate_res);$i++){?>
                                <option value="<?php echo $faq_cate_row['fc_code']; ?>" <?php echo get_selected($row['category'], $faq_cate_row['fc_code']); ?>><?php echo $faq_cate_row['fc_name']; ?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>제목</th>
                    <td>
                        <input type="text" name="faq_title" class="bansang_ipt ver2" value="<?php echo $row['faq_title']; ?>" size="100">
                    </td>
                </tr>
                <tr>
                    <th>내용</th>
                    <td><textarea name="faq_content" id="faq_content" class="bansang_ipt ver2 full ta"><?php echo $row['faq_content']; ?></textarea></td>
                </tr>
                <tr>
                    <th>우선순위</th>
                    <td>
                        <?php echo help('숫자가 낮을수록 먼저 출력됩니다.'); ?>
                        <input type="number" name="is_prior" class="bansang_ipt ver2" value="<?php echo $row['is_prior']; ?>">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <a href="./faq_list.php" class="btn btn_02">목록</a>
        <input type="submit" value="등록" class="btn_submit btn btn_02" accesskey='s'>
    </div>
</form>
<script>
 function ffaq_submit(f) {

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

