<?php
/**
 * Created by PhpStorm.
 * User: Rolland
 * Date: 13.03.2019
 * Time: 10:55
 */

namespace App\PhpParser;


abstract class CollectionDumper
{
    /**
     * @param CommentCollection $collection
     *
     * @return string
     */
    public function makeText(CommentCollection $collection) : string{
        $comments = $collection->getComments();
        if(empty($comments )){
            return '';
        }
        $output = 'File: '.$collection->getPath().PHP_EOL;
        /** @var Comment $comment */
        foreach($comments  as $comment){
            $output .= 'Line '.$comment->getLine().':'.PHP_EOL;
            $output .= $comment->getClearedText();
            $output .= PHP_EOL.str_repeat('-',10).PHP_EOL;
        }
        $output .= 'End of file '.$collection->getPath().PHP_EOL;
        $output .= str_repeat('=',80).PHP_EOL.PHP_EOL;
        return $output;
    }

    /**
     * @param CommentCollection $collection
     *
     * @return void
     */
    abstract public function dump(CommentCollection $collection) : void;
}