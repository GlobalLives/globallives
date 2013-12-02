<?php
	
	function ga_dash_classic_tracking(){
		$tracking_events="";
		$ga_default_domain=get_option('ga_default_domain');
		$tracking_0="<script type=\"text/javascript\">
	var _gaq = _gaq || [];";		
		$tracking_2="\n	(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>\n";
		$profiles=get_option('ga_dash_profile_list');
		if (is_array($profiles)){		
			foreach ($profiles as $items) {
				if ((get_option('ga_dash_default_ua')==$items[2])){
					$ga_default_domain=ga_dash_get_main_domain($items[3]);
					update_option('ga_default_domain',$ga_default_domain);
				} 
			}
		}
		switch ( get_option('ga_dash_tracking') ){
			case 2 	: $tracking_push="['_setAccount', '".get_option('ga_dash_default_ua')."'], ['_setDomainName', '".$ga_default_domain."']"; break;
			case 3 : $tracking_push="['_setAccount', '".get_option('ga_dash_default_ua')."'], ['_setDomainName', '".$ga_default_domain."'], ['_setAllowLinker', true]"; break;
			default : $tracking_push="['_setAccount', '".get_option('ga_dash_default_ua')."']"; break;				
		}

		if (get_option('ga_dash_anonim')){
			$tracking_push.=", ['_gat._anonymizeIp']";
		}	
		
		$tracking=$tracking_events.$tracking_0."\n	_gaq.push(".$tracking_push.", ['_trackPageview']);".$tracking_2;	
		
		return $tracking;	

	}

	function ga_dash_universal_tracking(){
		$tracking_events="";
		$ga_default_domain=get_option('ga_default_domain');
		$tracking_0="<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');";		
		$tracking_2="\n</script>\n";
		$profiles=get_option('ga_dash_profile_list');
		if (is_array($profiles)){
			foreach ($profiles as $items) {
					if ((get_option('ga_dash_default_ua')==$items[2])){
						$ga_default_domain=ga_dash_get_main_domain($items[3]);
						update_option('ga_default_domain',$ga_default_domain);
					} 
			}
		}
		switch ( get_option('ga_dash_tracking') ){
			case 2 	: $tracking_push="\n	ga('create', '".get_option('ga_dash_default_ua')."', {'cookieDomain': '".$ga_default_domain."'});"; break;
			case 3 : $tracking_push="\n	ga('create', '".get_option('ga_dash_default_ua')."');"; break;
			default : $tracking_push="\n	ga('create', '".get_option('ga_dash_default_ua')."');";
		}

		if (get_option('ga_dash_anonim')){
		
			$tracking_push.="\n	ga('send', 'pageview', {'anonymizeIp': true});";
		
		} else{
			
			$tracking_push.="\n	ga('send', 'pageview');";
			
		}	
		
		$tracking=$tracking_events.$tracking_0.$tracking_push.$tracking_2;	
		
		return $tracking;	

	}

	
	function ga_dash_get_main_domain($subdomain){
		$parsedomain=parse_url($subdomain,PHP_URL_HOST);
		$host_names = explode(".", $parsedomain);
		$domain = $host_names[count($host_names)-2] . "." . $host_names[count($host_names)-1];
		return $domain;
	}
	
	function ga_dash_get_profile_domain($domain){
	
		return str_replace(array("https://","http://"," "),"",$domain);
	
	}
	
	function ga_dash_pretty_error($e){
		return "<center><table><tr><td colspan='2' style='word-break:break-all;'>".$e->getMessage()."<br /><br /></td></tr><tr><td width='50%'><a href='http://wordpress.org/support/plugin/google-analytics-dashboard-for-wp' target='_blank'>".__("Help on Wordpress Forum",'ga-dash')."</a><td width='50%'><a href='http://forum.deconf.com/en/wordpress-plugins-f182/' target='_blank'>".__("Support on Deconf Forum",'ga-dash')."</a></td></tr></table></center>";	
	}

	function ga_dash_clear_cache(){
		global $wpdb;
		$sqlquery=$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_gadash%%'");
		$sqlquery=$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_gadash%%'");
	}
	
	function ga_dash_safe_get($key) {
		if (array_key_exists($key, $_POST)) {
			return $_POST[$key];
		}
		return false;
	}
	
	function ga_dash_store_token ($token){
		update_option('ga_dash_token', $token);
	}		
	
	function ga_dash_get_token (){

		if (get_option('ga_dash_token')){
			return get_option('ga_dash_token');
		}
		else{
			return;
		}
	
	}
	
	function ga_dash_reset_token (){
		update_option('ga_dash_token', "");
		update_option('ga_dash_tableid', "");
		update_option('ga_dash_tableid_jail', "");
		update_option('ga_dash_profile_list', "");
		update_option('ga_dash_access', ""); 		
	}

// Get Top Pages
	function ga_dash_top_pages($service, $projectId, $from, $to){

		$metrics = 'ga:pageviews'; 
		$dimensions = 'ga:pageTitle';
		try{
			$serial='gadash_qr4'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions, 'sort' => '-ga:pageviews', 'max-results' => '24', 'filters' => 'ga:pagePath!=/'));
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;	
			}			
		} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
		}	
		if (!isset($data['rows'])){
			return 0;
		}
		
		$ga_dash_data="";
		$i=0;
		while (isset($data['rows'][$i][0])){
			$ga_dash_data.="['".str_replace(array("'","\\")," ",$data['rows'][$i][0])."',".$data['rows'][$i][1]."],";
			$i++;
		}

		return $ga_dash_data;
	}
	
// Get Top referrers
	function ga_dash_top_referrers($service, $projectId, $from, $to){

		$metrics = 'ga:visits'; 
		$dimensions = 'ga:source,ga:medium';
		try{
			$serial='gadash_qr5'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions, 'sort' => '-ga:visits', 'max-results' => '24', 'filters' => 'ga:medium==referral'));	
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;		
			}			
		} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
		}
		if (!isset($data['rows'])){
			return 0;
		}
		
		$ga_dash_data="";
		$i=0;
		while (isset($data['rows'][$i][0])){
			$ga_dash_data.="['".str_replace(array("'","\\")," ",$data['rows'][$i][0])."',".$data['rows'][$i][2]."],";
			$i++;
		}

		return $ga_dash_data;
	}

// Get Top searches
	function ga_dash_top_searches($service, $projectId, $from, $to){

		$metrics = 'ga:visits'; 
		$dimensions = 'ga:keyword';
		try{
			$serial='gadash_qr6'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions, 'sort' => '-ga:visits', 'max-results' => '24', 'filters' => 'ga:keyword!=(not provided);ga:keyword!=(not set)'));
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;		
			}			
		} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
		}	
		if (!isset($data['rows'])){
			return 0;
		}
		
		$ga_dash_data="";
		$i=0;
		while (isset($data['rows'][$i][0])){
			$ga_dash_data.="['".str_replace(array("'","\\")," ",$data['rows'][$i][0])."',".$data['rows'][$i][1]."],";
			$i++;
		}

		return $ga_dash_data;
	}
// Get Visits by Country
	function ga_dash_visits_country($service, $projectId, $from, $to){

		$metrics = 'ga:visits'; 
		$dimensions = 'ga:country';
		try{
			$serial='gadash_qr7'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions));
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;		
			}			
		} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
		}
		if (!isset($data['rows'])){
			return 0;
		}
		
		$ga_dash_data="";
		for ($i=0;$i<$data['totalResults'];$i++){
			$ga_dash_data.="['".str_replace(array("'","\\")," ",$data['rows'][$i][0])."',".$data['rows'][$i][1]."],";
		}

		return $ga_dash_data;

	}	
// Get Traffic Sources
	function ga_dash_traffic_sources($service, $projectId, $from, $to){

		$metrics = 'ga:visits'; 
		$dimensions = 'ga:medium';
		try{
			$serial='gadash_qr8'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions));
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;		
			}			
		} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
		}	
		if (!isset($data['rows'])){
			return 0;
		}
		
		$ga_dash_data="";
		for ($i=0;$i<$data['totalResults'];$i++){
			$ga_dash_data.="['".str_replace("(none)","direct",$data['rows'][$i][0])."',".$data['rows'][$i][1]."],";
		}

		return $ga_dash_data;

	}

// Get New vs. Returning
	function ga_dash_new_return($service, $projectId, $from, $to){

		$metrics = 'ga:visits'; 
		$dimensions = 'ga:visitorType';
		try{
			$serial='gadash_qr9'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions));
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;		
			}			
		} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
		}	
		if (!isset($data['rows'])){
			return 0;
		}
		
		$ga_dash_data="";
		for ($i=0;$i<$data['totalResults'];$i++){
			$ga_dash_data.="['".str_replace(array("'","\\")," ",$data['rows'][$i][0])."',".$data['rows'][$i][1]."],";
		}

		return $ga_dash_data;

	}	
?>