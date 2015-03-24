<?
mysql_connect('localhost','root','root');
mysql_set_charset('UTF8');
mysql_select_db('films');
session_start();
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
class Game{
    public $listOfLastIds,$images,$name,$id;
    function __construct(){
        if(!isset($_SESSION['ids'])||!count($_SESSION['ids'])){
            $_SESSION['ids'] = array();
            $this->setSessionId('0');
            $this->listOfLastIds = 0;
        }else{
            $this->listOfLastIds = implode(', ',$_SESSION['ids']);
        }
    }
    public function setSessionId($id){
        $_SESSION['ids'][]= $id;
        return true;
    }
    public function getSessionIds(){
        return $_SESSION['ids'];
    }
    public function resetGame(){
        unset($_SESSION['ids']);
    }
    public function getFilm(){
        $q = "SELECT id, name from films where id NOT IN (".$this->listOfLastIds.") ORDER BY RAND() LIMIT 1";
        $query  = mysql_query($q);
        if($res = mysql_fetch_assoc($query)){
                $this->name = $res['name'];
                $this->id = $res['id'];
            if(is_dir($_SERVER['DOCUMENT_ROOT']."/img/".$this->id."/")&&is_file($_SERVER['DOCUMENT_ROOT']."/img/".$this->id."/0.jpg")){//if exists dir
                for($j=0;$j<=3;$j++){
                    $this->images[] = '/img/'.$this->id.'/'.$j.".jpg";
                }
            }else{
                $this->setSessionId($this->id);
                header('Location: /', true, 302);
                exit;
            }
            $this->setSessionId($this->id);
        }else{
            $this->name = 'prcav';
            $this->images  ='';

        }

    }
    public function deleteItem($id){
        if($id=intval($id)){
            if(mysql_query("DELETE FROM images WHERE film_id=".$id."")&&$result = mysql_query("DELETE FROM films WHERE id=".$id." LIMIT 1")){
                $dirPath = $_SERVER['DOCUMENT_ROOT'].'/img/'.$id.'/';
                foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                    $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
                }
                if(rmdir($dirPath)){
                    return true;
                }
            }

        }
    }
}

$game = new Game();
 $game->getFilm();
if(isset($_GET['rm'])&&$rmId = intval($_GET['rm'])){
    if($game->deleteItem($rmId)){
        print 'ok';
        exit;
    }
}
if(@intval($_GET['reset'])){
    if($game->resetGame()){
        print 'ok';
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Game</title>

    <!-- Bootstrap -->
    <link href="/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/js/fancybox/jquery.fancybox.css" rel="stylesheet" />
    <style>
        #name{
            font-size: 48px;
            text-align: center;
            margin-top:200px;

        }
        #name.win{
            color: green;
        }
        #name.ups{
            color: red;
        }
    </style>

</head>
<body >

    <?php if($game->images){
        for($i=0;$i<count($game->images);$i++):?>
        <?php if($i>0):?><div style="display:none;"><?endif?>
        <a class="fancybox" rel="gal" href="<?=$game->images[$i];?>"></a>
        <?php if($i>0):?></div><?endif?>
        <?php endfor;}?>
    <div id="name" style="display: none;"><?=$game->name?></div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="/js/jquery-1.11.0.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/js/bootstrap.min.js"></script>
<script src="/js/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        var itemId = <?=isset($game->id)?$game->id:0;?>;
        console.log(<?=@count($_SESSION['ids'])?>);
        $(".fancybox").fancybox({
                padding : 0
            });
        $(".fancybox").eq(0).trigger('click');
        $(document).keydown(function(e) {
            console.log(e.keyCode);
            if (e.keyCode == 87) {//w
                $.fancybox.close();
                $('#name').addClass('win').show();
            }else  if (e.keyCode == 68&& (e.ctrlKey|| e.metaKey)) {//ctrl+d delete
                $.get('?rm='+itemId,function(data){
                    if(data=='ok'){
                        window.location.href="/";
                    }
                });
            }else  if (e.keyCode == 82&& (e.ctrlKey|| e.metaKey)) { //ctrl+r reset
                $.get('?reset='+1,function(data){
                    if(data=='ok'){
                        window.location.href="/";
                    }
                });
                return false;
            }else if(e.keyCode ==82&&!e.ctrlKey&&!e.metaKey){//r
                window.location.href="/";
            }
        });





            //$('.newWindow').click(function (event){

                var url = '';
                var windowName = "popUp";//$(this).attr("name");
                var windowSize = "width=200,height=200";

                var w = window.open(url, windowName, windowSize);
                console.log($(w.document.body).html($('#name').html()));


            //});
    });

</script>
</body>
</html>
