<?php
/**
 * 导出excel功能
 * @author xiyu
 *
 */
class Excel {
	
	/**
	 * 下载excel
	 * @param array $title	表头
	 * @param array $data	数据
	 * @param array $config	其他配置
	 */
	public function downloadExcel($title,$data,$config=array()){
		$this->checkData($title,$data);
		$this->beforeWrite();
		$this->write($title,$data,$config);
		$this->output($config);
	}
	
	/**
	 * 检测数据是否合法
	 */
	protected function checkData($title,$data){
		if(!is_array($title) || !is_array($data))
			return '参数错误,需要数组';
	}

    protected $objPHPExcel;

    protected function beforeWrite(){
		spl_autoload_unregister(array('Base', 'autoload'));
		require_once BASE_DATA_PATH.'/excel/PHPExcel.php';
		require_once BASE_DATA_PATH.'/excel/PHPExcel/Writer/Excel2007.php'; // 用于 excel-2007 格式
		spl_autoload_register(array('Base', 'autoload'));
		$this->objPHPExcel = new PHPExcel();
		$this->objPHPExcel->setActiveSheetIndex(0);
	}


    protected function write($title,$data,$config){
		array_unshift($data,$title);
		$data=array_values($data);
		foreach ($data as $y=>$row){
			$row=array_values($row);
			foreach ($row as $x=>$vlaue){
				$location=chr($x+65).($y+1);
				$this->objPHPExcel->getActiveSheet()->setCellValue($location,$vlaue);
			}
		}
	}

    protected function output($config=array()){
		$file_name=isset($config['title'])?$config['title']:uniqid('excel_');
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");;
		header('Content-Disposition:inline;filename="'.$file_name.'.xls"');
		header("Content-Transfer-Encoding:binary");
		header("Pragma: no-cache");
		$objWriter = new PHPExcel_Writer_Excel2007($this->objPHPExcel);
		$objWriter->save('php://output');
	}
	
}

?>