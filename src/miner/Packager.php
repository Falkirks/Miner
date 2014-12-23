<?php
namespace miner;

use League\CLImate\CLImate;

class Packager{
    public static function package(CLImate $climate){
        date_default_timezone_set("UTC");
        $pharPath = dirname(MAIN_PATH) ."/Miner.phar";
        if(!is_file($pharPath) || $climate->confirm("An existing phar will be overwritten. Continue?")->confirmed()){
            $phar = new \Phar($pharPath);
            $phar->setMetadata([
                "name" => "Miner",
                "version" => "0.1",
                "creationDate" => strtotime("now")
            ]);
            $phar->setStub('<?php require_once("main.php");__HALT_COMPILER();');
            $phar->setSignatureAlgorithm(\Phar::SHA1);
            $phar->startBuffering();
            $filePath = MAIN_PATH;
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath));
            $progress = $climate->progress(iterator_count($iterator));
            foreach ($iterator as $file) {
                $progress->advance();
                $path = ltrim(str_replace(array("\\", $filePath), array("/", ""), $file), "/");
                if ($path{0} === "." || strpos($path, "/.") !== false) {
                    continue;
                }
                $phar->addFile($file, $path);
            }
            $phar->compressFiles(\Phar::GZ);
            $phar->stopBuffering();
            return $pharPath;
        }
    }
}