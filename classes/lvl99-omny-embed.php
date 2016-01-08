<?php
/*
 * LVL99 Omny Embed Plugin Class
 */

if ( !defined('ABSPATH') ) exit( 'No direct access allowed' );

if ( class_exists('LVL99_Plugin') && !class_exists( 'LVL99_Omny_Embed' ) )
{
  /*
  @class LVL99_Omny_Embed
  */
  class LVL99_Omny_Embed extends LVL99_Plugin
  {
    /*
    The version number of the plugin. Used to manage any API changes between versions.

    @property $version
    @since 0.1.0
    @public
    @type {String}
    */
    public $version = '0.1.0';

    /*
    The path to the plugin's directory.

    @property $plugin_dir
    @since 0.1.0
    @protected
    @type {String}
    */
    protected $plugin_dir;

    /*
    The url to the plugin's directory.

    @property $plugin_url
    @since 0.1.0
    @protected
    @type {String}
    */
    protected $plugin_url;

    /*
    The default options. This is set in `load_options`.

    @property $default_options
    @since 0.1.0
    @protected
    @type {Array}
    */
    protected $default_options = array();

    /*
    Holds the options for the plugin

    @property $options
    @since 0.1.0
    @protected
    @type {Array}
    */
    protected $options = array();

    /*
    The text domain for i18n

    @property $textdomain
    @since 0.1.0
    @protected
    @type {String}
    */
    protected $textdomain = 'lvl99-omny-embed';

    /*
    The object with the route's information

    @property $route
    @since 0.1.0
    @protected
    @type {Array}
    */
    protected $route = array();

    /*
    Default media info array

    @property default_omny_info
    @since 0.1.0
    @type {Array}
    */
    public static $default_omny_info = array(
      'embedUrl' => '',
      'mediaUrl' => '',
      'orgId' => '',
      'programId' => '',
      'clipId' => '',
      'width' => '100%',
      'height' => '150',
      'url' => '',
      'content' => '',
      // 'image' => '', // @TODO
      '_status' => FALSE,
      '_error' => '',
    );

    /*
    Embed handlers

    @property embed_handlers
    @since 0.1.0
    @type {Array}
    */
    public static $embed_handlers = array(
      // Handle embedding Omny URLs
      'omnyapp' => array(
        'regex' => '/https?\:\/\/(?:www\.)?omnyapp.com\/shows\/([^\/]+)\/([^\/\s]+)/i',
        'callback' => 'wp_embed_handler_omnyapp',
      ),
      // Handle embedding Omny embed URLs
      // @example: https://www.omnycontent.com/w/player/?orgId=65cc671a-66e3-45dc-98f3-a50200547d4e&amp;programId=7d59c4ff-f533-4daf-8005-a506000c2c15&amp;clipId=a8917ca7-e171-48a2-b4fb-a57200bf0b80&amp;source=whatever
      'omnycontent' => array(
        'regex' => '/https?\:\/\/(?:www\.)?omnycontent.com\/w\/player\/\?orgId=([^&]+)&(?:amp;)?programId=([^&]+)&(?:amp;)?clipId=([^&]+)&(?:amp;)?([^\s]*)/i',
        'callback' => 'wp_embed_handler_omnycontent',
      ),
    );

    /*
    PHP magic method which runs when plugin's class is created (initiates a lot of initial filters and all)

    @method __construct
    @since 0.1.0
    @returns {Void}
    */
    public function __construct( $plugin_dir = '' )
    {
      $this->set_plugin_dir( $plugin_dir );

      // Actions/filters
      register_activation_hook( __FILE__, array( $this, 'activate' ) );
      register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

      add_action( 'init', array( $this, 'i18n' ) );
      add_action( 'admin_init', array( $this, 'initialise' ) );
      add_action( 'admin_menu', array( $this, 'admin_menu' ) );
      add_action( 'wp_loaded', array( $this, 'detect_route' ), 99999 );
      add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 10, 1 );
      add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );

      add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'admin_plugin_links' ) );

      // Do as the label says when being saved
      // This should shift server-side polling to Omny to a once-off basis, rather than on view of post
      add_action( 'save_post', array( $this, 'convert_urls_omnyapp_to_shortcode_omny' ), 13, 3 );
      add_action( 'save_post', array( $this, 'convert_urls_omnyapp_to_omnycontent' ), 14, 3 );

      // Omny shortcode
      add_shortcode( 'omny', array( $this, 'shortcode_omny') );

      // Automate the handler embeds, also lets me reference the regex later on
      foreach ( self::$embed_handlers as $name => $eh )
      {
        wp_embed_register_handler( $name, $eh['regex'], array( $this, $eh['callback'] ) );
      }
    }

    /*
    Runs when the plugin is deactivated.

    @method deactivate
    @since 0.1.0
    @returns {Void}
    */
    public function deactivate()
    {
      $_plugin_installed = get_option( '_lvl99-omny-embed/installed', TRUE );
      $_plugin_version = get_option( '_lvl99-omny-embed/version', $this->version );

      if ( $_plugin_installed )
      {
        // Do anything after deactivation (depending on version)
        switch ($_plugin_version)
        {
          default:
            // Do nothing!
            break;

          case FALSE:
            break;

          // case '0.1.0':
          //  break;
        }
      }
    }

    /*
    Runs when the plugin is uninstalled/deleted.

    @method uninstall
    @since 0.1.0
    @param {String} $_plugin_version The version of the currently installed plugin
    @returns {Void}
    */
    public function uninstall( $_plugin_version = FALSE )
    {
      if ( !$_plugin_version ) $_plugin_version = get_option( '_lvl99-omny-embed/version', $this->version );

      // Do any particular operations based on which version is being uninstalled
      switch ($_plugin_version)
      {
        default:
          // Remove anything necessary during uninstall
          // break;

        case FALSE:
          break;

        // case '0.1.0':
        //  break;
      }
    }

    /*
    Enqueue the admin scripts for the plugin (only if viewing page related to plugin)

    @method admin_enqueue_scripts
    @returns {Void}
    */
    public function admin_enqueue_scripts ( $hook_suffix )
    {
      // Only include this one if viewing plugin's options
      if ( stristr( $hook_suffix, $this->textdomain ) !== FALSE )
      {
        wp_enqueue_style( $this->textdomain, $this->get_plugin_url( 'css/lvl99-omny-embed.css' ), FALSE, $this->version, 'all' );
      }

      // Always include this one in all admin areas
      wp_enqueue_script( $this->textdomain, $this->get_plugin_url( 'js/lvl99-omny-embed.js' ), array('jquery'), $this->version, TRUE );
    }

    /*
    Enqueue the scripts for the plugin on public site

    @method wp_enqueue_scripts
    @returns {Void}
    */
    public function wp_enqueue_scripts ( $hook_suffix )
    {
      // Only include this script if renderonserver is false
      if ( $this->get_option('renderonserver') == FALSE )
      {
        wp_enqueue_script( $this->textdomain, $this->get_plugin_url( 'js/lvl99-omny-embed.js' ), array('jquery'), $this->version, TRUE );
      }
    }

    /*
    Loads all options into the class.

    @method load_options
    @since 0.1.0
    @param {Boolean} $init Whether to run within the initialising `register_setting` WP method or just load the options (see `detect_route` for implementation)
    @returns {Void}
    */
    public function load_options ( $init = TRUE )
    {
      // Default options
      $this->default_options = array(
        /*
        hooksavepost
        */
        'hooksavepost' => array(
          'label' => _x('Convert <b>omnyapp.com</b> URLs on post save', 'field label: hooksavepost', $this->textdomain),
          'help' => _x('<p>When using plain <b>omnyapp.com</b> URLs, it\'ll require extra server efforts to get the media\'s information to embed. Switch this on to make it perform that extra effort only once on post\'s save, rather than per post view.</p>', 'field help: hooksavepost', $this->textdomain),
          'field_type' => 'radio',
          'default' => 'shortcode_omny',
          'values' => array(
            array(
              'label' => _x('Convert URLs to <code>[omny]</code> shortcode text <em>(recommended)</em>', 'field value label: hooksavepost=shortcode_omny', $this->textdomain),
              'value' => 'shortcode_omny',
              'description' => 'The shortcode will allow more customisation of the embedded content (such as setting width and height), however you won\'t see it within the post editor (only when previewing the post)',
            ),
            array(
              'label' => _x('Convert URLs to <b>omnycontent.com</b> Embed URLs', 'field value label: hooksavepost=embed_url', $this->textdomain),
              'value' => 'embed_url',
              'description' => 'Since this plugin supports showing rich media from <b>omnycontent.com</b>, this means that you\'ll see the embedded media within the post editor.<br/>',
            ),
            array(
              'label' => _x('Disabled', 'field value label: hooksavepost=off', $this->textdomain),
              'value' => 'off',
              // 'description' => '',
            ),
          ),
          'sanitise_callback' => array( $this, 'sanitise_option_text' ),
        ),

        /*
        renderonserver
        */
        'renderonserver' => array(
          'label' => _x('Render the embed server-side', 'field label: renderonserver', $this->textdomain),
          'help' => _x('<p>Sets embedded media to render on the server-side or via Asyncronous Javascript/AJAX. <span style="color:red">Please note this is currently disabled due to CORS issues and will default to server-side rendering.</span></p>', 'field help: renderonserver', $this->textdomain),
          'field_type' => 'radio',
          'default' => 'on',
          'values' => array(
            array(
              'label' => _x('Render on server-side', 'field value label: renderonserver=true', $this->textdomain),
              'value' => 'on',
              'description' => 'Can seem slower to load as the operation fetches the embedded media\'s data during server render.<br/>Use this if you have a problem with AJAX rendering.<br/>It\'s advised you run some kind of WP caching plugin if you set this option.',
            ),
            // @TODO when CORS issues are resolved, revisit this
            // array(
            //   'label' => _x('Render via Asyncronous Javascript/AJAX', 'field value label: renderonserver=false', $this->textdomain),
            //   'value' => 'off',
            //   'description' => 'Will fetch the embedded media\'s content through client-side, speeding up your server\'s rendering.'
            // ),
          ),
          'sanitise_callback' => array( $this, 'sanitise_option_switch' ),
        ),
      );

      $this->apply_options();
    }

    /*
    Example route method. Pass information using the `$this->route` object.

    @method route_example
    @since 0.1.0
    @returns {Void}
    */
    public function route_example ()
    {
      $this->check_admin();

      // Do whatever here
      echo '<pre>';
      print_r( $this->route['request'] );
      echo '</pre>';
      exit();
    }

    /*
    Adds extra links under the plugin's name within the plugins list.

    @method admin_plugin_links
    @since 0.1.0
    @param {Array} $links An array containing the HTML link code for the plugin's page links
    @returns {Void}
    */
    public function admin_plugin_links ( $links = array() )
    {
      $plugin_links = array(
        '<a href="options-general.php?page=lvl99-omny-embed-options">Options</a>',
      );
      return array_merge( $plugin_links, $links );
    }

    /*
    Runs when initialising admin menu. Sets up the links for the plugin's related pages.

    @method admin_menu
    @since 0.1.0
    @returns {Void}
    */
    public function admin_menu ()
    {
      $this->check_admin();

      // Options page
      add_options_page(
        __('Omny Embed', $this->textdomain),
        __('Omny Embed', $this->textdomain),
        'activate_plugins',
        'lvl99-omny-embed-options',
        array( $this, 'view_admin_index' )
      );
    }

    /*
    Shows the admin options page.

    @method view_admin_index
    @since 0.1.0
    @returns {Void}
    */
    public function view_admin_index ()
    {
      $this->check_admin();

      $route = $this->route;
      include( trailingslashit($this->plugin_dir) . 'views/admin-index.php' );
    }

    /*
    Fetch the Omny media info from a URL

    @method fetch_omny_info_from_url
    @since 0.1.0
    @param {}
    @returns {Void}
    */
    public function fetch_omny_info_from_url ( $url )
    {
      $url = trim($url);

      // The default media info array to return
      $omny_info = array_merge( array(), self::$default_omny_info );
      $omny_info['url'] = $url;

      $omny_info = apply_filters( $this->textdomain . '/pre_fetch_omny_info_from_url', $omny_info, $url );

      // Get the actual media file from the regular URL by cURLing
      $ch = curl_init( $url );

      // Set the options
      $curl_options = array(
        CURLOPT_HTTPHEADER => array('Content-type: text/plain'),
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_RETURNTRANSFER => 1,
      );
      curl_setopt_array( $ch, $curl_options );

      // Execute cURL
      $get_omny_media_url = curl_exec( $ch );
      if ( curl_error($ch) ) $omny_info['_error'] = curl_error($ch);
      curl_close( $ch );

      // Found some info to process
      if ( !empty($get_omny_media_url) )
      {
        $media_url_matches = array();
        $media_url = preg_match( '/\<meta\s+property="og\:video(?:\:secure_url)?"\s+content="([^"]+)"\s*\/?\>/i', $get_omny_media_url, $media_url_matches );

        // Successfully got the media URL
        if ( $media_url && count($media_url_matches) > 0 )
        {
          // Get Omny IDs
          $this->extract_omny_ids_from_embed_url( $media_url_matches[1], $omny_info );

          // Generate the embed/media URLs using the IDs
          $this->generate_omny_media_urls( $omny_info );

          if ( isset($omny_info['embedUrl']) && !empty($omny_info['embedUrl']) &&
               isset($omny_info['progId']) && !empty($omny_info['progId']) &&
               isset($omny_info['orgId']) && !empty($omny_info['orgId']) &&
               isset($omny_info['clipId']) && !empty($omny_info['clipId']) )
          {
            $omny_info['_status'] = TRUE;
          } else {
            $omny_info['_status'] = FALSE;
            $omny_info['_error'] = 'missing_info';
          }
        }
      }

      return apply_filters( $this->textdomain . '/post_fetch_omny_info_from_url', $omny_info, $url );
    }

    /*
    Extract orgId, programId, clipId from an omnycontent.com embed URL

    @method extract_omny_ids_from_embed_url
    @param {String} $url
    @param {Array} &$omny_info
    @returns {Array}
    */
    public function extract_omny_ids_from_embed_url ( $url, &$omny_info = array() )
    {
      // Extract the necessary details from the URL
      // @example: https://www.omnycontent.com/d/clips/65cc671a-66e3-45dc-98f3-a50200547d4e/7d59c4ff-f533-4daf-8005-a506000c2c15/028daa9c-1633-483c-afe4-a572007e0519/video.mp4?utm_source=Omny+Radio+(Facebook+Player)&amp;utm_medium=Video
      $info_bits = array();
      $match_info = preg_match( '/^https?\:\/\/(?:www\.)?omnycontent\.com\/d\/clips\/([^\/]+)\/([^\/]+)\/([^\/]+)\/([^\?]+)(.*)$/i', $url, $info_bits );

      // Successful match and extraction (strict match to # of bits)
      if ( $match_info && count($info_bits) == 6 )
      {
        $omny_info['orgId'] = $info_bits[1];
        $omny_info['programId'] = $info_bits[2];
        $omny_info['clipId'] = $info_bits[3];
        $omny_info['_status'] = TRUE;

        // $omny_info = $this->generate_omny_media_urls( $omny_info );
      } else {
        $omny_info['_status'] = FALSE;
        $omny_info['_error'] = 'incorrect_url_given: ' . $url;
      }

      return $omny_info;
    }

    /*
    Generate the omny embed and media URLs

    @method generate_omny_info_urls
    @since 0.1.0
    @param {Array}
    @returns {Array}
    */
    public function generate_omny_media_urls ( &$omny_info )
    {
      if ( !empty($omny_info['orgId']) && !empty($omny_info['programId']) && !empty($omny_info['clipId']) )
      {
        // Embed URL
        // @example: http://www.omnycontent.com/w/player/?orgId=65cc671a-66e3-45dc-98f3-a50200547d4e&amp;programId=7d59c4ff-f533-4daf-8005-a506000c2c15&amp;clipId=a8917ca7-e171-48a2-b4fb-a57200bf0b80&amp;source=whatever
        $omny_info['embedUrl'] = 'http://www.omnycontent.com/w/player/?orgId=' . $omny_info['orgId'] . '&amp;programId=' . $omny_info['programId'] . '&amp;clipId=' . $omny_info['clipId'] . '&amp;source=LVL99+Omny+Embed+(WordPress)';

        // Media URL
        // @example: http://www.omnycontent.com/d/clips/65cc671a-66e3-45dc-98f3-a50200547d4e/7d59c4ff-f533-4daf-8005-a506000c2c15/a8917ca7-e171-48a2-b4fb-a57200bf0b80/video.mp4?source=whatever
        $omny_info['mediaUrl'] = 'http://www.omnycontent.com/d/clips/' . $omny_info['orgId'] . '/' . $omny_info['programId'] . '/' . $omny_info['clipId'] . '/' . 'video.mp4?source=LVL99+Omny+Embed+(WordPress)';
      }

      return $omny_info;
    }

    /*
    Build the Omny Embed code

    @method build_omny_embed_html
    @since 0.1.0
    @param {Array} $omny_info An array of attributes as seen in $default_omny_info
    @returns {Void}
    */
    public function build_omny_embed_html ( $omny_info = array() )
    {
      $omny_info = array_merge( array(), self::$default_omny_info, $omny_info );
      $embed_html = '';

      if ( $this->get_option('renderonserver') == 'on' )
      {
        // Attempt to get information from the standard URL
        if ( empty($omny_info['embedUrl']) && !empty($omny_info['url']) )
        {
          $get_omny_info = $this->fetch_omny_info_from_url( $omny_info['url'] );

          // Merge
          if ( $get_omny_info['_status'] && empty($get_omny_info['_error']) )
          {
            $omny_info['mediaUrl']  = $get_omny_info['mediaUrl'];
            $omny_info['embedUrl']  = $get_omny_info['embedUrl'];
            $omny_info['orgId']     = $get_omny_info['orgId'];
            $omny_info['programId'] = $get_omny_info['programId'];
            $omny_info['clipId']    = $get_omny_info['clipId'];
          }
        }
      }

      // Default content
      if ( empty($omny_info['content']) && !empty($omny_info['url']) )
      {
        $omny_info['content'] = 'Listen online: <a href="'.$omny_info['url'].'" target="_blank">'.$omny_info['url'].'</a>';
      }

      // Format content
      if ( !empty($omny_info['content']) )
      {
        $omny_info['content'] = do_shortcode($omny_info['content']);
      }

      // Build the iframe embed code
      if ( !empty($omny_info['embedUrl']) )
      {
        $embed_html = '<iframe src="'.esc_url($omny_info['embedUrl']).'" width="'.$omny_info['width'].'" height="'.$omny_info['height'].'" frameborder="0" border="0">'.$omny_info['content'].'</iframe>';
      } else {
        if ( !empty($omny_info['url']) )
          $embed_html = $omny_info['content'];
      }

      return apply_filters( $this->textdomain . '/build_omny_embed_html', $embed_html, $omny_info );
    }

    /*
    Build the [omny] shortcode text

    @method build_omny_shortcode
    @since 0.1.0
    @param {Array} $atts An array of attributes as seen in $default_omny_info
    @returns {Void}
    */
    public function build_omny_shortcode ( $omny_info = array(), $content = '' )
    {
      $omny_info = array_merge( array(), self::$default_omny_info, $omny_info );
      $shortcode_text = '';

      // Attempt to get information from the standard URL
      if ( empty($omny_info['embedUrl']) && !empty($omny_info['url']) )
      {
        $get_omny_info = $this->fetch_omny_info_from_url( $omny_info['url'] );
        
        // Merge
        if ( $get_omny_info['_status'] && empty($get_omny_info['_error']) )
        {
          $omny_info['mediaUrl']  = $get_omny_info['mediaUrl'];
          $omny_info['embedUrl']  = $get_omny_info['embedUrl'];
          $omny_info['orgId']     = $get_omny_info['orgId'];
          $omny_info['programId'] = $get_omny_info['programId'];
          $omny_info['clipId']    = $get_omny_info['clipId'];
        }
      }

      // Default content
      if ( empty($omny_info['content']) && !empty($omny_info['url']) )
      {
        $omny_info['content'] = 'Listen online: <a href="'.$omny_info['url'].'" target="_blank">'.$omny_info['url'].'</a>';
      }

      // Build the shortcode text
      $shortcode_text = '[omny ';

      // Generate the publicly visible shortcode attributes
      foreach ( $omny_info as $key => $value )
      {
        if ( in_array( $key, array('orgId', 'programId', 'clipId', 'embedUrl', 'url', 'width', 'height') ) )
        {
          $shortcode_text .= ' ' . esc_attr($key) . '="'.esc_attr($value).'"';
        }
      }

      // End the initial square bracket
      $shortcode_text .= ']';

      // Add any content
      if ( !empty($omny_info['content']) )
      {
        $shortcode_text .= "\n" . $omny_info['content'] . "\n[/omny]";
      }

      return apply_filters( $this->textdomain . '/build_omny_shortcode', $shortcode_text, $omny_info );
    }

    /*
    Omnyapp.com WordPress URL Handler

    @method wp_embed_handler_omnyapp
    @since 0.1.0
    @returns {Void}
    */
    public function wp_embed_handler_omnyapp ( $matches, $attr, $url, $rawattr )
    {
      // // Build the embed code from the matched URL
      $omny_info = $this->fetch_omny_info_from_url( $url );
      $embed_html = $this->build_omny_embed_html( $omny_info );

      return apply_filters( $this->textdomain . '/embed_omnyapp', $embed_html, $matches, $attr, $url, $rawattr );
    }

    /*
    Omny WordPress Embed Handler

    @method wp_embed_handler_omnycontent
    @since 0.1.0
    @returns {Void}
    */
    public function wp_embed_handler_omnycontent ( $matches, $attr, $url, $rawattr )
    {
      // Build the embed code from the matched URL
      $embed_html = $this->build_omny_embed_html( array('embedUrl' => $url) );

      return apply_filters( $this->textdomain . '/embed_omnycontent', $embed_html, $matches, $attr, $url, $rawattr );
    }

    /*
    Convert omnyapp.com URLs to omnycontent.com embed URLs. Used within the 'save_post' action

    @method convert_urls_omnyapp_to_omnycontent
    @since 0.1.0
    @param {int} $post_id The post ID.
    @param {WP_Post} $post The post object.
    @param {bool} $update Whether this is an existing post being updated or not.
    @returns {Void}
    */
    public function convert_urls_omnyapp_to_omnycontent ( $post_id, $post, $update )
    {
      $content = wp_replace_in_html_tags( $post->post_content, array( "\n" => '<!-- wp-line-break -->' ) );

      // Find the Omny URLs within the post_content
      $omny_urls = array();
      $has_omny_urls = preg_match_all( self::$embed_handlers['omnyapp']['regex'], $post->post_content, $omny_urls );

      if ( count($omny_urls) > 0 && $this->get_option('hooksavepost') == 'embed_url' )
      {
        // Replace all the omnyapp.com URLs with [omny] shortcode text
        $content = preg_replace_callback( '|^(\s*)(https?://[^\s"]+)(\s*)$|im', array( $this, 'callback_omnyapp_to_omnycontent' ), $content );

        // Restore the linebreaks
        $content = str_replace( '<!-- wp-line-break -->', "\n", $content );

        // unhook this function so it doesn't loop infinitely
        remove_action( 'save_post', array( $this, 'convert_urls_omnyapp_to_omnycontent' ), 14, 3 );

        // update the post, which calls save_post again
        wp_update_post( array( 'ID' => $post_id, 'post_content' => $content ) );

        // re-hook this function
        add_action( 'save_post', array( $this, 'convert_urls_omnyapp_to_omnycontent' ), 14, 3 );
      }
    }

    /*
    Callback method for `preg_replace_callback` call within `convert_urls_omnyapp_to_omnycontent`

    @method callback_omnyapp_to_omnycontent
    @since 0.1.0
    @param {String} $match
    @returns {String}
    */
    public function callback_omnyapp_to_omnycontent ( $match )
    {
      $replace_text = $match[0];

      // Found omny URL
      $is_omny_url = preg_match( self::$embed_handlers['omnyapp']['regex'], $match[0] );
      if ( $is_omny_url )
      {
        $omny_info = $this->fetch_omny_info_from_url( $match[0] );

        // echo '<pre>';
        // var_dump( $omny_info );
        // echo '</pre>';
        // exit();

        $replace_text = $omny_info['embedUrl'];
      }

      return $replace_text;
    }

    /*
    Convert omnyapp.com URLs to [omny] shortcodes

    @method convert_urls_omnyapp_to_shortcode_omny
    @since 0.1.0
    @param {int} $post_id The post ID.
    @param {WP_Post} $post The post object.
    @param {bool} $update Whether this is an existing post being updated or not.
    @returns {Void}
    */
    public function convert_urls_omnyapp_to_shortcode_omny ( $post_id, $post, $update )
    {
      $content = wp_replace_in_html_tags( $post->post_content, array( "\n" => '<!-- wp-line-break -->' ) );

      // Find the Omny URLs within the post_content
      $omny_urls = array();
      $has_omny_urls = preg_match_all( self::$embed_handlers['omnyapp']['regex'], $content, $omny_urls );

      if ( count($omny_urls) > 0 && $this->get_option('hooksavepost') == 'shortcode_omny' )
      {
        // Replace all the omnyapp.com URLs with [omny] shortcode text
        $content = preg_replace_callback( '|^(\s*)(https?://[^\s"]+)(\s*)$|im', array( $this, 'callback_omnyapp_to_shortcode_omny' ), $content );

        // Restore the linebreaks
        $content = str_replace( '<!-- wp-line-break -->', "\n", $content );

        // unhook this function so it doesn't loop infinitely
        remove_action( 'save_post', array( $this, 'convert_urls_omnyapp_to_shortcode_omny' ), 13, 3 );

        // update the post, which calls save_post again
        wp_update_post( array( 'ID' => $post_id, 'post_content' => $content ) );

        // re-hook this function
        add_action( 'save_post', array( $this, 'convert_urls_omnyapp_to_shortcode_omny' ), 13, 3 );
      }
    }

    /*
    Callback method for `preg_replace_callback` call within `convert_urls_omnyapp_to_shortcode_omny`

    @method callback_omnyapp_to_shortcode_omny
    @since 0.1.0
    @param {Array} $match
    @returns {String}
    */
    public function callback_omnyapp_to_shortcode_omny ( $match )
    {
      $replace_text = $match[0];

      // Found omny URL
      $is_omny_url = preg_match( self::$embed_handlers['omnyapp']['regex'], $match[0] );
      if ( $is_omny_url )
      {
        $omny_info = $this->fetch_omny_info_from_url( $match[0] );

        // echo '<pre>';
        // var_dump( $omny_info );
        // echo '</pre>';
        // exit();

        $replace_text = $this->build_omny_shortcode( $omny_info );
      }

      return $replace_text;
    }

    /*
    [omny] Shortcode

    @method shortcode_omny
    @param {}
    @returns {String}
    */
    public function shortcode_omny ( $atts, $content = '' )
    {
      $omny_info = shortcode_atts( self::$default_omny_info, $atts );
      $omny_info['content'] = $content;

      // No embed URL? Generate one (as long as omny IDs are available)
      if ( empty($omny_info['embedUrl']) && !empty($omny_info['orgId']) && !empty($omny_info['programId']) && !empty($omny_info['clipId']) )
      {
        $this->generate_omny_media_urls( $omny_info );
      }

      // No omny IDs? Extract from the embed url
      // if ( (empty($omny_info['orgId']) || empty($omny_info['programId']) || empty($omny_info['clipId'])) && !empty($omny_info['embedUrl']) )
      // {
      //   $this->extract_omny_ids_from_embed_url( $omny_info['embedUrl'], $omny_info );
      // }

      // Error on missing info
      if ( empty($omny_info['url']) && ( empty($omny_info['embedUrl']) || ( empty($omny_info['orgId']) || empty($omny_info['programId']) || empty($omny_info['clipId']) ) ) )
      {
        error_log( 'LVL99 Omny Embed: Incorrectly formatted [omny] shortcode - missing important information and can\'t render' );
        error_log( json_encode($omny_info) );
        return '<!-- LVL99 Omny Embed: [omny] missing important information and can\'t render -->';

      } else {
        $embed_html = $this->build_omny_embed_html( $omny_info );
        return $embed_html;
      }
    }

  }
}