<?php


// простой коммент 1
// простой коммент 2 // что-то еще


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