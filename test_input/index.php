<?php declare(strict_types=1);

/** @var int $a описание переменной*/
$a = 5;

/**
 * какой-то многострочный php-doc
 */

/*
просто многострочный коммент

*/

// многострочный
// из
// однострочных


/*
многострочный, сразу после которого идет
*/
// однострочный
// с дополнением


// отдельный однострочный


/**
 *
 * описание функции doSomeThing
 * @param $a
 * @param int $b
 */
function doSomeThing($a,$b=4){
    // коммент вначале функции
    // он многострочный из отдельных строк

    if(1==4){
        // что-то внутри if-а
        if(22==44){
            /*
            что-то внутри второго вложенного ифа
            многострочный коммент
            */
        }
    }
}