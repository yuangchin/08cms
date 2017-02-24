<?php
    // 数据导出成Excel文件,暂不支持中文文件名称。尽量生成UTF-8编码的excel（包括从数据库取出来的数据转成UTF-8）,测试时某些WPS版本只支持UTF-8的编码
	/* @example 
		$xls = new cls_XmlExcelExport($charset);           //默认UTF-8编码
		$xls->generateXMLHeader('zhaobiao_'.date('Y-md-His',$timestamp));  //excel文件名
		$xls->worksheetStart('招标信息');
		$xls->setTableHeader($_value);  //表字段名
		$xls->setTableRows($data); //内容字段
		$xls->worksheetEnd();
		$xls->generateXMLFoot();
	*/

    /**
    * 导出 XML格式的 Excel 数据
	*
    */
!defined('M_COM') && exit('No Permisson');
    class cls_XmlExcelExport
    {

        /**
         * 文档头标签
         *
         * @var string
         */
        private $header = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";

        /**
         * 文档尾标签
         *
         * @var string
         */
        private $footer = "</Workbook>";

        /**
         * 内容编码
         * @var string
         */
        private $sEncoding;

        /**
         * 是否转换特定字段值的类型
         *
         * @var boolean
         */
        private $bConvertTypes;
       
        /**
         * 生成的Excel内工作簿的个数
         *
         * @var int
         */
        private $dWorksheetCount = 0;

        /**
         * 构造函数
         *
         * 使用类型转换时要确保:页码和邮编号以'0'开头
         *
         * @param string $sEncoding 内容编码
         * @param boolean $bConvertTypes 是否转换特定字段值的类型
         */
        function __construct($sEncoding = 'UTF-8', $bConvertTypes = false)
        {
            $this->bConvertTypes = $bConvertTypes;
            $this->sEncoding = $sEncoding;
        }

        /**
         * 返回工作簿标题,最大 字符数为 31
         *
         * @param string $title 工作簿标题
         * @return string
         */
        function getWorksheetTitle($title = 'Table1')
        {
            $title = preg_replace("/[\\\|:|\/|\?|\*|\[|\]]/", "", empty($title) ? 'Table' . ($this->dWorksheetCount + 1) : $title);
            return substr($title, 0, 31);
        }
       
        /**
         * 向客户端发送Excel头信息
         *
         * @param string $filename 文件名称,不能是中文
         */
        function generateXMLHeader($filename){
           
            $filename = preg_replace('/[^aA-zZ0-9\_\-]/', '', $filename);
            $filename = urlencode($filename);
           
            // 中文名称使用urlencode编码后在IE中打开能保存成中文名称的文件,但是在FF上却是乱码
            header("Pragma: public");   header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Type: application/vnd.ms-excel; charset={$this->sEncoding}");
            header("Content-Transfer-Encoding: binary");
            header("Content-Disposition: attachment; filename={$filename}.xls");
           
            echo stripslashes(sprintf($this->header, $this->sEncoding));
        }
       
        /**
         * 向客户端发送Excel结束标签
         *
         * @param string $filename 文件名称,不能是中文
         */
        function generateXMLFoot(){
            echo $this->footer;
        }
       
        /**
         * 开启工作簿
         *
         * @param string $title
         */
        function worksheetStart($title){
            $this->dWorksheetCount ++;
            echo "\n<Worksheet ss:Name=\"" . $this->getWorksheetTitle($title) . "\">\n<Table>\n";
        }
       
        /**
         * 结束工作簿
         */
        function worksheetEnd(){
            echo "</Table>\n</Worksheet>\n";
        }
       
        /**
         * 设置表头信息
         *
         * @param array $header
         */
        function setTableHeader($header=array()){
            echo $this->_parseRow($header);
        }
       
        /**
         * 设置表内行记录数据
         *
         * @param array $rows 多行记录
         */
        function setTableRows($rows=array()){
            foreach ($rows as $row) echo $this->_parseRow($row);
        }
       
        /**
         * 将传人的单行记录数组转换成 xml 标签形式
         *
         * @param array $array 单行记录数组
         */
        private function _parseRow($row=array())
        {
            $cells = "";
            foreach ($row as $k => $v){
                $type = 'String';
                if ($this->bConvertTypes === true && is_numeric($v))
                    $type = 'Number';
                   
                $v = @htmlentities($v, ENT_COMPAT, $this->sEncoding);
                $cells .= "<Cell><Data ss:Type=\"$type\">" . $v . "</Data></Cell>\n";
            }
            return "<Row>\n" . $cells . "</Row>\n";
        }

    }