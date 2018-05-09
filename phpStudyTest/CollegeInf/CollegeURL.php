<?php
/**
 * Created by PhpStorm.
 * User: Stanley
 * Date: 2018/5/4
 * Time: 18:48
 */


 /**
  *本来用来利用搜索引擎从学校名获得更多信息，
  *但是时间不太够，先放着吧哈哈哈ㄟ( ▔, ▔ )ㄏ... 
  */
class CollegeURL
{
    public $libraryURL;
    public $eduPortalURL;

    private static $engine_Baidu = "www.baidu.com/s?wd=";
    private static $engine_Bing = "cn.bing.com/search?q=";
    private static $engine_Sougou = "www.sogou.com/web?query=";
    private static $engine_360 = "www.so.com/s?q=";

/**获得信息 */
    private function getCollegeInf($key)
    {

    }

    /**用来测试网页是否有效及是否需要VPN */
    private function judger($url)
    {

    }

    
}

/**爬取得到所有高校官网网址,从而获得简称，
 * 便于爬取、筛选教务系统、信息门户网址
 * 似乎请求出现问题，请尝试用其他方法如curl*/

class Hao123_Subpage
{
    private static $hao123_edu = "http://www.hao123.com/edu.html";

    private static $config = "//*[@id=\"bd\"]/div[2]/div/table/tbody/tr/td[2]/a";

    public $Url;
    public function _construct()
    {
        $html_string = file_get_contents(self::$hao123_edu);//似乎这样直接请求会被重定向，尝试其他方案
        $document = new DOMDocument(1.0);
        $document->loadHTML($html_string);

        $xpath = new DOMXPath($document);
        $nodeList = $xpath->query(self::$config);

        $i = 0;

        foreach ($nodeList as $index => $node) {
            $this->Url[$i] = $node->getAttribute('href');
            $i++;
        }

    }
}

//解析特定网页获得学校官网
/*构造函数参数请输入该特定网页，即Hao123_Subpage的Url */
class OfficialSite
{
    private static $config = "/html/body/div[3]/center/div/table/tbody/tr[2]/td/table/tbody/tr/td/p/a";

    public $Official_Site_Inf;

    public function _construct($url)
    {


        $html_string = file_get_contents($url);
        $document = new DOMDocument(1.0);
        $document->loadHTML($html_string);

        $xpath = new DOMXPath($document);
        $nodeList = $xpath->query(self::$config);

        $i = 0;

        foreach ($nodeList as $index => $node) {
            $site_inf = new OfficialSiteInf;

            $site_inf->Name = $node->textContent;
            $site_inf->Url = $node->getAttribute('href');
            $name_shortened=new Collegename_Shortened;
            $name_shortened->_construct($site_inf->Url);
        
            $site_inf->Name_Shortened=$name_shortened->Name_Shortened;
            $Official_Site_Inf[$i] = $site_inf;
            $i++;
        }


    }

}

class OfficialSiteInf
{
    public $Name;
    public $Url;
    public $Name_Shortened;
}

/**
 * 截取学校官网网址获得学校英语简称
 * 请在构造函数传入OfficialSiteInf对象 */
class Collegename_Shortened
{

    public $Name_Shortened;
    public function _construct($siteInf)
    {
        $str = $siteInf->Url;
        substr($str, strpos($str, '.') + 1); //截取第一个. 后面的内容
        substr($str, 0, strpos($str, '.')); //截取第一个. 前面的内容
        $this->Name_Shortened = $str;
    }
}
