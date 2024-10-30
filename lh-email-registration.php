<?php
/**
Plugin Name: LH Email Registration
Plugin URI: https://lhero.org/plugins/lh-email-registration/
Description: Streamlines user registration in the backend by removing redundant fields and replaces usernames with email adresses
Version: 2.20
Author: Peter Shaw
Author URI: https://shawfactor.com
License: GPLv2 or later
**/

class LH_email_registration_plugin {

var $opt_name = "lh_email_registration-options";
var $hidden_field_name = 'lh_email_registration-submit_hidden';
var $use_email_field_name = 'lh_email_registration-use_email';
var $remove_url_field_name = 'lh_email_registration-remove_url';
var $message_field_name = 'lh_email_registration-message';
var $password_email_field_name = 'lh_email_registration-password_email';
var $email_override_field_name = 'lh_email_registration-email_override';
var $path = 'lh-email-registration/lh-email-registration.php';
var $namespace = 'lh_email_registration';
var $options;
var $filename;


private function is_this_plugin_network_activated(){

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( is_plugin_active_for_network( $this->path ) ) {
    // Plugin is activated

return true;

} else  {


return false;


}

}




public function reconfigure_fields_by_js() {

?>
<script>

jQuery(document).ready(function(){


<?php  if ($this->options[$this->use_email_field_name] == 1){?>


function lh_users_generateRandom() {
    var length = 8,
        charset = "abcdefghijklnopqrstuvwxyz",
        retVal = "";
    for (var i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
    }
    return retVal;
}

password = lh_users_generateRandom();

jQuery('#user_login').val(password);


jQuery('#user_login').parents('tr').hide();



<?php }  ?>




<?php  if ($this->options[$this->remove_url_field_name] == 1){?>jQuery('#url').parents('tr').remove();   <?php }  ?>


});

</script>

<?php



 
}

public function override_usernames_on_save( $user_id ) {

global $wpdb;

if (is_admin() and ($this->options[$this->use_email_field_name] == 1)){

$sql = "update ".$wpdb->users." set user_login = user_email where ID = '".$user_id."'";

$result = $wpdb->get_results($sql);

}

}



public function run_wp_loaded(){

//get rid of actions on user new form we want this as simple as possible

remove_all_actions( 'user_new_form');

}


public function wp_redirect_after_user_new( $location ){
    global $pagenow;

    if( is_admin() && 'user-new.php' == $pagenow ) {
        $user_details = get_user_by( 'email', $_REQUEST[ 'email' ] );
        $user_id = $user_details->ID;

        if( $location == 'users.php?update=add&id=' . $user_id )
            return add_query_arg( array( 'user_id' => $user_id ), 'user-edit.php' );
    }

    return $location;
}



public function plugin_menu() {
add_options_page('LH Email Registration', 'Email Registration', 'manage_options', $this->filename, array($this,"plugin_options"));

}

public function network_plugin_menu() {
add_submenu_page('settings.php', 'Email Registration', 'Email Registration', 'manage_options', $this->filename, array($this,"plugin_options")); 

}


public function plugin_options() {

if (!current_user_can('manage_options')){

wp_die( __('You do not have sufficient permissions to access this page.') );

}

if( isset($_POST[ $this->hidden_field_name ]) && $_POST[ $this->hidden_field_name ] == 'Y' ) {

        // Read their posted value


if (($_POST[$this->use_email_field_name] == "0") || ($_POST[$this->use_email_field_name] == "1")){
$options[$this->use_email_field_name] = $_POST[ $this->use_email_field_name ];
}

if (($_POST[$this->remove_url_field_name] == "0") || ($_POST[$this->remove_url_field_name] == "1")){
$options[$this->remove_url_field_name] = $_POST[ $this->remove_url_field_name ];
}

if (($_POST[$this->password_email_field_name] == "0") || ($_POST[$this->password_email_field_name] == "1")){
$options[$this->password_email_field_name] = $_POST[ $this->password_email_field_name ];
}

if (($_POST[$this->email_override_field_name] == "0") || ($_POST[$this->email_override_field_name] == "1")){
$options[$this->email_override_field_name] = $_POST[$this->email_override_field_name];
}

if ($_POST[$this->message_field_name] and ($_POST[$this->email_override_field_name] == "1")){

$options[$this->message_field_name] = stripslashes(wp_filter_post_kses(addslashes($_POST[$this->message_field_name])));

}

if (update_site_option( $this->opt_name, $options )){


$this->options = get_site_option($this->opt_name);

?>
<div class="updated"><p><strong><?php _e('User registration settings saved', 'menu-test' ); ?></strong></p></div>
<?php


}


}

// Now display the settings editing screen

include ('partials/option-settings.php');
    


}

// add a settings link next to deactive / edit
public function add_settings_link( $links, $file ) {

	if( $file == $this->filename ){
		$links[] = '<a href="'. admin_url( 'options-general.php?page=' ).$this->filename.'">Settings</a>';
	}
	return $links;
}

public function return_password_email() {

return $this->options[$this->password_email_field_name];

}

public function on_activate(){


if (!$this->options[$this->message_field_name]){

$options = $this->options;

$options[$this->message_field_name] = "foobarius this is just a place holder";

update_site_option( $this->opt_name, $options );


}



}

public function return_message(){


if ($this->options[$this->email_override_field_name] == 1){ 

return $this->options[$this->message_field_name];


} else {

return false;

}


}


public function woocommerce_new_customer_data( $data ) {

if ($this->options[$this->use_email_field_name] == 1){
	$data['user_login'] = $data['user_email'];

}
	return $data;
}


public function sanitize_user($username, $raw_username, $strict) {
    $new_username = strip_tags($raw_username);
    // Kill octets
    $new_username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $new_username);
    $new_username = preg_replace('/&.?;/', '', $new_username); // Kill entities

   // If strict, reduce to ASCII for max portability.
   if ( $strict )
        $new_username = preg_replace('|[^a-z0-9 _.\-@+]|i', '', $new_username);

    return $new_username;
}


public function __construct() {

$this->options = get_site_option($this->opt_name);
$this->filename = plugin_basename( __FILE__ );




if ($this->is_this_plugin_network_activated()){
add_action('network_admin_menu', array($this,"network_plugin_menu"));
} else {
add_action('admin_menu', array($this,"plugin_menu"));
}

add_action( 'wp_loaded', array($this,"run_wp_loaded"));
add_action('admin_head-user-new.php',array($this,"reconfigure_fields_by_js"));

add_action('profile_update', array($this,"override_usernames_on_save"), 10, 2 );


add_filter('wp_redirect', array($this,"wp_redirect_after_user_new"), 1, 1 );
add_filter('plugin_action_links', array($this,"add_settings_link"), 10, 2);

//override new users user_name
add_action('user_register', array($this,"override_usernames_on_save"), 10, 1 );

//override for woocomerce
add_filter( 'woocommerce_new_customer_data', array($this, 'woocommerce_new_customer_data'), 10, 1);

//Allow plus symbols in usernames
add_filter( 'sanitize_user', array($this, 'sanitize_user'), 10, 3);

}


}


$lh_email_registration_instance = new LH_email_registration_plugin();
register_activation_hook(__FILE__, array($lh_email_registration_instance,'on_activate') );

?>