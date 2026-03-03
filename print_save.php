<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    file_put_contents('saved_guide.html', $content);
    echo "저장 성공";
}
?>