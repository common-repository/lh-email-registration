<?php


class LH_personalised_content_plugin {

//action shortcodes in title is shortcode exists

public function the_title_filter( $title, $id = null ) {

if (has_shortcode( $title, 'lh_personalised_content' )){

$title = do_shortcode($title);

}

return $title;
}


function check_user(){

if ($GLOBALS['lh_personalised_user']){

$current_user = $GLOBALS['lh_personalised_user'];

} else {

$current_user = wp_get_current_user();


}

if ($current_user->ID){

return $current_user;

} else {


return false;


}




}

function strReplaceAssoc(array $replace, $subject) {
   return str_replace(array_keys($replace), array_values($replace), $subject);   
} 

function return_sender_user($to){


$emailArray = explode(',', str_replace(' ', '', $to));

$result = count($emailArray);

if ($result > 1){

// There is more than one recipient

return false;

} else {

if ($user = get_user_by('email', $emailArray[0])){


// The recipient is in the system

return $user;


} else {


// The recipient is not in the system

return false;


}

}


}


function wp_mail_filter( $args ) {

$subject = $args['subject'];


if ((has_shortcode( $args['message'], 'lh_personalised_content' )) or (has_shortcode( $args['subject'], 'lh_personalised_content' )) ) {


if ($user = $this->return_sender_user($args['to'])){

$GLOBALS['lh_personalised_user'] = $user;


} else {


$GLOBALS['lh_personalised_user'] = "none";


}


$args['subject'] = do_shortcode($args['subject']);


$args['message'] = do_shortcode($args['message']);




}


	$new_wp_mail = array(
		'to'          => $args['to'],
		'subject'     => $args['subject'],
		'message'     => $args['message'],
		'headers'     => $args['headers'],
		'attachments' => $args['attachments'],
	);
	
	return $new_wp_mail;
}


private function return_reset_url($user){
global $wpdb;

	// Generate something random for a password reset key.
	$key = wp_generate_password( 20, false );

	/** This action is documented in wp-login.php */
	do_action( 'retrieve_password_key', $user->user_login, $key );


	// Now insert the key, hashed, into the DB.
	if ( empty( $wp_hasher ) ) {
		require_once ABSPATH . WPINC . '/class-phpass.php';
		$wp_hasher = new PasswordHash( 8, true );
	}
	$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
	$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );



$url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');

return $url;


}



function lh_personalised_content_output($atts,$content = null) {

    // define attributes and their defaults
    extract( shortcode_atts( array (
        'role' => '', //Allow matching of roles, this functionality will be added later
        'loggedout' => ''
    ), $atts ) );


if ($current_user = $this->check_user()){


$add = $current_user->data;

//print_r($current_user);


foreach ($add as $key => $value) {

$newkey = "%".$key."%";

$newarray[$newkey] = $value;

}

$newarray['%first_name%'] = get_user_meta( $add->ID, 'first_name', true );

$newarray['%last_name%'] = get_user_meta( $add->ID, 'last_name', true );

$newarray['%description%'] = get_user_meta($add->ID, 'description', true);


//Need to add check that this is done in the email context


if (strpos($content, '%reset_link%')){

$newarray['%reset_link%'] = $this->return_reset_url($add);

}


$content = $this->strReplaceAssoc($newarray, $content);



} else {


$content = $loggedout;


}

return $content;

}



function register_shortcodes(){

add_shortcode('lh_personalised_content', array($this,"lh_personalised_content_output"));

}





function __construct() {

add_filter( 'wp_mail', array($this,"wp_mail_filter"));
add_action( 'init', array($this,"register_shortcodes"));
add_filter( 'the_title', array($this,"the_title_filter"));


}


}

$lh_personalised_content = new LH_personalised_content_plugin();


?>