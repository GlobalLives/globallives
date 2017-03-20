<?php
// Allow List - returns false if not found

if (!defined('ABSPATH')) exit;

class chkcloudflare extends be_module {
// if the cloudflare plugin is not installed then the ip will be cloudflare's can't check this.
// as of 6.03 can also correct it.
// no longer returns anything but false. If we detect cloudflare we fix it.
// if we detect cloudflare and can't fix it we can't really do anything about it. Just block it.
// cloudflare will be whitelisted in the generated white list.
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		//return false;
		if (function_exists('cloudflare_init')) return false; // no sense proceeding, cloudflare is on the case.
		if (!array_key_exists('HTTP_CF_CONNECTING_IP',$_SERVER)) return false;		// we would normally whitelist if cloudflare plugin is not active and we detect cloudflare ip - here were are fixing that
		// ranges last update 2/27/2015
		$ip4ranges = array(
			"199.27.128.0/21",
			"173.245.48.0/20",
			"103.21.244.0/22",
			"103.22.200.0/22",
			"103.31.4.0/22",
			"141.101.64.0/18",
			"108.162.192.0/18",
			"190.93.240.0/20",
			"188.114.96.0/20",
			"197.234.240.0/22",
			"198.41.128.0/17",
			"162.158.0.0/15",
			"104.16.0.0/12"
		);
		$ip6ranges = array(
			"2400:cb00::/32",
			"2606:4700::/32",
			"2803:f800::/32",
			"2405:b500::/32",
			"2405:8100::/32"
		);
		$cf_found=false;
		if (strpos($ip,'.')!==false) {
			// check the cloudflare ranges using cidr
			$ipl=ip2long($ip);
			foreach($ip4ranges as $ip4) {
				list($range, $bits) = explode('/', $ip4, 2);
				$ipr=ip2long($range);
				$mask = -1 << (32 - $bits);
				$ipt=$ipl & $mask;
				$ipr=$ipr & $mask;
				//echo "$ipr - $ipl <br>";
				if($ipt == $ipr)  {
					// goto is not supported in older versions of PHP
					// goto cf_true; // I love it! I haven't coded a goto in over 25 years.
					$cf_found=true;
					break;
				}
		    }
		} else if (strpos($ip,':')!==false && strlen($ip)>=9) {
			$ip=strtolower($ip); // not sure what apache sends us
			foreach($ip6ranges as $ip6) {
				// cheat - cf uses 32 bit masks so just use the first 9 characters
                if (substr($ip6,0,9)==substr($ip,0,9)) {
					//goto cf_true;
					$cf_found=true;
					break;
				}
			}
		}
		if (!$cf_found) return false;
		
//cf_true:
		// we need to use the ip borrowed from cloudflare
		if (array_key_exists('HTTP_CF_CONNECTING_IP',$_SERVER)) {
			if (array_key_exists('REMOTE_ADDR',$_SERVER)) {
				$_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
				return false;
			}
		}
		return false;
	}
}
?>