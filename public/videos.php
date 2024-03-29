<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'scan.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'getRandomKeys.php';

if (isset($_GET['file']) && is_file('message' . DIRECTORY_SEPARATOR . $_GET['file'])) {
    $files = [$_GET['file']];
} else {
    $files = scan();
}

$fileNames = [];

foreach (getRandomKeys($files) as $selectKey) {
    $fileNames[] = $files[$selectKey];
}
$files = [];
?><!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="utf-8">
        <title><?php echo $_GET['file'] ?? '随机的视频展示网站'; ?></title>
    </head>
    <body>
        <div style="margin:20px auto 20px auto;width:800px">
            <?php foreach ($fileNames as $fileName) { ?>
            <div>
                <video width="100%" preload="Metadata" controls>
                    <source src="message/<?php echo $fileName ?>">
                </video>
            </div>
            <?php } ?>
        </div>
    </body>
</html>
