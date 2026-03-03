<?php
include_once('./common.php');

include_once(G5_PATH.'/head.sub2.php');
require_once G5_EDITOR_LIB;
?>
<style>
.write_wrap {width: 100%;max-width: 992px;min-width: 280px;margin: 0 auto;background: #fff;}
.write_form {padding:20px;}
.write_title {font-size: 18px;color: #31333F;font-weight: 700;display: flex;gap:10px;align-items:center;}
.write_title span {font-size: 14px;font-weight: 500;color: #b8b8b8;}

.write_category {margin:20px 0;}
.write_category button {background: #fff;border: 1px solid #DEDEDE;border-radius:10px;padding:0 15px;height:42px;font-size: 15px;font-weight: 500;}
.write_category button i {width: 10px;height: 4px;display: inline-block;position: relative;top:-1px;margin-left:5px;}
.write_category button i img {max-width: 100%;}

.write_title input {width:100%;height: 46px;border:1px solid #DEDEDE;background: #F9F9F9;font-weight: 400;border-radius:10px;padding-left:15px;font-size: 16px;}

.write_content {margin-top: 20px;}
.write_content .alert_texts {padding-left: 20px;position: relative;font-size: 11px;color: #137CBD;}
.write_content textarea {width: 100%;}

.write_btn_wrap {display: flex;gap:10px;flex-wrap:wrap;margin-top:40px;}
.write_btn_wrap button {background: none;border:none;width: calc(50% - 5px);border-radius:10px;height:52px;font-size: 16px;font-weight: 600;}
.write_btn_wrap button:first-child {border:1px solid #137CBD;color:#137CBD}
.write_btn_wrap button:last-child {border:1px solid #137CBD;background: #137CBD;color: #fff;}
</style>
<div class="write_wrap">
    <div class="write_form">
        <div class="write_title"><?php echo $title; ?> <span>글 작성</span></div>
        <div class="write_category">
            <button type="button" onclick="categorySelect();"><span class="cate_titles"><?=$cate != '' ? $cate : '카테고리'; ?></span> </i></button>
        </div>
        <div class="write_title">
            <input type="text" name="title" id="title" class="frm_input" placeholder="제목을 입력해 주세요." value="<?php echo $row['title'];?>">
        </div>
        <div class="write_content">
        <?php echo editor_html('content', get_text(html_purifier($row['content']), 0)); ?>
        
        <p class="alert_texts">부적절한 글은 관리자에 의해 임의로 삭제될 수 있습니다.</p>
        
        </div>
      
    </div>
</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>