<?php
namespace FakeLaravel\services;

use \FakeLaravel\base\Service;
use \FakeLaravel\exceptions\InvalidKeyException;

class FileService extends Service
{
    public function __construt()
    {
        parent::__construt();
    }

    public function saveFile($tmpsrc, $dest, $append = false)
    {
        if (!$append) {
            $status = move_uploaded_file($tmpsrc, __DIR__ . "/../files/$dest");
            if ($status === false) {
                throw new InvalidKeyException("Cannot save file");
                
            }
        }


    }

    public function loadFile()
    {
        
    }

    public function fileOffset()
    {
        
    }
    
}
