<?php
/*面向对象的PHP爬虫：获得图书借阅历史 */
//先POST模拟登陆获得cookie,再用cookie GET得到指定借书历史
//参数sid password start_date end_date,示例：http://localhost/APIs/Library_Rented.php?sid=2016123456&password=123456&start_date=2017-06-01&end_date=2018-04-01

/*目前有几个问题*/
/*1. curl返回数据不完整，相关资料：https://blog.csdn.net/glovenone/article/details/72650133?locationNum=3&fps=1 */
/*2. 返回数据乱码，初步判断为chunked编码，但仍未解决 */
/*3. 爬虫部分由于无法获得完整、正确编码的HTML代码，所以暂无法进行 */

$origionaldata = new Data;
$data = new DoWithData();

$origionaldata->_Construct();
$origionaldata->Post();
//$data->Http_chunked_decode($origionaldata->Get()) ;
echo ($origionaldata->Get());
class Data
{
    private $Sid;
    private $Password;
    private $StartDate;
    private $EndDate;
    private $Cookie;

    public function _Construct()
    {
        $this->Sid = $_GET["sid"];
        $this->Password = $_GET["password"];
        $this->StartDate = $_GET["start_date"];
        $this->EndDate = $_GET["end_date"];

    }

    public function Post()
    {
        $url = 'http://202.197.232.4:8081/opac_two/include/login_app.jsp';

        $fields = array(
            'login_type' => '',
            'barcode' => $this->Sid,
            'password' => $this->Password,
            '_' => '',
        );
        $ch = curl_init($url); //初始化
        curl_setopt($ch, CURLOPT_HEADER, 1); //不返回header部分
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回字符串，而非直接输出
        $result = curl_exec($ch);
        curl_close($ch);

        list($header, $body) = explode("\r\n\r\n", $result, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches); //正则表达式匹配
        $this->Cookie = substr($matches[1][0], 1); //得到Cookie

    }

    public function Get()
    {
        $url =
        'http://202.197.232.4:8081/opac_two/reader/jieshulishi.jsp?library_id=%25C3%258B%25C3%25B9%25C3%2593%25C3%2590%25C2%25B7%25C3%2596%25C2%25B9%25C3%259D&fromdate=' . $this->StartDate . '&todate=' . $this->EndDate . '&b1=%BC%EC%CB%F7';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: */*',
            'Accept-Charset: UTF-8,*;q=0.5',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: zh-CN,zh;q=0.8',
            'Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'User-Agent: Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.95 Safari/537.11',
            'X-Requested-With: XMLHttpRequest',
        ));

        curl_setopt($ch, CURLOPT_COOKIE, $this->Cookie);

        //curl_setopt($ch, CURLOPT_COOKIE,'JSESSIONID=03C4DEBC99CEAD76FA7D8D5999103184');//测试用

        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}

class DoWithData
{
    private $Data;

    public function Http_chunked_decode($data) //chunked解码
    {
        $pos = 0;
        $temp = '';
        while ($pos < strlen($data)) {
            // chunk部分(不包含CRLF)的长度,即"chunk-size [ chunk-extension ]"
            $len = strpos($data, "/r/n", $pos) - $pos;
            // 截取"chunk-size [ chunk-extension ]"
            $str = substr($data, $pos, $len);
            // 移动游标
            $pos += $len + 2;
            // 按;分割,得到的数组中的第一个元素为chunk-size的十六进制字符串
            $arr = explode(';', $str, 2);
            // 将十六进制字符串转换为十进制数值
            $len = hexdec($arr[0]);
            // 截取chunk-data
            $temp .= substr($data, $pos, $len);
            // 移动游标
            $pos += $len + 2;
        }
        $this->Data = $temp;
    }

    public function GetData
{

}
}

class BookModel
{
    public $BookName;
    public $BookCode;
    public $Action;
    public $Date;
}
