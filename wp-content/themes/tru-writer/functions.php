<?php

# -----------------------------------------------------------------
# Theme activation
# -----------------------------------------------------------------

// run when this theme is activated
add_action('after_switch_theme', 'truwriter_setup');

function truwriter_setup () { 
  // make sure our categories are present
  
  wp_insert_term( 'In Progress', 'category' );
  wp_insert_term( 'Published', 'category' );

}


# -----------------------------------------------------------------
# Set up the table and put the napkins out
# -----------------------------------------------------------------


// we need to load the options this before the auto login so we can use the pass
add_action( 'after_setup_theme', 'truwriter_load_theme_options', 9 );

// change the name of admin menu items from "New Posts"
// -- h/t http://wordpress.stackexchange.com/questions/8427/change-order-of-custom-columns-for-edit-panels
// and of course the Codex http://codex.wordpress.org/Function_Reference/add_submenu_page

add_action( 'admin_menu', 'truwriter_change_post_label' );
add_action( 'init', 'truwriter_change_post_object' );
add_action('admin_menu', 'truwriter_drafts_menu');


function truwriter_change_post_label() {
    global $menu;
    global $submenu;
    
    $thing_name = 'Writing';
    
    $menu[5][0] = $thing_name . 's';
    $submenu['edit.php'][5][0] = 'All ' . $thing_name . 's';
    $submenu['edit.php'][10][0] = 'Add ' . $thing_name;
    $submenu['edit.php'][15][0] = $thing_name .' Categories';
    $submenu['edit.php'][16][0] = $thing_name .' Tags';
    echo '';
}
function truwriter_change_post_object() {

    $thing_name = 'Writing';

    global $wp_post_types;
    $labels = &$wp_post_types['post']->labels;
    $labels->name =  $thing_name . 's';;
    $labels->singular_name =  $thing_name;
    $labels->add_new = 'Add ' . $thing_name;
    $labels->add_new_item = 'Add ' . $thing_name;
    $labels->edit_item = 'Edit ' . $thing_name;
    $labels->new_item =  $thing_name;
    $labels->view_item = 'View ' . $thing_name;
    $labels->search_items = 'Search ' . $thing_name;
    $labels->not_found = 'No ' . $thing_name . ' found';
    $labels->not_found_in_trash = 'No ' .  $thing_name . ' found in Trash';
    $labels->all_items = 'All ' . $thing_name;
    $labels->menu_name =  $thing_name;
    $labels->name_admin_bar =  $thing_name;
}
 

function truwriter_drafts_menu() {
	add_submenu_page('edit.php', 'Writings in Progress (not submitted)', 'In Progress', 'manage_options', 'edit.php?post_status=draft&post_type=post&cat=' . get_cat_ID( 'In Progress' ) ); 
	
	add_submenu_page('edit.php', 'Writings Submitted for Approval', 'Pending Approval', 'manage_options', 'edit.php?post_status=pending&post_type=post' ); 
}

function truwriter_cookie_expiration( $expiration, $user_id, $remember ) {

	if ( current_user_can( 'edit_pages' )  ) {
		// default 14 day logout function 
    	return $remember ? $expiration : 1209600; 
    } else {
    	// shorter auto logout for guests (2 hours)
      	return $remember ? $expiration : 7200; 
    }
}

add_filter( 'auth_cookie_expiration', 'truwriter_cookie_expiration', 99, 3 );


add_filter('comment_form_defaults', 'truwriter_comment_mod');

function truwriter_comment_mod( $defaults ) {
	$defaults['title_reply'] = 'Provide Feedback';
	$defaults['logged_in_as'] = '';
	$defaults['title_reply_to'] = 'Provide Feedback for %s';
	return $defaults;
}



function truwriter_tinymce_buttons($buttons)
 {
	//Remove the more button
	$remove = 'wp_more';

	//Find the array key and then unset
	if ( ( $key = array_search($remove,$buttons) ) !== false )
		unset($buttons[$key]);

	return $buttons;
 }
add_filter('mce_buttons','truwriter_tinymce_buttons');



function truwriter_tinymce_2_buttons($buttons)
 {
	//Remove the keybord shortcut and paste text buttons
	$remove = array('wp_help','pastetext');

	return array_diff($buttons,$remove);
 }
add_filter('mce_buttons_2','truwriter_tinymce_2_buttons');


// -----  add allowable url parameter
add_filter('query_vars', 'truwriter_tqueryvars' );

function truwriter_tqueryvars( $qvars ) {
	$qvars[] = 'tk'; // token key for editing previously made stuff
	$qvars[] = 'wid'; // post id for editing
	
	return $qvars;
}   



# -----------------------------------------------------------------
# Options Panel for Admin
# -----------------------------------------------------------------

// -----  Add admin menu link for Theme Options
add_action( 'wp_before_admin_bar_render', 'truwriter_options_to_admin' );

function truwriter_options_to_admin() {
    global $wp_admin_bar;
    
    // we can add a submenu item too
    $wp_admin_bar->add_menu( array(
        'parent' => '',
        'id' => 'truwriter-options',
        'title' => __('TRU Writer Options'),
        'href' => admin_url( 'themes.php?page=truwriter-options')
    ) );
}


function truwriter_enqueue_options_scripts() {
	// Set up javascript for the theme options interface
	
	// media scripts needed for wordpress media uploaders
	wp_enqueue_media();
	
	// custom jquery for the options admin screen
	wp_register_script( 'truwriter_options_js' , get_stylesheet_directory_uri() . '/js/jquery.truwriter-options.js', null , '1.0', TRUE );
	wp_enqueue_script( 'truwriter_options_js' );
}

function truwriter_load_theme_options() {
	// load theme options Settings

	if ( file_exists( get_stylesheet_directory()  . '/class.truwriter-theme-options.php' ) ) {
		include_once( get_stylesheet_directory()  . '/class.truwriter-theme-options.php' );
	}
}


# -----------------------------------------------------------------
# login stuff
# -----------------------------------------------------------------

// Add custom logo to entry screen... because we can
// While we are at it, use CSS to hide the back to blog and retried password links
add_action( 'login_enqueue_scripts', 'my_login_logo' );

function my_login_logo() { ?>
    <style type="text/css">
        body.login div#login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/site-login-logo.png);
            padding-bottom: 30px;
        }    
	#backtoblog {display:none;}
	#nav {display:none;}
    </style>
<?php }


// Make logo link points to blog, not Wordpress.org Change Dat
// -- h/t http://www.sitepoint.com/design-a-stylized-custom-wordpress-login-screen/

add_filter( 'login_headerurl', 'login_link' );

function login_link( $url ) {
	return get_bloginfo( 'url' );
}
 
 
// Auto Login
// create a link that can automatically log in as a specific user, bypass login screen
// -- h/t  http://www.wpexplorer.com/automatic-wordpress-login-php/

add_action( 'after_setup_theme', 'truwriter_autologin' );

function truwriter_autologin() {
	
	if ($_GET['autologin'] == 'writer') {
		
		// ACCOUNT USERNAME TO LOGIN TO
		$creds['user_login'] = 'writer';
		
		// ACCOUNT PASSWORD TO USE- stored as option
		$creds['user_password'] = truwriter_option('pkey');

		$creds['remember'] = true;
		$autologin_user = wp_signon( $creds, false );
		
		
		if ( !is_wp_error($autologin_user) ) {
				wp_redirect ( site_url() . '/write' );
		}
	}
}

// remove admin tool bar for non-admins, remove access to dashboard
// -- h/t http://www.wpbeginner.com/wp-tutorials/how-to-disable-wordpress-admin-bar-for-all-users-except-administrators/

add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar() {
	if ( !current_user_can('edit_others_posts')  ) {
	  show_admin_bar(false);
	}

}

# -----------------------------------------------------------------
# For the Writing Form
# -----------------------------------------------------------------

add_action('wp_enqueue_scripts', 'add_truwriter_scripts');

function add_truwriter_scripts() {	 
 
 	if ( is_page('write') ) { // use on just our form page
    
		 // add media scripts if we are on our maker page and not an admin
		 // after http://wordpress.stackexchange.com/a/116489/14945
    	 
		if (! is_admin() ) wp_enqueue_media();
		
		// Build in tag auto complete script
   		wp_enqueue_script( 'suggest' );

		// custom jquery for the uploader on the form
		wp_register_script( 'jquery.writer' , get_stylesheet_directory_uri() . '/js/jquery.writer.js', 'suggest' , '1.23', TRUE );
		wp_enqueue_script( 'jquery.writer' );
		
		// add scripts for fancybox (used for help) 
		//-- h/t http://code.tutsplus.com/tutorials/add-a-responsive-lightbox-to-your-wordpress-theme--wp-28100
		wp_enqueue_script( 'fancybox', get_stylesheet_directory_uri() . '/includes/lightbox/js/jquery.fancybox.pack.js', array( 'jquery' ), false, true );
    	wp_enqueue_script( 'lightbox', get_stylesheet_directory_uri() . '/includes/lightbox/js/lightbox.js', array( 'fancybox' ), '1.1',
    null , '1.0', TRUE );
    
    	wp_enqueue_style( 'lightbox-style', get_stylesheet_directory_uri() . '/includes/lightbox/css/jquery.fancybox.css' );

	} elseif ( is_single() ) {
		// single writings, give is the jQuery for edit link stuff
		
		wp_register_script( 'jquery.editlink' , get_stylesheet_directory_uri() . '/js/jquery.editlink.js', null , '0.1', TRUE );
		wp_enqueue_script( 'jquery.editlink' );
	}
}

function oembed_filter( $str ) {
	// filters text for URLs WP can autoembed, and returns with proper embed code
	// lifted somewhat from oembed-in-comments plugin
	global $wp_embed;

	// Automatic discovery would be a security risk, safety first
	add_filter( 'embed_oembed_discover', '__return_false', 999 );
	$str = $wp_embed->autoembed( $str );

	// ...but don't break your posts if you use it
	remove_filter( 'embed_oembed_discover', '__return_false', 999 );

	return $str;
}

// set the default upload image size to "large' cause medium is puny
// ----- h/t http://stackoverflow.com/a/20019915/2418186

function my_default_image_size () {
    return 'large'; 
}

add_filter( 'pre_option_image_default_size', 'my_default_image_size' );

# -----------------------------------------------------------------
# User Edit Link
# -----------------------------------------------------------------

// add meta box to show edit link on posts
function truwriter_editlink_meta_box() {

	add_meta_box(
		're_editlink',
		'Author Re-Edit Link',
		'truwriter_editlink_meta_box_callback',
		'post',
		'side'
	);
}
add_action( 'add_meta_boxes', 'truwriter_editlink_meta_box' );

// content for edit link meta box
function truwriter_editlink_meta_box_callback( $post ) {

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'truwriter_editlink_meta_box_data', 'truwriter_editlink_meta_box_nonce' );


	// get edit key
	$ekey = get_post_meta( $post->ID, 'wEditKey', 1 );
	
	// make an edit link if it does not exist
	if ( !$ekey ) {
		truwriter_make_edit_link( $post->ID );
		$ekey = get_post_meta( $post->ID, 'wEditKey', 1 );
	}

	echo '<label for="writing_edit_link">';
	_e( 'Click to highlight, then copy', 'radcliffe' );
	echo '</label> ';
	echo '<input style="width:100%; type="text" id="writing_edit_link" name="writing_edit_link" value="' . get_bloginfo('url') . '/write/?wid=' . $post->ID . '&tk=' . $ekey  . '"  onclick="this.select();" />';
	
	}


function truwriter_make_edit_link( $post_id, $post_title='') {
	// add a token for editing
	// ----h/t via http://www.sitepoint.com/generating-one-time-use-urls/
	
	if ( $post_title == '')   $post_title = get_the_title($post_id );
	update_post_meta( $post_id, 'wEditKey',sha1( uniqid( $post_title, true ) ) );
}

function truwriter_mail_edit_link ( $wid, $mode = 'request' )  {

	// for post id = $wid
	// requested means by click of button vs  one sent when published.
	
	// look up the stored edit key 
	$wEditKey = get_post_meta( $wid, 'wEditKey', 1 );

	// While in there get the email address
	$wEmail = get_post_meta( $wid, 'wEmail', 1 );

	// Link for the written thing
	$wLink = get_permalink( $wid);
	
	// who gets mail? They do.
	$to_recipient = $wEmail;

	$wTitle = htmlspecialchars_decode( get_the_title( $wid ) );
	
	$edit_instructions = '<p>To be able to edit this work, just follow these steps</p> <ol><li><a href="' . get_bloginfo('url') . '/desk">Activate the writer</a>. This will send you to the blank writing form. Ignore it. </li><li>Now, use this link to access your work<br />&nbsp; &nbsp; <a href="' . get_bloginfo('url') . '/write/?wid=' . $wid . '&tk=' . $wEditKey  . '">' . get_bloginfo('url') . '/write/?wid=' . $wid . '&tk=' . $wEditKey  . '</a></li></ol><p>That link should open your last edited version in the writer so you can make any modifications to it. Save this email as a way to always return to edit your writing.</p>';
	
	if ( $mode == 'request' ) {
		// subject and message for a edut link request
		$subject ='Edit Link for "' . $wTitle . '"';
		
		$message = '<p>A request was made hopefully by you for the link to edit the content of <a href="' . $wLink . '">' . $wTitle . '</a> published on ' . get_bloginfo( 'name')  . ' at <strong>' . $wLink . '</strong>. (If this was not done by you, just ignore this message)</p>' . $edit_instructions;
		
	} else {
		// message for a just been published notification
		$subject = '"' . $wTitle . '" ' . 'is now published';
		
		$message = 'Your writing <a href="' . $wLink . '">' . $wTitle . '</a> has been published on ' . get_bloginfo( 'name')  . ' and is now available at <strong><a href="' . $wLink . '">' . $wLink . '</a></strong>.</p>' . $edit_instructions;
	}

	// turn on HTML mail
	add_filter( 'wp_mail_content_type', 'set_html_content_type' );

	// mail it!
	$mail_sent = wp_mail( $to_recipient, $subject, $message );

	// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
	remove_filter( 'wp_mail_content_type', 'set_html_content_type' );	

	if ($mode == 'request') {
		if 	($mail_sent) {
			echo 'Instructions sent via email';
		} else {
			echo 'Uh op mail not sent';
		}
	}
}

function truwriter_publish ( $post ) {
	 truwriter_mail_edit_link ( $post->ID, 'published' );
    // Send edit link when published  
}

add_action(  'publish_post',  'truwriter_publish', 10, 2 );

# -----------------------------------------------------------------
# Creative Commons Licensing
# -----------------------------------------------------------------


function cc_license_html ($license, $author='', $yr='') {
	// outputs the proper license
	// $license is abbeviation. author is from post metadata, yr is from post date
	
	if ( !isset( $license ) or $license == '' ) return '';
	
	if ($license == 'copyright') {
		// boo copyrighted! sigh, slap on the copyright text. Blarg.
		return 'This work by ' . $author . ' is &copy;' . $yr . ' All Rights Reserved';
	} 
	
	// names of creative commons licenses
	$commons = array (
		'by' => 'Attribution',
		'by-sa' => 'Attribution-ShareAlike',
		'by-nd' => 'Attribution-NoDerivs',
		'by-nc' => 'Attribution-NonCommercial',
		'by-nc-sa' => 'Attribution-NonCommercial-ShareAlike',
		'by-nc-nd' => 'Attribution-NonCommercial-NoDerivs',
	);
	
	// do we have an author?
	$credit = ($author == '') ? '' : ' by ' . $author;
	
	return '<a rel="license" href="http://creativecommons.org/licenses/' . $license . '/4.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/' . $license . '/4.0/88x31.png" /></a><br />This work' . $credit . ' is licensed under a <a rel="license" href="http://creativecommons.org/licenses/' . $license . '/4.0/">Creative Commons ' . $commons[$license] . ' 4.0 International License</a>.';            
}


function cc_license_select_options ($curr) {
	// output for select form options for use in forms

	$str = '';
	
	// to restrict the list of options, comment out lines you do not want
	// to make available (HACK HACK HACK)
	$licenses = array (
		'by' => 'Creative Commons Attribution',
		'by-sa' => 'Creative Commons Attribution-ShareAlike',
		'by-nd' => 'Creative Commons Attribution-NoDerivs',
		'by-nc' => 'Creative Commons Attribution-NonCommercial',
		'by-nc-sa' => 'Creative Commons Attribution-NonCommercial-ShareAlike',
		'by-nc-nd' => 'Creative Commons Attribution-NonCommercial-NoDerivs',
		'copyright' => 'Copyrighted All Rights Reserved',
	);
	
	foreach ($licenses as $key => $value) {
		// build the striing of select options
		$selected = ( $key == $curr ) ? ' selected' : '';
		$str .= '<option value="' . $key . '"' . $selected  . '>' . $value . '</option>';
	}
	
	return ($str);
}

# -----------------------------------------------------------------
# Useful spanners and wrenches
# -----------------------------------------------------------------


// function to get the caption for an attachment (stored as post_excerpt)
// -- h/t http://wordpress.stackexchange.com/a/73894/14945
function get_attachment_caption_by_id( $post_id ) {
    $the_attachment = get_post( $post_id );
    return ( $the_attachment->post_excerpt ); 
}

function reading_time_check() {
// checks for installation of Estimated Reading Time plugin

	if ( shortcode_exists( 'est_time' ) ) {
		// yep, golden
		return ('The Estimated Post Reading Time plugin is installed. No further action necessary.');
	} else {
		// nope, send them off to set it up
		return ('The <a href="http://wordpress.org/extend/plugins/estimated-post-reading-time/" target="_blank">Estimated Post Reading Time plugin</a> is NOT installed.  <a href="' . admin_url( 'plugins.php') . '">Do it now!</a>');
	}
}

function truwriter_author_user_check( $expected_user = 'writer' ) {
// checks for the proper authoring account set up

	$auser = get_user_by( 'login', $expected_user );
		
	
	if ( !$auser) {
		return ('Authoring account not set up. You need to <a href="' . admin_url( 'user-new.php') . '">create a user account</a> with login name <strong>' . $expected_user . '</strong> with a role of <strong>Author</strong>. Make a killer strong password; no one uses it.');
	} elseif ( $auser->roles[0] != 'author') {
	
		// for multisite lets check if user is not member of blog
		if ( is_multisite() AND !is_user_member_of_blog( $auser->ID, get_current_blog_id() ) )  {
			return ('The user account <strong>' . $expected_user . '</strong> is set up but has not been added as a user to this site (and needs to have a role of <strong>Author</strong>). You can <a href="' . admin_url( 'user-edit.php?user_id=' . $auser->ID ) . '">edit it now</a>'); 
			
		} else {
		
			return ('The user account <strong>' . $expected_user . '</strong> is set up but needs to have it\'s role set to <strong>Author</strong>. You can <a href="' . admin_url( 'user-edit.php?user_id=' . $auser->ID ) . '">edit it now</a>'); 
		}
		
		
		
	} else {
		return ('The authoring account <strong>' . $expected_user . '</strong> is correctly set up.');
	}
}


function truwriter_check_user( $allowed='writer' ) {
	// checks if the current logged in user is who we expect
	global $current_user;
    get_currentuserinfo();
	
	// return check of match
	return ( $current_user->user_login == $allowed );
}


function twitternameify( $str ) {
	// convert any "@" in astring to a linked twitter name
	// ----- h/t http://snipe.net/2009/09/php-twitter-clickable-links/
	$str = preg_replace( "/@(\w+)/", "<a href=\"https://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $str );

	return $str;
}

function splot_the_author() {
	// utility to put in template to show status of special logins
	// nothing is printed if there is not current user, 
	//   echos (1) if logged in user is the special account
	//   echos (0) if logged in user is the another account
	//   in both cases the code is linked to a logout script

	
	if ( is_user_logged_in() and !current_user_can( 'edit_others_posts' ) )  {
	
		$user_code = ( truwriter_check_user() ) ? 1 : 0;
		echo '<a href="' . wp_logout_url( site_url() ). '">(' . $user_code  .')</a>';
	}
}

function set_html_content_type() {
	// from http://codex.wordpress.org/Function_Reference/wp_mail
	return 'text/html';
}

function br2nl ( $string )
// from http://php.net/manual/en/function.nl2br.php#115182
{
    return preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $string);
}

?>