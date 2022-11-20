<?php
/**
 * 随机获取最多4个key
 */
function getRandomKeys(array $files): array
{
    if (count($files) <= 1) {
        return [0];
    }
    return array_rand($files, count($files) < 4 ? count($files) : 4);
}
