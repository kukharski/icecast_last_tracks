<?php

/**************************************************************************************
 * Простой php-скрипт, который ыдает файл в виде csv-подобного файла в виде:
 *                           ДАТА|ИСПОЛНИТЕЛЬ|ТРЕК
 *************************************************************************************/

//~ Где лежит лог плейлиста icecast?
$plst_log = '/var/log/icecast.playlist.log';

//~ Поехали :o)

//~ Читаем лог-файл icecast'а в массив
//~ и сохраняем только уникальные строки
$lines = array_unique(file($plst_log));

//~ Сортируем массив по убыванию
arsort($lines);

$linez = '';

//~ Добавляем первую строчку для CSV
//~ (раскомментировать, если нужно выдавать как csv)
// $linez .= "DATETIME|ARTIST|TITLE\n";

foreach ($lines as $line) $linez .= prepareString($line);

$linez = preg_replace("/[\r\n]+/", "\n", $linez);

//~ Если нужен в виде CSV-файла
// header("Content-type: text/csv");
//~ Если выводить в виде TXT
header("Content-type: text/txt");
//~ header("Content-Disposition: attachment; filename=lasttracks.csv");
header("Pragma: no-cache");
header("Expires: 0");

echo trim($linez) . "\n";

/*** Функция очистки строки ***/

function prepareString($istr)
{
    //~ Чистим (удаляем вхождения для потоков 192.aac|192.mp3)
    $ostr = preg_replace("/(.)*\/192\.(aac|mp3)(.)*/", '',  $istr);
    //~ Удаляем джинглы
    $ostr = preg_replace("/(.)*\Jingle(.)*/i",          '',  $ostr);
    //~ Удаляем упоминание о последнем потоке
    $ostr = str_replace('|/96.aac',                    '',  $ostr);
    //~ Удаляем ненужное поле
    $ostr = preg_replace("/\|[0-9]*\|/",               '|', $ostr);

    $ostr = trim($ostr);

    $item  = explode('|', $ostr);

    if ((isset($item[0])) && ($item[0] != ''))
    {
        //~ Преобразуем дату в удобный формат
        $dt = new \DateTime($item[0]);
        $tt = $dt->format('Y-m-d H:i:s');
        //~ Получаем первую часть (исполнитель)
        $pa = strstr($item[1], " - ", true);
        //~ Получаем вторую часть (название песни) и удаляем " - "
        $pt = substr(strstr($item[1], " - ", false), 3);

        //~ Строим строку
        $nstr = $tt . '|' . $pa . '|' . $pt . "\n";
    }
    else
    {
        $nstr = '';
    }

    return $nstr;
}
