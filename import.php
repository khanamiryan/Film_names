<?php
mysql_connect('localhost','root','root');
mysql_set_charset('UTF8');
mysql_select_db('films');
function getPage($url){
    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, "http://www.wowhead.com");
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type:application/x-www-form-urlencoded"));
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    // grab URL and pass it to the browser
    $get = curl_exec($ch);
    // close cURL resource, and free up system resources
    curl_close($ch);
    return $get;
}

function getImages($name,$save_path,$film_id){
    $page  = getPage("http://www.kinopoisk.ru/index.php?first=yes&what=&kp_query=".$name);
    $page  =iconv('CP1251','UTF-8',$page);
    $pattern = '/\<a href\=\"(.*)\"\>скриншоты\<\/a\>/';
    preg_match($pattern, $page, $matches, PREG_OFFSET_CAPTURE);
    if(!$matches[1][0]){
        unset($matches);
        $pattern = '/\<a href\=\"(.*)\"\>\<span\>\<b\>к\<\/b\>адры\<\/span\>\<\/a\>/';
        preg_match($pattern, $page, $matches, PREG_OFFSET_CAPTURE);
    }
    $screens_page  = getPage('http://www.kinopoisk.ru'.$matches[1][0]);
    $screens_page  =iconv('CP1251','UTF-8',$screens_page);
    $pattern = "/<a href=\"(.*)\"(.*?) title=\"Открыть в новом окне\">/";
    preg_match_all($pattern, $screens_page, $matches2);
    $pictures_page = array_rand($matches2[1],4);
    foreach($pictures_page as $k=>$v){

        $img_page = getPage('http://www.kinopoisk.ru'.$matches2[1][$v]);
        $pattern = "/<img .* id\=\"image\" src=\"(.*?)\"/";
        preg_match($pattern, $img_page, $matches3);
        //$matches3[1].'" />';
        $ext = pathinfo($matches3[1], PATHINFO_EXTENSION);
        $img_name = $k.'.'.$ext;
       @copy($matches3[1],$save_path.$img_name);
        $qu = "INSERT INTO `films`.`images`  SET film_id='".$film_id."', url='".$img_name."'";
        $req = mysql_query($qu);

    }
}
$file_of_names = 'film_names.txt';
$lines = file($file_of_names);
foreach($lines as $val){
    preg_match('/^(.*?) - (.*)/',$val,$match);
    $dir = './img/'.$match[1].'/';


    $name = $match[2];
    $qu = "INSERT INTO films  SET id='".$match[1]."', name='".$match[2]."'";

    $req = mysql_query($qu);
    if(!is_dir($dir)||count(scandir($dir))<=2){
    @mkdir($dir);
    getImages(urlencode($name),$dir,mysql_insert_id());

    sleep(rand(3,10));
    }
}


?>