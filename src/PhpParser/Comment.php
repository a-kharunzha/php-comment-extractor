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
        /*
        однострочные комменты могут заканчиваться, а могут и не заканчиваться переносом строки, когда приходят объектом из парсера
        многострочные тоже. Потому, в самом конце строки перенос нужно удалить
        флаг m не ставлю, чтобы многострочная строка учитывалась как одна, и не удалялись переносы в конце промежуточных строк
        */
        $text = preg_replace('@[\s]+$@','',$text);
        // добавляем перенос строки только если собственный текст не пустой. Иначе, он был создан с пустым текстом, и это только первый вызов addToText
        if(!empty($this->text)){
            $text = PHP_EOL.$text;
        }
        $this->text .= $text;
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
        // +1 потому что строк на одну больше чем их разделителей, ведь сначала и с конца строки отрезаны
        return substr_count($text ,PHP_EOL) + 1;

    }
}