<?php
/*
 * LVL99 Omny Embed
 * @view Admin Options
 * @since 0.1.0
 */

if ( !defined('ABSPATH') ) exit('No direct access allowed');

global $lvl99_omny_embed;
$textdomain = $lvl99_omny_embed->get_textdomain();
?>

<div class="wrap">
  <h2><?php _e('LVL99 Omny Embed', $textdomain); ?></h2>
  <div class="lvl99-plugin-page lvl99-plugin-page-no-menu">
      <form method="post" action="options.php">
        <div class="lvl99-plugin-intro"><?php _ex('Embed media hosted on Omny within your WordPress site. This plugin utilises WordPress\'s <a href="https://codex.wordpress.org/Embeds" target="_blank">Embeds API</a>.<br/><br/>Simply refer to the <b>omnyapp.com</b> URL, <b>omnycontent.com</b> URL, or use the <code>[omny]</code> shortcode.', 'Options page description', $textdomain); ?></div>

        <?php settings_fields( $textdomain ); ?>
        <?php do_settings_sections( $textdomain ); ?>
        <?php $lvl99_omny_embed->render_options( $lvl99_omny_embed->default_options ); ?>
        <?php submit_button(); ?>
      </form>
  </div>

</div>