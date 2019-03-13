<?php
/**
 * Created by PhpStorm.
 * User: Rolland
 * Date: 13.03.2019
 * Time: 10:55
 */

namespace App\PhpParser;


class CommentDumper
{
    /** @var CommentCollector */
    private $collector;

    public function __construct(CommentCollector $collector)
    {
        $this->collector = $collector;
    }

    public function dump(){
        $output = '';
        foreach($this->collector->getComments() as $comment){
            $output .= $comment->getClearedText();
            $output .= PHP_EOL.str_repeat('-',80).PHP_EOL;
        }
        return $output;
    }

    public function dumpToFile($filePath){
        file_put_contents($filePath,$this->dump());
    }
}