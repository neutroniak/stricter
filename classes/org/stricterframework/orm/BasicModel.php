<?php

include_once("org/stricterframework/orm/types/BasicType.php");

class BasicModel
{
	const RELATION_ROOT=0;
	const RELATION_HAS=1;
	const RELATION_ONE=2;
	const RELATION_MANY=3;

	private $_db;
	private $_name;
	private $_alias;
	private $_numberOfErrors=0;
	private $_primaryKey;
	private $_uniqueKeys = array();
	private $_validateOnly = array();
	private $_where;
	private $_limit;
	private $_order;
	private $_fk;
	private $_fknm;
	private $_relation;
	private $_joins;
	private $_list=array();
	private $_paginationInfo=array();
	private $_pageCurrent;
	private $_pageMax;

	public function __construct($alias){

		$this->_alias=$alias;

		$this->init();

		foreach($this as $k=>$v) {
			if($v instanceof BasicType) {
				$hash='_'.sha1($this->_name.'.'.$v->getName().'.'.$this->_alias);
				$v->setHash($hash);
				if($_POST[$hash] || $_FILES[$hash])
					$v->filterPost($_POST[$hash]);
			}
		}
	}

	public function find($id=null){
		$db=Stricter::getInstance()->inject( Stricter::getInstance()->getDefaultDatabaseId() );

		if($db==null)
			return;

		$classname = $this->_name;
		$classalias = '_'.$this->_alias;
		$a_pk = $this->_primaryKey; # TODO - composite needed
		$where = null;

		$from = ' FROM '.$classname.' '.str_replace('.','',$classalias);

		if($id) {
			$where=" WHERE ".$a_pk[0]->getName().'='.$id;
			$finalsql = "SELECT * ".$from.$where.$this->_order.$this->_limit;
			$q=$db->query($finalsql);
			$r=$db->fetch($q, DatabaseInterface::STRICTER_DB_SQL_ASSOC);
			foreach($this as $k=>$v) {
				if($v instanceof BasicType )  {
					if($v===$this->_fk && $this->_fk!=null) # we cannot reset the _fk, a.k.a. joined field from parent object
						continue;
					$fieldname=$v->getName();
					$fieldhash=$v->getHash();
					if($q)
						$this->$k->setValue( $r[$fieldname] ); //read from db

					if( isset($_POST[$fieldhash]) || isset($_FILES[$fieldhash]) ) {
						if($_POST[$fieldhash]=="")
							unset($_POST[$fieldhash]);
						$v->filterPost($_POST[$fieldhash]); //is dirty
					}
				} else if ($v instanceof BasicModel) {
					if($v->_fk->getValue()!=NULL && $v->getRelation()==RELATION_HAS){
						$v->find($v->_fk->getValue());
					} else if($v->getRelation()==RELATION_MANY) {
						$pk=$this->getPrimaryKey();
						$v->where($v->_fknm, WHERE_EQ, $pk[0]->getValue());
						$v->find();
					}
				}
			}
		} else {
			if($this->_pageMax!==null && $this->_pageCurrent!==null) {
				$totalCountSql = 'SELECT _'.$this->_alias.'.* '.$from.$this->_joins.$this->_where;
				$this->_paginate($totalCountSql);
			}

			$finalsql = 'SELECT _'.$this->_alias.'.*'.$from.$this->_joins.$this->_where.$this->_order.$this->_limit;
			$q = $db->query($finalsql);
			$n = $db->numrows($q);
			$ser = serialize($this);
			for($i=0;$i<$n;$i++)
				array_push($this->_list, unserialize($ser));
			$i=0;
			while( $r=$db->fetch($q, DatabaseInterface::STRICTER_DB_SQL_ASSOC) ) {
				foreach($this->_list[$i] as $k=>$v) {
					if($v instanceof BasicType) {
						$fieldname=$v->getName();
						$fieldhash=$v->getHash();

						$v->setValue( $r[$fieldname] ); //read from db

						if( isset($_POST[$fieldhash]) || isset($_FILES[$fieldhash]) ) {
							if($_POST[$fieldhash]=="")
								unset($_POST[$fieldhash]);
							$v->filterPost($_POST[$fieldhash]); //is dirty
						}
					} else if ($v instanceof BasicModel) {
						if($v->_fk->getValue()!=NULL && $v->getRelation()==RELATION_HAS){
							$v->find($v->_fk->getValue());
						} else if($v->getRelation()==RELATION_MANY){
							$pk=$this->getPrimaryKey();
							if($pk[0]->getValue()!=NULL) {
								$pkval=$pk[0]->getValue();
							} else {
								$pk=$this->_list[$i]->getPrimaryKey();
								$pkval=$pk[0]->getValue();
							}
							$v->where($v->_fknm, WHERE_EQ, $pkval);
							$v->find();
						}
					}
				}
				$i++;
			}
		}
	}

	public function many($alias, $obj, $fk) {
		$ak=$this->getPrimaryKey();
		$pk=$ak[0];
		$pknm=$pk->getName();
		$this->$alias = new $obj("_".$this->getAlias()."_".$alias);
		$this->$alias->setFk($this->$alias->$fk);
		$this->$alias->setFkNm($fk);
		$this->$alias->_relation=RELATION_MANY;
		return $this->$alias;
	}

	public function has($alias, $obj, $field) {
		$this->$alias = new $obj($this->getAlias()."_".$alias);
		$fk=$this->$alias->getPrimaryKey();
		$this->$alias->_relation=RELATION_HAS;
		$fknm=$fk[0]->getName();
		$this->$alias->setFk($this->$field);
		$this->$alias->setFkNm($field);
		$as=$this->$alias->getAlias();
		$this->$alias->_joins=" INNER JOIN ".$this->$alias->getName()." AS _".$as." ON _".$as.'.'.$fknm.'=_'.$this->getAlias().'.'.$this->$field->getName()." ";
		return $this->$alias;
	}

	public function one($alias, $obj, $field) {

	}

	public function order($fields, $by) {
		$vals="";
		if(!strstr($fields,'.')) {
			$vals=$this->$fields->getName();
		} else {
			$ex=explode('.',$fields);
			$tmpobj;
			foreach($ex as $k=>$v) {
				if($tmpobj==NULL)
					$tmpobj=$this->$v;
				else
					$tmpobj=$tmpobj->$v;

				if($tmpobj instanceof BasicModel) {
					if(!strstr($this->_joins, $tmpobj->_joins))
						$this->_joins .= sprintf(' %s ', $tmpobj->_joins);
				} elseif($tmpobj instanceof BasicType) {
					$vals=sprintf(' _%s.%s ', $lastobj->getAlias(), $tmpobj->getName());
				}

				$lastobj=$tmpobj;
			}
		}
		if($this->_order=="")
			$this->_order=" ORDER BY ".$vals;
		else
			$this->_order.=', '.$vals;
		switch($by) {
			case ORDER_ASC:
				$this->_order.=" ASC ";
			break;
			case ORDER_DESC:
				$this->_order.=" DESC ";
			break;
			default:
				$this->_order.=" ASC ";
			break;
		}
		return $this;
	}

	public function where($fields, $criteria, $value) {
		if(trim($fields)=="") return;

		$vals="";
		if(!strstr($fields,'.')) {
			$vals=$this->$fields->getName();
		} else {
			$ex=explode('.',$fields);
			$tmpobj;
			foreach($ex as $k=>$v) {
				if($tmpobj==NULL)
					$tmpobj=$this->$v;
				else
					$tmpobj=$tmpobj->$v;

				if($tmpobj instanceof BasicModel) {
					if(!strstr($this->_joins, $tmpobj->_joins))
						$this->_joins .= sprintf(' %s ', $tmpobj->_joins);
				} elseif($tmpobj instanceof BasicType) {
					$vals=sprintf(' _%s.%s ', $lastobj->getAlias(), $tmpobj->getName());
				}

				$lastobj=$tmpobj;
			}
		}

		if($value===true)
			$value='t';
		elseif($value===false)
			$value='f';

		if(!$this->_where)
			$this->_where=" WHERE ".$vals;
		else
			$this->_where.=" AND ".$vals;

		switch($criteria) {
			case WHERE_ILIKE:
				$this->_where.=" ILIKE '%".$value."%' ";
			break;
			case WHERE_LIKE:
				$this->_where.=" LIKE '%".$value."%' ";
			break;
			case WHERE_EQ:
				$this->_where.="='$value' ";
			break;
			case WHERE_EQ_EXP:
				$this->_where.="=$value ";
			break;
			case WHERE_BETWEEN:
				$this->_where.= sprintf(" BETWEEN '%s' AND '%s' ", $value[0],$value[1]);
			break;
			default:
				$this->_where.="='$value' ";
			break;
		}
		return $this;
	}

	public function paginate($max, $current) {
		$this->_pageMax=$max;
		$this->_pageCurrent=$current;
		return $this;
	}

	private function _paginate(&$query) {
		$currentPage=$this->_pageCurrent;
		$limit=$this->_pageMax;
		$db=Stricter::getInstance()->inject( Stricter::getInstance()->getDefaultDatabaseId() );
		$offset=$currentPage*$limit;
		$q=$db->query($query);
		$n=$db->numrows($q);
		$this->limit($limit+0, $offset+0);
		$toceil=$n/$limit;
		$pages = ceil($toceil)+1;
		$currentPage++;
		$this->_paginationInfo=array('length'=>$pages-1,'count'=>$pages,'limit'=>$limit,'offset'=>$offset,'current'=>$currentPage,'total'=>$n,'isgetpg'=>$isgetpg, 'url'=>$_SERVER['SCRIPT_URL']);
	}

	public function limit($limit,$offset=0) {
		$this->_limit=" LIMIT ".$limit.' '.' OFFSET '.$offset;
		return $this;
	}

	public function insert()
	{
		$db=Stricter::getInstance()->inject( Stricter::getInstance()->getDefaultDatabaseId() );
		$entity_name = $this->getName();

		$a_pk = $this->getPrimaryKey();

		foreach($this as $key=>$tfield) {
			if($tfield instanceof BasicType && $tfield->getSequence()==null ) {
				$fields .= $tfield->getName().", ";
				$values .= $db->formatField( $tfield ).", ";
			}
		}

		$fields = substr($fields, 0, strlen($fields)-2);
		$values = substr($values, 0, strlen($values)-2);

		$sql = "INSERT INTO $entity_name($fields) VALUES ($values)";

		if( $db->query($sql)) {
			if($a_pk[0]->getSequence()!==null) {
				$last = $db->lastInsertId($this);
				$a_pk[0]->setValue($last);
			} else {
				$last = $a_pk[0]->getValue();  # TODO - return array of pks
			}
		} else {
			$last = null;
		}

		return $last;
	}

	public function update()
	{
		$db=Stricter::getInstance()->inject( Stricter::getInstance()->getDefaultDatabaseId() );
		$a_pk = $this->getPrimaryKey();

		$entity_name = $this->getName();

		foreach($this as $key=>$tfield) {
			if($tfield instanceof BasicType) {
				if($this->_fk!==$tfield){
					$val = $db->formatField( $tfield );
					$fields .= $tfield->getName()."=".$val.",";
				}
			} elseif ($tfield instanceof BasicModel) {
				$tfield->update();
			}
		}

		$fields = substr($fields, 0, strlen($fields)-1);

		$sql = "UPDATE ".$entity_name." SET ".$fields;

		$where = " WHERE ".$a_pk[0]->getName()."=".$a_pk[0]->getValue();

		$sql .= $where;

		if( $db->query($sql) )
			$last = $a_pk[0]->getValue(); # TODO - return array of pks

		return $last;
	}

	public function save()
	{
		$a_pk = $this->getPrimaryKey();

		if($a_pk[0]->getValue()==null) # TODO - Check multiple keys
			return $this->insert();
		else
			return $this->update();
	}

	public function delete()
	{
		$db=Stricter::getInstance()->inject( Stricter::getInstance()->getDefaultDatabaseId() );
		$a_pk = $this->getPrimaryKey();

		$entity_name = $this->getName();

		if(is_array($a_pk))
		{
			$where = " WHERE ";
			foreach($a_pk as $kk=>$vv){
				$where .= " ".str_replace(':','.',$vv->getName())." =  '".$vv->getValue()."' ";
				if($kk<count($a_pk)-1)
					$where .= " AND ";	
			}
		} else {
			$where = " WHERE ".$a_pk[0]->getName() ." = ".$a_pk[0]->getValue();
		}

		$sql = "DELETE FROM ".$this->_name." AS ".$this->_alias." ".$where;

		if($db->query($sql))
			return true;
		else
			return false;
	}

	public function retrieveNode($str, $idx=null) {
		if(!strstr($str,'.')) {
			$obj=&$this->$str;
		} else {
			$ex=explode('.',$str);
			$tmpobj;
			foreach($ex as $k=>$v) {
				if($tmpobj==NULL)
					$tmpobj=$this->$v;
				else
					$tmpobj=$tmpobj->$v;
				if($tmpobj instanceof BasicModel) {
					// what now..?
				} elseif($tmpobj instanceof BasicType) {
					$obj=&$tmpobj;
				}
				$lastobj=$tmpobj;
			}
		}
		return $obj;
	}

	public function validate()
	{
		$this->checkUniqueKeys();

		if(count($this->_validateOnly)>0)
			$fields =& $this->_validateOnly;
		else
			$fields =& $this;

		$this->setNumberOfErrors(0);

		foreach($fields as $key=>$tfield) {
			if($tfield instanceof BasicType) {
				if($tfield->getSequence()===null)
					$tfield->isValid();
				if($tfield->getError())
					$this->numberOfErrors++;
			}
		}

		if($this->numberOfErrors>0)
			return false;
		else
			return true;
	}

	public function printErrors()
	{
		$name = $this->getAlias();

		foreach($this as $k=>$v)
			if($v instanceof BasicType)
				if($v->getError())
					$out .= '- '.$v->getName().": ".$v->getError()."\n";

		if($out) {
			Stricter::getInstance()->log("Model validation errors: \n".$out);
			return $out;
		} else {
			return null;
		}
	}

	public function reset()
	{
		foreach( $this as $fk=>$vk )
		{
			if($vk instanceOf BasicType) {
				$vk->setValue( null );
				$vk->setError( null );
			}
		}
		$this->_sqlWhere=null;
		$this->_numberOfErrors=0;
	}

	private function checkUniqueKeys()
	{
		$db=Stricter::getInstance()->inject( Stricter::getInstance()->getDefaultDatabaseId() );
		if( count($this->_uniqueKeys)==0 )
			return;

		$pk = $this->getPrimaryKey();
		$pkname = $pk[0]->getName();
		$pkval = $pk[0]->getValue();

		if($pkval)
			$andPk = " AND ".$pkname." <> '".$pkval."'";

		foreach ( $this->_uniqueKeys as $k=>$v ) {
			$sql = "SELECT ".$pkname." FROM ".$this->_name;
			foreach($v as $kk=>$vv){
				$params = $vv->getName()."='".$vv->getValue()."' ";
				$andWhere=="" ? $andWhere=' WHERE ' : $andWhere=' AND ';
				$sql.=$andWhere.$params.$andPk;
			}
			unset($andWhere);
			$q = $db->query($sql);
			$n = $db->numrows($q);
			if($n>0) {
				foreach($v as $kk=>$vv)
					$vv->setError( LANG_ALREADY_REGISTERED_ERROR );
			}
		}
	}

	public function setFk(&$fkref){$this->_fk=$fkref;}
	public function getFk(){return $this->_fk;}
	public function setFkNm(&$fknm){$this->_fknm=$fknm;}
	public function getFkNm(){return $this->_fknm;}
	public function getDb(){return $this->_db;}
	public function setDb(&$val) {$this->_db=$val;}
	public function getName() {return $this->_name;}
	public function setName($modelname) {$this->_name=$modelname;}
	public function getAlias() {return $this->_alias;}
	public function setAlias($val) {$this->_alias = $val;}
	public function setNumberOfErrors($interrors) {$this->_numberOfErrors=$interrors;}
	public function getNumberOfErrors() {return $this->_numberOfErrors;}
	public function getPrimaryKey() {return $this->_primaryKey;}
	public function setPrimaryKey($arr) {$this->_primaryKey=$arr;}
	public function getUniqueKeys() {return $this->_uniqueKeys;}
	public function addUniqueKey($arr) {if(count($this->_uniqueKeys)==0) $this->_uniqueKeys[0]=$arr; else array_push($this->_uniqueKeys,$arr);  }
	public function getValidateOnly() {return $this->_validateOnly;}
	public function setValidateOnly($val) {$this->_validateOnly = $val;}
	public function getList(){return $this->_list;}
	public function getListItem($k){if($this->_list[$k]===null) return $this; else $ser=serialize($this->_list[$k]); return unserialize($ser); $this->_list[$k];}
	public function getRelation(){return $this->_relation;}
	public function getPaginationInfo(){return $this->_paginationInfo;}
}

interface ModelInterface
{
	public function init();
}

?>
