<?php
require_once './_common.php';

//지역 리스트 불러오기
$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);
?>
<option value="">선택</option>
 <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
    <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($row['post_id'], $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
<?php }?>