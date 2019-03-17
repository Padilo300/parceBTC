<?php
header('Content-Type: text/html; charset=utf-8', true);
ini_set('error_reporting', E_ALL)       ;
ini_set('display_errors', 1)            ;
ini_set('display_startup_errors', 1)    ;

$host       = "localhost"  ;
$db         = "laravel"    ;
$db_login   = "padilo"     ;
$db_pass    = "padilo300"  ;

// база
try {
    $DB 	=	new PDO("mysql:host=$host;dbname=$db;charset=utf8;", $db_login, $db_pass);
    $DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    echo 'Ошибка: ' . $e->getMessage();
}


function dd($var) {
    echo '<pre style="    background: black;
    padding: 15px;
    color: #0f0;
    font-size: 15px;
    font-weight: 500;
    font-family: monospace;
    width: max-content;
    position: relative;">';
    print_r($var);
    echo '<img src="https://i.ytimg.com/vi/waNM9_ipO5Y/maxresdefault.jpg" style="
            position: absolute;
            width: 300px;
            top: 0;
            right: -300px;">';
    echo '</pre>';
    die();
}

function dl($var) {
    echo '<pre style="    background: black;
    padding: 15px;
    color: #0f0;
    font-size: 15px;
    font-weight: 500;
    font-family: monospace;
    width: max-content;
    position: relative;">';
    print_r($var);
    echo '</pre>';
}

require_once(__DIR__.'/phpQuery-onefile.php'); // библа для удомного разбора ДОМа

// крутим цикл столько сколько страниц пагинации
$maxPage = 167;
for($a = 1; $a < $maxPage; $a++ ){

    $html       = file_get_contents('https://www.bestchange.ru/iobmen-exchanger-'.$a.'.html'); // нужный урл (меняем только название обменника)
    $document   = phpQuery::newDocument($html); 

    $names  = $document->find('td.nospace:not(.inback)'); // имя
    $ip     = $document->find('td.inback.nospace')      ; // ip 
    $date   = $document->find('td:last-child.inback')   ; // дата
    $text   = $document->find('div.review_text')        ; // текст

    $comment = array(); // тут потом все до купы соберем

    $i = 0;

    // вытягиваем все комменты
    foreach($text as $it){
        $comment[$i]['text'] =  htmlspecialchars($it->textContent);
        $i++;
    }

    $i = 0;
    // вытягиваем все имена
    foreach($names as $it){
        $comment[$i]['name'] =  htmlspecialchars($it->textContent);
        $i++;
    }
    $i = 0;

    // вытягиваем все ip
    foreach($ip as $it){
        $comment[$i]['ip'] =  htmlspecialchars($it->textContent);
        $i++;
    }
    $i = 0;

    // вытягиваем все даты
    foreach($date as $it){
        $comment[$i]['date'] =  htmlspecialchars($it->textContent) ;
        $i++;
    }


    // пишем в базу по одной строке (используем подготовленный запрос что-бы не ломать строки)
    foreach($comment as $item){
        $db_str  = "INSERT INTO `rewiew`(`date`, `text`, `ip`, `name`) VALUES (?,?,?,?)";
        $addRow  = $DB->prepare($db_str);
        $addRow->execute(array($item['date'],$item['text'],$item['ip'],$item['name']));
        dl(array($item['date'],$item['text'],$item['ip'],$item['name']));
    }
    
    sleep(rand(5,15 )); // рандомная пауза от 5 до 15 сек что-бы меньше капчу ловить
}

dl($comment); 
?>