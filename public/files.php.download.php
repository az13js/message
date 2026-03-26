<?php
/**
 * 后台下载服务
 *
 * 用法：php files.php.download.php
 * 或配置systemd服务自动运行
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain');
    echo "This script must be run from command line.\n";
    exit(1);
}

$scriptDir = dirname(__FILE__);

// 找到主脚本：files.php.download.php -> files.php
$mainScript = preg_replace('/\.download\.php$/', '', basename(__FILE__));
$mainScriptPath = $scriptDir . DIRECTORY_SEPARATOR . $mainScript;

if (!is_file($mainScriptPath)) {
    echo "Main script not found: $mainScriptPath\n";
    echo "Expected to find $mainScript in the same directory.\n";
    exit(1);
}

// 数据目录和文件目录
$scriptName = pathinfo($mainScript, PATHINFO_FILENAME);
$dataDir = $scriptDir . DIRECTORY_SEPARATOR . '.' . $scriptName;
$tasksDir = $dataDir . DIRECTORY_SEPARATOR . 'tasks';
$filesDir = $scriptDir . DIRECTORY_SEPARATOR . $scriptName;

if (!is_dir($tasksDir)) {
    mkdir($tasksDir, 0755, true);
}
if (!is_dir($filesDir)) {
    mkdir($filesDir, 0755, true);
}

function log_msg(string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
}

function download_file(string $url, string $savePath): bool {
    $fp = fopen($savePath, 'wb');
    if ($fp === false) {
        return false;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_FILE => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 86400,
        CURLOPT_CONNECTTIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);

    if (!$result || $httpCode >= 400) {
        unlink($savePath);
        return false;
    }

    return true;
}

function process_tasks(string $tasksDir, string $filesDir): void {
    $handle = opendir($tasksDir);
    if ($handle === false) {
        return;
    }

    while (($file = readdir($handle)) !== false) {
        if ($file === '.' || $file === '..' || pathinfo($file, PATHINFO_EXTENSION) !== 'json') {
            continue;
        }

        $taskPath = $tasksDir . DIRECTORY_SEPARATOR . $file;
        $task = json_decode(file_get_contents($taskPath), true);

        if (!$task || ($task['status'] ?? '') !== 'pending') {
            continue;
        }

        $task['status'] = 'downloading';
        file_put_contents($taskPath, json_encode($task));

        log_msg("Downloading: {$task['filename']}");

        $tempPath = $filesDir . DIRECTORY_SEPARATOR . '.' . uniqid() . '.tmp';
        $filename = $task['filename'];

        // 如果文件已存在，生成新文件名
        $finalPath = $filesDir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($finalPath)) {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $counter = 1;
            while (file_exists($filesDir . DIRECTORY_SEPARATOR . $name . '_' . $counter . ($ext ? '.' . $ext : ''))) {
                $counter++;
            }
            $filename = $name . '_' . $counter . ($ext ? '.' . $ext : '');
            $finalPath = $filesDir . DIRECTORY_SEPARATOR . $filename;
            log_msg("Renamed to: {$filename}");
        }

        if (download_file($task['url'], $tempPath)) {
            rename($tempPath, $finalPath);
            log_msg("Completed: {$filename}");
        } else {
            log_msg("Failed: {$task['filename']}");
            if (is_file($tempPath)) {
                unlink($tempPath);
            }
        }

        unlink($taskPath);
    }

    closedir($handle);
}

log_msg('Download service started');
log_msg("Tasks: $tasksDir");
log_msg("Files: $filesDir");

while (true) {
    process_tasks($tasksDir, $filesDir);
    sleep(1);
}