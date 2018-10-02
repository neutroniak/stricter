<?php

class BasicController
{
	private $entity;
	public $debug=false;
	public $ui;
	private $db;

	public function __construct() {
		$this->db = Stricter::getInstance()->getDefaultDatabase();
	}

	public function index() {
		//$this->view->addPlugin('ErpPager');

		$this->view->assign('_ui', $this->ui);

		if($_GET['deleted'])
			$this->view->assign('flash_message', 'LANG_DELETE_SUCCESSFUL');

		//$this->setUis();

		if($_GET['c'])
			$this->ui['count']=$_GET['c'];
		else if ($this->ui===NULL)
			$this->ui['count']=10;

		if($_GET['o'])
			$this->entity->order($_GET['o'], $_GET['ot']);
		else if($this->ui['order'])
			$this->entity->order($this->ui['order']['field'], $this->ui['order']['type']);
		else
			$this->entity->order($this->ui['fields'][0], 'ORDER_ASC');

		if($this->ui['keys'] && $this->params[0]) {
			$this->entity->where($this->ui['keys'][1], 'WHERE_EQ', $this->params[0]);
		}

		# TODO - Move all this stuff to a private method
		foreach($this->ui['fields'] as $k=>$v) {
			$getv=str_replace('.','_',$v);
			$getv_begin=$getv.'_begin';
			$getv_end=$getv.'_end';

			if(!$_GET[$getv] && !($_GET[$getv_begin] || $_GET[$getv_end]))
				continue;

			$thenode=$this->entity->retrieveNode($v);

			switch($thenode->getType()) {
				case "StringType":
					$this->entity->where($v, 'WHERE_ILIKE', $_GET[$getv]);
					break;
				case "NumericType":
					$this->entity->where($v, 'WHERE_EQ', $_GET[$getv]);
					break;
				case "BooleanType":
					if($_GET[$getv]=='true') {
						$this->view->assign('boolval', $_GET[$getv]);
						$_GET[$getv]='t';
						$this->entity->where($v, 'WHERE_EQ', $_GET[$getv]);
					} elseif($_GET[$getv]=='false') {
						$this->view->assign('boolval', $_GET[$getv]);
						$_GET[$getv]='f';
						$this->entity->where($v, 'WHERE_EQ', $_GET[$getv]);
					}
					break;
				case "DateType":
					$exb=explode('-',$_GET[$getv_begin]);
					$begin=sprintf("%s-%s-%s 00:00:00",$exb[2], date('n', strtotime($exb[1])), $exb[0]);
					if($_GET[$getv_begin] && $_GET[$getv_end]!="") {
						$exe=explode('-',$_GET[$getv_end]);
						$end=sprintf("%s-%s-%s 23:59:59",$exe[2], date('n', strtotime($exe[1])), $exe[0]);
					} elseif ($_GET[$getv_begin]) {
						$end=sprintf("%s-%s-%s 23:59:59",$exb[2], date('n', strtotime($exb[1])), $exb[0]);
					} 
					$this->entity->where($v, 'WHERE_BETWEEN', array($begin,$end));
					break;
			}
		}

		if(!$_GET['pg'])
			$_GET['pg']=0;

		if($this->ui['fields']) {
			$this->entity->
				paginate( $this->ui['count'], $_GET['pg'])->
				find();
			$this->view->assign('_ent', $this->entity);
		}

		$this->view->assign('optionsc', array(10=>10,15=>15,30=>30,60=>60,100=>100,200=>200));
		$this->view->assign('optionsbool', array(''=>null,'true'=>'On','false'=>'Off'));
		$this->view->assign('get', $_GET);
		$this->view->assign("uri", $_SERVER['REQUEST_URI']);
		$this->view->assign('id', $this->params[0]);
		$this->view->assign('_pager', $this->entity->getPaginationInfo());
		$this->view->assign('_nfields', count($this->ui['fields'])+3);

		if($this->db->error())
			$this->view->assign('flash_error', $this->db->error());
	}

	public function add() {

		//$this->setUis();

		$this->view->assign('_ui', $this->ui);
		$ptitle = $this->view->getTemplateVars('ptitle');
		if($ptitle)
			array_push($ptitle, LANG_ADD);
		$this->view->assign('ptitle', $ptitle);

		$this->view->assign('sv',true);

		$this->view->assign('ida',$this->params[0]);
		if($this->stricter->isPost() && $this->entity->validate()){
			if($n=$this->entity->insert()){
				if($this->params[0])
					$n=$this->params[0].'/'.$n;
				$this->stricter->redirect('/'.$this->stricter->getMdl().'/edit/'.$n.'/?added=true');
			} else {
				$this->view->assign('flash_error', $this->db->error());
			}
		} else if($this->stricter->isPost()) {
			if($this->debug)
				$this->entity->printErrors();
			$this->view->assign('flash_warning', 'LANG_FORM_ERRORS');
		}
	}

	public function setModel(&$ent){
		$this->entity=$ent;
	}

	public function edit() {
		//$this->setUis();

		$this->view->assign('_ui', $this->ui);

		$ptitle = $this->view->getTemplateVars('ptitle');
		array_push($ptitle, LANG_EDIT);
		$this->view->assign('ptitle',$ptitle);

		if($_GET['added'])
			$this->view->assign('flash_message', 'LANG_INSERT_SUCCESSFUL');

		if(count($this->ui['keys']==2))
			$this->view->assign("id", $this->params[0]);

		$this->view->assign("sv", true);

		if($this->params[1]){
			$id=$this->params[1];
		} else {
			$id=$this->params[0];
		}

		$this->entity->find($id);

		if($this->stricter->isPost()) {
			if($this->entity->validate()) {
				if($n=$this->entity->update())
					$this->view->assign('flash_message', 'LANG_UPDATE_SUCCESSFUL');
				else
					$this->view->assign('flash_error', $this->db->error());
			} else {
				if($this->debug)
					$this->entity->printErrors();
				$this->view->assign('flash_warning', 'LANG_FORM_ERRORS');
			}
		}
	}
}

?>
