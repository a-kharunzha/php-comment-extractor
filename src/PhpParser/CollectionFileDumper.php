<?php
/**
 * Created by PhpStorm.
 * User: Rolland
 * Date: 14.03.2019
 * Time: 17:40
 */

namespace App\PhpParser;


use Webmozart\PathUtil\Path;

class CollectionFileDumper extends CollectionDumper
{
    private $outputFilePath;
    private $dumpMode = FILE_APPEND;

    const OUTPUT_DIR_RIGHTS = 0644;

    /**
     * CollectionFileDumper constructor.
     *
     * @param string $outputFilePath путь для файла для записи
     * @param bool $clearFile создать и очистить файл при создании дампера
     */
    public function __construct(string $outputFilePath,bool $clearFile = true)
    {
        $this->outputFilePath = $outputFilePath;
        if(
            $clearFile
            &&
            file_exists($this->outputFilePath)
            &&
            is_file($this->outputFilePath)
        ){
            file_put_contents($this->outputFilePath,'');
        }
    }

    /**
     * @param CommentCollection $collection
     */
    public function dump(CommentCollection $collection) : void
    {
        $text = $this->makeText($collection);
        $this->makeSureDirExists();
        // @todo: вот думаю, було бы лучше перейти на стримы, а?
        file_put_contents($this->outputFilePath,$text,$this->dumpMode);
    }

    /**
     * @param int $dumpMode combination of flags FILE_USE_INCLUDE_PATH,FILE_APPEND,LOCK_EX
     */
    public function setDumpMode(int $dumpMode): void
    {
        $this->dumpMode = $dumpMode;
    }

    /**
     * проверят, что директория для файла, куда будет записан ответ, существует
     */
    private function makeSureDirExists()
    {
        // проверяем, что директория файла существует
        $dirPath = Path::getDirectory($this->outputFilePath);
        if(!file_exists($dirPath )){
            // нет директории, куда будем складывать результат, создаем
            mkdir ($dirPath  ,self::OUTPUT_DIR_RIGHTS,true);
        }
    }

}