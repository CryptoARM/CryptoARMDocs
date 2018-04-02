<?php
namespace TrustedNet\Docs;

// TODO: remove class Directory?
class Directory
{

    protected $path;

    public static function getFileName($path)
    {
        $dirs = explode("/", $path);
        $len = count($dirs);
        return $dirs[--$len];
    }

    public static function create($path, $cb = null)
    {
        $dirs = explode("/", $path);

        $pos = $_SERVER['DOCUMENT_ROOT'];
        $created = false;
        foreach ($dirs as &$dir) {
            $pos .= '/' . $dir;
            if (!TrustedDirectory::exists($path)) {
                mkdir($pos, 0777);
                $created = true;
            }
        }
        $res = TrustedDirectory::open($path);
        if ($created && isset($cb)) {
            $cb($res);
        }
        return $res;
    }

    public static function exists($path)
    {
        if (file_exists($path)) {
            return true;
        } else {
            return false;
        }
    }

    public static function open($path)
    {
        $res = null;
        if (TrustedDirectory::exists(TrustedDirectory::getLocalRoot() . '/' . $path)) {
            $res = new TrustedDirectory();
            $res->path = $path;
        }
        return $res;
    }

    public static function getLocalRoot()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getSystemPath()
    {
        return TrustedDirectory::getLocalRoot() . '/' . $this->path;
    }

    public function getHttpPath()
    {
        return TrustedDirectory::getHttpRoot() . '/' . $this->path;
    }

    public static function getHttpRoot()
    {
        return TRUSTED_PROJECT_HOST;
    }

    public function remove($cb = null)
    {
        unlink($this->path);
        if (isset($cb)) {
            $cb();
        }
    }

}

