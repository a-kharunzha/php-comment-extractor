<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Rolland
 * Date: 13.03.2019
 * Time: 10:35
 */

namespace App\PhpParser;


use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use \App\PhpParser\Comment as AppComment;

class CommentCollector extends NodeVisitorAbstract
{
    /** @var CommentCollection */
    private $collection;

    public function enterNode(Node $node)
    {
        /*
        сложные выражения на одной строке содержат более одной ноды.
        и, получается, что для каждого из них триггерится enterNode с одними и теми же комментами. Это ж дерево))
        потому пока как временная мера, проверяем что ноду с таким номером уже обрабатывали.
        тут потенциально две дырки
            - обработка многострочных нод, у детей могут отличаться строки от родителя, а комменты те же
            - обработка двух файлов, для которых по случайности есть всего один коммент, находящийся на одном номере строки
        Думаю решать это дело хешем от текста коммента
        */
        static $prevNodeLine = null;
        // dump(get_class($node));
        if ($prevNodeLine === $node->getLine()) {
            return;
        }
        $nodeComments = $node->getComments();
        // dump($nodeComments);
        if ($nodeComments) {
            foreach ($nodeComments as $comment) {
                $this->storeComment($comment);
            }
        }
        $prevNodeLine = $node->getLine();
    }

    protected function storeComment(Comment $comment)
    {
        // помни, что объекты передаются по ссылке

        // условие, что текущий комент не является продолжением предыдущего
        /** @var AppComment $prevComment */
        $prevComment = $this->collection->last();
        // таковым признаком будем считать то, что он начинается сразу после окончания предудущего
        $sameComment =
            $prevComment
            &&
            ($prevComment->getEndLine() + 1 == $comment->getLine());
        if (!$sameComment) {
            $storedComment = new AppComment('');
            $storedComment->copyAttributesFrom($comment);
            $this->collection->addComment($storedComment);
        } else {
            $storedComment = $prevComment;
        }
        $storedComment->addToText($comment->getText());
    }

    /**
     * @return CommentCollection
     */
    public function getCollection(): CommentCollection
    {
        return $this->collection;
    }

    /**
     * @param CommentCollection $comments
     */
    public function setCollection(CommentCollection $comments): void
    {
        $this->collection = $comments;
    }

    public function unsetCollection()
    {
        $this->collection = null;
    }
}