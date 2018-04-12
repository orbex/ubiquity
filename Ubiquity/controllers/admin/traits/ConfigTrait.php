<?php

namespace Ubiquity\controllers\admin\traits;

use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\controllers\admin\utils\CodeUtils;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\UResponse;

/**
 *
 * @author jc
 * @property \Ajax\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 */
trait ConfigTrait{

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();

	abstract public function _getAdminFiles();

	abstract public function loadView($viewName, $pData = NULL, $asString = false);

	abstract public function config($hasHeader = true);
	
	abstract protected function showConfMessage($content, $type, $url, $responseElement, $data, $attributes = NULL);

	abstract protected function showSimpleMessage($content, $type, $icon = "info", $timeout = NULL, $staticName = null): HtmlMessage;

	public function formConfig($hasHeader = true) {
		global $config;
		if ($hasHeader === true){
			$this->getHeader ( "config" );
		}
		$this->_getAdminViewer ()->getConfigDataForm ( $config );
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getAdminFiles ()->getViewConfigForm () );
	}
	
	public function _config(){
		global $config;
		echo $this->_getAdminViewer ()->getConfigDataElement ( $config );
		echo $this->jquery->compile($this->view);
	}
	
	public function submitConfig($partial=true){
		$result=Startup::getConfig();
		$postValues=$_POST;
		if($partial!==true){
			$postValues["database-cache"]=isset($postValues["database-cache"]);
			$postValues["debug"]=isset($postValues["debug"]);
			$postValues["test"]=isset($postValues["test"]);
			$postValues["templateEngineOptions-cache"]=isset($postValues["templateEngineOptions-cache"]);
		}
		foreach ($postValues as $key=>$value){
			if(strpos($key, "-")===false){
				$result[$key]=$value;
			}else{
				list($k1,$k2)=explode("-", $key);
				if(!isset($result[$k1])){
					$result[$k1]=[];
				}
				$result[$k1][$k2]=$value;
			}
		}
		$content="<?php\nreturn ".UArray::asPhpArray($result,"array",1,true).";";
		if(CodeUtils::isValidCode($content)){
			if(Startup::saveConfig($content)){
				$msg=$this->showSimpleMessage("The configuration file has been successfully modified!", "positive","check square",null,"msgConfig");
			}else{
				$msg=$this->showSimpleMessage("Impossible to write the configuration file <b>{$fileName}</b>.", "negative","warning circle",null,"msgConfig");
			}
		}else{
			$msg=$this->showSimpleMessage("Your configuration contains errors.<br>The configuration file has not been saved.", "negative","warning circle",null,"msgConfig");
		}
		echo $msg;
		$this->_config();
	}
	
	public function _checkArray() {
		if (URequest::isPost()) {
			$result=[ ];
			UResponse::asJSON();
			$value=$_POST["_value"];
			try{
				$array=eval("return ".$value.";");
				$result["result"]=is_array($array);
			}catch(\ParseError $e){
				$result["result"]=false;
			}
			echo json_encode($result);
		}
	}
}