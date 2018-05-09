<?php
/**
 * Created in Visual Studio Code
 * 2018-05-07 by StanleyOf427
 * 用于测试封装的类并Debug
 *  */

/**已知问题：xls文件读取很慢，可能是PHPExcel不适合读取大量数据
 *
 */

require_once './DBManager/DataBase.php';
require_once './CollegeInf/CollegeName.php';
require_once './CollegeInf/CollegeURL.php';

require_once './PHPExcel/PHPExcel.php';
require_once './PHPExcel/PHPExcel/IOFactory.php';

//region 简单测试建库插数据

//region 建库
$college_db=CreateDB();

//endregion

//region 将所有高校名放入默认创建的数据库中college表
echo ("\nreading all college name...\n");

SaveAllCollege($college_db);

//endregion

//region 获得使用正方教务系统的学校并存入数据库
echo ("\nfind colleges using zf_soft\n");

GetCollegeUsing_zfsoft($college_db);

//endregion

//region 获得学校简称并存入数据库  


echo ("\nsearching college official site and shortened name... please wait...\n\n");
//GetOfficialSite($college_db);
//$site_inf_list=GetOfficialSite($college_db);

//endregion

$college_db->CloseConn();

echo ("\n\tFinished!");

//endregion

//region 测试用函数，虽然并没有用,放这里吧，嗯（￣。。￣）
//建立数据库
function CreateDB()
{

    $columnlist = [];

    $tname = "college";
    $column = new Column;
    $column->_construct4('id', 'int', true, false);
    $columnlist[0] = $column;
    $column = new Column;
    $column->_construct('name', 'char (30)');
    $columnlist[1] = $column;
    $column = new Column;
    $column->_construct('name_shortened', 'char (10)');
    $columnlist[2] = $column;
    $column = new Column;
    $column->_construct('is_zfsoft', 'bool');
    $columnlist[3] = $column;
    $column = new Column;
    $column->_construct('officialsite_url', 'char (100)');
    $columnlist[4] = $column;
    $column = new Column;
    $column->_construct('portal_url', 'char (100)');
    $columnlist[5] = $column;
    $column = new Column;
    $column->_construct('library_url', 'char (100)');
    $columnlist[6] = $column;

    $college_db = new DataBase;
    $college_db->_construct();
    $college_db->GetConn();

    if (!$college_db->CreateDB()) {
        echo ("\ncan't create new database，maybe have existed，deleting...\n");
        $college_db->DeleteDB();
        if ($college_db->CreateDB()) {
            echo ("\ncreating finished！\n");
        }

    } else {
        echo ("\ncreating finished！\n");
    }

    $college_db->CreatTable($tname, $columnlist);
    return $college_db;
}

//获得所有高校名并放入数据库
function SaveAllCollege($college_db)
{
    $i = 0;
    $rowlist = [];
    $names = new CollegeName;
    $names->_construct_all();
    $college_namelist = $names->College;

    foreach ($college_namelist as $name_in_list) {
        $row = new Row;
        $row->_construct('name', $name_in_list); //学校名称
        $rowlist[0] = $row;
        $row = new Row;
        $row->_construct('id', $i); //主键
        $rowlist[1] = $row;
        $row = new Row;
        $row->_construct('is_zfsoft', false); //默认非正方教务系统
        $rowlist[2] = $row;
        $college_db->AddData('college', $rowlist);
        $i++;
    }
}

//获取使用正方教务系统的学校名单
function GetCollegeUsing_zfsoft($college_db)
{
    $names = new CollegeName;
    $names->_construct_zfsoft();
    $college_namelist = $names->College; //获得使用正方系统的学校名数组
    $i = 0;
    $rowlist = []; //用来存放查询结果

    foreach ($college_namelist as $name_in_list) {
        $row = new Row;
        $row->RowName = "name";
        $row->Value = $name_in_list;
        $college_db->Search($row);
        $resultlist = $college_db->Result;
        foreach ($resultlist as $result) {
            $row_key = new Row;
            $row_key->RowName = "id";
            $row_key->Value = $result["id"];
            $rowlist_key[0] = $row_key;

            $row_change = new Row;
            $row_change->RowName = "is_zfsoft";
            $row_change->Value = true;
            $rowlist_change[0] = $row_change;

            $college_db->Update($tname, $rowlist_change, $rowlist_key);
        }

        $i++;
    }

}

//获得学校官网和简称
function GetOfficialSite($college_db)
{
    $subpage_url = new Hao123_Subpage;
    $subpage_url->_construct();
    $site_inf_list = array();
    $i = 0;
    foreach ($subpage_url->Url as $url) {

        $officialsite = new OfficialSite;
        $officialsite->_construct($url);
        $site_inf_list[$i] = $officialsite->Official_Site_Inf;
        $i++;
        sleep(3); //作为一个负责任的爬虫，这里不要忽略(→_→)
    }

    foreach ($site_inf_list as $site_inf) {
        $row = new Row;
        $row->RowName = "name";
        $row->Value = $site_inf->Name;
        $college_db->Search($row);
        $resultlist = $college_db->Result;
        foreach ($resultlist as $result) {
            $row_key = new Row;
            $row_key->RowName = "id";
            $row_key->Value = $result["id"];
            $rowlist_key[0] = $row_key;

            $row_change = new Row;
            $row_change->RowName = "name_shortened";
            $row_change->Value = $site_inf->Name_Shortened;
            $rowlist_change[0] = $row_change;
            $row_change = new Row;
            $row_change->RowName = "officialsite_url";
            $row_change->Value = $site_inf->Url;

            $college_db->Update($tname, $rowlist_change, $rowlist_key);
        }

        $i++;
    }
}

//endregion





//region 多线程操作测试 还没写≡(▔﹏▔)≡，以下是参考源码

//class test_thread_run extends Thread
//{
//    public $url;
//    public $data;
//
//    public function __construct($url)
//    {
//        $this->url = $url;
//    }
//
//    public function run()
//    {
//        if(($url = $this->url))
//        {
//            $this->data = model_http_curl_get($url);
//        }
//    }
//}
//
//function model_thread_result_get($urls_array)
//{
//    foreach ($urls_array as $key => $value)
//    {
//        $thread_array[$key] = new test_thread_run($value["url"]);
//        $thread_array[$key]->start();
//    }
//
//    foreach ($thread_array as $thread_array_key => $thread_array_value)
//    {
//        while($thread_array[$thread_array_key]->isRunning())
//        {
//            usleep(10);
//        }
//        if($thread_array[$thread_array_key]->join())
//        {
//            $variable_data[$thread_array_key] = $thread_array[$thread_array_key]->data;
//        }
//    }
//    return $variable_data;
//}
//
//function model_http_curl_get($url,$userAgent="")
//{
//    $userAgent = $userAgent ? $userAgent : 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2)';
//    $curl = curl_init();
//    curl_setopt($curl, CURLOPT_URL, $url);
//    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
//    curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
//    $result = curl_exec($curl);
//    curl_close($curl);
//    return $result;
//}
//
//for ($i=0; $i < 100; $i++)
//{
//    $urls_array[] = array("name" => "baidu", "url" => "http://www.baidu.com/s?wd=".mt_rand(10000,20000));
//}
//
//$t = microtime(true);
//$result = model_thread_result_get($urls_array);
//$e = microtime(true);
//echo "多线程：".($e-$t)."\n";
//
//$t = microtime(true);
//foreach ($urls_array as $key => $value)
//{
//    $result_new[$key] = model_http_curl_get($value["url"]);
//}
//$e = microtime(true);
//echo "For循环：".($e-$t)."\n";

//$config = array(
////    'name' => "neirong",
////    'selector' => "//*[@id=\"post\"]/div[2]",
////    'required' => true,
////);
//
//$config = array(
//    'name' => "neirong",
//    'selector' => "//*[@id=\"content\"]/div[1]/ul/li/text()",
//    'required' => true,
//);
//
//
//
////$url = "http://news.ci123.com/article/106582.html";
////http://www.zfsoft.com/type_al/0400000103.html
//$url="http://www.zfsoft.com/type_al/0400000103.html";
//
//
//$html_string = file_get_contents($url);
//$document = new DOMDocument(1.0);
//$document->loadHTML($html_string);
//
//$xpath = new DOMXPath($document);
//$nodeList = $xpath->query($config['selector']);
//
//foreach ($nodeList as $index => $node){
//    echo $node->textContent; //不包含HTML标签
//    echo "<br/>";
//  //  echo $node->ownerDocument->saveHTML($node); //包含HTML标签
//}

//endregion
