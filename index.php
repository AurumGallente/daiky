<?php
/*
 * grabs titles and links of all posts
 * also gets html of all pages from http://www.daikynguyenvn.com/video-tieu-bieu.html
 */
header("Content-type: text/html; charset=utf-8");
require_once('db.php');
require_once('simple_html_dom.php');
$url = "http://www.daikynguyenvn.com/video-tieu-bieu.html/page/";
$db = Database::getInstance();
$firstPage = 1;
$lastPage = 45;
$file = 'result.json';
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

function quote ($arg){
  return Database::getInstance()->quote($arg);  
}
//for ($i = $firstPage; $i <= $lastPage; $i++) {
//    $currentUrl = $url . $i;
//    $html = file_get_html($currentUrl, false, $context);
//    $pageTitle = quote($html->find('title',0)->plaintext);
//    $postTitles = $html->find('h2.post-box-title');
//    foreach ($postTitles as $postTitle) {
//        $href = quote($postTitle->find('a',0)->href);
//        $title = quote($postTitle->plaintext);
//        $query = "INSERT INTO `posts` (`title`,`page`, `href`) VALUES ('$title',$i,'$href')";
//        $db->query($query);        
//        $meta= $html->find('meta');
//        $metaTags = array();
//        foreach($meta as $tag){
//            $metatag = array();
//            $metaTag['name'] = $tag->name;
//            $metaTag['content'] = $tag->content;
//            $metaTag['property'] = $tag->property;
//            array_push($metaTags, $metaTag);
//            $pageMetaTags = quote(json_encode($metaTags, JSON_UNESCAPED_UNICODE));
//        }
//        $pagehtml = quote($html);
//        $query = "INSERT INTO `pages` (`page_number`, `html`, `metatags`) VALUES ($i, '$pagehtml', '$pageMetaTags')";
//        $db->query($query);
//    }
//}
$query = "select id, href from posts where NOT is_parsed";
$posts = $db->query($query)->fetchAll();

foreach($posts as $post){
    $id = $post['id'];
    $href = $post['href'];
    $html = file_get_html($href, false, $context);
    $meta= $html->find('meta');
    $metaTags = array();
    foreach($meta as $tag){        
        $metatag = array();
        $metaTag['name'] = $tag->name;
        $metaTag['content'] = $tag->content;
        $metaTag['property'] = $tag->property;
        array_push($metaTags, $metaTag);
        
    } 
    $pageMetaTags = quote(json_encode($metaTags ,JSON_UNESCAPED_UNICODE));
    $pagehtml = quote($html);
    $tags = array();
    $tagArray = $html->find('.post-cat-tag a');
    foreach($tagArray as $tag){
        array_push($tags,$tag->plaintext);
    }
    $tags = quote(json_encode($tags, JSON_UNESCAPED_UNICODE));
    $img = $html->find('.single-post-thumb img', 0);    
    $img = isset($img) && @isset($img->src) ? $img->src : null;
    $query = "UPDATE posts SET html='$pagehtml', metatags='$pageMetaTags', is_parsed=1, tags='$tags', img='$img' WHERE id=$id";
    $db->query($query);
}

$query = "SELECT * FROM `posts` WHERE is_parsed";
$result = $db->query($query)->fetchAll();

$fp = fopen($file, 'w');
fwrite($fp, '[');
$posts = array();
foreach($result as $r){
    $r['metatags']=json_decode($r['metatags']);
    $r['tags']=json_decode($r['tags']);
    unset($r['id']);
    fwrite($fp, json_encode($r, JSON_UNESCAPED_UNICODE));
    fwrite($fp, ",");
    //array_push($posts, $r);
}
fwrite($fp,']');
fclose($fp);
//file_put_contents($file, json_encode($posts, JSON_UNESCAPED_UNICODE));


$db->close();