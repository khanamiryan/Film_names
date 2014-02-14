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

if(isset($_SESSION['ids'])){

            $listOfLastIds = implode(', ',$_SESSION['ids']);
        }else{
            $listOfLastIds = '0';
        }
       $q = "SELECT id, name from films where id NOT IN (0, ".$listOfLastIds.") ORDER BY RAND() LIMIT 1";
        $query  = mysql_query($q);
        if($res = mysql_fetch_assoc($query)){
            $name = $res['name'];
            $id = $res['id'];

            $q2= "SELECT id,url from images where film_id=".$res['id']." ORDER BY RAND()";
            $query2  = mysql_query($q2);
            if(mysql_num_rows($query2)){
                while($res2= mysql_fetch_assoc($query2)){

                    $images[] = '/'.$id.'/'.$res2['url'];
                }
            }else{

            header('Location: /', true, 302);
                exit;
            }
            $_SESSION['ids'][]= $id;
        }else{
            $name = 'prcav';
            $images  ='';

        }
function deleteItem($id){

    if($id=intval($id)){
         $query = "DELETE FROM films WHERE id=".$id." LIMIT 1";

        mysql_query("DELETE FROM images WHERE film_id=".$id."");
        if($result = mysql_query($query)){

            $dirPath = './img/'.$id.'/';
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
            }
            if(rmdir($dirPath)){
                return true;
            }
        }

    }
}
if(isset($_GET['rm'])&&$rmId = intval($_GET['rm'])){
    if(deleteItem($rmId)){
        print 'ok';
        exit;
    }
}
if(@$rmId = intval($_GET['reset'])){
    unset($_SESSION['ids']);
    print 'ok';
    exit;

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
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="js/fancybox/jquery.fancybox.css" rel="stylesheet" />
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

    <?php if($images){for($i=0;$i<count($images);$i++):?>
        <?php if($i>0):?><div style="display:none;"><?endif?>
        <a class="fancybox" rel="gal" href="img<?=$images[$i];?>"></a>
        <?php if($i>0):?></div><?endif?>
    <?php endfor;}?>
    <div id="name" style="display: none;"><?=$name?></div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery-1.11.0.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>
<script src="js/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        var itemId = <?=isset($id)?$id:0;?>;
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
            }else  if (e.keyCode == 68&& (e.ctrlKey|| e.metaKey)) {//d delete
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

    });

</script>
</body>
</html>
