<?php
/**
 * Plugin Name: Send Custom Email
 * Plugin URI: https://github.com/Layt17
 * Description: Send Custom Email.
 * Version: 1.0
 * Author URI: https://github.com/Layt17
 *
 * @category  PHP_Version_7.4
 * @package   Send-email
 * @author    Layt17
 * @copyright https://github.com/Layt17
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * License: GPLv2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 **/

function add_settings_page() {
    add_options_page(
        'Send Custom Email page',
        'Send Custom Email',
        'manage_options',
        'send_custom_email',
        'render_settings_page'
    );
}
add_action( 'admin_menu', 'add_settings_page' );

function render_settings_page() {
    ?>
    <h2 style="margin-top: 50px;">Send Custom Emails Plugin</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields('send_custom_email_options');
        do_settings_sections( 'send_custom_email' ); ?>
        <p>Send user data to the admin</p>
        <input name="send_to_admin" style="margin-bottom: 20px;" type="checkbox" value="<?php esc_attr_e('yes'); ?>" />
        <p>Send user data to the user</p>
        <input name="send_to_user" style="margin-bottom: 20px;" type="checkbox" value="<?php esc_attr_e('yes'); ?>" />
        <input name="submit" class="button button-primary" style="margin-right: 20px; display: block;" type="submit" value="<?php esc_attr_e('Apply'); ?>" />
    </form>
    <?php
}

function send_custom_email_register_settings() {
    register_setting(
        'send_custom_email_options',
        'send_custom_email_options',
        'send_email'
    );
    add_settings_section(
        'settings',
        'Enter email addresses and choose flag to send to user data mail',
        'send_custom_email_section_text',
        'send_custom_email'
    );

    add_settings_field(
        'set_email',
        'User Email Address',
        'set_email',
        'send_custom_email',
        'settings'
    );

    add_settings_field(
        'set_admin_email',
        'Admin Email Address',
        'set_admin_email',
        'send_custom_email',
        'settings'
    );
}

add_action('admin_init', 'send_custom_email_register_settings');

function send_email() {
    $email = $_POST['set_email']['email_address'];
    $admin_email = $_POST['set_admin_email']['email_address'];
    $send_to_user = $_POST['send_to_user'];
    $send_to_admin = $_POST['send_to_admin'];
    $user = get_user_by('email', $email);
    if ($user == NULL) {
        echo '<h1>ERROR: A user with such an email address does not exist</h1>';
    }

    $client_id = get_user_meta($user->ID, 'client_id', true);
    if ($send_to_user) {
        $user_pass = bin2hex(openssl_random_pseudo_bytes(10));
        wp_set_password($user_pass, $user->ID);
        $domain = get_site_url();
        $url = $domain . '/my-account/';
        $Logo = '<p>&nbsp;</p>' .
            '<p><img src="'. $domain . '/wp-content/uploads/2022/03/upictv-mail-logo.png"' .
            'alt="upictv mail logo" width="200" height="98" /></p>';
        $message = $Logo .
            "<h2>Hi Dear Valued UPICtv Subscriber.<br /><br /></h2>" .
            "<p>Your Client ID is $client_id</p> " .
            "<p>Your Login:    $email</p> " .
            "<p>Your Password:    $user_pass</p> " .
            "<h2><a href=$url>Please login and and change your password.</a></h2> ";
        $subject = 'Welcome';
        $body = "<html><body>" . $message . "</body></html>";
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($email, $subject, $body, $headers);
    }
    if ($send_to_admin) {
        $domain = get_site_url();
        $Logo = '<p>&nbsp;</p>' .
            '<p><img src="'. $domain . '/wp-content/uploads/2022/03/upictv-mail-logo.png"' .
            'alt="upictv mail logo" width="200" height="98" /></p>';
        $message = $Logo .
        "<h2>Data about user.<br /><br /></h2>" .
        "<p>Login:    $email</p> " .
        "<p>Client ID is $client_id</p> ";
        $body = "<html><body>" . $message . "</body></html>";
        $subject = 'Data about user';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($admin_email, $subject, $body, $headers);
    }
}

function set_email() {
    $options = get_option('send_custom_email_options');
    echo "<input id='set_email' name='set_email[email_address]' style='width: 400px' type='text' value='" . esc_attr( $options['email_address'] ) . "' />";
}

function set_admin_email() {
    $options = get_option('send_custom_email_options');
    echo "<input id='set_admin_email' name='set_admin_email[email_address]' style='width: 400px' type='text' value='" . esc_attr( $options['email_address'] ) . "' />";
}
