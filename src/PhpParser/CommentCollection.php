<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Rolland
 * Date: 14.03.2019
 * Time: 15:22
 */

namespace App\PhpParser;


class CommentCollection implements \Iterator
{
    /** @var string */
    private $path;
    /** @var array  */
    private $comments = [];
    /** @var int  */
    private $position = 0;

    function __construct($path)
    {
        $this->path = $path;
    }

    function addComment(Comment $comment){
        $this->comments[] = $comment;
    }

    function getComments(){
        return $this->comments;
    }

    public function rewind() {
        $this->position = 0;
    }

    public function current() : Comment{
        return $this->comments[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->comments[$this->position]);
    }

    public function last()
    {
        if(empty($this->comments)){
            return false;
        }
        // было бы круто, но увы, array_key_last (PHP 7 >= 7.3.0)
        // return $this->comments[\array_key_last($this->comments)];
        // заюзаю end, пока не критично не смещать указатеть внутреннего массива
        return end($this->comments);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}