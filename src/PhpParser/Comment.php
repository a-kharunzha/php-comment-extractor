<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Rolland
 * Date: 13.03.2019
 * Time: 11:05
 */

namespace App\PhpParser;


use PhpParser\Comment as BaseComment;

class Comment extends BaseComment
{
    function getClearedText(){
        $text = $this->getReformattedText();
        $removePatterns = [
            // начало комментария и переносы строк перед текстом
            '@^[\t\s*/]+@m',
            // закрывающий комментарий в конце строки
            '@[*/\s]+$@'
        ];
        $text = preg_replace($removePatterns,'',$text);
        return $text;
    }

    function copyAttributes(BaseComment $source){
        // текст копировать не нужно, потому что он задается при создании объекта
        $this->line = $source->getLine();
        $this->filePos = $source->getFilePos();
        $this->tokenPos = $source->getTokenPos();
    }

    function addToText($text){
        $this->text .= PHP_EOL.$text;
    }

    public function getEndLine()
    {
        // просто напросто считаем от стартовой строки, прибавляя количество строк в текущем неформатированном тексте
        // тут важно, что если текст был склеен из двух и более отдельных комментов через addToText, то перенос между ними даст верное число
        // минус один потому, что первая строка находится на позиции $this->getLine(), ее не нужно учитывать
        return $this->getLine() + $this->getLinesCount() - 1;
    }

    private function getLinesCount(){
        $text = $this->getText();
        /*
        всегда отрезаем вначале коммента один перенос строки. По какой-то пока неясной причине, PhpParser то добавляет там перено, то не добавляет.
        так что можно получить однострочный коммент такой
        "// простой коммент 1"
        или такой
        "\n\r// простой коммент 1"
        или такой
        "\n// простой коммент 1"
        */
        $text = preg_replace('@^[\s]+@','',$text,1);
        return substr_count($text ,"\n");

    }
}