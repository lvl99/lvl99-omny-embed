<?php
/*
 * LVL99 Omny Embed
 * @view Admin Index
 * @since 0.1.0
 */

if ( !defined('ABSPATH') ) exit('No direct access allowed');

// Router
$action = isset($_GET['action']) ? $_GET['action'] : 'options';

// Options
if ( $action == 'options' ) include( 'admin-options.php' );

// Put other pages here...
?>