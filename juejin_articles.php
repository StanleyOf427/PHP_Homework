<?php
/*面向对象的爬虫：获得掘金网最新文章 */
//参数type,num,示例：http://localhost/APIs/juejin_articles.php?type=ai&num=5
//type参数包含：frontend（前端） backend（后端） ios（IOS） android（安卓） ai（人工智能） freebie（工具） article（文章）


/*同样的问题，curl返回HTML不全………… */

$type=$_GET["type"];
$num=$_GET["num"];

$Url = "https://juejin.im/welcome/" . $type;

$Html = GetHTML($Url);
$htmlnf = new HTMLInf;
$results[]=new ArticleModel;

$results=$htmlnf->_Construct($type, $Html);

$jsonencode = json_encode($results); //序列化为JSON传回
echo $jsonencode; 

function GetHTML($url)
{

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, array('Expect:'));
   
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

class HTMLInf
{
    private $Type;
    private $Author;
    private $Title;
    private $Href;
    private $Content;

    private function FindNode_ArticleList($html) //在文章列表抓取标题、作者及文章详情地址

    {

        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $nodeList = $xpath->query('//*[@id="juejin"]/div[2]/main/div/div[1]/ul/[@class="item"]'); //定位到首页文章列表项

        $result = [];
        $i = 0;
        foreach ($nodeList as $node) {
            $node2 = $node->nodeValue;
            $dom2 = new DOMDocument();
            $dom2->loadHTML($node2);
            $xpath2 = new DOMXPath($dom);
            $authornode = $xpath2->query('//div/div/a/div/div/div[1]/ul/li[1]/div/a'); //定位到作者
            $titlenode = $xpath2->query('//div/div/a/div/div/div[2]/a'); //定位到标题
            $articles[i] = new ArticleModel;

            $this->Author = $authornode->nodeValue;
            $this->Title = $titlenode->nodeValue;
            $this->Href = $srcList[] = $node->attributes->getNamedItem('href')->nodeValue; //文章内容地址
            $contenturl = "https://juejin.im/" . $href;
            FindNode_Article(GetHTML($contenturl));

            $articles->_Construct($this->Type, $this->Title, $this->Author, $this->Content);
            $i++;

            if($i>$num)
            return $articles;
        }

        return $articles;
    }

    private function FindNode_Article($html) //抓取文章详情

    {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $node = $xpath->query('//*[@id="juejin"]/div[2]/main/div[1]/div[1]/article/div[5]'); //定位到文章内容
        $this->Content = $node->nodeValue;

    }
    public function _Construct($type, $html)
    {
        $this->Type = $type;
        $result = $this->FindNode_ArticleList($html);
        return $result;
    }

}

class ArticleModel
{
    public $Type;
    public $Title;
    public $Author;
    public $Content;
    public function _Construct($type, $title, $author, $content)
    {
        $this->Type = $type;
        $this->Title = $title;
        $this->Author = $author;
        $this->Content = $content;
    }

}

?>