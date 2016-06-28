<?php 

/**
* 比较二个目录php文件
* 	类名比较
* 	类变量名比较
* 	类方法比较
*/
class Compphpdir
{
	/**
	 * 目标目录
	 * @var string
	 */
	protected $dest_dir = '';

	/**
	 * 比较目录
	 * @var string
	 */
	protected $src_dir = '';

	/**
	 * 目标地图
	 * @var array
	 * 结构如下
	 * [
	 * 	'filename.php'=>[
	 * 						'classname'=>[
	 * 										'vars'=>[],
	 * 										'methods'=>['get','add']
	 * 									 ],
	 * 						...
	 * 					],
	 * 	 ...
	 * 	]
	 */
	protected $dest_map = array();

	/**
	 * 比较地图
	 * @var array
	 * 结构如下
	 * [
	 * 	'filename.php'=>[
	 * 						'classname'=>[
	 * 										'vars'=>[],
	 * 										'methods'=>['get','add']
	 * 									 ],
	 * 						...
	 * 					],
	 * 	 ...
	 * 	]
	 */
	protected $src_map = array();

	/**
	 * 差异地图
	 */
	protected $comp_map = array();

	function __construct($dest_dir='',$src_dir='')
	{
		if($dest_dir != '' && $src_dir != ''){
			$this->dest_dir = $dest_dir;
			$this->src_dir  = $src_dir;
		}
	}

	public function load($dest_dir='',$src_dir='')
	{
		if($dest_dir != '' && $src_dir != ''){
			$this->dest_dir = $dest_dir;
			$this->src_dir  = $src_dir;
			return true;
		}
		return false;
	}

	/**
	 * 函数处理
	 * @return [type] [description]
	 */
	public function proc()
	{
		$this->_read_dest_dir();
		$this->_read_src_dir();
		$this->_comp_dir();
		$this->_print_comp();
	}

	/**
	 * 读取目标地图
	 * @return [type] [description]
	 */
	private function _read_dest_dir()
	{
		if($this->dest_dir=='')return false;
		if(!is_dir($this->dest_dir))return false;

		$dd = opendir($this->dest_dir);
		while(($file = readdir($dd))!==false){
			if($file == '.' || $file == '..'){
				continue;
			}
			if(strpos($file, ".php") == false){
				continue;
			}
			$content = file_get_contents($this->dest_dir.'/'.$file);

			//类名
			preg_match("/[c|C]{1}lass ([a-zA-Z0-9_]*)/", $content, $class_m);
			$class_name = $class_m[1];
			//变量名
			preg_match_all("/[public|private|protected]{1} [\$]{0,}(\w*) {0,};/", $content, $vars_m);
			foreach ($vars_m[1] as $vars) {
				$this->dest_map[$file][$class_name]['vars'][] = $vars;
			}
			//函数名称
			preg_match_all("/function (\w*)\(/", $content, $function_m);
			foreach ($function_m[1] as $methods) {
				$this->dest_map[$file][$class_name]['methods'][] = $methods;
			}
		}
		closedir($dd);
	}

	/**
	 * 读取比较地图
	 * @return [type] [description]
	 */
	private function _read_src_dir()
	{
		if($this->src_dir=='')return false;
		if(!is_dir($this->src_dir))return false;

		$dd = opendir($this->src_dir);
		while(($file = readdir($dd))!==false){
			if($file == '.' || $file == '..'){
				continue;
			}
			if(strpos($file, ".php") == false){
				continue;
			}
			$content = file_get_contents($this->src_dir.'/'.$file);

			//类名
			preg_match("/[c|C]{1}lass ([a-zA-Z0-9_]*)/", $content, $class_m);
			$class_name = $class_m[1];
			//变量名
			preg_match_all("/[public|private|protected]{1} [\$]{0,}(\w*) {0,};/", $content, $vars_m);
			foreach ($vars_m[1] as $vars) {
				$this->src_map[$file][$class_name]['vars'][] = $vars;
			}
			//函数名称
			preg_match_all("/function (\w*)\(/", $content, $function_m);
			foreach ($function_m[1] as $methods) {
				$this->src_map[$file][$class_name]['methods'][] = $methods;
			}
		}
		closedir($dd);
	}


	/**
	 * 目标地图，比较地图 比较差异
	 * @return [type] [description]
	 */
	private function _comp_dir()
	{
		$dest_map = $this->dest_map;
		foreach ($this->src_map as $file => $map) {
			if(!isset($dest_map[$file])) {
				//文件不存在
				$this->comp_map[$file] = $map;
				continue;
			}
			foreach ($map as $classname => $_v) {
				if(!isset($this->dest_map[$file][$classname])){
					//类不存在
					$this->comp_map[$file][$classname] = $_v;
					continue;
				}
				if(isset($_v['vars'])){
					foreach ($_v['vars'] as $_var) {
						if(!in_array($_var,$this->dest_map[$file][$classname]['vars'])){
							//vars 不存在
							$this->comp_map[$file][$classname]['vars'][] = $_var;
						}
					}
				}
				if (isset($_v['methods'])) {
					foreach ($_v['methods'] as $_method) {
						if(!in_array($_method,$this->dest_map[$file][$classname]['methods'])){
							//menthod 不存在
							$this->comp_map[$file][$classname]['methods'][] = $_method;
						}
					}
				}
			}
		}
	}

	/**
	 * 差异打印
	 */
	private function _print_comp()
	{
		ob_start();
		foreach ($this->comp_map as $file => $map) {
			echo "file:$file\n";
			foreach ($map as $classname => $class) {
				if(isset($class['vars']) && !empty($class['vars'])){
					foreach ($class['vars'] as $_not_find_vars) {
						echo "vars:$_not_find_vars"."\n";
					}
				}
				if(isset($class['methods']) && !empty($class['methods'])){
					foreach ($class['methods'] as $_not_find_methods) {
						echo "methods:$_not_find_methods"."\n";
					}
				}
			}
		}
		$ob_content = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__file__).'/comp.txt', $ob_content);
	}
}
	

?>

