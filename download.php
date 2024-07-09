<?php
/**
 * 这是一个常驻后台的脚本。 
 */

function info(string $message)
{
    echo date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;
}

function check()
{

    $dir = __DIR__ . '/public/.urls';
    $fp = opendir($dir);
    if (false === $fp) {
        return false;
    }
    $have_files = false;
    while (true) {

        $file = readdir($fp);
        if (empty($file)) {
            break;
        }
        if (in_array($file, ['.', '..'])) {
            continue;
        }
        $have_files = true;
        break;
    }
    closedir($fp);
    return $have_files;
}

function real_download(string $file, string $url)
{
    $save_file_tmp_path = __DIR__ . '/public/.tmp_' . $file;
    # 使用CURL下载地址 $url 下载的内容保存到 $save_file_tmp_path
    $handle = curl_init();
    if (false === $handle) {
        return false;
    }
    $fp = fopen($save_file_tmp_path, 'wb');
    if (false === $fp) {
        return false;
    }

    $set_result = curl_setopt_array($handle, [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_COOKIESESSION => false,
        CURLOPT_FILETIME => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_FORBID_REUSE => true,
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_HTTPPROXYTUNNEL => false,
        CURLOPT_NETRC => false,
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_UNRESTRICTED_AUTH => false,
        CURLOPT_UPLOAD => false,
        CURLOPT_VERBOSE => false,
        CURLOPT_CONNECTTIMEOUT => 60,
        CURLOPT_TIMEOUT => 24 * 60 * 60,
        CURLOPT_DNS_CACHE_TIMEOUT => 120,
        CURLOPT_MAXCONNECTS => 65535,
        CURLOPT_MAXREDIRS => 20,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 Edg/126.0.0.0',
        CURLOPT_HTTPHEADER => ['Accept-Language: en,en-US;q=0.9,en-GB;q=0.8,zh-CN;q=0.7,zh;q=0.6'],
        CURLOPT_URL => $url,
        CURLOPT_FILE => $fp,
    ]);
    if (false === $set_result) {
        curl_close($handle);
        fclose(fp);
        return false;
    }
    if (false === curl_exec($handle)) {
        fwrite(STDERR, curl_error($handle).PHP_EOL);
    }
    curl_close($handle);
    fclose($fp);
    rename($save_file_tmp_path, __DIR__ . '/public/message/' . $file);
    return true;
}

function download()
{
    $dir = __DIR__ . '/public/.urls';
    $fp = opendir($dir);
    if (false === $fp) {
        return;
    }
    $should_delete_files = [];
    while (true) {

        $file = readdir($fp);
        if (empty($file)) {
            break;
        }
        if (in_array($file, ['.', '..'])) {
            continue;
        }
        $contents = file_get_contents("$dir/$file");
        if (false === $contents) {
            $should_delete_files[] = $file;
            continue;
        }
        $info = json_decode($contents, true);
        if (empty($info)) {
            $should_delete_files[] = $file;
            continue;
        }
        if (empty($info[0]) || empty($info[1])) {
            $should_delete_files[] = $file;
            continue;
        }
        real_download($info[0], $info[1]);
        $should_delete_files[] = $file;
    }
    closedir($fp);
    foreach ($should_delete_files as $file) {
        unlink("$dir/$file");
    }
}

info('The script is running.');

while (true) {
    $have_download_task = check();
    if ($have_download_task) {
        info('Downloading.');
        download();
    }
    sleep(1);
}
