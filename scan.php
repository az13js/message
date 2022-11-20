<?php
/**
 * 扫描videos目录内的视频文件。
 */
function scan() {
    $scanFiles = [];

    $videosDirectoryName = implode(DIRECTORY_SEPARATOR, [
        __DIR__,
        'message',
    ]);

    if (false !== ($dirResource = opendir($videosDirectoryName))) {
        while (false !== ($fileName = readdir($dirResource))) {
            if (!in_array($fileName, ['.', '..', '.gitignore', 'index.html'])) {
                if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) == 'mp4') {
                    $scanFiles[] = $fileName;
                }
            }
        }
        closedir($dirResource);
    }

    return $scanFiles;
}
