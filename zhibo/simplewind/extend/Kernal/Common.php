<?php

namespace Kernal;
use \PHPExcel;
use \PHPExcel_Style_Alignment;
use \PHPExcel_Writer_Excel5;
class Common
{
    /**
     * 导出Excel功能
     * @data array array 要导出的数据
     * @table_header array 表头信息
     * $table_name   导出的Excel 表格的表名
     * $field_width  array   表格的宽度
     **/
    public $excel;
    public $headers;
    public function __construct()
    {
        $this->excel = new PHPExcel();

        $header = range('A', 'Z');
        $header_besides = range('A', 'Z');
        $header_beside = array_map(function ($n) {
            return 'A' . $n;
        }, $header_besides);

        $this->headers = array_merge($header, $header_beside);
    }

    public function Excel($data, $table_header, $table_name = 'Excel导出表', $field_width = [])
    {

        /**
         * 数据验证
        **/
        if (!is_array($data) || empty($data)) {
            return 'error';
        }
        if (!is_array($table_header) || empty($table_header)) {
            return 'error';
        }

        $total = count($table_header);
        $count = count($data);
        if (!$total) {
            return 'error';
        }


        $letter = array_slice($this->headers,0,$total);



        //设置表头水平居中对齐
        foreach ($letter as $value) {
            $this->excel->getActiveSheet()->getStyle($value)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }


        if (!empty($field_width)) {
            $this->field_width($field_width);
        }



        //样式
        $styleArray1 = array(
            'font'      => array(
                'bold'  => true,
                'size'  => 12,
                'color' => array(
                    'argb' => '00000000',
                ),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );


        // 将A1单元格设置为加粗，居中
        for ($i = 0; $i < count($table_header); $i++) {
            $this->excel->getActiveSheet()->setCellValue("$letter[$i]1", "$table_header[$i]");
            $this->excel->getActiveSheet()->getStyle("$letter[$i]1")->applyFromArray($styleArray1);
        }

        //填充表格信息
        for ($i = 2; $i <= $count + 1; $i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $this->excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $j++;
            }
        }

        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($this->excel);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="' . $table_name . '.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }


    public function field_width($field_width)
    {
        //设置表格宽度

        foreach($field_width as $key =>$value) {
            $value = intval($value) ? $value : 15 ;
            $this->excel->getActiveSheet()->getColumnDimension($key)->setWidth($value);
        }
    }
}
?>