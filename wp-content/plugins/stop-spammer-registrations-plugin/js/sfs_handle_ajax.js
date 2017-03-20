	var sfs_ajax_who="";
	function sfs_ajax_process(sip,contx,sfunc,url) {
		sfs_ajax_who=contx;
		var data= {
			action: 'sfs_process',
			ip:sip,
			cont: contx, 
			func: sfunc, 
			ajax_url: url
		}
		jQuery.get(ajaxurl, data, sfs_ajax_return_process);
	}
	function sfs_ajax_return_process(response) {
		var el="";
		if (response=="OK") {
			return false;
		}
		if (response.substring(0,3)=="err") {
			alert(response);
			return false;
		}
		if (response.substring(0,4)=="\r\n\r\n") {
			alert(response);
			return false;
		}

		if (sfs_ajax_who!="") {
			var el=document.getElementById(sfs_ajax_who);
			el.innerHTML=response;
		}
		return false;
	}
	function sfs_ajax_report_spam(t,id,blog,url) {
		sfs_ajax_who=t;
		
		var data= {
			action: 'sfs_sub',
			blog_id: blog,
			comment_id: id,
			ajax_url: url
		}
		jQuery.get(ajaxurl, data, sfs_ajax_return_spam);
	}
	function sfs_ajax_return_spam(response) {
		sfs_ajax_who.innerHTML="Spam reported";
		sfs_ajax_who.style.color="green";
		sfs_ajax_who.style.fontWeight="bolder";
		if (response.indexOf('data submitted successfully')>0) {
			return false;
		}
		if (response.indexOf('recent duplicate entry')>0) {
			sfs_ajax_who.innerHTML="Spam Already reported";
			sfs_ajax_who.style.color="brown";
			sfs_ajax_who.style.fontWeight="bolder";
			return false;
		}
		sfs_ajax_who.innerHTML="Error reporting spam:"+response;
		sfs_ajax_who.style.color="red";
		sfs_ajax_who.style.fontWeight="bolder";
		alert(response);
		return false;
	}
/* these are not used. Delete when finished testing	
	function sfs_ajax_bcache_delete(t,tip,url) {
		sfs_ajax_who=t;
		var data= {
		action: 'sfs_bc_del',
			id:t,
			ip: tip,
			ajax_url: url
		}
		jQuery.get(ajaxurl, data, sfs_ajax_return_bcache_delete);
	}
	function sfs_ajax_return_bcache_delete(response) {
		var el=document.getElementById(sfs_ajax_who);
		el.innerHTML=response;
		return false;
	}
	
	function sfs_ajax_gcache_delete(t,tip,url) {
		sfs_ajax_who=t;
		var data= {
		action: 'sfs_gc_del',
			id:t,
			ip: tip,
			ajax_url: url
		}
		jQuery.get(ajaxurl, data, sfs_ajax_return_gcache_delete);
	}
	function sfs_ajax_return_gcache_delete(response) {
		var el=document.getElementById(sfs_ajax_who);
		el.innerHTML=response;
		
		return false;
	}

	function sfs_ajax_addblack(t,tip,url) {
		sfs_ajax_who=t;
		var data= {
		action: 'sfs_addblack',
			id:t,
			ip: tip,
			ajax_url: url
		}
		jQuery.get(ajaxurl, data, sfs_ajax_return_addblack);
	}
	function sfs_ajax_addgoodblack(t,tip,url) {
		sfs_ajax_who=t;
		var data= {
		action: 'sfs_goodaddblack',
			id:t,
			ip: tip,
			ajax_url: url
		}
		jQuery.get(ajaxurl, data, sfs_ajax_return_goodaddblack);
	}
	function sfs_ajax_return_addblack(response) {
		var el=document.getElementById(sfs_ajax_who);
		el.innerHTML=response;
		return false;
	}
	function sfs_ajax_return_goodaddblack(response) {
		var el=document.getElementById(sfs_ajax_who);
		el.innerHTML=response;
		return false;
	}
	
	function sfs_ajax_addwhite(t,tip,url) {
		sfs_ajax_who=t;
		var data= {
		action: 'sfs_addwhite',
			id:t,
			ip: tip,
			ajax_url: url
		}
		jQuery.get(ajaxurl, data, sfs_ajax_return_addblack);
	}
	function sfs_ajax_return_addwhite(response) {
		var el=document.getElementById(sfs_ajax_who);
		el.innerHTML=response;
		return false;
	}
*/


