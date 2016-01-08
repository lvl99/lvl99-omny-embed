<?php
/*
Plugin Name: LVL99 Omny Embed
Plugin URI: http://www.github.com/lvl99/lvl99-omny-embed
Description: Easily embed media hosted on Omny within your WordPress site. Supports omnyapp.com and omnycontent.com URLs and the `[omny]` shortcode.
Author: Matt Scheurich
Author URI: http://www.lvl99.com/
Version: 0.1.0
Text Domain: lvl99-omny-embed
*/

if ( !defined('ABSPATH') ) exit( 'No direct access allowed' );

require_once('lib/classes/lvl99-plugin.php');
require_once('classes/lvl99-omny-embed.php');

// The global instance of the plugin (ew, PHP)
$lvl99_omny_embed = new LVL99_Omny_Embed( dirname(__FILE__) );
