<?php

namespace Impack\PHPTool;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

// Archive::create()->addDir('../app')->close();
// Archive::create()->build(['../app', '../js'], 'site');
// archiveByCMD('../app', null, 'tar');

// outputZipFile(Archive::create()->build(['../app', '../js']));

class Archive
{
    protected $zip;

    protected $filename;

    protected $entrydir = '';

    public function __construct()
    {
        $this->zip = new ZipArchive;
    }

    public function openFile($filename = null, $flags = ZipArchive::CREATE)
    {
        $this->filename = empty($filename) ? time() . '.zip' : $filename;

        if ($this->zip->open($this->filename, $flags) !== true) {
            throw new Exception("Cannot open <$this->filename>\n");
        }

        return $this;
    }

    public static function create($filename = null, $flags = ZipArchive::CREATE)
    {
        $instance = new static;
        $instance->openFile($filename, $flags);
        return $instance;
    }

    /**
     * @param string[]|string $from
     */
    public function build($from, $entrydir = null)
    {
        empty($entrydir) || $this->setEntrydir($entrydir);
        $this->addFiles($from);
        $this->zip->close();
        return $this->getFilename();
    }

    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string[]|string $from
     */
    public function addFiles($from)
    {
        $from = is_array($from) ? $from : [$from];
        foreach ($from as $file) {
            $this->addFrom($file);
        }
        return $this;
    }

    public function addFrom($from)
    {
        if (is_file($from)) {
            $this->zip->addFile($from, $this->getEntryname(basename($from)));
        } else if (is_dir($from)) {
            $this->addDir($from);
        }

        return $this;
    }

    public function addDir($dir, $hasSelf = true)
    {
        $dir        = realpath($dir);
        $removePath = $hasSelf ? dirname($dir) : $dir;

        // 创建递归目录迭代器，可遍历目录下的所有文件
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $this->zip->addFile($filePath, $this->getEntryname(substr($filePath, strlen($removePath) + 1)));
            }
        }

        return $this;
    }

    public function getEntryname($subentryname)
    {
        return ($this->entrydir ? $this->entrydir . '/' : '') . $subentryname;
    }

    public function setEntrydir($dirname)
    {
        if (!$this->entrydir && $dirname) {
            $this->zip->addEmptyDir($this->entrydir);
            $this->entrydir = $dirname;
        }

        return $this;
    }

    public function close()
    {
        $this->zip->close();
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function __call($name, $arguments)
    {
        return $this->zip->{$name}(...(array) $arguments);
    }
}

function outputZipFile($file)
{
    outputArchiveFile($file, 'application/zip');
}

function outputArchiveFile($file, $contentType = 'application/zip')
{
    header('Content-type: ' . $contentType);
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Content-length: ' . filesize($file));
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($file);
    exit;
}

function archiveByCMD($source, $target = null, $format = 'zip')
{
    $target = $target ? $target : time() . '.' . $format;

    if (file_exists($target)) {
        return false;
    }

    $source = is_array($source) ? implode('" "', $source) : $source;

    switch ($format) {
        case 'zip':
            $command = "zip -r \"$target\" \"$source\"";
            break;
        case 'tar':
            $command = "tar -cvf \"$target\" \"$source\"";
            break;
        case 'tar.gz':
            $command = "tar -czvf \"$target\" \"$source\"";
            break;
        case 'tar.bz2':
            $command = "tar -cjvf \"$target\" \"$source\"";
            break;
        default:
            return false;
    }

    exec($command);

    if (file_exists($target)) {
        return $target;
    }

    return false;
}