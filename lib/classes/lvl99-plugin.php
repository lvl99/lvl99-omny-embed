<?php
/*
 * LVL99 Plugin Class
 */

if ( !defined('ABSPATH') ) exit( 'No direct access allowed' );

if ( !class_exists( 'LVL99_Plugin' ) )
{
  /*
  @class LVL99_Plugin
  */
  class LVL99_Plugin
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
    The URL to the plugin's directory.

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
    protected $textdomain = 'lvl99-plugin';

    /*
    The object with the route's information

    @property $route
    @since 0.1.0
    @protected
    @type {Array}
    */
    protected $route = array();

    /*
    PHP magic method which runs when plugin's class is created (initiates a lot of initial filters and all)

    @method __construct
    @since 0.1.0
    @returns {Void}
    */
    public function __construct( $plugin_dir = '' )
    {
      // Set the plugin dir
      $this->set_plugin_dir( $plugin_dir );

      // Actions/filters
      register_activation_hook( __FILE__, array( $this, 'activate' ) );
      register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

      add_action( 'init', array( $this, 'i18n' ) );
      add_action( 'admin_init', array( $this, 'initialise' ) );
      add_action( 'admin_menu', array( $this, 'admin_menu' ) );
      add_action( 'wp_loaded', array( $this, 'detect_route' ), 99999 );
      add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );

      add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'admin_plugin_links' ) );
    }

    /*
    Sets the plugin's directory

    @method set_plugin_dir
    @since 0.1.0
    @returns {Void}
    */
    public function set_plugin_dir ( $plugin_dir )
    {
      // ChromePhp::log( 'set_plugin_dir = ' . $plugin_dir );

      if ( file_exists($plugin_dir) )
      {
        $this->plugin_dir = $plugin_dir;
        $this->plugin_url = plugins_url( '/' . $this->textdomain );
        
      } else {
        $this->plugin_dir = dirname(__FILE__);
        $this->plugin_url = plugins_url( '/', __FILE__ );
      }

      // ChromePhp::log( array(
      //   'plugin_dir' => $this->plugin_dir,
      //   'plugin_url' => $this->plugin_url,
      // ) );
    }

    /*
    Get the folder/file from the relative plugin dir location

    @method get_plugin_dir
    @param {String} $path
    @returns {String}
    */
    public function get_plugin_dir ( $path = '' )
    {
      return trailingslashit( $this->plugin_dir ) . $path;
    }

    /*
    Get the folder/file from the relative plugin URL location

    @method get_plugin_url
    @param {String} $path
    @returns {String}
    */
    public function get_plugin_url ( $path = '' )
    {
      return trailingslashit( $this->plugin_url ) . $path;
    }

    /*
    @method get_textdomain
    @since 0.1.0
    @description Gets the text domain string
    @returns {String}
    */
    public function get_textdomain()
    {
      return $this->textdomain;
    }

    /*
    Checks if the user is an admin and can perform the operation.

    @method check_admin
    @since 0.1.0
    @protected
    @returns {Boolean}
    */
    protected function check_admin()
    {
      if ( !is_admin() )
      {
        $callee = debug_backtrace();
        error_log( _x( sprintf('%s Error: Non-admin attempted operation %s', $this->get_textdomain(), $callee[1]['function']), $this->textdomain), 'wp error_log' );
        wp_die( __( sprintf('%s Error: You must have administrator privileges to operate this functionality', $this->get_textdomain()), $this->textdomain) );
      }

      return TRUE;
    }

    /*
    Loads the plugin's text domain for translation purposes.

    @method i18n
    @since 0.1.0
    @returns {Void}
    */
    public function i18n()
    {
      load_plugin_textdomain( $this->textdomain, FALSE, basename( dirname(__FILE__) ) . '/languages' );
    }

    /*
    Runs when the plugin is activated.

    @method activate
    @since 0.1.0
    @returns {Void}
    */
    public function activate()
    {
      // Install the options
      $_plugin_installed = get_option( '_lvl99-plugin/installed', FALSE );
      $_plugin_version = get_option( '_lvl99-plugin/version', $this->version );
      if ( !$_plugin_installed )
      {
        // Set the initial options
        foreach ( $this->default_options as $name => $value )
        {
          add_option( 'lvl99-plugin/' . $name, $value );
        }
      }

      // Mark that the plugin is now installed
      update_option( '_lvl99-plugin/installed', TRUE );
      update_option( '_lvl99-plugin/version', $this->version );
    }

    /*
    Runs when the plugin is deactivated.

    @method deactivate
    @since 0.1.0
    @returns {Void}
    */
    public function deactivate()
    {
      $_plugin_installed = get_option( '_lvl99-plugin/installed', TRUE );
      $_plugin_version = get_option( '_lvl99-plugin/version', $this->version );

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
      if ( !$_plugin_version ) $_plugin_version = get_option( '_lvl99-plugin/version', $this->version );

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
    Runs when the plugin is initialised via WP.

    @method initialise
    @since 0.1.0
    @returns {Void}
    */
    public function initialise ()
    {
      $this->check_admin();

      // Load in the options (via DB or use defined defaults above)
      $this->load_options();
    }

    /*
    Enqueue the admin scripts for the plugin (only if viewing page related to plugin)

    @method admin_enqueue_scripts
    @returns {Void}
    */
    public function admin_enqueue_scripts ( $hook_suffix )
    {
      if ( stristr( $hook_suffix, $this->textdomain ) !== FALSE )
      {
        wp_enqueue_style( $this->textdomain, plugins_url( 'css/lvl99-plugin.css', __FILE__ ), FALSE, $this->version, 'all' );
        wp_enqueue_script( $this->textdomain, plugins_url( 'js/lvl99-plugin.js', __FILE__ ), TRUE, $this->version, array('jquery') );
      }
    }

    /*
    Loads all options into the class.

    @method load_options
    @since 0.1.0
    @param {Boolean} $init Whether to run within the initialising `register_setting` WP method or just load the options (see `detect_route` for implementation)
    @returns {Void}
    */
    protected function load_options ( $init = TRUE )
    {
      // Default options
      $this->default_options = array(
        /*
        enable
        */
        'enable' => array(
          'label' => _x('Enable Omny Embed shortcode', 'field label: enable', $this->textdomain),
          'help' => _x('<p>Turn on or off the Omny Embed shortcode functionality.</p>', 'field help: enable', $this->textdomain),
          'field_type' => 'radio',
          'default' => true,
          'values' => array(
            array(
              'label' => _x('Turn on', 'field value label: enable=true', $this->textdomain),
              'value' => true,
            ),
            array(
              'label' => _x('Turn off', 'field value label: enable=false', $this->textdomain),
              'value' => false,
            ),
          ),
          'sanitise_callback' => NULL,
        ),
      );

      // Apply and init the options
      $this->apply_options( $init );
    }

    /*
    Applies and initialises the options to be used

    @method apply_options
    @since 0.1.0
    @returns {Void}
    */
    protected function apply_options ( $init = TRUE )
    {
      // Get the saved options
      if ( count($this->default_options) > 0 )
      {
        foreach ( $this->default_options as $name => $option  )
        {
          // Ignore static option types: `heading`
          if ( $option['field_type'] == 'heading' ) continue;

          // Ensure `sanitise_callback` is NULL
          if ( !array_key_exists('sanitise_callback', $option) ) $option['sanitise_callback'] = NULL;

          // Get the database's value
          $this->options[$name] = get_option( $this->textdomain . '/' . $name, $option['default'] );

          // Register the setting
          if ( $init )
          {
            if ( !is_null($option['sanitise_callback']) )
            {
              register_setting( $this->textdomain, $this->textdomain . '/' . $name, $option['sanitise_callback'] );
            }
            else
            {
              register_setting( $this->textdomain, $this->textdomain . '/' . $name );
            }
          }
        }
      }
    }

    /*
    Sanitise the option's value

    @method sanitise_option
    @since 0.1.0
    @param {String} $input
    @returns {Mixed}
    */
    protected function sanitise_option ( $option, $input )
    {
      // If the sanitise_option has been set...
      if ( array_key_exists('sanitise_callback', $option) && !empty($option['sanitise_callback']) && !is_null($option['sanitise_callback']) )
      {
        return call_user_func( $option['sanitise_callback'], $input );
      }

      return $input;
    }

    /*
    sanitise the option's text value (strips HTML)

    @method sanitise_option_text
    @since 0.1.0
    @param {String} $input
    @returns {String}
    */
    public static function sanitise_option_text ( $input )
    {
      // ChromePhp::log( 'sanitise_option_text' );
      // ChromePhp::log( $input );

      return strip_tags(trim($input));
    }

    /*
    sanitise the option's value if it's a switch (on/off)

    @method sanitise_option_switch
    @since 0.1.0
    @param {String} $input
    @returns {String}
    */
    public static function sanitise_option_switch ( $input )
    {
      // ChromePhp::log( 'sanitise_option_text' );
      // ChromePhp::log( $input );
      $input = strip_tags(trim(strtolower($input)));

      return ( in_array( $input, array('on', 'off') ) ? $input : 'off' );
    }

    /*
    sanitise the option's HTML value (strips only some HTML)

    @method sanitise_option_html
    @since 0.1.0
    @param {String} $input
    @returns {String}
    */
    public static function sanitise_option_html ( $input )
    {
      // ChromePhp::log( 'sanitise_option_html' );
      // ChromePhp::log( $input );

      return strip_tags( trim($input), '<b><strong><i><em><u><del><strikethru><a><br><span><div><p><h1><h2><h3><h4><h5><h6><ul><ol><li><dl><dd><dt>' );
    }

    /*
    sanitise the option's number value

    @method sanitise_option_number
    @since 0.1.0
    @param {String} $input
    @returns {Integer}
    */
    public static function sanitise_option_number ( $input )
    {
      // ChromePhp::log( 'sanitise_option_number' );
      // ChromePhp::log( $input );

      return intval( preg_replace( '/\D+/i', '', $input ) );
    }

    /*
    sanitise the option's URL value. Namely, remove any absolute domain reference (make it relative to the current domain)

    @method sanitise_option_url
    @since 0.1.0
    @param {String} $input
    @returns {Integer}
    */
    public static function sanitise_option_url ( $input )
    {
      // ChromePhp::log( 'sanitise_option_url' );
      // ChromePhp::log( $input );

      if ( stristr($input, WP_HOME) !== FALSE )
      {
        $input = str_replace(WP_HOME, '', $input);
      }

      return strip_tags(trim($input));
    }

    /*
    sanitise the option's boolean value

    @method sanitise_option_boolean
    @since 0.1.0
    @param {String} $input
    @returns {Integer}
    */
    public static function sanitise_option_boolean ( $input )
    {
      if ( $input === 1 || strtolower($input) === 'true' || $input === TRUE || $input === '1' ) return TRUE;
      if ( $input === 0 || strtolower($input) === 'false' || $input === FALSE || $input === '0' || empty($input) ) return FALSE;
      return (bool) $input;
    }

    /*
    Sanitises SQL, primarily by looking for specific SQL commands

    @method sanitise_sql
    @since 0.1.0
    @param {String} $input The string to sanitise
    @returns {String}
    */
    protected function sanitise_sql ( $input )
    {
      $search = array(
        '/(CREATE|DROP|UPDATE|ALTER|RENAME|TRUNCATE)\s+(TABLE|TABLESPACE|DATABASE|VIEW|LOGFILE|EVENT|FUNCTION|PROCEDURE|TRIGGER)[^;]+/i',
        '/\d\s*=\s*\d/',
        '/;.*/',
      );

      $replace = array(
        '',
        '',
        '',
      );

      $output = preg_replace( $search, $replace, $input );
      return $output;
    }

    /*
    Get option field ID

    @method get_field_id
    @param {String} $field_name The name of the option
    @returns {String}
    */
    protected function get_field_id( $option_name )
    {
      // if ( array_key_exists($option_name, $this->default_options) )
      // {
        return $this->textdomain . '_' . $option_name;
      // }
      // return '';
    }

    /*
    Get option field name

    @method get_field_name
    @param {String} $field_name The name of the option
    @returns {String}
    */
    protected function get_field_name( $option_name )
    {
      if ( array_key_exists($option_name, $this->default_options) )
      {
        return $this->textdomain . '/' . $option_name;
      }
      else
      {
        return $this->textdomain . '_' . $option_name;
      }
    }

    /*
    Render options' input fields.

    @method render_options
    @since 0.1.0
    @param {Array} $options The options to render out
    @returns {Void}
    */
    protected function render_options ( $options )
    {
      $this->check_admin();

      // Check if its the plugin's settings screen
      $screen = get_current_screen();
      $is_settings_options = $screen->id == 'settings_page_' . $this->textdomain . '-options';

      if ( count($options > 0) )
      {
        foreach( $options as $name => $option )
        {
          // ID and name (changes if not settings page)
          $field_id = $is_settings_options ? $this->get_field_id($name) : $this->get_field_id($name);
          $field_name = $is_settings_options ? $this->get_field_name($name) : $this->get_field_id($name);

          // Visible field
          $is_visible = array_key_exists('visible', $option) ? $option['visible'] : TRUE;
          if ( $option['field_type'] == 'hidden' ) $is_visible = FALSE;

          // Headings and other static option types
          if ( $option['field_type'] == 'heading' )
          {
?>
          <div class="lvl99-plugin-option-heading" id="<?php echo esc_attr($field_id); ?>">
            <h3><?php echo $option['label']; ?></h3>
            <hr/>

            <?php if ( isset($option['help']) ) : ?>
            <div class="lvl99-plugin-option-help lvl99-plugin-option-help-before">
              <?php echo $option['help']; ?>
            </div>
            <?php endif; ?>

            <?php if ( isset($option['help_after']) ) : ?>
            <div class="lvl99-plugin-option-help lvl99-plugin-option-help-after">
              <?php echo $option['help_after']; ?>
            </div>
            <?php endif; ?>
          </div>
<?php
            continue;
          }

          // Singular field (e.g. single checkbox or radio)
          $is_singular = $option['field_type'] == 'checkbox' && !array_key_exists('values', $option);

          // Sortable fields
          $is_sortable = ( $option['field_type'] == 'checkbox' && array_key_exists('sortable', $option) && !$is_singular ? $option['sortable'] : FALSE );

          // Input class
          $input_class = !empty($option['input_class']) ? $option['input_class'] : 'widefat';

          // Default values for the option
          $option_value = !empty($this->options[$name]) ? $this->options[$name] : $option['default'];

          if ( $is_visible )
          {
?>
          <div class="lvl99-plugin-option <?php if ($is_sortable && $option['field_type'] != 'checkbox' && $option['field_type'] != 'radio') : ?>lvl99-draggable lvl99-sortable lvl99-sortable-handle<?php endif; ?>">

            <?php do_action( 'lvl99_plugin_option_field_footer_' . $name, '' ); ?>

            <?php if ( !$is_singular ) : ?>
            <label for="<?php echo $field_id; ?>" class="lvl99-plugin-option-label"><?php echo $option['label']; ?></label>
            <?php endif; ?>

            <?php if ( isset($option['help']) ) : ?>
            <div class="lvl99-plugin-option-help lvl99-plugin-option-help-before">
              <?php echo $option['help']; ?>
            </div>
            <?php endif; ?>

            <?php if ( !empty($option['input_before']) ) : ?>
            <span class="lvl99-plugin-option-input-before">
              <?php echo $option['input_before']; ?>
            </span>
            <?php endif; ?>

            <?php if ( $option['field_type'] == 'text' ) : ?>
              <input id="<?php echo $field_id; ?>" type="text" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" class="<?php echo esc_attr($input_class); ?>" />

            <?php elseif ( $option['field_type'] == 'number' ) : ?>
              <input id="<?php echo $field_id; ?>" type="number" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" class="<?php echo esc_attr($input_class); ?>" />

            <?php elseif ( $option['field_type'] == 'email' ) : ?>
              <input id="<?php echo $field_id; ?>" type="email" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" class="<?php echo esc_attr($input_class); ?>" />

            <?php elseif ( $option['field_type'] == 'select' ) : ?>
              <select id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" class="<?php echo esc_attr($input_class); ?>">
              <?php foreach( $option['values'] as $value ) : ?>
                <?php if ( is_array($value) ) : ?>
                <option value="<?php echo $value['value']; ?>" <?php if ( $option_value == $value['value'] ) : ?>selected="selected"<?php endif; ?>>
                <?php if ( isset($value['label']) ) : ?>
                  <?php echo $value['label']; ?>
                <?php else : ?>
                  <?php echo $value['value']; ?>
                <?php endif; ?>
                </option>
                <?php else : ?>
                <option <?php if ( $option_value == $value ) : ?>selected="selected"<?php endif; ?>><?php echo $value; ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
              </select>

            <?php elseif ( $option['field_type'] == 'radio' ) : ?>
              <ul id="<?php echo $field_id; ?>-list">
                <?php foreach( $option['values'] as $value ) : ?>
                <?php if ( is_array($value) ) : ?>
                  <li>
                    <label class="lvl99-plugin-option-value">
                      <input type="radio" name="<?php echo $field_name; ?>" value="<?php echo $value['value']; ?>" <?php if ( $option_value == $value['value'] ) : ?>checked="checked"<?php endif; ?> />
                      <div class="lvl99-plugin-option-value-label">
                        <?php if ( isset($value['label']) ) : ?>
                          <?php echo $value['label']; ?>
                        <?php else : ?>
                          <?php echo $value['value']; ?>
                        <?php endif; ?>
                        <?php if ( !empty($value['description']) ) : ?>
                        <p class="lvl99-plugin-option-value-description"><?php echo $value['description']; ?></p>
                        <?php endif; ?>
                      </div>
                    </label>
                  </li>
                <?php else : ?>
                  <li>
                    <label class="lvl99-plugin-option-value">
                      <input type="radio" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($value); ?>" <?php if ( $option_value == $value ) : ?>checked="checked"<?php endif; ?> />
                      <div class="lvl99-plugin-option-value-label">
                        <?php if ( $is_singular ) : ?>
                        <?php echo $option['label']; ?>
                        <?php else : ?>
                        <?php echo $value; ?>
                        <?php endif; ?>
                        <?php if ( !empty($value['description']) ) : ?>
                        <p class="lvl99-plugin-option-value-description"><?php echo $value['description']; ?></p>
                        <?php endif; ?>
                      </div>
                    </label>
                  </li>
                <?php endif; ?>
                <?php endforeach; ?>
              </ul>

            <?php elseif ( $option['field_type'] == 'checkbox' ) : ?>
              <ul id="<?php echo $field_id; ?>-list" class="<?php if ($is_sortable) : ?>lvl99-sortable<?php endif; ?>">
                <?php $option_values = isset($option['values']) ? $option['values'] : array($option_value); ?>

                <?php if ( $is_sortable ) :
                  // If the field is sortable, we'll need to render the options in the sorted order
                  if ( stristr($option_value, ',') !== FALSE )
                  {
                    $option_values = explode( ',', $option_value );

                    // Add the other values that the $option_values is missing (because they haven't been checked)
                    foreach( $option['values'] as $key => $value )
                    {
                      if ( !in_array($key, $option_values) )
                      {
                        array_push( $option_values, $key );
                      }
                    }

                    // Re-order the options' rendering order
                    $reordered_values = array();
                    foreach ( $option_values as $key => $value )
                    {
                      $reordered_values[$key] = $option['values'][$value];
                    }
                    $option_values = $reordered_values;

                  } ?>
                  <input type="hidden" id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" />
                <?php endif; ?>

                <?php foreach ( $option_values as $value ) : ?>
                  <?php if ( is_array($value) ) : ?>
                  <li <?php if ( $is_sortable ) : ?>class="ui-draggable ui-sortable"<?php endif; ?>>
                    <?php if ($is_sortable) : ?><span class="fa-arrows-v lvl99-sortable-handle"></span><?php endif; ?>
                    <label class="lvl99-plugin-option-value">
                      <input type="checkbox" name="<?php if ( $is_sortable ) : echo esc_attr($name).'['.esc_attr($value['value']).']'; else : echo $field_name; endif; ?>" value="true" <?php if ( stristr($option_value, $value['value'])) : ?>checked="checked"<?php endif; ?> />
                      <div class="lvl99-plugin-option-value-label">
                        <?php if ( isset($value['label']) ) : ?>
                          <?php echo $value['label']; ?>
                        <?php else : ?>
                          <?php echo $value['value']; ?>
                        <?php endif; ?>
                        <?php if ( !empty($value['description']) ) : ?>
                        <p class="lvl99-plugin-option-value-description"><?php echo $value['description']; ?></p>
                        <?php endif; ?>
                      </div>
                    </label>
                  </li>
                  <?php else : ?>
                  <li <?php if ( $is_sortable ) : ?>class="ui-draggable ui-sortable"<?php endif; ?>>
                    <?php if ($is_sortable) : ?><span class="fa-arrows-v lvl99-sortable-handle"></span><?php endif; ?>
                    <label class="lvl99-plugin-option-value">
                      <input type="checkbox" name="<?php if ( $is_sortable ) : echo esc_attr($name).'['.esc_attr($value['value']).']'; else : echo $field_name; endif; ?>" value="<?php echo ( $is_singular ? 'true' : esc_attr($value) ); ?>" <?php if ( !empty($option_value) && $option_value == $value ) : ?>checked="checked"<?php endif; ?> />
                      <div class="lvl99-plugin-option-value-label">
                        <?php if ( $is_singular ) : ?>
                        <?php echo $option['label']; ?>
                        <?php else : ?>
                        <?php echo $value; ?>
                        <?php endif; ?>
                        <?php if ( !empty($value['description']) ) : ?>
                        <p class="lvl99-plugin-option-value-description"><?php echo $value['description']; ?></p>
                        <?php endif; ?>
                      </div>
                    </label>
                  </li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ul>

            <?php elseif ( $option['field_type'] == 'image' ) : ?>
              <a href="javascript:void(0);" class="upload_file_button">
                <div class="button-primary"><?php _e( 'Upload or select image', 'lvl99' ); ?></div>
                <input type="hidden" id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" />
                <p><img src="<?php echo esc_url($option_value); ?>" style="max-width: 100%; <?php if ( $option_value == "" ) : ?>display: none<?php endif; ?>" /></p>
              </a>
              <a href="javascript:void(0);" class="remove_file_button button" <?php if ( $option_value == "" ) : ?>style="display:none"<?php endif; ?>>Remove image</a>

            <?php elseif ( $option['field_type'] == 'textarea' ) : ?>
              <textarea id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" class="<?php echo esc_attr($input_class); ?>"><?php echo $option_value; ?></textarea>

            <?php endif; ?>

            <?php if ( !empty($option['input_after']) ) : ?>
            <span class="lvl99-plugin-option-input-after">
              <?php echo $option['input_after']; ?>
            </span>
            <?php endif; ?>

            <?php if ( isset($option['help_after']) ) : ?>
            <div class="lvl99-plugin-option-help lvl99-plugin-option-help-after">
              <?php echo $option['help_after']; ?>
            </div>
            <?php endif; ?>

            <?php do_action( 'lvl99_plugin_option_field_footer_' . $name, '' ); ?>

            <?php if ( $is_sortable ) : ?>
            <script type="text/javascript">
              jQuery(document).ready( function () {
                jQuery('#<?php echo $field_id; ?>-list.lvl99-sortable').sortable({
                  items: '> li',
                  handle: '.lvl99-sortable-handle'
                });
              });
            </script>
            <?php endif; ?>
          </div>
<?php
          // Hidden fields
          } else {
            if ( $option['field_type'] == 'hidden' )
            {
?>
          <input type="hidden" id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" />
<?php
            }
          }
        } // endforeach;
      }
    }

    /*
    Replace {tags} within a string using an array's properties (and other custom functions)

    @method replace_tags
    @since 0.1.0
    @param {String} $input
    @param {Array} $tags The array with tags to replace
    @returns {String}
    */
    protected function replace_tags( $input, $tags = array() )
    {
      $output = $input;
      preg_match_all( '/\{[a-z0-9\:\_\-\/\\\]+\}/i', $input, $matches );

      if ( count($matches[0]) )
      {
        foreach( $matches[0] as $tag )
        {
          $tag_search = $tag;
          $tag_name = preg_replace( '/[\{\}]/', '', $tag );
          $tag_replace = '';

          // Get string to replace tag with
          if ( array_key_exists( $tag_name, $tags ) != FALSE )
          {
            $tag_replace = $tags[$tag_name];
          }

          // Tag has arguments
          if ( strstr($tag_name, ':') != FALSE )
          {
            $tag_split = explode( ':', $tag_name );
            $tag_name = $tag_split[0];
            $tag_replace = $tag_split[1];

            // Supported special functions (defined by {function:argument})
            switch ($tag_name)
            {
              case 'date':
                $tag_replace = date( $tag_replace );
                break;
            }
          }

          // Replace
          $output = str_replace( $tag_search, $tag_replace, $output );
        }
      }

      return $output;
    }

    /*
    Gets value of plugin's option.

    @method get_option
    @since 0.1.0
    @description
    @param {String} $name The name of the option
    @param {Mixed} $default The default value to return if it is not set
    @returns {Mixed}
    */
    public function get_option ( $name = FALSE, $default = NULL )
    {
      if ( !$name || !array_key_exists($name, $this->options) ) return $default;
      return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /*
    Filters the tags (e.g. `ABSPATH`, `WP_CONTENT_DIR` ) in the `path` option.

    @method get_option_path
    @since 0.1.0
    @returns {String}
    */
    public function get_option_path ()
    {
      $path = $this->replace_tags( $this->get_option('path'), array(
        'ABSPATH' => ABSPATH,
        'WP_CONTENT_DIR' => WP_CONTENT_DIR,
      ) );
      return $path;
    }

    /*
    Get an array of the option names

    @method get_option_names
    @since 0.1.0
    @returns {Array}
    */
    protected function get_option_names()
    {
      $option_names = array();

      foreach( $this->options as $name => $option )
      {
        array_push( $option_names, $name );
      }

      return $option_names;
    }

    /*
    Get an array of the default option values

    @method get_default_option_values
    @since 0.1.0
    @returns {Array}
    */
    protected function get_default_option_values()
    {
      $default_option_values = array();

      foreach( $this->options as $name => $option )
      {
        if ( !empty($option['default']) ) {
          $default_option_values[$name] = $option['default'];
        } else {
          $default_option_values[$name] = '';
        }
      }

      return $default_option_values;
    }

    /*
    Sets an option for the plugin

    @method set_option
    @since 0.1.0
    @param {String} $name The name of the option
    @param {Mixed} $default The default value to return if it is not set
    @returns {Mixed}
    */
    public function set_option ( $name = FALSE, $value = NULL )
    {
      if ( !$name || !array_key_exists($name, $this->options) ) return;
      update_option( 'lvl99-plugin/'.$name, $value );
      $this->options[$name] = $value;
    }

    /*
    Detects if a route was fired and then builds `$this->route` object and fires its corresponding method after the plugins have loaded.

    Routes are actions which happen before anything is rendered.

    @method detect_route
    @since 0.1.0
    @returns {Void}
    */
    public function detect_route ()
    {
      // Ignore if doesn't match this plugin's textdomain
      if ( !isset($_GET['page']) && !isset($_REQUEST[$this->textdomain]) ) return;

      // Do the detection schtuff
      if ( (isset($_REQUEST[$this->textdomain]) && !empty($_REQUEST[$this->textdomain])) || ($_GET['page'] == $this->textdomain && isset($_GET['action'])) )
      {
        $this->check_admin();
        $this->load_options(FALSE);

        // Process request params
        $_request = array(
          'get' => $_GET,
          'post' => $_POST,
        );

        $request = array();
        foreach ( $_request as $_method => $_array )
        {
          $request[$_method] = array();
          foreach( $_array as $name => $value )
          {
            if ( stristr($name, $this->textdomain.'_') != FALSE )
            {
              $request[$_method][str_replace( $this->textdomain.'_', '', strtolower($name) )] = is_string($value) ? urldecode($value) : $value;
            }
          }
        }

        // Get the method name depending on the type
        if ( isset($_REQUEST[$this->textdomain]) && !empty($_REQUEST[$this->textdomain]) )
        {
          $method_name = $_REQUEST[$this->textdomain];
        }
        else if ( $_GET['page'] == $this->textdomain && isset($_GET['action']) )
        {
          $method_name = $_GET['action'];
        }

        // Build and set the route to the class for later referral when running the route's method
        $this->route = array(
          'method' => 'route_' . preg_replace( '/[^a-z0-9_]+/i', '', $method_name ),
          'referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL,
          'request' => $request,
        );

        $this->perform_route();
      }
    }

    /*
    Performs the route's method (only if one exists)

    @method perform_route
    @since 0.1.0
    @returns {Void}
    */
    public function perform_route ()
    {
      $this->check_admin();

      if ( isset($this->route['method']) && !empty($this->route['method']) && method_exists( $this, $this->route['method'] ) )
      {
        call_user_func( array( $this, $this->route['method'] ) );
      }
      else
      {
        error_log( sprintf('%d Error: invalid route method called: %s', $this->textdomain, $this->route['method']) );
        // $this->admin_error( sprintf( __('Invalid route method was called: <strong><code>%s</code></strong>', $this->textdomain), $this->route['method'] ) );
      }
    }

    /*
    Example route method. Pass information using the `$this->route` object.

    @method route_example
    @since 0.1.0
    @returns {Void}
    */
    // public function route_example ()
    // {
    //   $this->check_admin();

    //   // Do whatever here
    //   echo '<pre>';
    //   print_r( $this->route['request'] );
    //   echo '</pre>';
    //   exit();
    // }

    /*
    Displays the notices in the admin section (used in admin view code).

    @method admin_notices
    @since 0.1.0
    @returns {Void}
    */
    public function admin_notices ()
    {
      $this->check_admin();

      if ( count($this->notices) > 0 )
      {
        foreach( $this->notices as $notice )
        {
?>
<div class="<?php echo esc_attr($notice['type']); ?>">
  <p><?php echo $notice['content']; ?></p>
</div>
<?php
        }
      }
    }

    /*
    Adds a notice to the admin section

    Example: `$this->admin_notice( sprintf( __('%s: <strong><code>%s</code></strong> was successfully deleted', 'lvl99-plugin'), $this->textdomain, $file_name ) );`

    @method admin_notice
    @since 0.1.0
    @param {String} $type The type of notice: `updated | error`
    @param {String} $message The notice's message to output to the admin messages
    @returns {Void}
    */
    public function admin_notice ( $msg, $type = 'updated' )
    {
      array_push( $this->notices, array(
        'type' => $type,
        'content' => $msg,
      ) );
    }

    /*
    Adds an error notice to the admin section

    Example: `$this->admin_error( sprintf( __('%s: Could not remove <strong><code>%s</code></strong> from the server. Please check file and folder permissions', 'lvl99-plugin'), $this->textdomain, $file_name ) );`

    @method admin_error
    @since 0.1.0
    @param {String} $message The error's message to output to the admin messages
    @returns {Void}
    */
    public function admin_error ( $msg )
    {
      array_push( $this->notices, array(
        'type' => 'error',
        'content' => $msg,
      ) );
      error_log( sprintf( __('LVL99_DBS Error: %s', 'lvl99-dbs' ), $msg ) );
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
        // '<a href="tools.php?page=lvl99-plugin&action=page_route_key">LVL99 Plugin</a>',
        // '<a href="options-general.php?page=lvl99-plugin-options">Options</a>',
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

      // // General pages
      // add_management_page(
      //   __('LVL99 Plugin', $this->textdomain),
      //   __('LVL99 Plugin', $this->textdomain),
      //   'activate_plugins',
      //   'lvl99-plugin',
      //   array( &$this, 'view_admin_index' )
      // );

      // // Options page
      // add_options_page(
      //   __('LVL99 Plugin', $this->textdomain),
      //   __('LVL99 Plugin', $this->textdomain),
      //   'activate_plugins',
      //   'lvl99-plugin-options',
      //   array( &$this, 'view_admin_index' )
      // );
    }

    /*
    Shows the admin options page.

    @method view_admin_index
    @since 0.1.0
    @returns {Void}
    */
    // public function view_admin_index ()
    // {
    //   $this->check_admin();

    //   $route = $this->route;
    //   include( trailingslashit($this->plugin_dir) . 'views/admin-index.php' );
    // }

    /*
    Formats a file size (given in byte value) with KB/MB signifier.

    @method format_file_size
    @since 0.1.0
    @param {Integer} $input The file size in bytes
    @param {Integer} $decimals The number of decimal points to round to
    @returns {String}
    */
    public function format_file_size ( $input, $decimals = 2 )
    {
      $input = intval( $input );
      if ( $input < 1000000 ) return round( $input/1000 ) . 'KB';
      if ( $input < 1000000000 ) return round( ($input/1000)/1000, $decimals ) . 'MB';
      return $input;
    }
  }
}