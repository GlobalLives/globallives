<?php
/*
Plugin Name: Really Simple Twitter Feed Widget
Plugin URI: http://www.whiletrue.it/
Description: Displays your public Twitter messages in the sidbar of your blog. Simply add your username and all your visitors can see your tweets!
Author: WhileTrue
Version: 2.4.11
Author URI: http://www.whiletrue.it/
*/
/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2, 
    as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

/**
 * ReallySimpleTwitterWidget Class
 */
class ReallySimpleTwitterWidget extends WP_Widget {
	private /** @type {string} */ $languagePath;

    /** constructor */
    function ReallySimpleTwitterWidget() {
		$this->languagePath = basename(dirname(__FILE__)).'/languages';
        load_plugin_textdomain('rstw', 'false', $this->languagePath);

		$this->options = array(
			array(
				'label' => __( 'Twitter Authentication', 'rstw' ),
				'type'	=> 'separator', 	'notes' => __('Get them creating your Twitter Application', 'rstw' ).' <a href="https://dev.twitter.com/apps" target="_blank">'.__('here', 'rstw' ).'</a>'	),
			array(
				'name'	=> 'consumer_key',	'label'	=> 'Consumer Key',
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'consumer_secret',	'label'	=> 'Consumer Secret',
				'type'	=> 'password',	'default' => ''			),
			array(
				'name'	=> 'access_token',	'label'	=> 'Access Token',
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'access_token_secret',	'label'	=> 'Access Token Secret',
				'type'	=> 'password',	'default' => ''			),
			array(
				'label' => __( 'Twitter Data', 'rstw' ),
				'type'	=> 'separator'			),
			array(
				'name'	=> 'username',		'label'	=> __( 'Twitter Username', 'rstw' ),
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'num',			'label'	=> __( 'Show # of Tweets', 'rstw' ),
				'type'	=> 'text',	'default' => '5'			),
			array(
				'name'	=> 'skip_text',		'label'	=> __( 'Skip tweets containing this text', 'rstw' ),
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'skip_replies',		'label'	=> __( 'Skip replies', 'rstw' ),
				'type'	=> 'checkbox',	'default' => true	),
			array(
				'name'	=> 'skip_retweets',		'label'	=> __( 'Skip retweets', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false	),
			array(
				'label' => __( 'Widget Title', 'rstw' ),
				'type'	=> 'separator'			),
			array(
				'name'	=> 'title',	'label'	=> __( 'Title', 'rstw' ),
				'type'	=> 'text',	'default' => __( 'Last Tweets', 'rstw' )			),
			array(
				'name'	=> 'title_icon',	'label'	=> __( 'Show Twitter icon on title', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'name'	=> 'title_thumbnail',	'label'	=> __( 'Show account thumbnail on title', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'name'	=> 'link_title',	'label'	=> __( 'Link above Title with Twitter user', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'label' => __( 'Widget Footer', 'rstw' ),
				'type'	=> 'separator'			),
			array(
				'name'	=> 'link_user',		'label'	=> __( 'Show a link to the Twitter user profile', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'name'	=> 'link_user_text',	'label'	=> __( 'Link text', 'rstw' ),
				'type'	=> 'text',	'default' => 'See me on Twitter'			),
			array(
				'name'	=> 'button_follow',		'label'	=> __( 'Show a Twitter Follow Me button', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'name'	=> 'button_follow_text',	'label'	=> __( 'Button text', 'rstw' ),
				'type'	=> 'text',	'default' => 'Follow @me'			),
			array(
				'label' => __( 'Items and Links', 'rstw' ),
				'type'	=> 'separator'			),
			array(
				'name'	=> 'linked',		'label'	=> __( 'Show this linked text at the end of each Tweet', 'rstw' ),
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'update',	'label'	=> __( 'Show timestamps', 'rstw' ),
				'type'	=> 'checkbox',	'default' => true			),
			array(
				'name'	=> 'date_format',	'label'	=> __( 'Timestamp format (e.g. M j )', 'rstw' ).' <a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">?</a>',
				'type'	=> 'text',	'default' => 'M j'			),
			array(
				'name'	=> 'thumbnail',	'label'	=> __( 'Include thumbnail before tweets', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),			
			array(
				'name'	=> 'thumbnail_retweets',	'label'	=> __( 'Use author thumb for retweets', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),			
			array(
				'name'	=> 'hyperlinks',	'label'	=> __( 'Find and show hyperlinks', 'rstw' ),
				'type'	=> 'checkbox',	'default' => true			),
			array(
				'name'	=> 'replace_link_text',	'label'	=> __( 'Replace hyperlinks text with fixed text (e.g. "-->")', 'rstw' ),
				'type'	=> 'text',	'default' => ''			),
			array(
				'name'	=> 'twitter_users',	'label'	=> __( 'Find Replies in Tweets', 'rstw' ),
				'type'	=> 'checkbox',	'default' => true			),
			array(
				'name'	=> 'link_target_blank',	'label'	=> __( 'Create links on new window / tab', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'label' => __( 'Debug', 'rstw' ),
				'type'	=> 'separator',		'notes' => 	__('Use them only for a few minutes, when having issues', 'rstw')	),
			array(
				'name'	=> 'debug',	'label'	=> __( 'Show debug info', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'name'	=> 'erase_cached_data',	'label'	=> __( 'Erase cached data', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'name'	=> 'encode_utf8',	'label'	=> __( 'Force UTF8 Encode', 'rstw' ),
				'type'	=> 'checkbox',	'default' => false			),
			array(
				'type'	=> 'donate'			),
		);

        $control_ops = array('width' => 500);
        parent::WP_Widget(false, 'Really Simple Twitter', array(), $control_ops);	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;  
		if ( $title != '') {
			echo $before_title;
			$title_icon = ($instance['title_icon']) ? '<img src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/twitter_small.png" alt="'.$title.'" title="'.$title.'" /> ' : '';
			$title_thumb = '';
			if (isset($instance['title_thumbnail']) && $instance['title_thumbnail']) {
				$transient_name = 'twitter_thumb_'.$options['username'];
				$twitter_thumb = get_transient($transient_name);
				if ($twitter_thumb=='') {
					if ($instance['consumer_key'] == '' or $instance['consumer_secret'] == '' or $instance['access_token'] == '' or $instance['access_token_secret'] == '') {
						return __('Twitter Authentication data is incomplete','rstw');
					} 
					if (!$this->cb) {
						$this->really_simple_twitter_codebird_set ($instance);
					}
					$user_data =  $this->cb->users_show(array('screen_name'=>$instance['username']));
					$twitter_thumb = $user_data['profile_image_url'];
					set_transient($transient_name, $twitter_thumb, 60*60*24); // 1 day
				}
				if ($twitter_thumb!='') {
					$title_thumb = '<img src="'.$twitter_thumb.'" alt="'.$title.'" title="'.$title.'" class="really_simple_twitter_author" /> ';
				}
			}
			if ( $instance['link_title'] === true ) {
				$link_target = ($instance['link_target_blank']) ? ' target="_blank" ' : '';
				echo '<a href="http://twitter.com/' . $instance['username'] . '" class="twitter_title_link" '.$link_target.'>'. $title_icon . $title_thumb . $title . '</a>';
			} else {
				echo $title_icon . $title_thumb . $title;
			}
			echo $after_title;
		}
		echo $this->really_simple_twitter_messages($instance);
		echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		
		foreach ($this->options as $val) {
			if ($val['type']=='text' || $val['type']=='password') {
				$instance[$val['name']] = strip_tags($new_instance[$val['name']]);
			} else if ($val['type']=='checkbox') {
				$instance[$val['name']] = ($new_instance[$val['name']]=='on') ? true : false;
			}
		}
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
		if (empty($instance)) {
			foreach ($this->options as $val) {
				if ($val['type']=='separator') {
					continue;
				}
				$instance[$val['name']] = $val['default'];
			}
		}					
	
		// CHECK AUTHORIZATION
		if (!function_exists('curl_init')) {
			echo __('CURL extension not found. You need enable it to use this Widget');
			return;
		}
		
		echo '<div class="rstw_form">';

		foreach ($this->options as $val) {
			if ($val['type']=='separator') {
				if (isset($val['label']) && $val['label']!='') {
					echo '<h3>'.$val['label'].'</h3>';
				} else {
					echo '<hr />';
				}
				if (isset($val['notes']) && $val['notes']!='') {
					echo '<div class="description">'.$val['notes'].'</div>';
				}
			} else if (isset($val['type']) && ($val['type']=='text' || $val['type']=='password')) {
				echo '
					<input class="widefat" id="'.$this->get_field_id($val['name']).'"  name="'.$this->get_field_name($val['name']).'" type="'.$val['type'].'" value="'.esc_attr(isset($instance[$val['name']]) ? $instance[$val['name']] : '').'" />
					<label for="'.$this->get_field_id($val['name']).'">'.$val['label'].'</label>
					<div class="rstw_clear"></div>';
			} else if (isset($val['type']) && $val['type']=='checkbox') {
				$checked = (isset($instance[$val['name']]) && $instance[$val['name']]) ? 'checked="checked"' : '';
				echo '
					<div class="rstw_checkbox"><input id="'.$this->get_field_id($val['name']).'" name="'.$this->get_field_name($val['name']).'" type="checkbox" '.$checked.' /></div>
					<label for="'.$this->get_field_id($val['name']).'">'.$val['label'].'</label>
					<div class="rstw_clear"></div>';
      } else if (isset($val['type']) && $val['type']=='donate') {
        echo '<p style="text-align:center; font-weight:bold;">
            '.__('Do you like it? I\'m supporting it, please support me!', 'rstw').'<br />
            <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=giu%40formikaio%2eit&item_name=WhileTrue&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted" target="_blank">
         			<img alt="PayPal - The safer, easier way to pay online!" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" > 
            </a>
          </p>';
      }
		}
		echo '
			</div>
			<style>
			.rstw_form h3, .rstw_form .description { text-align:center; margin-top:1.2em; margin-bottom:0.6em; }
			.rstw_form input[type="text"], .rstw_form input[type="password"] { float:left; width:200px; }
			.rstw_form .rstw_checkbox { float:left; width:200px; text-align:right; }
			.rstw_form label { width:270px; padding-left:5px; }
			.rstw_form .rstw_clear { clear:both; height:2px; margin-bottom:1px; border-bottom:1px solid #eee; }
			</style>';
	}


	protected function debug ($options, $text) {
		if ($options['debug']) {
			echo $text."\n";
		}
	}
	
	
	public function really_simple_twitter_codebird_set ($options) {
		if (!class_exists('Codebird')) {
			require ('lib/codebird.php');
		}
		Codebird::setConsumerKey($options['consumer_key'], $options['consumer_secret']); 
		$this->cb = Codebird::getInstance();	
		$this->cb->setToken($options['access_token'], $options['access_token_secret']);
		
		// From Codebird documentation: For API methods returning multiple data (like statuses/home_timeline), you should cast the reply to array
		$this->cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
	}
	

	// Display Twitter messages
	public function really_simple_twitter_messages($options) {
	
		// CHECK OPTIONS

		if (!isset($options['skip_retweets'] ) ) {
			$options['skip_retweets'] = false;
		}
		if (!isset($options['thumbnail_retweets']) ) {
			$options['thumbnail_retweets'] = false;
		}
		if (!isset($options['button_follow']) ) {
			$options['button_follow'] = false;
		}
		if (!isset($options['date_format']) ) {
			$options['date_format'] = 'M j';
		}
		if ($options['username'] == '') {
			return __('Twitter username is not configured','rstw');
		} 
		if (!is_numeric($options['num']) or $options['num']<=0) {
			return __('Number of tweets is not valid','rstw');
		}
		if ($options['consumer_key'] == '' or $options['consumer_secret'] == '' or $options['access_token'] == '' or $options['access_token_secret'] == '') {
			return __('Twitter Authentication data is incomplete','rstw');
		} 
		if (!isset($this->cb) ) {
			$this->really_simple_twitter_codebird_set ($options);
		}

		// SET THE NUMBER OF ITEMS TO RETRIEVE - IF "SKIP TEXT" IS ACTIVE, GET MORE ITEMS
		$max_items_to_retrieve = $options['num'];
		if ($options['skip_text']!='' or $options['skip_replies'] or $options['skip_retweets']) {
			$max_items_to_retrieve *= 4;
		}
		// TWITTER API GIVES MAX 200 TWEETS PER REQUEST
		if ($max_items_to_retrieve>200) {
			$max_items_to_retrieve = 200;
		}
	
		$transient_name = 'twitter_data_'.$options['username'].$options['skip_text'].'_'.$options['num'];

		if ($options['erase_cached_data']) {
			$this->debug($options, '<!-- '.__('Fetching data from Twitter').'... -->');
			$this->debug($options, '<!-- '.__('Erase cached data option enabled').'... -->');
			delete_transient($transient_name);
			delete_transient($transient_name.'_status');
			delete_option($transient_name.'_valid');
			
			try {
				$twitter_data =  $this->cb->statuses_userTimeline(array(
							'screen_name'=>$options['username'], 
							'count'=>$max_items_to_retrieve,
							'exclude_replies'=>$options['skip_replies'],
							'include_rts'=>(!$options['skip_retweets'])
					));
			} catch (Exception $e) {
				$this->debug($options, $e->getMessage().'<br />');
				return __('Error retrieving tweets','rstw'); 
			}

			if (isset($twitter_data['errors'])) {
				$this->debug($options, __('Twitter data error:','rstw').' '.$twitter_data['errors'][0]['message'].'<br />');
			}
		} else {
	
			// USE TRANSIENT DATA, TO MINIMIZE REQUESTS TO THE TWITTER FEED
	
			$timeout = 10 * 60; //10m
			$error_timeout = 5 * 60; //5m
    
			$twitter_data = get_transient($transient_name);
			$twitter_status = get_transient($transient_name.'_status');
    
			// Twitter Status
			if(!$twitter_status || !$twitter_data) {
				try {
					$twitter_status = $this->cb->application_rateLimitStatus();
					set_transient($transient_name."_status", $twitter_status, $error_timeout);
				} catch (Exception $e) { 
					$this->debug($options, __('Error retrieving twitter rate limit').'<br />');
				}
			}
    
			// Tweets

			if (empty($twitter_data) or count($twitter_data)<1 or isset($twitter_data['errors'])) {
				$calls_limit   = (int)$twitter_status['resources']['statuses']['/statuses/user_timeline']['limit'];
				$remaining     = (int)$twitter_status['resources']['statuses']['/statuses/user_timeline']['remaining'];
				$reset_seconds = (int)$twitter_status['resources']['statuses']['/statuses/user_timeline']['reset']-time();

				$this->debug($options, '<!-- '.__('Fetching data from Twitter').'... -->');
				$this->debug($options, '<!-- '.__('Requested items').' : '.$max_items_to_retrieve.' -->');
				$this->debug($options, '<!-- '.__('API calls left').' : '.$remaining.' of '.$calls_limit.' -->');
				$this->debug($options, '<!-- '.__('Seconds until reset').' : '.$reset_seconds.' -->');

				if($remaining <= 7 and $reset_seconds >0) {
				    $timeout       = $reset_seconds;
				    $error_timeout = $reset_seconds;
				}

				try {
					$twitter_data =  $this->cb->statuses_userTimeline(array(
							'screen_name'=>$options['username'], 
							'count'=>$max_items_to_retrieve, 
							'exclude_replies'=>$options['skip_replies'],
							'include_rts'=>(!$options['skip_retweets'])
						));
				} catch (Exception $e) { return __('Error retrieving tweets','rstw'); }

				if(!isset($twitter_data['errors']) and (count($twitter_data) >= 1) ) {
				    set_transient($transient_name, $twitter_data, $timeout);
				    update_option($transient_name."_valid", $twitter_data);
				} else {
				    set_transient($transient_name, $twitter_data, $error_timeout);	// Wait 5 minutes before retry
					if (isset($twitter_data['errors'])) {
						$this->debug($options, __('Twitter data error:','rstw').' '.$twitter_data['errors'][0]['message'].'<br />');
					}
				}
			} else {
				$this->debug($options, '<!-- '.__('Using cached Twitter data').'... -->');

				if(isset($twitter_data['errors'])) {
					$this->debug($options, __('Twitter cache error:','rstw').' '.$twitter_data['errors'][0]['message'].'<br />');
				}
			}
    
			if (empty($twitter_data) and false === ($twitter_data = get_option($transient_name."_valid"))) {
			    return __('No public tweets','rstw');
			}

			if (isset($twitter_data['errors'])) {
				// STORE ERROR FOR DISPLAY
				$twitter_error = $twitter_data['errors'];
			    if(false === ($twitter_data = get_option($transient_name."_valid"))) {
					$debug = ($options['debug']) ? '<br /><i>Debug info:</i> ['.$twitter_error[0]['code'].'] '.$twitter_error[0]['message'].' - username: "'.$options['username'].'"' : '';
				    return __('Unable to get tweets'.$debug,'rstw');
				}
			}
		}


		if (empty($twitter_data) or count($twitter_data)<1) {
		    return __('No public tweets','rstw');
		}
		$link_target = ($options['link_target_blank']) ? ' target="_blank" ' : '';
		
		$out = '
			<ul class="really_simple_twitter_widget">';

		$i = 0;
		foreach($twitter_data as $message) {
      if (!is_array($message)) {
        continue;
      }

			// CHECK THE NUMBER OF ITEMS SHOWN
			if ($i>=$options['num']) {
				break;
			}

			$msg = $message['text'];
			
			// RECOVER ORIGINAL MESSAGE FOR RETWEETS
      if (!isset($message['retweeted_status']) ) {
        $message['retweeted_status'] = array();
      }
			if (count($message['retweeted_status'])>0) {
				$msg = 'RT @'.$message['retweeted_status']['user']['screen_name'].': '.$message['retweeted_status']['text'];

				if ($options['thumbnail_retweets']) {
					$message = $message['retweeted_status'];
				}
			}
		
			if ($msg=='') {
				continue;
			}
			if ($options['skip_text']!='' and strpos($msg, $options['skip_text'])!==false) {
				continue;
			}
			if($options['encode_utf8']) $msg = utf8_encode($msg);
				
			$out .= '<li>';
			
			// TODO: LINK
			if ($options['thumbnail'] and $message['user']['profile_image_url_https']!='') {
				$out .= '<img src="'.$message['user']['profile_image_url_https'].'" />';
			}
			if ($options['hyperlinks']) {
				if ($options['replace_link_text']!='') {
					// match protocol://address/path/file.extension?some=variable&another=asf%
					$msg = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\" ".$link_target." title=\"$1\">".$options['replace_link_text']."</a>", $msg);
					// match www.something.domain/path/file.extension?some=variable&another=asf%
					$msg = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\" ".$link_target." title=\"$1\">".$options['replace_link_text']."</a>", $msg);    
				} else {
					// match protocol://address/path/file.extension?some=variable&another=asf%
					$msg = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\" ".$link_target.">$1</a>", $msg);
					// match www.something.domain/path/file.extension?some=variable&another=asf%
					$msg = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\" ".$link_target.">$1</a>", $msg);    
				}
				// match name@address
				$msg = preg_replace('/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i',"<a href=\"mailto://$1\" class=\"twitter-link\" ".$link_target.">$1</a>", $msg);
				//NEW mach #trendingtopics
				//$msg = preg_replace('/#([\w\pL-.,:>]+)/iu', '<a href="http://twitter.com/#!/search/\1" class="twitter-link">#\1</a>', $msg);
				//NEWER mach #trendingtopics
				$msg = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://twitter.com/#!/search/%23\2" class="twitter-link" '.$link_target.'>#\2</a>', $msg);
			}
			if ($options['twitter_users'])  { 
				$msg = preg_replace('/([\.|\,|\:|\¡|\¿|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/$2\" class=\"twitter-user\" ".$link_target.">@$2</a>$3 ", $msg);
			}
          					
			$link = 'http://twitter.com/#!/'.$options['username'].'/status/'.$message['id_str'];
			if($options['linked'] == 'all')  { 
				$msg = '<a href="'.$link.'" class="twitter-link" '.$link_target.'>'.$msg.'</a>';  // Puts a link to the status of each tweet 
			} else if ($options['linked'] != '') {
				$msg = $msg . ' <a href="'.$link.'" class="twitter-link" '.$link_target.'>'.$options['linked'].'</a>'; // Puts a link to the status of each tweet
			} 
			$out .= $msg;
		
			if($options['update']) {				
				$time = strtotime($message['created_at']);
				$h_time = ( ( abs( time() - $time) ) < 86400 ) ? sprintf( __('%s ago', 'rstw'), human_time_diff( $time )) : date($options['date_format'], $time);
				$out .= '<span class="rstw_comma">,</span> <span class="twitter-timestamp" title="' . date(__('Y/m/d H:i', 'rstw'), $time) . '">' . $h_time . '</span>';
			}          
                  
			$out .= '</li>';
			$i++;
		}
		$out .= '</ul>';
	
		if ($options['link_user']) {
			$out .= '<div class="rstw_link_user"><a href="http://twitter.com/' . $options['username'] . '" '.$link_target.'>'.$options['link_user_text'].'</a></div>';
		}
		if ($options['button_follow']) {
			$out .= '
				<a href="https://twitter.com/' . $options['username'] . '" class="twitter-follow-button" data-show-count="false">'.$options['button_follow_text'].'</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script>';
		}
		return $out;
	}

} // class ReallySimpleTwitterWidget


// SHORTCODE FUNCTION
function really_simple_twitter_shortcode ($atts) {
	// e.g. [really_simple_twitter username="" consumer_key="" consumer_secret="" access_token="" access_token_secret=""]
	
	$rstw = new ReallySimpleTwitterWidget();

	$default_options = array();
	foreach ($rstw->options as $val) {
		if ($val['type']=='separator') {
			continue;
		}
		$default_options[$val['name']] = $val['default'];
	}
	$atts = shortcode_atts( $default_options , $atts );

	// CLEAN CHECKBOX BOOLEAN VALUES
	foreach ($rstw->options as $val) {
		if ($val['type']=='checkbox' and $atts[$val['name']]==="true") {
			$atts[$val['name']] = true;
		}
		if ($val['type']=='checkbox' and $atts[$val['name']]==="false") {
			$atts[$val['name']] = false;
		}
	}

	return $rstw->really_simple_twitter_messages($atts);
}


// register ReallySimpleTwitterWidget widget
add_action('widgets_init', create_function('', 'return register_widget("ReallySimpleTwitterWidget");'));

add_shortcode( 'really_simple_twitter', 'really_simple_twitter_shortcode' );
