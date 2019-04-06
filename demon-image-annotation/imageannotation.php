<?php 
/*
Plugin Name: Demon Image Annotation
Plugin URI: http://www.superwhite.cc/demon/image-annotation-plugin
Description: Allows you to add textual annotations to images by select a region of the image and then attach a textual description, the concept of annotating images with user comments.
Author: Demon
Version: 3.8
Author URI: http://www.superwhite.cc
Plugin URI: http://www.superwhite.cc/demon/image-annotation-plugin
*/

include_once('imageannotation-data.php');

//*************** Header function ***************
function dia_jquery() {
	wp_register_style( 'annotate-style', plugins_url( '/css/annotation.css', __FILE__ ));
    wp_enqueue_style( 'annotate-style' ); 

	wp_deregister_script('jquery');
	wp_register_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js');
    wp_enqueue_script( 'jquery' );
	
	wp_deregister_script('jquery-ui');
	wp_register_script('jquery-ui', '//code.jquery.com/ui/1.11.2/jquery-ui.min.js');
	wp_enqueue_script('jquery-ui');
	
	wp_deregister_script('jquery-annotate');
	wp_register_script('jquery-annotate', plugins_url( 'js/jquery.annotate.js' , __FILE__ ),array('jquery'));
	wp_enqueue_script('jquery-annotate');
	
	wp_deregister_script('jquery-annotate-config');
	wp_register_script('jquery-annotate-config', plugins_url( 'js/jquery.annotate.config.js' , __FILE__ ),array('jquery'));
	wp_enqueue_script('jquery-annotate-config');
}

function dia_init_js() {
	function ae_detect_ie()
	{
		if (isset($_SERVER['HTTP_USER_AGENT']) && 
		(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
			return true;
		else
			return false;
	}
	
	$page = 0;
	if (is_single()) {
		$page = 0;
	} else if(is_page()){
		/*if( (get_option('demon_image_annotation_pages') == '1') ) {
			$page = 2;
		} else {
			$page = 1;
		}*/	
	}
	
	$initNote = false;
	if(get_option('demon_image_annotation_display') == '0'){
		if(get_option('demon_image_annotation_comments') == '0' && comments_open()){
			$initNote = true;
		}else{
			$initNote = true;	
		}
	}
	
	if($initNote){
	?>
    <script language="javascript">
	jQuery(document).ready(function(){
		jQuery(this).initAnnotate({
				pluginPath:'<?php echo plugins_url( 'imageannotation-run' , __FILE__ ); ?>',
				container:'<?php echo get_option('demon_image_annotation_postcontainer'); ?>',
				pageOnly:<?php echo $page; ?>,
				adminOnly:<?php echo get_option('demon_image_annotation_admin'); ?>,
				autoResize:'<?php echo get_option('demon_image_annotation_autoresize'); ?>',
				numbering:'<?php echo get_option('demon_image_annotation_numbering'); ?>',
				removeImgTag:<?php echo get_option('demon_image_annotation_clickable_text'); ?>,
				mouseoverDesc:'<?php echo get_option('demon_image_annotation_mouseoverdesc'); ?>',
				maxLength:<?php echo get_option('demon_image_annotation_maxlength'); ?>,
				imgLinkOption:'<?php echo get_option('demon_image_annotation_linkoption'); ?>',
				imgLinkDesc:'<?php echo get_option('demon_image_annotation_linkdesc'); ?>',
				userLevel:<?php get_currentuserinfo(); global $user_level; echo $user_level;?>
		});
	});
	</script>
    <?php
	}
}

//*************** Comment function ***************
function getImgID() {
	global $comment;
	$commentID = $comment->comment_ID;
	
	global $wpdb;
	$table_name = $wpdb->prefix . "demon_imagenote";
	$imgIDNow = $wpdb->get_var("SELECT note_img_ID FROM ".$table_name." WHERE note_comment_id = ".(int)$commentID);
	
	// if($imgIDNow != "") {
	// 	$str = substr($imgIDNow, 4, strlen($imgIDNow));
	// 	echo "<div id=\"comment-".$str."\"><a href='#".$str."'>noted on #".$imgIDNow."</a></div>";
	// } else {
	// 	echo "&nbsp;";	
	// }
}

function dia_thumbnail() {
	if(basename($_SERVER['PHP_SELF']) != 'imageannotation-run.php'){
		global $comment;
		$commentID = $comment->comment_ID;
		
		global $wpdb;
		$table_name = $wpdb->prefix . "demon_imagenote";
		$imgIDNow = $wpdb->get_var("SELECT note_img_ID, note_ID FROM ".$table_name." WHERE note_comment_id = ".(int)$commentID);
		$imgID = $wpdb->get_var("SELECT note_ID FROM ".$table_name." WHERE note_comment_id = ".(int)$commentID);
		
		if($imgIDNow != "") {
			$str = substr($imgIDNow, 4, strlen($imgIDNow));
			if (is_admin()){
				echo "<div id=\"comment-".$str."\"><a href='admin.php?page=dia_image_notes&action=edit&note=".$imgID."'>Edit Image Note > #".$imgIDNow."</a></div>";	
			}else{
				echo "<div id=\"comment-".$str."\"><a href='#".$str."'>noted on #".$imgIDNow."</a></div>";
			}
		} else {
			echo "&nbsp;";	
		}
	}
}

function dia_thumbnail_inserter($comment_ID = 0){
	dia_thumbnail();
	$comment_content = get_comment_text();
	return $comment_content;
}

if( (get_option('demon_image_annotation_display') == '0') ) {
	if( (get_option('demon_image_annotation_thumbnail') == '0') ) {
		add_filter('comment_text', 'dia_thumbnail_inserter', 10, 4);
	}
}

add_action('wp_enqueue_scripts', 'dia_jquery');
add_action('wp_head', 'dia_init_js');

//*************** Admin function ***************
function dia_admin_init(){
	$dia_display = dia_default('demon_image_annotation_display');
	$dia_admin = dia_default('demon_image_annotation_admin');
	$dia_gravatar = dia_default('demon_image_annotation_gravatar');
	$dia_resize = dia_default('demon_image_annotation_autoresize');
	$dia_autoimageid = dia_default('demon_image_annotation_autoimageid');
	$dia_numbering = dia_default('demon_image_annotation_numbering');
	$dia_mouseoverdesc = dia_default('demon_image_annotation_mouseoverdesc');
	$dia_linkoption = dia_default('demon_image_annotation_linkoption');
	$dia_linkdesc = dia_default('demon_image_annotation_linkdesc');
	$dia_clickable_text = dia_default('demon_image_annotation_clickable_text');
	$dia_maxlength = dia_default('demon_image_annotation_maxlength');
	$dia_autoapprove = dia_default('demon_image_annotation_autoapprove');
	$dia_comments = dia_default('demon_image_annotation_comments');
	$dia_thumbnail = dia_default('demon_image_annotation_thumbnail');
	
	dia_createtable();
	dia_admin_ignore_notice();
}

function dia_default($con){
	$dia_option = get_option($con);
	if($dia_option==''){
		switch ($con) {
			case 'demon_image_annotation_autoresize':
				$dia_option='1';
				break;
			case 'demon_image_annotation_display':
				$dia_option='0';
				break;
			case 'demon_image_annotation_admin':
				$dia_option='1';
				break;
			case 'demon_image_annotation_gravatar':
				$dia_option='0';
				break;
			case 'demon_image_annotation_autoimageid':
				$dia_option='0';
				break;
			case 'demon_image_annotation_numbering':
				$dia_option='0';
				break;
			case 'demon_image_annotation_mouseoverdesc':
				$dia_option='Mouseover to load notes';
				break;
			case 'demon_image_annotation_linkoption':
				$dia_option='0';
				break;
			case 'demon_image_annotation_linkdesc':
				$dia_option='%TITLE%';
				break;
			case 'demon_image_annotation_clickable_text':
				$dia_option='0';
				break;
			case 'demon_image_annotation_maxlength':
				$dia_option='140';
				break;
			case 'demon_image_annotation_comments':
				$dia_option='0';
				break;
			case 'demon_image_annotation_thumbnail':
				$dia_option='0';
				break;
			case 'demon_image_annotation_autoapprove':
				$dia_option='1';
				break;
		} 
		update_option($con, $dia_option);	
	}
	return $dia_option;
}

function dia_admin() {
	include('imageannotation-admin.php');
}

function dia_admin_menu() {
	$imgtitle = 'Image Notes';
	
	//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	add_menu_page(__($imgtitle,'dia-menu'), __($imgtitle,'dia-menu'), 1, 'dia_image_notes', 'dia_admin', plugins_url('icon.png',__FILE__));
	
	//add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	if ( current_user_can('manage_options') ) {
		add_submenu_page('dia_image_notes', __($imgtitle,'dia-menu'), __('Image Notes','dia-menu'), 1, 'dia_image_notes');
		add_submenu_page('dia_image_notes', __('Settings','dia-menu'), __('Settings','dia-menu'), 'manage_options', 'dia_settings', 'dia_admin');
		add_submenu_page('dia_image_notes', __('Usage','dia-menu'), __('Usage','dia-menu'), 'manage_options', 'dia_usage', 'dia_admin');
		
		global $wpdb;
		$table_name = $wpdb->prefix . "demon_imagenote";
		$notesCount = 0;
		
		if(get_option('demon_image_annotation_comments') == '0'){
			$notesCount = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_name." WHERE note_approved = 0 AND note_comment_ID != 0");
		}else{
			$notesCount = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_name." WHERE note_approved = 0 AND note_comment_ID = 0");
		}
		
		global $menu;		 
		foreach($menu as $key => $value){
			if ($menu[$key][0] == $imgtitle) {
				$menu[$key][0] .= sprintf("<span class='update-plugins count-%s'><span class='plugin-count'>%s</span></span>", $notesCount, $notesCount);
			}
		}
	}
}

function dia_admin_head() {	
	echo '<link rel="stylesheet" type="text/css" href="' .plugins_url('css/admin.css', __FILE__). '">';
	
	$page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '';
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
	if (is_admin() && $page=='dia_image_notes' && $action == 'edit'){
	dia_jquery();
	?>
	<script language="javascript">
	jQuery(document).ready(function(){
		jQuery(this).initAnnotate({
				container:'#dia-admin-holder',
				numbering:1,
				pluginPath:'<?php echo plugins_url( 'imageannotation-run' , __FILE__ ); ?>',
				mouseoverDesc:'Mouseover to edit note',
				maxLength:<?php echo get_option('demon_image_annotation_maxlength'); ?>,
				imgLinkOption:1,
				userLevel:<?php get_currentuserinfo(); global $user_level; echo $user_level;?>,
				previewOnly:1
		});
	});
	</script>
    <?php
	}
}

function dia_createtable() {
	$dia_curVersion = 3.3;
	
	global $wpdb;
	$table_name = $wpdb->prefix . "demon_imagenote";
	$dia_pluginver = get_option('demon_image_annotation_pluginver');
	
	//rename old table
	if($dia_pluginver=='' || $dia_pluginver <$dia_curVersion){
		if($wpdb->get_var("show tables like '".$table_name."'") != $table_name) {
			$sql = "Rename table `demon_imagenote` to `".$table_name."`;";
			$wpdb->query($sql);
			
			$sql = "Rename table `wp_imagenote` to `".$table_name."`;";
			$wpdb->query($sql);
		}
	}
	
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	  $sql = "CREATE TABLE IF NOT EXISTS `".$table_name."` (
	  `note_ID` bigint(20) NOT NULL AUTO_INCREMENT,
	  `note_img_ID` varchar(30) NOT NULL,
	  `note_comment_ID` bigint(20) NOT NULL,
	  `note_post_ID` bigint(20) NOT NULL,
	  `note_author` varchar(100) NOT NULL,
	  `note_email` varchar(100) NOT NULL,
	  `note_top` bigint(20) NOT NULL,
	  `note_left` bigint(20) NOT NULL,
	  `note_width` bigint(20) NOT NULL,
	  `note_height` bigint(20) NOT NULL,
	  `note_text` text NOT NULL,
	  `note_text_ID` varchar(100) NOT NULL,
	  `note_editable` tinyint(1) NOT NULL,
	  `note_approved` VARCHAR(20) NOT NULL,
	  `note_date` datetime NOT NULL,
	  PRIMARY KEY (`note_ID`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=21 ;";

	  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	  dbDelta($sql);
	  
   } else {
	  if($dia_pluginver=='' || $dia_pluginver <=$dia_curVersion){
		  $sql = "ALTER TABLE `".$table_name."` CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
		  $wpdb->query($sql);
		  
		  $sql = "ALTER TABLE `".$table_name."` modify `note_img_ID` VARCHAR(30);";
		  $wpdb->query($sql);
		  
		  $sql = "ALTER TABLE `".$table_name."` 
		  modify `note_ID` bigint(20) NOT NULL AUTO_INCREMENT,
		  modify `note_comment_ID` bigint(20),
		  modify `note_top` bigint(20),
		  modify `note_left` bigint(20),
		  modify `note_width` bigint(20),
		  modify `note_height` bigint(20),
		  modify `note_text` text,
		  modify `note_approved` VARCHAR(20);";
		  $wpdb->query($sql);
		  
		  $sql = "ALTER TABLE `".$table_name."` ADD `note_post_ID` bigint(20) NOT NULL AFTER `note_comment_ID`;";
		  $wpdb->query($sql);
		  
		  $dia_pluginver = $dia_curVersion;
		  update_option('demon_image_annotation_pluginver', $dia_pluginver);
	  }
   }
}

function dia_admin_notice(){
	global $current_user;
	if ( ! get_user_meta($current_user->ID, 'dia_admin_ignore_notice') ) {
		global $pagenow;
		$paged = !empty($_GET["page"]) ? mysqli_real_escape_string($_GET["page"]) : '';
		$message = '<b>Important:</b> Please update the new version of settings and usage';
		echo '<div class="updated"><h3>demon Image annotation</h3><p>';
		if($paged == 'dia_image_notes' || $paged == 'dia_settings' || $paged == 'dia_usage'){
			printf(__($message.' | <a href="admin.php?page=dia_settings">Settings</a> | <a href="'.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'%1$s">Hide Notice</a>'), '&dia_admin_ignore_notice=0');
		}else{
			printf(__($message.' | <a href="admin.php?page=dia_settings">Settings</a> | <a href="%1$s">Hide Notice</a>'), '?dia_admin_ignore_notice=0');	
		}
		echo "</p></div>";
	}
}

function dia_admin_ignore_notice() {
	global $current_user;
	if ( isset($_GET['dia_admin_ignore_notice']) && '0' == $_GET['dia_admin_ignore_notice'] ) {
		add_user_meta($current_user->ID, 'dia_admin_ignore_notice', 'true', true);
	}
}

if(!function_exists('wp_get_current_user')) {
	include(ABSPATH . "wp-includes/pluggable.php"); 
}
if (is_admin())
{
	if ( current_user_can('manage_options') ) { 
		add_action('admin_init', 'dia_admin_init');
		add_action('admin_notices', 'dia_admin_notice');
		add_filter('comment_text', 'dia_thumbnail_inserter', 10, 4);
	}
}
add_action('admin_head', 'dia_admin_head');
add_action('admin_menu', 'dia_admin_menu');
add_action('admin_bar_menu', 'dia_admin_bar', 70);

function dia_admin_bar($admin_bar) {
	if ( current_user_can('manage_options') ) {
		
		global $wpdb;
		$table_name = $wpdb->prefix . "demon_imagenote";
		$notesCount = 0;
		
		if(get_option('demon_image_annotation_comments') == '0'){
			$notesCount = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_name." WHERE note_approved = 0 AND note_comment_ID != 0");
		}else{
			$notesCount = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_name." WHERE note_approved = 0 AND note_comment_ID = 0");
		}
		
		$args = array(
			'id' => 'dia',
			'title' => '<span class="ab-icon"><img src='.plugins_url('icon.png',__FILE__).' /></span><span class="ab-label count-'.$notesCount.'">'.$notesCount.'</span>',
			'href' => sprintf( '/wp-admin/admin.php?page=%s', 'dia_image_notes'),
			'meta' => array(
			)
		);	
		$admin_bar->add_node($args);
	}
}


//*************** auto Generate Img ID ***************
function dia_filterImg( $content ) {	
	//$matches will be an array with all images
	preg_match_all("/<img[^>]+\>/i", $content, $matches);
	//remove all images form content
	//$content = preg_replace("/<img[^>]+\>/i", "", $content);
	
	//print_r($matches);
	$img_arr = array();
	
	global $post;
	foreach($matches[0] as $img) {
		$postIDTag = 'data-post-id="'.$post->ID.'" ';
		$imgIDTag = '';
		
		preg_match_all('/(id|src)=("[^"]*")/i', $img, $img_arr);
		
		//print_r($img_arr);
		if($img_arr[1][0] == 'id' && $img_arr[2][0] !=''){
			//'ID exist';
		}else{
			if(get_option('demon_image_annotation_autoimageid') == '0'){
				if($img_arr[1][0] == 'src' && $img_arr[2][0] !=''){
					$imgsrc = substr($img_arr[2][0],1,-1);
					$imgID=md5($imgsrc);
					$imgIDTag='id="img-'.$post->ID.'-'.substr($imgID,0,10).'" ';
				}
			}
		}
		$newimg = str_replace('img ', 'img '.$imgIDTag.$postIDTag, $img);
		$content = str_replace($img, $newimg, $content);
	}
	return $content;
}

add_filter( 'the_content', 'dia_filterImg' );

//*************** Plugin function ***************
function dia_plugin_action($links) { 
  $settings_link = '<a href="admin.php?page=dia_settings">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}

function dia_plugin_row_meta( $links, $file ) {
	$plugin = plugin_basename(__FILE__);
	if ($file == $plugin) {
		return array_merge(
			$links,
			array( sprintf( '<a href="admin.php?page=%s">Settings</a>', 'dia_settings'),
				 sprintf( '<a href="admin.php?page=%s">Image Notes</a>', 'dia_image_notes'),
				 sprintf( '<a href="http://goo.gl/cMgHz4">Donate</a>'))
		);
	}
	return $links;
}

add_filter("plugin_action_links_$plugin", 'dia_plugin_action' );
add_filter( 'plugin_row_meta', 'dia_plugin_row_meta', 10, 2 );

/*add_filter( 'add_menu_classes', 'update_dia_count');
function update_dia_count( $menu ){
    $pending_count = 10; // Use your code to create this number
	
	global $wpdb;
	$table_name = $wpdb->prefix . "demon_imagenote";
	$notesCount = 0;
	
	if(get_option('demon_image_annotation_comments') == '0'){
		$notesCount = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_name." WHERE note_approved = 0 AND note_comment_ID != 0");
	}else{
		$notesCount = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_name." WHERE note_approved = 0 AND note_comment_ID = 0");
	}
	
    foreach( $menu as $menu_key => $menu_data ) 
    {
        // From the plugin URL http://example.com/wp-admin/edit.php?post_type=acf
        if( 'dia_image_notes' != $menu_data[2] )
            continue;
        $menu[$menu_key][0] = "Image Notes <span class='update-plugins count-$pending_count'><span class='plugin-count'>" . number_format_i18n($pending_count) . '</span></span>';
    }
    return $menu;
}*/
?>