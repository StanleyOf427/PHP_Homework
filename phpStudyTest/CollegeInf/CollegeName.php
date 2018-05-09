<?php
/**
 * 参考：https://segmentfault.com/q/1010000009701301
 * 爬取使用正方系统的学校并存入数据库
 * 从xls文件读取全国所有高校并插入数据库
 * 爬取所有高校官网网址及英语简称
 */

require_once './PHPExcel/PHPExcel.php';
require_once './PHPExcel/PHPExcel/IOFactory.php';


class CollegeName
{
    private static $zfsoft = "http://www.zfsoft.com/type_al/0400000103.html";
    //XPath路径
    private static $config_zfsoft = "//*[@id=\"content\"]/div[1]/ul/li/text()";

    private static $file_path = "./AllCollege_2017_06_Name.xls";

    public $College;

    public function _construct_zfsoft()
    {
        //不管用什么方法，都应该存到字符串中再用DOM
        $html_string = file_get_contents(self::$zfsoft);
        $document = new DOMDocument(1.0);
        $document->loadHTML($html_string);

        $xpath = new DOMXPath($document);
        $nodeList = $xpath->query(self::$config_zfsoft);

        $i = 0;

        foreach ($nodeList as $index => $node) {
            $this->College[$i] = $node->textContent;
            $i++;
        }

    }

    public function _construct_all()
    {
        if (!file_exists(self::$file_path)) {
            die('no file!');
        }
        $ext = strtolower(pathinfo(self::$file_path, PATHINFO_EXTENSION));
        if ($ext == 'xlsx') {
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load(self::$file_path, 'utf-8');
        } elseif ($ext == 'xls') {
            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load(self::$file_path, 'utf-8');
        }
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
        $num = 0;
        for ($j = 0; $j <= $highestRow; $j++) {
            $str = '';
            for ($k = 'A'; $k <= $highestColumn; $k++) {
                $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue() . '\\'; //读取单元格
            }
            $strs = explode("\\", $str);
            $this->College[$num] = $strs[0];
            $num++;
        }

    }

    

}


