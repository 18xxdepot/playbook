<?php
/**
 *
 * @package Klatch
 * @version 0.0.1
 */
/*
Plugin Name: Klack Plugin
URI: https://github.com/18xxdepot/playbook/tree/master/roles/web/files/wordpress
Description: This plugin handles Klatch login and any other small settings that we need to apply.
Author: Christopher Giroir
Version: 0.0.1
Author URI: https://github.com/kelsin
*/

/* Copyright 2019 Christopher Giroir (email : kelsin@valefor.com)

   Permission is hereby granted, free of charge, to any person obtaining a copy
   of this software and associated documentation files (the "Software"), to deal
   in the Software without restriction, including without limitation the rights
   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   copies of the Software, and to permit persons to whom the Software is
   furnished to do so, subject to the following conditions:

   The above copyright notice and this permission notice shall be included in
   all copies or substantial portions of the Software.

   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
   SOFTWARE.
*/

function klatch_authenticate_username($user, $username, $pass) {
    $user = new WP_User($user['ID']);
    return $user;
}

function klatch_create_userdata($login, $email, $name) {
    [$first_name, $last_name] = explode(" ", $name, 2);

    $userdata = array(
        'user_login' => $login,
        'user_email' => $email,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'user_nicename' => $name,
        'display_name' => $name);

    return $userdata;
}

function klatch_create_or_update_user($login, $email, $name) {
    $userdata = klatch_create_userdata($login, $email, $name);

    $id = username_exists($login);
    if($id) {
        $userdata['ID'] = $user_id;
        wp_update_user($userdata);
    } else {
        $userdata['role'] = 'subscriber';
        $id = wp_insert_user($userdata);
    }

    wp_authenticate($login, NULL);
    wp_set_auth_cookie($id, false);
    do_action('wp_login', $login, new WP_User($id));
    show_admin_bar(true);
}

function klatch_user_login() {
    $klatch_header_id = "Token-Claim-Slack.id";
    $klatch_header_name = "Token-Claim-Slack.name";
    $klatch_header_email = "Token-Claim-Slack.email";

    $headers = apache_request_headers();

    $current_user = wp_get_current_user();
    if(strtolower($current_user->user_login) !== strtolower($headers[$klatch_header_id])) {
        wp_logout();
    }

    if($current_user->ID == 0 &&
       (isset($headers[$klatch_header_id]) &&
        ($headers[$klatch_header_id] != ""))) {

        // Logged into Slack but not Wordpress
        $login = strtolower($headers[$klatch_header_id]);
        $email = $headers[$klatch_header_email];
        $name = $headers[$klatch_header_name];

        klatch_create_or_update_user($login, $email, $name);
        wp_redirect($_SERVER['REQUEST_URI']);
        exit;

    } else if($current_user->ID > 0 &&
              (!isset($headers[$klatch_header_id]) ||
               ($headers[$klatch_header_id] == ""))) {

        // Logged into Wordpress but not Slack
        wp_logout();
        wp_redirect($_SERVER['REQUEST_URI']);
        exit;

    } else if($current_user->ID > 0 &&
              (isset($headers[$klatch_header_id]) &&
               ($headers[$klatch_header_id] != ""))) {

        // Logged into both
        if(strpos($_SERVER['REQUEST_URI'], 'wp-login.php')) {
            $redirect_to = str_replace('wp-login.php', '', $_SERVER['REQUEST_URI']);
            wp_redirect($redirect_to);
            exit;
        }
    }
}

function klatch_login_url () {
    return "https://login.18xxdepot.com/";
}

function klatch_logout_url () {
    return "https://login.18xxdepot.com/logout";
}

add_action('init', 'klatch_user_login', 1);
remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
add_filter('authenticate', 'klatch_authenticate_username', 10, 3);
add_filter('login_url', 'klatch_login_url');
add_filter('logout_url', 'klatch_logout_url');
