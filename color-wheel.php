<?php
/*
* Plugin Name: Color Wheel Plugin
* Description: Tool for Color Wheel
* Version: 1.0.0
* Plugin URI: 
* Author: myhope1227
* 
*/ 
// Exit if accessed directly

if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'color_wheel' ) ):
class color_wheel{
	public function instance(){
		add_action( 'admin_menu', array( $this, 'plugin_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_wp_media_files' ) );
		add_action( 'wp_ajax_cw_get_background', array( $this, 'color_wheel_get_background' ) );
		add_action( 'wp_ajax_cw_get_masks', array( $this, 'color_wheel_get_masks' ) );
		add_shortcode( 'color-wheel', array( $this, 'color_wheel_frontend_render' ) );
	}

	public function plugin_admin_page() {
		add_menu_page( 'Color Wheel Option', 'Color Wheel', 'manage_options', 'color_wheel', array( $this, 'plugin_setting' ) );
	}

	function load_wp_media_files( $page ) {
		// change to the $page where you want to enqueue the script
		if( $page == 'toplevel_page_color_wheel' ) {
			// Enqueue WordPress media scripts
			wp_enqueue_media();
			wp_enqueue_style( 'color-wheel.css', plugins_url( '/css/color-wheel.css', __FILE__), array(), '1.0');
			// Enqueue custom script that will interact with wp.media
			wp_enqueue_script( 'color-wheel-js', plugins_url( '/js/color-wheel.js' , __FILE__ ), array('jquery'), '1.1' );
		}
	}

	public function plugin_setting(){
		$options = get_option( 'color_wheel_option', '' );
		$optionArray = unserialize( $options );
		if ( isset( $_REQUEST['save'] ) ){
			if (!empty($_REQUEST['color-wheel-background'])){
				$optionArray['background'] = $_REQUEST['color-wheel-background'];
			}

			if (!empty($_REQUEST['color-wheel-masks'])){
				$optionArray['masks'] = $_REQUEST['color-wheel-masks'];
			}
			$optionArray['cw-max-width'] = $_REQUEST['cw-max-width'];
			update_option( 'color_wheel_option', serialize( $optionArray ) );
		}
		$image = "";
		$image_id = $optionArray['background'];
		if ( intval( $optionArray['background'] ) > 0 ){
			$image = wp_get_attachment_image( $image_id, 'medium', false, array( 'id' => 'color-wheel-bg-preview' ) );
		}
?>
		<div class="wrap">
			<h2>Color Wheel Option</h2>
			<form action="" method="post" id="color_wheel_option">
				<p><label class="text-label" for="shortcode">Short Code</label><input type='text' value="[color-wheel]" readonly name="cw-max-width" id="shortcode"/></p>
				<p><label class="text-label" for="select-cw-background">Background Image</label>&nbsp;<input type='button' class="button-primary" value="<?php esc_attr_e( 'Select', 'color-wheel' ); ?>" id="select-cw-background"/></p>
				<div id="background-preview">
				<?php
					if (intval($image_id) > 0){
						echo $image;
					}
				?>
				</div>
				<input type="hidden" name="color-wheel-background" id="color-wheel-background" value="<?php echo esc_attr( $image_id ); ?>" class="regular-text" />
				<p><label class="text-label" for="select-cw-masks">Mask Images</label>&nbsp;<input type='button' class="button-primary" value="<?php esc_attr_e( 'Select', 'color-wheel' ); ?>" id="select-cw-masks"/></p>
				<div id="masks-preview">
		<?php
			if ( !empty($optionArray['masks'])){
				$mask_array = explode(',', $optionArray['masks']);
				foreach ($mask_array as $ind => $mask_id) {
					$mask_image = wp_get_attachment_image($mask_id, 'medium', false);
		?>
					<div class="mask-thumbnail">
					<?php echo $mask_image;?>
					</div>
		<?php			
				}
			}
		?>
				</div>
				<input type="hidden" name="color-wheel-masks" id="color-wheel-masks" value="<?php echo esc_attr( $optionArray['masks'] ); ?>" class="regular-text" />
				<p><label class="text-label" for="cw-max-width">Max Width</label>&nbsp;<input type='text' value="<?php echo $optionArray['cw-max-width'] ?>" name="cw-max-width" id="cw-max-width"/></p>
				<p class="submit"><input type="submit" name="save" value="Save" /></p>
			</form>
		</div>
<?php
	}


	function color_wheel_get_background() {
	    if(isset($_GET['id']) ){
	        $image = wp_get_attachment_image( $_GET['id'], 'medium', false, array( 'id' => 'color-wheel-bg-preview' ) );
	        $data = array(
	            'image'    => $image,
	        );
	        wp_send_json_success( $data );
	    } else {
	        wp_send_json_error();
	    }
	}

	function color_wheel_get_masks() {
	    if(isset($_GET['id']) ){
	    	$mask_ids = explode(',', $_GET['id']);
	    	$mask_images = array();
	    	foreach ($mask_ids as $key => $mask_id) {
	    		$image = wp_get_attachment_image( $mask_id, 'medium', false);
	    		$mask_images[] = $image;
	    	}
	        
	        $data = array(
	            'images'    => $mask_images,
	        );
	        wp_send_json_success( $data );
	    } else {
	        wp_send_json_error();
	    }
	}

	function color_wheel_frontend_render() {
		$options = get_option( 'color_wheel_option', '' );
		$optionArray = unserialize( $options );
	    wp_enqueue_style('bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');
	    wp_enqueue_style('bootstrap-slider', plugins_url( '/css/bootstrap-slider.min.css' , __FILE__ ));
	    wp_enqueue_style('color-wheel', plugins_url( '/css/color-wheel.css' , __FILE__ ));
	    wp_enqueue_script('bootstrap-slider-js', plugins_url( '/js/bootstrap-slider.min.js' , __FILE__ ));
	    wp_enqueue_script('color-wheel-js', plugins_url( '/js/color-wheel.js' , __FILE__ ));
	    $mask_image_ids = explode(',', $optionArray['masks']);

	    $first_mask = wp_get_attachment_image_src($mask_image_ids[0], 'full');

	    $content = "<div class='color-wheel-container'>\r\n";
		$content .= "<div class='color-wheel-wrapper' style='width:".$optionArray['cw-max-width']."px;'>\r\n";
		$content .= "<div class='background'>\r\n";
		$content .= wp_get_attachment_image($optionArray['background'], 'full')."\r\n";
		$content .= "</div>\r\n";
		$content .= "<div class='mask'>\r\n";
		$content .= "<img src='".$first_mask[0]."'>\r\n";
		//$content .= wp_get_attachment_image($mask_image_ids[0], 'full')."\r\n";
		$content .= "</div>\r\n";
		$content .= "<div class='mask-selector-wrapper'>\r\n";
		foreach ($mask_image_ids as $ind => $mask_id) {
			$mask_image_title = get_the_title($mask_id);
			$mask_image_url = wp_get_attachment_url($mask_id);
			if ($ind == 0){
				$is_active = 'active';
			}else{
				$is_active = '';
			}
			$content .= "<div class='mask-image ".$is_active."' data-img='".$mask_image_url."'>\r\n";
			$content .= $mask_image_title."\r\n";
			$content .= "</div>";
		}
		$content .= "<div class='button-wrapper'><button class='close'>Close</button></div>\r\n";
		$content .= "</div>\r\n";
		$content .= "<p>Please click the image to change the Color Chord.</p>\r\n";
		$content .= "</div>\r\n";
		$content .= "<div class='degree-slider-wrapper'>\r\n";
		$content .= "<span>0</span>\r\n";
		$content .= "<div class='slider-wrapper'>\r\n";
		$content .= "<input type='text' class='degree_slider' data-provide='slider' value='' data-slider-min='0' data-slider-max='360' data-slider-step='1' data-slider-value='0' data-slider-orientation='vertical'/>";
		$content .= '</div>';
		$content .= "<span>360</span>\r\n";
		$content .= "</div>\r\n";
		$content .= "</div>\r\n";
		 
		return $content;
	}

}

$cw_obj = new color_wheel(); 
$cw_obj->instance();

endif;