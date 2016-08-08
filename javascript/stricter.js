//=========================
function Stricter()
{
	this.ajax = new Ajax();
	this.FCKEditor = null;
}
//=========================
Stricter.prototype.accessCSS = function(layerID)
{
	if(document.getElementById){
		return document.getElementById(layerID).style; 
	} 
	else if(document.all) { 
		return document.all[layerID].style; 
	} 
	else if(document.layers) { 
		return document.layers[layerID]; 
	}
	else {
		return false;
	}
}
//=========================
function addMethod(object, name, fn)
{
	var old = object[ name ];
	object[ name ] = function(){
		if ( fn.length == arguments.length )
			return fn.apply( this, arguments );
		else if ( typeof old == 'function')
			return old.apply( this, arguments );
		};
}
//=========================
//Created by: Cyanide_7
function autoTab(input, len, e)
{
	var isNN = (navigator.appName.indexOf("Netscape") != -1);
	var keyCode = (isNN) ? e.which : e.keyCode; 
	var filter = (isNN) ? [0,8,9] : [0,8,9,16,17,18,37,38,39,40,46];
	if(input.value.length >= len && !containsElement(filter,keyCode)){
		input.value = input.value.slice(0, len);
		input.form[(getIndex(input)+1) % input.form.length].focus();
	}
	function containsElement(arr, ele)
	{
		var found = false, index = 0;
		while(!found && index < arr.length)
		if(arr[index] == ele)
		found = true;
		else
		index++;
		return found;
	}
	function getIndex(input)
	{
		var index = -1, i = 0, found = false;
		while(i < input.form.length && index == -1)
		if(input.form[i] == input)index = i;
		else i++;
		return index;
	}
	return true;
}
//=========================
function Ajax()
{
	this.ajaxobj = null;
	this.method = "POST";
	this.charset = "UTF-8";
	this.container = null;
	this.loading = null;
	this.url = null;
	this.formid = null;
	this.async = true;	
	addMethod(this, "get", function(curl, todiv) {
		this.method = "GET";
		this.url = curl;
		this.container = todiv;
		this.execute();
	});

	addMethod(this, "post", function(curl, todiv, formid) {
		this.method = "POST";
		this.formid = formid;
		this.url = curl;
		this.container = todiv;
		this.execute();
	});
}
//=========================
Ajax.prototype.execute = function() 
{
	if(this.loading)
		stricter.accessCSS(this.loading).visibility = "visible";

	var parameters = null;

	if(this.method=="POST")
		parameters = this.getFormVars(this.formid);

	if (window.XMLHttpRequest)
		this.ajaxobj = new XMLHttpRequest();
	else if (window.ActiveXObject) {
		try { this.ajaxobj = new ActiveXObject("Msxml2.XMLHTTP");}
		catch (e) {
			try{ this.ajaxobj = new ActiveXObject("Microsoft.XMLHTTP"); }
			catch (e){}
			}
	}

	if (this.ajaxobj.overrideMimeType)
		this.ajaxobj.overrideMimeType('text/xml; charset='+this.charset );

	this.ajaxobj.onreadystatechange = function() {
		if (stricter.ajax.ajaxobj.readyState == 4 ) {
			if (stricter.ajax.ajaxobj.status == 200) {
				result = stricter.ajax.ajaxobj.responseText;

				if(stricter.ajax.container!=null) {
					document.getElementById(stricter.ajax.container).innerHTML = result;
					__runScripts(document.getElementById(stricter.ajax.container));
				}
				if(stricter.ajax.loading) {
					stricter.accessCSS(stricter.ajax.loading).visibility = "hidden";
				}
			}
		}
	}

	this.ajaxobj.open(this.method, this.url, this.async);

	this.ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	if(parameters)
		this.ajaxobj.setRequestHeader("Content-length", this.url.length + parameters.length);
	else
		this.ajaxobj.setRequestHeader("Content-length", this.url.length);
	this.ajaxobj.setRequestHeader("Connection", "close");
	this.ajaxobj.send(parameters);
	this.formid=null;
}

//evaluate all js from ajax template.. 
//found on http://brightbyte.de/page/Loading_script_tags_via_AJAX
function __runScripts(e) {
	if (e.nodeType != 1) return; //if it's not an element node, return
	if (e.tagName.toLowerCase() == 'script') {
		var $s = document.createElement('script'); 
		$s.text=e.text;
		document.body.appendChild($s);
	} else {
		var n = e.firstChild;
		while ( n ) {
			if ( n.nodeType == 1 ) __runScripts( n ); //if it's an element node, recurse
			n = n.nextSibling;
		}
	}
}
//=========================
Ajax.prototype.getFormVars = function (formid)
{
	var strparams;
	var i = 0;
	var tmps;
	var poststr = "";
	var felements;

	if(document.forms[formid])
	{
		felements = document.forms[formid].elements;

		while(felements[i]!=null)
		{
			tmps = felements[i].name;

			switch(felements[i].type)
			{
				case "select-multiple":
				{
					var x = 0;
					var len = felements[i].options.length;
					var concat_sel = "";
					var num_sel;
					for(x=0; x<len; x++) {
						if(felements[i].options[x].selected == true)
							concat_sel += "&" + felements[i].name + "=" + felements[i].options[x].value;
					}
					poststr += concat_sel;
					break;
				}
				//================
				case "checkbox":
				case "radio":
				{
					if(tmps==undefined)	break;
					if(felements[i].checked)
						poststr += "&" + felements[i].name +"="+ felements[i].value;
					break;
				}
				//================
				default:
				{
					if(tmps==undefined)	break;
					poststr += "&" + felements[i].name +"="+ felements[i].value;
					break;
				}
			}		
			i++;
		}
	}
	poststr = poststr.substr(1, poststr.length -1) ;
	poststr = encodeURI(poststr);
	return poststr;
}

stricter = new Stricter();
