<?php
/**
 * @param string $dir
 * @return array
 */
function get_file_list(string $dir)
{
    $handle = opendir($dir);
    $files = [];
    while (false !== ($file = readdir($handle))) {
        if (in_array($file, ['.', '..'])) {
            continue;
        }
        $files[] = $file;
    }
    closedir($handle);
    return $files;
}

/**
 * @param string $target
 */
function do_file_upload(string $target)
{
    if (isset($_FILES['file'])) {
        move_uploaded_file(
            $_FILES['file']['tmp_name'],
            $target . DIRECTORY_SEPARATOR . $_FILES['file']['name']
        );
        $upload_finish = <<<UPLOAD_FINISH
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="2; url="message.php"/>
    <title>INFO</title>
    <style>
        body {font-family:"Arial","WenQuanYi Zen Hei";}
    </style>
</head>
<body>
    <p>OK.<p>
</body>
</html>
UPLOAD_FINISH;
        echo $upload_finish;
    } else {
        header('Content-type: text/plain');
        var_dump($_FILES);
    }
}

/**
 * @param string $target
 */
function do_message_save(string $target)
{
    if (isset($_POST['message'])) {
        $day =  date('Y-m-d', $_SERVER['REQUEST_TIME']);
        $time = date('H:i:s', $_SERVER['REQUEST_TIME']);
        $ms = sprintf('%3d', round(($_SERVER['REQUEST_TIME_FLOAT'] - $_SERVER['REQUEST_TIME']) * 1000));
        $handle = fopen($target . DIRECTORY_SEPARATOR . 'message ' . $day . '.txt', 'ab');
        fwrite($handle, "[$day $time $ms]" . PHP_EOL . $_POST['message'] . PHP_EOL);
        fclose($handle);
        $save_finish = <<<UPLOAD_FINISH
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="2; url="message.php"/>
    <title>INFO</title>
    <style>
        body {font-family:"Arial","WenQuanYi Zen Hei";}
    </style>
</head>
<body>
    <p>OK.<p>
</body>
</html>
UPLOAD_FINISH;
        echo $save_finish;
    } else {
        header('Content-type: text/plain');
        var_dump($_POST);
    }
}

if (!is_dir($message = __DIR__ . DIRECTORY_SEPARATOR . 'message')) {
    mkdir($message);
}

if (isset($_SERVER['REQUEST_METHOD']) && 'POST' == mb_strtoupper($_SERVER['REQUEST_METHOD'])) {
    if (0 === mb_stripos($_SERVER['HTTP_CONTENT_TYPE'] ?? '', 'multipart/form-data')) {
        do_file_upload($message);
        exit();
    }
    if (0 === mb_stripos($_SERVER['HTTP_CONTENT_TYPE'] ?? '', 'application/x-www-form-urlencoded')) {
        do_message_save($message);
        exit();
    }
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>message</title>
    <style>
        body {font-family:"Arial","WenQuanYi Zen Hei";}
    </style>
</head>
<body>
    <form action="message.php" method="POST" enctype="application/x-www-form-urlencoded">
        <p>
            message:<br/><textarea name="message" placeholder="your message"></textarea><br/>
            <input type="submit" name="submit" value="save"/>
        </p>
    </form>
    <form action="message.php" method="POST" enctype="multipart/form-data">
        <p>
            <input type="file" name="file"/><br/>
            <input type="submit" name="submit" value="upload"/>
        </p>
    </form>
    <ol>
        <?php foreach (get_file_list($message) as $file) { ?>
            <li><a href="message/<?php echo $file; ?>" download="<?php echo $file; ?>"><?php echo $file; ?></a></li>
        <?php } ?>
    </ol>
</body>
</html>