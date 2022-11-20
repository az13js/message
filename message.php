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
    sort($files, SORT_NATURAL|SORT_FLAG_CASE);
    return $files;
}

/**
 * @param string $target
 */
function do_file_append(string $target)
{
    if (isset($_FILES['file'])) {
        if (empty($_GET['p'])) {
            move_uploaded_file(
                $_FILES['file']['tmp_name'],
                $target . DIRECTORY_SEPARATOR . $_POST['filename']
            );
        } else {
            $temp = uniqid('_', true);
            move_uploaded_file(
                $_FILES['file']['tmp_name'],
                $target . DIRECTORY_SEPARATOR . $_POST['filename'] . $temp
            );
            $handle = fopen($target . DIRECTORY_SEPARATOR . $_POST['filename'], 'ab');
            fwrite($handle, file_get_contents($target . DIRECTORY_SEPARATOR . $_POST['filename'] . $temp));
            fclose($handle);
            unlink($target . DIRECTORY_SEPARATOR . $_POST['filename'] . $temp);
        }
        header('Content-type: application/json');
        echo json_encode(['code' => 0]);
    } else {
        header('Content-type: text/plain');
        var_dump($_FILES);
    }
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
    if (isset($_GET['append']) && $_GET['append'] == 1 && 0 === mb_stripos($_SERVER['HTTP_CONTENT_TYPE'] ?? '', 'multipart/form-data')) {
        do_file_append($message);
        exit();
    }
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
    <script>
    var queue = {"datas":[], "pointer": 0, "function": null, "notify": null};
    function queueAppend(data)
    {
        queue.datas.push(data);
    }
    function queueSetFunction(func)
    {
        queue.function = func;
    }
    function queueFire()
    {
        queue.function(queue.datas[queue.pointer], queue.pointer, queue.datas.length - 1);
    }
    function queueSuccess()
    {
        queue.pointer++;
        queue.notify(queue.pointer, queue.datas.length);
        if (queue.pointer >= queue.datas.length) {
            queue.pointer = 0;
            queue.datas = [];
            queue.function = null;
            queue.notify = null;
        } else {
            queueFire();
        }
    }
    function queueFail()
    {
        queueFire();
    }
    function queueNotify(func)
    {
        queue.notify = func;
    }
    function queueExists()
    {
        return queue.datas.length > 0;
    }
    function uploadBigFile(inputId, sliceSize)
    {
        if (queueExists()) {
            return;
        }
        console.log("In function uploadBigFile");
        document.getElementById("progress").setAttribute("value", 0);
        document.getElementById("progressval").innerText="0%";
        queueNotify(function(success, total){
            document.getElementById("progress").setAttribute("value", success);
            document.getElementById("progressval").innerText = parseInt(100 * success / total) + "%";
        });
        var inputElement = document.getElementById(inputId);
        if (inputElement.files.length > 0) {
            var file = inputElement.files[0];
            console.log("File size: " + file.size + " byte");
            var sliceTotal = Math.ceil(file.size / sliceSize);
            console.log("Total slice: " + sliceTotal);
            document.getElementById("progress").setAttribute("max", sliceTotal);
            for (var i = 0; i < sliceTotal; i++) {
                queueAppend({
                    "id": i + 1,
                    "offset": i * sliceSize,
                    "end": Math.min(i * sliceSize + sliceSize, file.size),
                    "fileObject": file
                });
            }
            queueSetFunction(function (data, p, total) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "message.php?append=1&p=" + p, true);
                xhr.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        var result = JSON.parse(this.responseText);
                        if (result && result.code == 0) {
                            queueSuccess();
                        } else {
                            queueFail();
                        }
                    }
                }
                var formData = new FormData();
                formData.append('filename', data.fileObject.name);
                formData.append('file', data.fileObject.slice(data.offset, data.end));
                xhr.send(formData);
            });
            queueFire();
        }
        console.log("Out function uploadBigFile");
    }
    </script>
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
            Normal file upload:<br/>
            <input type="file" name="file"/><br/>
            <input type="submit" name="submit" value="upload"/>
        </p>
    </form>
    <p>
        BIG FILE UPLOAD<br>
        <input id="bigFile" type="file" name="file"><br>
        <progress id="progress" value='0' max='100'></progress><br>
        <span id="progressval">-%</span><br>
        <button onclick="uploadBigFile('bigFile', 204800)">UPLOAD</button>
    </p>
    <ol>
        <?php foreach (get_file_list($message) as $file) { ?>
            <li>
                <a href="message/<?php echo $file; ?>" download="<?php echo $file; ?>"><?php echo htmlspecialchars($file); ?></a>
                <?php if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'mp4') { ?>
                    <a href="videos.php?file=<?php echo $file; ?>">播放视频</a>
                <?php } ?>
            </li>
        <?php } ?>
    </ol>
</body>
</html>