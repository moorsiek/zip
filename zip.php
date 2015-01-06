<?php

class Zip {
    const ROOT_DIR = 'upload';
    const UNZIP_PATH = '/usr/bin/unzip';
    const ZIP_PATH = '/usr/bin/zip';
    static $lastDirId = 0;
    function __construct(){
        $this->file = null;
    }
    function open($path){
        error_log('Zip:: trying to open file "' . $path . "'");
        if (!file_exists($path) || !is_file($path)) {
            return false;
        }

        $this->tmpDir = self::ROOT_DIR . '/tmp' . self::$lastDirId++;
        $this->file = $path;

        $this->exec('rm -Rf %s', [$this->tmpDir]);

        if (!$this->exec('%s -o %s -d %s', [self::UNZIP_PATH, $path, $this->tmpDir])) {
            return false;
        }
    }
    function deleteName($path){
        error_log('Zip:: deleting path "' . $path . "'");
        $path = $this->getAbsPath($path);
        if (!$this->exec('rm -Rf %s', [$path])) {
            return false;
        }
    }
    function close(){
        $oldDir = getcwd();
        chdir($this->tmpDir);
        if (!$this->exec('%s -o -0 -r %s ./**', [self::ZIP_PATH, '../../' . $this->file])) {
            chdir($oldDir);
            return false;
        }
        chdir($oldDir);
    }
    function locateName($path){
        error_log('Zip:: locating path "' . $path . "'");
        $path = $this->getAbsPath($path);
        return file_exists($path);
    }
    function getFromName($path){
        error_log('Zip:: getting by name path "' . $path . "'");
        $path = $this->getAbsPath($path);
        if (!file_exists($path) || !is_file($path)) {
            return false;
        }
        return file_get_contents($path);
    }
    function addFromString($path, $string){
        error_log('Zip:: adding from string path "' . $path . "'");
        $path = $this->getAbsPath($path);
        $this->exec('rm -Rf %s', [$path]);
        file_put_contents($path, $string);
    }
    function addFile($absPath, $path){
        error_log('Zip:: adding from file path "' . $path . "'");
        $path = $this->getAbsPath($path);
        if (!file_exists($absPath) || !is_file($absPath)) {
            return false;
        }
        $this->exec('rm -Rf %s', [$path]);
        file_put_contents($path, file_get_contents($absPath));
    }
    function addEmptyDir($path){
        error_log('Zip:: adding directory "' . $path . "'");
        $path = $this->getAbsPath($path);
        mkdir($path, 0777, true);
        return true;
    }
    function exec($cmd, $args) {
        foreach ($args as &$arg) {
            $arg = escapeshellarg($arg);
        }
        error_log('Zip:: executing cmd "' . vsprintf($cmd, $args) . "'");
        exec(vsprintf($cmd, $args), $out, $exitCode);
        if (!(0 === $exitCode || '0' === $exitCode)) {
            error_log('Zip:: failed to execute!');
            error_log('Zip:: cwd: ' . getcwd());
        }
        return 0 === $exitCode || '0' === $exitCode;
    }
    function getAbsPath($path){
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        return $this->tmpDir . $path;
    }
}