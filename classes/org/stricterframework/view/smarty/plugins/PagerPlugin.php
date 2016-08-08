<?php

class PagerPlugin extends BasicPlugin
{
	public $smarty;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin("function", "pager", array(&$this,"pager") );
		$this->addAttribute( 'getvars' );
	}

	function pager($params, &$smarty)
	{
		parent::init($params, $objvar);

		$pager=$smarty->getTemplateVars('_pager');

		$previous=$pager['current']-2;
		$last=$pager['length']-1;
		
		if($pager['count']<3)
			return "";

		if($params['getvars']){
			$a_append=explode(',',$params['getvars']);
			foreach($a_append as $ka=>$va)
				$gets.='&'.$va.'='.$_GET[$va];
		}

		$str = "<div class='str_pager_container'><div class='str_pager_subcontainer'>";
		$str.="<span class='str_pager str_pager_first'><a href='".$pager['url']."?pg=0".$gets."'>first</a></span>";
		$str.="<span class='str_pager str_pager_previous'><a href='".$pager['url']."?pg=".$previous.$gets."'>previuos</a></span>";
		for($i=1; $i<$pager['count']; $i++) {
			$ii=$i-1;
			$i==$pager['current'] ? $css='current' : $css='number';
			$str.="<span class='str_pager str_pager_".$css."'><a href='".$pager['url']."?pg=".$ii.$gets."'>".$i."</a></span>";
		}
		if($previous+1 == $last)
			$next = $last; 
		else
			$next=$pager['current'];
		$str.="<span class='str_pager str_pager_next'><a href='".$pager['url']."?pg=".$next.$gets."'>next</a></span>";
		$str.="<span class='str_pager str_pager_last'><a href='".$pager['url']."?pg=".$last.$gets."'>last</a></span>";
		$str.="</div></div>";

		return $str;
	}
}

?>
