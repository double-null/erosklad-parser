<?php

namespace App\Helpers;

class  FileSystem
{
    public static function folder()
    {

    }

    public static function saveFile($link, $filePath)
    {
        $file = file_get_contents($link);
        file_put_contents($filePath, $file);
    }
}
