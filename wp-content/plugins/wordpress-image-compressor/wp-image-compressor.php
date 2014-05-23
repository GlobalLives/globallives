<?php
/*
Plugin Name: WordPress Image Compressor
Plugin URI: http://www.fusionswift.com/wordpress/wordpress-image-compressor/
Description: A plugin that auto-resizes all images and converts them to JPG
Author: Tech163
Version: 0.3
Author URI: http://www.fusionswift.com/
*/

function fs_image_compress_add_options() {
	$options = array(
		'imagequality' => 75,
		'maxwidth' => 400,
		'maxheight' => 400,
	);
	add_option('fs_image_compress', $options);
	return $options;
}

function fs_image_compress_test($arg) {
	$status = get_user_meta(wp_get_current_user()->data->ID, 'fs_image_compressor', true); 
	if($status == 'disabled') {
		return $arg;
	}
	$options = get_option('fs_image_compress');
	if(!is_array($options)) {
		$options = fs_image_compress_add_options();
	}
	$ext = strtolower(substr(strrchr($arg['file'], '.'), 1));
	
	if($ext == 'jpg' || $ext == 'jpeg') {
		$im = imagecreatefromjpeg($arg['file']);
	} elseif($ext == 'png') {
		$im = imagecreatefrompng($arg['file']);
	} elseif($ext == 'gif') {
		$im = imagecreatefromgif($arg['file']);
	} else {
		return $arg;
	}
	
	$imgdata = getimagesize($arg['file']);
	$pre_width = $imgdata[0];
	$pre_height = $imgdata[1];
	
	if($pre_width <= $options['maxwidth'] && $pre_height <= $options['maxheight']) {
		$width = $pre_width;
		$height = $pre_height;
	} else {
		if(($pre_width / $pre_height) > ($options['maxwidth'] / $options['maxheight'])) {
			$width = $options['maxwidth'];
			$height = round($options['maxwidth'] * $pre_height / $pre_width);
		} else {
			$height = $options['maxheight'];
			$width = round($options['maxheight'] * $pre_width / $pre_height);
		}
	}
	
	$final_image = imagecreatetruecolor($width, $height);
	imagecopyresampled($final_image, $im, 0, 0, 0, 0, $width, $height, $pre_width, $pre_height);

	imagejpeg($final_image, substr($arg['file'], 0, strlen($arg['file']) - strlen($ext)) . 'jpg', $options['imagequality']);

	imagedestroy($im);
	imagedestroy($final_image);
	
	$out = array(
		'file' => substr($arg['file'], 0, strlen($arg['file']) - strlen($ext)) . 'jpg',
		'url' => substr($arg['url'], 0, strlen($arg['url']) - strlen($ext)) . 'jpg',
		'type' => 'image/jpeg'
	);
	
	if($out['file'] != $arg['file']) {
		unlink($arg['file']);
	}
	
	return $out;
}

function fs_image_compress_admin() {
	if(!empty($_POST['update_fs_image_compress'])) {
		$options = array();
		if(!preg_match('/^\d+$/', $_POST['imagequality'])) {
			$options['imagequality'] = 75;
		} else {
			$options['imagequality'] = $_POST['imagequality'];
		}
		if(!preg_match('/^\d+$/', $_POST['maxwidth'])) {
			$options['maxwidth'] = 400;
		} else {
			$options['maxwidth'] = $_POST['maxwidth'];
		}
		if(!preg_match('/^\d+$/', $_POST['maxheight'])) {
			$options['maxheight'] = 400;
		} else {
			$options['maxheight'] = $_POST['maxheight'];
		}
		update_option('fs_image_compress', $options);
		echo '<div id="message" class="updated fade"><p><strong>WordPress Image Compressor options has been updated.</strong></p></div>';
	} else {
		$options = get_option('fs_image_compress');
		if(!is_array($options)) {
			$options = fs_image_compress_add_options();
		}
	}
	?>
	<div class="wrap"><h2>WordPress Image Compressor</h2>
	<p>Please use the options below to configure WordPress Image Compressor.</p>
	<form action="" method="post">
	<table>
		<tr>
			<td>Image Quality</td>
			<td><input type="text" name="imagequality" value="<?php echo $options['imagequality']; ?>" /></td>
		</tr>
		<tr>
			<td>Maximum Width</td>
			<td><input type="text" name="maxwidth" value="<?php echo $options['maxwidth']; ?>" /></td>
		</tr>
		<tr>
			<td>Maximum Height</td>
			<td><input type="text" name="maxheight" value="<?php echo $options['maxheight']; ?>" /></td>
		</tr>
	</table>
	<input type="submit" name="update_fs_image_compress" value="Update Options" />
	</form>
	</div>
<?php }

function fs_image_compress_custom() {
	$userID = wp_get_current_user()->data->ID;
	
	if(!empty($_POST['fs_image_compressor_direction'])) {
		if($_POST['fs_image_compressor_direction'] == 'Enable') {
			 delete_user_meta($userID, 'fs_image_compressor');
		} else {
			add_user_meta($userID, 'fs_image_compressor', 'disabled', true);
		}
	}
	
	$status = get_user_meta($userID, 'fs_image_compressor', true); 

	if($status != 'disabled') {
		$verb = 'Disable';
		$current = '<span style="color:green">Enabled</span>';
	} else {
		$verb = 'Enable';
		$current = '<span style="color:red">Disabled</span>';
	}
	?>
	<div class="wrap"><h2>WordPress Image Compressor - Disable Compression</h2>
		<p>Status: <?php echo $current; ?></p>
		<form action="" method="post">
			<input type="hidden" name="fs_image_compressor_direction" value="<?php echo $verb; ?>" />
			<input type="submit" name="fs_image_compressor_action" value="<?php echo $verb; ?> WordPress Image Compressor" />
		</form>
	</div>
<?php }

function fs_image_compress_menu() {
	add_options_page('Image Compressor', 'Image Compressor', 8, basename(__FILE__), 'fs_image_compress_admin');
	add_submenu_page('upload.php', 'WP Image Compressor', 'Image Compressor', 8, basename(__FILE__), 'fs_image_compress_custom');

}

add_filter('wp_handle_upload', 'fs_image_compress_test');
add_action('admin_menu', 'fs_image_compress_menu');
