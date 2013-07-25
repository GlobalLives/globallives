<?php

/**
 * Handles all tasks mostly common to any Gravity Forms Add-On, including third party ones
 *
 * Facilitates:
 *   - Initialization
 *   - Enforcing Gravity Forms minimum version requirement
 *   - Creating settings pages (generic and form specific)
 *   - Permissions and the Members plugin integration
 *   - Some of the most important GF events (i.e. validation and after submission)
 *   - Script enqueuing so that it can take care of the no-conflict mode integration
 *   - Creating form feeds
 *   - Standardization of UI elements and styles
 *   - Clean uninstall
 *
 * @package GFAddOn
 * @author Rocketgenius
 */
abstract class GFAddOn {

    /**
     * @var string Version number of the Add-On
     */
    protected $_version;
    /**
     * @var string Gravity Forms minimum version requirement
     */
    protected $_min_gravityforms_version;
    /**
     * @var string URL-friendly identifier used for form settings, add-on settings, text domain localization...
     */
    protected $_slug;
    /**
     * @var string Relative path to the plugin from the plugins folder. Example "gravityformsmailchimp/mailchimp.php"
     */
    protected $_path;
    /**
     * @var string Full path the the plugin. Example: __FILE__
     */
    protected $_full_path;
    /**
     * @var string URL to the Gravity Forms website. Example: "http://www.gravityforms.com" OR affiliate link.
     */
    protected $_url;
    /**
     * @var string Title of the plugin to be used on the settings page, form settings and plugins page. Example: "Gravity Forms MailChimp Add-On"
     */
    protected $_title;
    /**
     * @var string Short version of the plugin title to be used on menus and other places where a less verbose string is useful. Example: "MailChimp"
     */
    protected $_short_title;
    /**
     * @var array Members plugin integration. List of capabilities to add to roles.
     */
    protected $_capabilities = array();

    private $_saved_settings = array();
    
    /**
    * @var array Stores a copy of setting fields that failed validation; only populated after validate_settings() has been called.
    */
    private $_setting_field_errors = array();

    // ------------ Permissions -----------
    /**
     * @var string|array A string or an array of capabilities or roles that have access to the settings page
     */
    protected $_capabilities_settings_page = array();
    /**
     * @var string|array A string or an array of capabilities or roles that have access to the form settings
     */
    protected $_capabilities_form_settings = array();
    /**
     * @var string|array A string or an array of capabilities or roles that can uninstall the plugin
     */
    protected $_capabilities_uninstall = array();

    // ------------ RG Autoupgrade -----------

    /**
     * @var bool Used by Rocketgenius plugins to activate auto-upgrade.
     */
    protected $_enable_rg_autoupgrade = false;

    // ------------ Private -----------

    private $_no_conflict_scripts = array();
    private $_no_conflict_styles = array();

    /**
     * Class constructor which hooks the instance into the WordPress init action
     */
    function __construct() {
        add_action('init', array($this, 'init'));
        if ($this->_enable_rg_autoupgrade) {
            require_once("class-gf-auto-upgrade.php");
            $rg_upgrade = new GFAutoUpgrade($this->_slug, $this->_version, $this->_min_gravityforms_version, $this->_title, $this->_full_path, $this->_path, $this->_url);
        }

    }

    /**
     * Plugin starting point. Handles hooks and loading of language files.
     */
    public function init() {

        load_plugin_textdomain($this->_slug, FALSE, $this->_slug . '/languages');

        if(RG_CURRENT_PAGE == 'admin-ajax.php') {

            //If gravity forms is supported, initialize AJAX
            if($this->is_gravityforms_supported()){
                $this->init_ajax();
            }

        }
        else if (is_admin()) {

            $this->init_admin();

        }
        else {

            if($this->is_gravityforms_supported()){
                $this->init_frontend();
            }
        }

        if($this->is_gravityforms_supported()){

            //Entry meta
            $entry_meta_config = $this->get_entry_meta(null, null);
            if (false !== $entry_meta_config) {
                add_filter('gform_entry_meta', array($this, 'get_entry_meta'), 10, 2);
            }

        }
    }

    /**
    * Override this function to add initialization code (i.e. hooks) for the admin site (WP dashboard)
    */
    protected function init_admin(){

        // enqueues admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'), 10, 0);

        // message enforcing min version of Gravity Forms
        if (isset($this->_min_gravityforms_version) && RG_CURRENT_PAGE == "plugins.php" && false === $this->_enable_rg_autoupgrade)
            add_action('after_plugin_row_' . $this->_path, array($this, 'plugin_row'));

        // STOP HERE IF GRAVITY FORMS IS NOT SUPPORTED
        if (isset($this->_min_gravityforms_version) && !$this->is_gravityforms_supported($this->_min_gravityforms_version))
            return;

        $this->setup();

        // Add form settings only when there are form settings fields configured or form_settings() method is implemented
        if (self::has_form_settings_page()) {
            $this->form_settings_init();
        }

        // Add addon settings page only when there are addon settings fields configured or settings_page() method is implemented
        if (self::has_plugin_settings_page()) {
            if ($this->current_user_can_any($this->_capabilities_settings_page)) {
                $this->plugin_settings_init();
            }
        }

        // Members plugin integration
        if (self::has_members_plugin())
            add_filter('members_get_capabilities', array($this, 'members_get_capabilities'));

        // Results page
        $results_page_config = $this->get_results_page_config();
        if (false === empty($results_page_config)) {
            $this->results_page_init($results_page_config);
        }

        // No conflict scripts
        add_filter('gform_noconflict_scripts', array($this, 'register_noconflict_scripts'));
        add_filter('gform_noconflict_styles', array($this, 'register_noconflict_styles'));

    }

    /**
    * Override this function to add initialization code (i.e. hooks) for the public (customer facing) site
    */
    protected function init_frontend(){
        add_action("gform_enqueue_scripts", array($this, "enqueue_scripts"), 10, 2);
    }

    /**
    * Override this function to add AJAX hooks or to add initialization code when an AJAX request is being performed
    */
    protected function init_ajax(){

    }


    //--------------  Setup  ---------------

    protected function setup() {

        //upgrading add-on
        $installed_version = get_option("gravityformsaddon_" . $this->_slug . "_version");
        if ($installed_version != $this->_version)
            $this->upgrade($installed_version);

        update_option("gravityformsaddon_" . $this->_slug . "_version", $this->_version);
    }

    protected function upgrade($previous_version) {
        return;
    }


    //--------------  Script enqueuing  ---------------

    /**
    * Override this function to provide a list of styles to be enqueued.
    * See styles() for an example of the format expected to be returned.
    */
    protected function styles(){
        return array(
            array(  "handle" => "gaddon_form_settings_css",
                    "src" => $this->get_gfaddon_base_url() . "/css/gaddon_form_settings.css",
                    "version" => GFCommon::$version,
                    "enqueue" => array(
                        array("admin_page" => array("form_settings", "plugin_settings") )
                    )
            )
        );
    }

    /**
    * Override this function to provide a list of scripts to be enqueued.
    * Following is an example of the array that is expected to be returned by this function:
    * <code>
    *    array(
    *        array(  "handle" => "maskedinput",
    *                "src" => GFCommon::get_base_url() . "/js/jquery.maskedinput-1.3.min.js",
    *                "version" => GFCommon::$version,
    *                "deps" => array("jquery"),
    *                "in_footer" => false,
    *
    *                //Determines where the script will be enqueued. The script will be enqueued if any of the conditions match
    *                "enqueue" => array(
    *                                    //admin_page - Specified one or more pages (known pages) where the script is supposed to be enqueued. When this setting is specified, scripts will only be enqueued in those pages.
    *                                    //To enqueue scripts in the front end (public website), simply don't define this setting
    *                                    array("admin_page" => array("form_settings", "plugin_settings") ),
    *
    *                                    //tab - Specifies a form settings or plugin settings tab in which the script is supposed to be enqueued. If none is specified, the script will be enqueued in any of the form settings or plugin_settings page
    *                                    array("tab" => "signature"),
    *
    *                                    //query - Specifies a set of query string ($_GET) values. If all specified query string values match the current requested page, the script will be enqueued
    *                                    array("query" => "page=gf_edit_forms&view=settings&id=_notempty_")
    *
    *                                    //post - Specifies a set of post ($_POST) values. If all specified posted values match the current request, the script will be enqueued
    *                                    array("post" => "posted_field=val")
    *
    *                                    )
    *            ),
    *        array(
    *            "handle" => "super_signature_script",
    *            "src" => $this->get_base_url() . "/super_signature/ss.js",
    *            "version" => $this->_version,
    *            "deps" => array("jquery"),
    *            "callback" => array($this, "localize_scripts"),
    *            "strings" => array(
    *                               // Accessible in JavaScript using the global variable "[script handle]_strings"
    *                               "stringKey1" => __("The string", "gravityforms"),
    *                               "stringKey2" => __("Another string.", "gravityforms")
    *                               )
    *            "enqueue" => array(
    *                                //field_types - Specifies one or more field types that requires this script. The script will only be enqueued if the current form has a field of any of the specified field types. Only applies when a current form is available.
    *                                array("field_types" => array("signature"))
    *                                )
    *        )
    *  );
    *
    * </code>
    */
    protected function scripts(){
        return array(
            array(
                'handle' => 'gform_form_admin',
                'enqueue' => array( array( "admin_page" => array("form_settings") ) )
                )
                ,
            array(
                'handle' => 'gform_gravityforms',
                'enqueue' => array( array( "admin_page" => array("form_settings") ) )
                )
        );
    }

    /**
    * Target of admin_enqueue_scripts and gform_enqueue_scripts hooks.
    * Not intended to be overridden by child classes.
    * In order to enqueue scripts and styles, override the scripts() and styles() functions
     *
    * @ignore
    */
    public function enqueue_scripts($form="", $is_ajax=false){

        if(empty($form))
            $form = $this->get_current_form();

        //Enqueueing scripts
        $scripts = $this->scripts();
        foreach($scripts as $script){
            if($this->_can_enqueue_script($script["enqueue"], $form, $is_ajax)){
                $this->add_no_conflict_scripts(array($script["handle"]));
                $src = isset($script["src"]) ? $script["src"] : false;
                $deps = isset($script["deps"]) ? $script["deps"] : array();
                $version =  isset($script["version"]) ? $script["version"] : false;
                $in_footer =  isset($script["in_footer"]) ? $script["in_footer"] : false;
                wp_enqueue_script($script["handle"],$src, $deps, $version, $in_footer);
                if(isset($script["strings"]))
                    wp_localize_script($script["handle"], $script["handle"]."_strings", $script["strings"]);
                if(isset($script["callback"]) && is_callable($script["callback"])){
                    $args = compact("form", "is_ajax");
                    call_user_func_array($script["callback"], $args);
                }
            }
        }

        //Enqueueing styles
        $styles =  $this->styles();
        foreach($styles as $style){
            if($this->_can_enqueue_script($style["enqueue"], $form, $is_ajax)){
                $this->add_no_conflict_styles(array($style["handle"]));
                $src = isset($style["src"]) ? $style["src"] : false;
                $deps = isset($style["deps"]) ? $style["deps"] : array();
                $version =  isset($style["version"]) ? $style["version"] : false;
                $media =  isset($style["media"]) ? $style["media"] : "all";
                wp_enqueue_style($style["handle"], $src, $deps, $version, $media);
            }
        }
    }

    /**
     * Adds scripts to the list of white-listed no conflict scripts.
     *
     * @param $scripts
     */
    private function add_no_conflict_scripts($scripts) {
        $this->_no_conflict_scripts = array_merge($scripts, $this->_no_conflict_scripts);

    }

    /**
     * Adds styles to the list of white-listed no conflict styles.
     *
     * @param $styles
     */
    private function add_no_conflict_styles($styles) {
        $this->_no_conflict_styles = array_merge($styles, $this->_no_conflict_styles);
    }

    private function _can_enqueue_script($enqueue_conditions, $form, $is_ajax){
        if(empty($enqueue_conditions))
            return false;

        foreach($enqueue_conditions as $condition){
            if(is_callable($condition)){
                return call_user_func($condition, $form, $is_ajax);
            }
            else{
                $query_matches = isset($condition["query"]) ? $this->_request_condition_matches($_GET, $condition["query"]) : true;
                $post_matches  = isset($condition["post"]) ? $this->_request_condition_matches($_POST, $condition["query"]) : true;
                $admin_page_matches = isset($condition["admin_page"]) ? $this->_page_condition_matches($condition["admin_page"], rgar($condition,"tab")) : true;
                $field_type_matches = isset($condition["field_type"]) ? $this->_field_condition_matches($condition["field_type"], $form) : true;

                //Scripts will only be enqueued in any admin page if "admin_page", "query" or "post" variable is set.
                //Scripts will only be enqueued in the front end if "admin_page" is not set.
                $site_matches = ( isset($condition["admin_page"]) && is_admin() ) || ( !isset($condition["admin_page"]) && !is_admin() ) || ( isset($condition["query"]) || isset($condition["post"]) ) ;

                if($query_matches && $post_matches && $admin_page_matches && $field_type_matches && $site_matches){
                    return true;
                }
            }
        }
        return false;
    }

    private function _request_condition_matches($request, $query){
        parse_str($query, $query_array);
        foreach($query_array as $key => $value){

            switch ($value){
                case "_notempty_" :
                    if(rgempty($key, $request))
                        return false;
                    break;
                case "_empty_" :
                    if(!rgempty($key, $request))
                        return false;
                    break;
                default :
                    if (rgar($request, $key) != $value)
                        return false;
                    break;
            }

        }
        return true;
    }

    private function _page_condition_matches($pages, $tab){
        if(!is_array($pages))
            $pages = array($pages);

        foreach($pages as $page){
            switch($page){
                case "form_editor" :
                    if($this->is_form_editor())
                        return true;

                    break;

                case "form_settings" :
                    if($this->is_form_settings($tab))
                        return true;

                    break;

                case "plugin_settings" :
                    if($this->is_plugin_settings($tab))
                        return true;

                    break;

                case "entry_view" :
                    if($this->is_entry_view())
                        return true;

                    break;

                case "entry_detail" :
                    if($this->is_entry_edit())
                        return true;

                    break;
            }
        }
        return false;

    }

    private function _field_condition_matches($field_types, $form){
        if(!is_array($field_types))
            $field_types = array($field_types);

        $fields = GFCommon::get_fields_by_type($form, $field_types);
        if(count($fields) > 0)
            return true;

        return false;
    }


    //AC: I think we can remove all of these script registering funtions -------------------
    /**
     * Registers a script with WordPress and adds it to the list of white-listed Gravity Forms no conflict scripts.
     *
     * @param string $handle Script name
     * @param string $src Script url
     * @param array $deps (optional) Array of script names on which this script depends
     * @param string|bool $ver (optional) Script version (used for cache busting), set to null to disable
     * @param bool $in_footer (optional) Whether to enqueue the script before </head> or before </body>
     * @return null
     */
    protected function register_script($handle, $src, $deps = array(), $ver = false, $in_footer = false) {
        wp_register_script($handle, $src, $deps, $ver, $in_footer);
        $this->add_no_conflict_scripts(array($handle));
    }

    /**
     * Registers a CSS file with WordPress and adds it to the list of white-listed Gravity Forms no conflict styles.
     *
     * @param string $handle Name of the stylesheet.
     * @param string|bool $src Path to the stylesheet from the root directory of WordPress. Example: '/css/mystyle.css'.
     * @param array $deps Array of handles of any stylesheet that this stylesheet depends on.
     *  (Stylesheets that must be loaded before this stylesheet.) Pass an empty array if there are no dependencies.
     * @param string|bool $ver String specifying the stylesheet version number. Set to null to disable.
     *  Used to ensure that the correct version is sent to the client regardless of caching.
     * @param string $media The media for which this stylesheet has been defined.
     */
    protected function register_style($handle, $src, $deps = array(), $ver = false, $media = 'all') {
        wp_register_style($handle, $src, $deps, $ver, $media);
        $this->add_no_conflict_styles(array($handle));
    }

    /**
     * Target for the gform_noconflict_scripts filter. Adds scripts to the list of white-listed no conflict scripts.
     *
     * Not intended to be overridden or called directed by Add-Ons.
     *
     * @ignore
     *
     * @param array $scripts Array of scripts to be white-listed
     * @return array
     */
    public function register_noconflict_scripts($scripts) {
        //registering scripts with Gravity Forms so that they get enqueued when running in no-conflict mode
        return array_merge($scripts, $this->_no_conflict_scripts);
    }

    /**
     * Target for the gform_noconflict_styles filter. Adds styles to the list of white-listed no conflict scripts.
     *
     * Not intended to be overridden or called directed by Add-Ons.
     *
     * @ignore
     *
     * @param array $styles Array of styles to be white-listed
     * @return array
     */
    public function register_noconflict_styles($styles) {
        //registering styles with Gravity Forms so that they get enqueued when running in no-conflict mode
        return array_merge($styles, $this->_no_conflict_styles);
    }
    //--------------------------------------------------------------------------------------


    //--------------  Entry meta  --------------------------------------

    /**
     * Override this method to activate and configure entry meta.
     *
     *
     * @param array $entry_meta An array of entry meta already registered with the gform_entry_meta filter.
     * @param int $form_id The form id
     *
     * @return array
     */
    protected function get_entry_meta($entry_meta, $form_id) {
        return false;
    }


    //--------------  Results page  --------------------------------------
    /**
     * Returns the configuration for the results page. By default this is not activated.
     * To activate the results page override this function and return an array with the configuration data.
     *
     * Example:
     * public function get_results_page_config(){
     *     array("title"=>"My Results Page", "field_types" => array("radio","checkbox");
     * }
     *
     * todo: implement permissions
     */
    public function get_results_page_config() {
        return array();
    }

    protected function results_page_init($results_page_config) {
        require_once("class-gf-results.php");
        $this->register_script("gaddon_results_js", GFAddOn::get_gfaddon_base_url() . "/js/gaddon_results.js", array("jquery", "sack"), GFCommon::$version, true);
        $this->register_script('jquery-ui-resizable', false, array('jquery'), false, false);
        $this->register_script('jquery-ui-datepicker', false, array('jquery'), false, false);
        $this->register_script('google_charts', "https://www.google.com/jsapi", array('jquery'), false, false);
        $this->register_style("gaddon_results_css", GFAddOn::get_gfaddon_base_url() . "/css/gaddon_results.css", null, GFCommon::$version);

        $gf_results = new GFResults($this->_slug, $results_page_config);
        $gf_results->init();
    }


    //--------------  Members plugin integration  --------------------------------------

    /**
     * Checks whether the Members plugin is installed and activated.
     *
     * Not intended to be overridden or called directly by Add-Ons.
     *
     * @ignore
     *
     * @return bool
     */
    protected function has_members_plugin() {
        return function_exists('members_get_capabilities');
    }

    /**
     * Not intended to be overridden or called directly by Add-Ons.
     *
     * @ignore
     *
     * @param $caps
     * @return array
     */
    public function members_get_capabilities($caps) {
        return array_merge($caps, $this->_capabilities);
    }

    //--------------  Permissions: Capabilities and Roles  ----------------------------

    /**
     *  Checks whether the current user is assigned to a capability or role.
     *
     * @param string|array $caps An string or array of capabilities to check
     * @return bool Returns true if the current user is assigned to any of the capabilities.
     */
    protected function current_user_can_any($caps) {
        return GFCommon::current_user_can_any($caps);
    }


    //------- Settings Helper Methods (Common to all settings pages) -------------------

    /***
    * Renders the UI of all settings page based on the specified configuration array $sections
    *
    * @param array $sections - Configuration array containing all fields to be rendered grouped into sections
    * @param array $settings - Current saved settings. Will be used to populate the inputs with their current values. Name/Value pair of input name and input value
    */
    public function render_settings( $sections ) {
        ?>
        <style type="text/css">
            .gaddon-section {
                padding:20px 0 0;
                margin:0 0 20px;
                border-top:1px solid #f7f7f7;
            }

            .gaddon-section.gaddon-first-section {
                padding-top:0px;
                border-top:0px;
            }

            .gaddon-setting.large {
                width:95%;
            }

            .gaddon-setting.medium {
                width:50%;
            }

            .gaddon-setting.gaddon-checkbox {
                margin-right:8px;
            }
        </style>

        <form id="gform-settings" action="" method="post">

            <?php
            $this->settings($sections);
            ?>

            <p class="submit">
                <?php $this->settings_save_button() ?>
            </p>

        </form>

        <?php
    }

    /***
    * Renders settings fields based on the specified configuration array $sections
    *
    * @param array $sections - Configuration array containing all fields to be rendered grouped into sections
    */
    public function settings( $sections ){
        $is_first = true;
        foreach( $sections as $section ) {
            if( !rgar( $section, 'dependency' ) || $this->setting_dependency_met( rgar( $section, 'dependency' ) ) )
                $this->single_section( $section, $is_first);

            $is_first = false;
        }
    }

    /***
    * Displays the UI for a field section
    *
    * @param array $section - The section to be displayed
    * @param bool $is_first - true for the first section in the list, false for all others
    */
    public function single_section( $section, $is_first = false) {
        ?>

        <div class="gaddon-section<?php echo $is_first ? " gaddon-first-section" : ""?>">

            <?php if( rgar( $section, 'title' ) ): ?>
                <h3><?php echo $section['title']; ?></h3>
            <?php endif; ?>

            <?php if( rgar( $section, 'description' ) ): ?>
                <p class="gaddon-section-description"><?php echo $section['description']; ?></p>
            <?php endif; ?>

            <table class="form-table">

                <?php
                foreach($section['fields'] as $field) {

                    if( !rgar( $field, 'dependency' ) || $this->setting_dependency_met( rgar( $field, 'dependency' ) ) )
                        $this->single_setting_row( $field );
                }
                ?>

            </table>

        </div>

        <?php
    }

    /***
    * Displays the UI for the field container row
    *
    * @param array $field - The field to be displayed
    */
    public function single_setting_row( $field ) {
    
        $display = rgar( $field, 'hidden' ) ? 'style="display:none;"' : '';
        
        ?>
        <tr <?php echo $display; ?>>
            <th>
                <?php
                
                    echo $field["label"];
                    
                    if( rgar( $field, 'required' ) )
                        echo ' ' . $this->get_required_indicator( $field );
                    
                    if( isset( $field['tooltip'] ) )
                        echo ' ' . gform_tooltip( $field['tooltip'], rgar( $field, 'tooltip_class'), true );
                    
                ?>
            </th>
            <td>
                <?php echo $this->single_setting( $field ); ?>
            </td>
        </tr>

        <?php
    }

    /***
    * Calls the appropriate field function to handle rendering of each specific field type
    *
    * @param array $field - The field to be rendered
    */
    public function single_setting( $field ) {
        if( is_callable( rgar( $field, 'callback' ) ) ) {
            call_user_func($field["callback"], $field);
        } else if (is_callable(array($this, "settings_{$field["type"]}"))) {
            call_user_func(array($this, "settings_{$field["type"]}"), $field);
        } else {
            printf(__("Field type '%s' has not been implemented", "gravityforms"), $field["type"] );
        }
    }

    /***
    * Displays the save button
    */
    public function settings_save_button() {
        ?>
        <input type="submit" class="button-primary" name='gform-settings-save'
               value="<?php _e("Save Settings", "gravityforms") ?>"/>
        <?php
    }

    /***
    * Sets the current saved settings to a class variable so that it can be accessed by lower level functions in order to initialize inputs with the appropriate values
    *
    * @param array $settings: Settings to be saved
    */
    public function set_settings( $settings ) {
        $this->_saved_settings = $settings;
    }

    /***
    * Gets settings from $_POST variable, returning a name/value collection of setting name and setting value
    */
    public function get_posted_settings(){
        global $_gaddon_posted_settings;

        if(isset($_gaddon_posted_settings))
            return $_gaddon_posted_settings;

        $_gaddon_posted_settings = array();
        if(count($_POST) > 0){
            foreach($_POST as $key => $value){
                if(preg_match("|_gaddon_setting_(.*)|", $key, $matches)){
                    $_gaddon_posted_settings[$matches[1]] = self::maybe_decode_json(stripslashes_deep($value));
                }
            }
        }

       return $_gaddon_posted_settings;
    }

    public static function maybe_decode_json($value) {
        if (self::is_json($value))
            return json_decode($value, ARRAY_A);

        return $value;
    }

    public static function is_json($value) {
        if (is_string($value) && substr($value, 0, 1) == '{' && is_array(json_decode($value, ARRAY_A)))
            return true;

        return false;
    }

    /***
    * Gets the "current" settings, which are settings from $_POST variables if this is a postback request, or the current saved settings for a get request.
    */
    public function get_current_settings(){
        //try getting settings from post
        $settings = $this->get_posted_settings();

        //if nothing has been posted, get current saved settings
        if(empty($settings)){
            $settings = $this->_saved_settings;
        }

        return $settings;
    }

    /***
    * Retrieves the setting for a specific field/input
    *
    * @param string $setting_name: The field or input name
    */
    public function get_setting($setting_name, $default_value = "") {

        $settings = $this->get_current_settings();

        if (false === $settings)
            return $default_value;

        if (strpos($setting_name, "[") !== false) {
            $path_parts = explode("[", $setting_name);
            foreach ($path_parts as $part) {
                $part = trim($part, "]");
                if (false === isset($settings[$part]))
                    return $default_value;
                $settings = rgar($settings, $part);
    }
            $setting = $settings;
        } else {
            if (false === isset($settings[$setting_name]))
                return $default_value;
            $setting = $settings[$setting_name];
        }

        return $setting;
    }

    /***
    * Determines if a dependent field has been populated.
    *
    * @param string $dependency - Field or input name of the "parent" field.
    * @return bool - true if the "parent" field has been filled out and false if it has not.
    *
    */
    public function setting_dependency_met( $dependency ) {

        //use a callback if one is specified in the configuration
        if(is_callable($dependency)){
            return call_user_func($dependency);
        }

        if(is_array($dependency)){
            //supports: "dependency" => array("field" => "myfield", "values" => array("val1", "val2"))
            $dependency_field = $dependency["field"];
            $dependency_value = $dependency["values"];
        }
        else{
            //supports: "dependency" => "myfield"
            $dependency_field = $dependency;
            $dependency_value = "_notempty_";
        }

        if(!is_array($dependency_value))
            $dependency_value = array($dependency_value);

        $current_value = $this->get_setting($dependency_field);

        foreach($dependency_value as $val){
            if($current_value == $val)
                return true;

            if($val == "_notempty_" && !rgblank($current_value))
                return true;
        }

        return false;
    }

    //------------- Field Types ------------------------------------------------------

    /***
    * Renders and initializes a text field based on the $field array
    *
    * @param array $field - Field array containing the configuration options of this field
    * @param bool $echo = true - true to echo the output to the screen, false to simply return the contents as a string
    */
    public function settings_text($field, $echo = true) {
        $field["type"] = "text"; //making sure type is set to text
        $attributes = $this->get_field_attributes($field);

        $value = $this->get_setting($field["name"]);

        $html = '<input
                    type="text"
                    name="_gaddon_setting_' . esc_attr($field["name"]) . '"
                    value="' . esc_attr($value) . '"' .
                    implode( ' ', $attributes ) .
                ' />';

        $feedback_callback = rgar($field, 'feedback_callback');
        if(is_callable($feedback_callback)){
            $is_valid = call_user_func($feedback_callback, $value);
            $icon = "";
            if($is_valid === true)
                $icon = "tick.png";
            else if($is_valid === false)
                $icon = "stop.png";

            if(!empty($icon))
                $html .= "&nbsp;&nbsp;<img src='" . GFCommon::get_base_url() . "/images/{$icon}' />";
        }
        
        if( $this->field_failed_validation( $field ) )
            $html .= $this->get_error_icon( $field );
        
        if ($echo)
            echo $html;

        return $html;
    }

    /***
    * Renders and initializes a textarea field based on the $field array
    *
    * @param array $field - Field array containing the configuration options of this field
    * @param bool $echo = true - true to echo the output to the screen, false to simply return the contents as a string
    */
    public function settings_textarea($field, $echo = true) {
        $field["type"] = "textarea"; //making sure type is set to textarea
        $attributes = $this->get_field_attributes($field);

        $value = $this->get_setting($field["name"]);

        $html = '<textarea
                    name="_gaddon_setting_' . esc_attr($field["name"]) . '"' .
                    implode( ' ', $attributes ) .
                 '>' .
                    esc_html($value) .
                '</textarea>';

        if ($echo)
            echo $html;

        return $html;
    }


    /***
    * Renders and initializes a hidden field based on the $field array
    *
    * @param array $field - Field array containing the configuration options of this field
    * @param bool $echo = true - true to echo the output to the screen, false to simply return the contents as a string
    */
    public function settings_hidden($field, $echo = true) {
        $field["type"] = "hidden"; //making sure type is set to hidden
        $attributes = $this->get_field_attributes($field);

        $default_value = rgar( $field, 'value' ) ? rgar( $field, 'value' ) : rgar( $field, 'default_value' );
        $value = $this->get_setting($field["name"]);
        
        if( !$value )
            $value = $default_value;
        
        if ( is_array( $value ) )
            $value = json_encode($value);

        $html = '<input
                    type="hidden"
                    name="_gaddon_setting_' . esc_attr($field["name"]) . '"
                    value="' . esc_attr($value) . '"' .
                    implode( ' ', $attributes ) .
                ' />';

        if ($echo)
            echo $html;

        return $html;
    }

    /***
    * Renders and initializes a checkbox field or a collection of checkbox fields based on the $field array
    *
    * @param array $field - Field array containing the configuration options of this field
    * @param bool $echo = true - true to echo the output to the screen, false to simply return the contents as a string
    */
    public function settings_checkbox( $field, $echo = true ) {

        $field["type"] = "checkbox"; //making sure type is set to checkbox

        $default_attributes = array("onclick" => 'jQuery(this).siblings("input[type=hidden]").val(jQuery(this).prop("checked") ? 1 : 0);');
        $attributes = $this->get_field_attributes($field, $default_attributes);
        $html = "";

        if(is_array($field["choices"])){
            foreach( $field["choices"] as $choice ) {

                $value        = $this->get_setting($choice['name'], rgar($choice, "default_value"));
                $choice['id'] = $choice['name'];

                foreach( $choice as $prop => $val ) {
                    $attributes[$prop] = "{$prop}='" . esc_attr($val) . "'";
                }
                $hidden_field_value = $value == '1' ? '1' : '0';
                $check_value        = isset($value[$choice["name"]]) ? $value[$choice["name"]] : false;

                $html .= '
                    <div class="gaddon-setting-checkbox">
                        <input type=hidden name="_gaddon_setting_' . esc_attr($choice["name"]) . '" value="' . $hidden_field_value . '" />
                        <input
                            type = "checkbox"' .
                            implode( ' ', $attributes ) . ' ' .
                            checked( $value, "1", false ) .
                        ' />
                        <label for="' . esc_attr($choice['id']) . '">' . esc_html($choice['label']) . '</label>
                    </div>
                    ';
            }
        }

        if ($echo)
            echo $html;

        return $html;
    }

    /***
    * Renders and initializes a drop down field based on the $field array
    *
    * @param array $field - Field array containing the configuration options of this field
    * @param bool $echo = true - true to echo the output to the screen, false to simply return the contents as a string
    */
    public function settings_select( $field, $echo = true ) {

        $field["type"] = "select"; //making sure type is set to select
        $attributes = $this->get_field_attributes($field);
        $value = $this->get_setting($field["name"]);

        $html = "";
        $options = "";
        if(is_array($field["choices"])){
            foreach( $field["choices"] as $choice ) {
                $options .= '<option value="' . esc_attr($choice['value']) . '" ' . selected( $value, $choice['value'], false ) . '>' .
                            $choice['label'] .
                         '</option>';
            }
        }

        $html =     '<select
                            name="_gaddon_setting_' . esc_attr($field["name"]) . '"' .
                            implode( ' ', $attributes ) .
                            '>' .
                        $options .
                    '</select>';

        if( $this->field_failed_validation( $field ) )
            $html .= $this->get_error_icon( $field );
                    
        if ($echo)
            echo $html;

        return $html;
    }

    /**
    * Helper to create a simple conditional logic set of fields. It creates one row of conditional logic with Field/Operator/Value inputs.
    *
    * @param mixed $setting_name_root - The root name to be used for inputs. It will be used as a prefix to the inputs that make up the conditional logic fields
    */
    public function simple_condition($setting_name_root){

        $conditional_fields = $this->get_conditional_logic_fields();
        $create_condition_value_script = "";

        $str = $this->settings_select(array(
                                    "name" => "{$setting_name_root}_field_id",
                                    "type" => "select",
                                    "choices" => $conditional_fields,
                                    "class" => "optin_select",
                                    "onchange" => "jQuery('#" . esc_attr($setting_name_root) . "_container').html(GetRuleValues('gf_setting', 0, jQuery(this).val(), '', '_gaddon_setting_" . esc_attr($setting_name_root) . "_value'));"
                                ), false);

        $str .= $this->settings_select(array(
                                    "name" => "{$setting_name_root}_operator",
                                    "type" => "select",
                                    "choices" => array(
                                        array(
                                            "value" => "is",
                                            "label" => __("is", "gravityformsmailchimp")
                                            ),
                                        array(
                                            "value" => "isnot",
                                            "label" => __("is not", "gravityformsmailchimp")
                                            ),
                                        array(
                                            "value" => ">",
                                            "label" => __("greater than", "gravityformsmailchimp")
                                            ),
                                        array(
                                            "value" => "<",
                                            "label" => __("less than", "gravityformsmailchimp")
                                            ),
                                        array(
                                            "value" => "contains",
                                            "label" => __("contains", "gravityformsmailchimp")
                                            ),
                                        array(
                                            "value" => "starts_with",
                                            "label" => __("starts with", "gravityformsmailchimp")
                                            ),
                                        array(
                                            "value" => "ends_with",
                                            "label" => __("ends with", "gravityformsmailchimp")
                                            )
                                        )
                                ), false);

        $str .= "<span id='{$setting_name_root}_container'></span>";

        $field_id = $this->get_setting("{$setting_name_root}_field_id");

        if(!empty($field_id)){
            $current_condition_value = $this->get_setting("{$setting_name_root}_value");
            $str .= "<script type='text/javascript'>jQuery(document).ready(function(){jQuery('#" . esc_attr($setting_name_root) . "_container').html(GetRuleValues('gf_setting', 0, {$field_id}, '" . esc_attr($current_condition_value) . "', '_gaddon_setting_" . esc_attr($setting_name_root) . "_value'));});</script>";
        }
        return $str;
    }

    //TODO: cleanup by moving field specific logic to each individual field function
    public function get_field_attributes($field, $default = array()){

        // each nonstandard property will be extracted from the $props array so it is not auto-output in the field HTML
        $no_output_props = apply_filters( 'gaddon_no_output_field_properties',
                array( 'default_value', 'label', 'choices', 'feedback_callback', 'checked', 'checkbox_label', 'value', 'type', 
                    'validation_callback', 'required' ), $field );

        $default_props = array(
            'class' => '',          // will default to gaddon-setting
            'default_value' => ''  // default value that should be selected or entered for the field
            );

        // Property switch case
        switch( $field['type'] ) {
        case 'select':
            $default_props['default_choice'] = array();
            $default_props['choices'] = array();
            break;
        case 'checkbox':
            $default_props['checked'] = false;
            $default_props['checkbox_label'] = '';
            $default_props['choices'] = array();
            break;
        case 'text':
        default:
            break;
        }

        $props = wp_parse_args( $field, $default_props );
        $props['id'] = $props['name'];
        $props['class'] = trim("{$props['class']} gaddon-setting gaddon-{$props['type']}");

        // extract no-output properties from $props array so they are not auto-output in the field HTML
        foreach( $no_output_props as $prop ) {
            if( isset( $props[$prop] ) ) {
                ${$prop} = $props[$prop];
                unset( $props[$prop] );
            }
        }

        //adding default attributes
        foreach($default as $key=>$value){
            if(isset($props[$key]))
                $props[$key] = $value . $props[$key];
            else
                $props[$key] = $value;
        }

        // get an array of property strings, example: name='myFieldName'
        $prop_strings = array();
        foreach( $props as $prop => $value ) {
            $prop_strings[$prop] = "{$prop}='" . esc_attr($value) . "'";
        }

        return $prop_strings;  
    }
    
    public function validate_settings( $fields, $settings ) {
        
        foreach( $fields as $section ) {
            foreach( $section['fields'] as $field ) {
                
                $field_setting = rgar( $settings, $field['name'] );
                
                if( is_callable( rgar( $field, 'validation_callback' ) ) ) {
                    call_user_func( rgar( $field, 'validation_callback' ), $field, $field_setting );
                } else if( rgar( $field, 'required' ) ) {
                    if( rgblank( $field_setting ) )
                        $this->set_field_error( $field, rgar( $field, 'error_message' ) );
                }
            
            }
        }
        
        $field_errors = $this->get_field_errors();
        $is_valid = empty( $field_errors );
        
        return $is_valid;
    }
    
    public function set_field_error( $field, $error_message = '' ) {
        
        // set default error message if none passed
        if( !$error_message )
            $error_message = __( 'This field is required.', 'gravityforms' );
                    
        $this->_setting_field_errors[$field['name']] = $error_message;
    }
    
    public function get_field_errors( $field = false ) {
        
        if( !$field )
            return $this->_setting_field_errors;
        
        return isset( $this->_setting_field_errors[$field['name']] ) ? $this->_setting_field_errors[$field['name']] : array();
    }
    
    public function get_error_icon( $field ) {
        
        $error = $this->get_field_errors( $field );
        
        return '<span 
            class="tooltip_left" 
            tooltip="<h6>' . __( 'Validation Error', 'gravityforms' ) . '</h6>' . $error . '" 
            style="background-image: url(\'' . GFCommon::get_base_url() . '/images/exclamation.png\');display:inline-block; position:relative;right:-3px;top: 3px;width:16px;height:16px;">
            </span>';
    }
    
    public function get_required_indicator( $field ) {
        return '<span class="required">*</span>';
    }
    
    public function field_failed_validation( $field ) {
        $field_error = $this->get_field_errors( $field );
        return !empty( $field_error ) ? $field_error : false;
    }
    
    //--------------  Form settings  ---------------------------------------------------

    /**
     * Hooks up the required scripts and actions for the Form Settings
     */
    protected function form_settings_init() {
        $view    = rgget("view");
        $subview = rgget("subview");
        add_action('gform_form_settings_menu', array($this, 'add_form_settings_menu'), 10, 2);
        if (rgget("page") == "gf_edit_forms" && $view == "settings" && $subview == $this->_slug && $this->current_user_can_any($this->_capabilities_form_settings)) {
            require_once(GFCommon::get_base_path() . "/tooltips.php");
            add_action("gform_form_settings_page_" . $this->_slug, array($this, 'form_settings_page'));
        }
    }

    /**
     * Renders the form settings page.
     *
     * Not intended to be overridden or called directly by Add-Ons.
     * Sets up the form settings page.
     *
     * @ignore
     */
    public function form_settings_page() {

        GFFormSettings::page_header($this->_title);
        ?>
        <div class="gform_panel gform_panel_form_settings" id="form_settings">

            <form id="gform-settings" action="" method="post">

                <?php
                $form = $this->get_current_form();

                if(is_callable(array($this, 'form_settings'))){

                    //enables plugins to override settings page by implementing a form_settings() function
                    call_user_func(array($this, 'form_settings'), $form);
                } else {
                    //saves form settings if save button was pressed
                    $this->maybe_save_form_settings($form);

                    //reads current form settings
                    $settings = $this->get_form_settings($form);
            
                    //reading addon fields
                    $sections = $this->form_settings_fields();

                    //rendering settings based on fields and current settings
                    $this->render_settings( $sections );
                }
                ?>

            </form>
            <script type="text/javascript">
                var form = <?php echo json_encode($this->get_current_form()) ?>;
            </script>
        </div>
        <?php
        GFFormSettings::page_footer();
    }

    /***
    * Saves form settings if the submit button was pressed
    *
    */
    public function maybe_save_form_settings($form){
        if($this->is_save_postback()){
            $settings = $this->get_posted_settings();
            $this->save_form_settings($form, $settings);
        }
    }

    /***
    * Saves form settings to form object
    *
    * @param array $form
    * @param array $settings
    */
    public function save_form_settings($form, $settings){
        $form[$this->_slug] = $settings;
        GFFormsModel::update_form_meta($form["id"], $form);
    }

    /**
     * Checks whether the current Add-On has a form settings page.
     *
     * @return bool
     */
    private function has_form_settings_page() {

        $sections = $this->form_settings_fields();
        $has_sections = is_array( $sections ) && count( $sections ) > 0;

        return is_callable( array($this, 'form_settings') ) || $has_sections;
    }

    /**
     * Enqueues the scripts for the form settings page
     */
    public function form_settings_enqueue_scripts() {
        //wp_enqueue_style('gaddon_form_settings_css');
        //wp_enqueue_script("gform_json");
        //$this->localize_settings_script();
    }

    /**
     * Add the localization variables to the settings pages (Form, Feed and Add-On) so JavaScript has access to them.
     */
    private function localize_settings_script() {

        // Example: localize strings
        /*$strings = array(
            "ajaxError"       => __("Ajax error while saving settings. Please contact support.", "gravityforms"),
            "savingProblem"   => __("There was a problem while saving the settings. Please contact support.", "gravityforms"),
            "savingSucessful" => __("Settings saved successfully.", "gravityforms"),
        );
        wp_localize_script('your_script_handle', 'objectName', $strings);*/

    }

    /**
     * Returns the form settings for the Add-On
     *
     * @param $form
     * @return string
     */
    protected function get_form_settings($form) {
        
        $settings = rgar( $form, $this->_slug );
        $this->set_settings( $settings );
        
        return $settings;
    }

    /**
     * Add the form settings tab.
     *
     * Not intended to be overridden or called directly by Add-Ons.
     *
     * @ignore
     *
     * @param $tabs
     * @param $form_id
     * @return array
     */
    public function add_form_settings_menu($tabs, $form_id) {

        $tabs[] = array("name" => $this->_slug, "label" => $this->get_short_title(), "query" => array("fid" => null));

        return $tabs;
    }

    /***
    * Override this function to specify the settings fields to be rendered on the form settings page
    */
    public function form_settings_fields(){
        // should return an array of sections, each section contains a title, description and an array of fields
        return array();
    }

    //--------------  Plugin Settings  ---------------------------------------------------

    protected function plugin_settings_init() {
        $subview = rgget("subview");
        RGForms::add_settings_page( array(
            'name' => $this->get_short_title(),
            'tab_label' => $this->get_short_title(),
            'handler' => array($this, 'plugin_settings_page')
            ) );
        if (rgget("page") == "gf_settings" && $subview == $this->get_short_title() && $this->current_user_can_any($this->_capabilities_settings_page)) {
            require_once(GFCommon::get_base_path() . "/tooltips.php");
        }
    }

    /***
    * Plugin settings page
    */
    public function plugin_settings_page() {

        if(is_callable(array($this, 'plugin_settings'))){
            //enables plugins to override settings page by implementing a plugin_settings() function
            call_user_func(array($this, 'plugin_settings'));
        } else {
            //saves settings page if save button was pressed
            $this->maybe_save_plugin_settings();

            //reads main addon settings
            $settings = $this->get_plugin_settings();

            //reading addon fields
            $sections = $this->plugin_settings_fields();

            //rendering settings based on fields and current settings
            $this->render_settings( $sections, $settings);

            //renders uninstall section
            $this->render_uninstall();

        }

    }

    /**
     * Checks whether the current Add-On has a settings page.
     *
     * @return bool
     */
    public function has_plugin_settings_page() {

        $sections = $this->plugin_settings_fields();
        $has_sections = is_array( $sections ) && count( $sections ) > 0;

        return method_exists($this, 'plugin_settings') || $has_sections;
    }

    /**
     * Enqueues the scripts for the form settings
     */
    public function plugin_settings_enqueue_scripts() {
        //wp_enqueue_script("gform_json");
        //$this->localize_settings_script("gf_add_on_save_settings_" . $this->_slug);
    }

    /***
    * Returns the currently saved plugin settings
    *
    */
    protected function get_plugin_settings() {
        
        $settings = get_option("gravityformsaddon_" . $this->_slug . "_settings");
        $this->set_settings( $settings );
        
        return $settings;
    }

    /**
    * Updates plugin settings with the provided settings
    *
    * @param array $settings - Plugin settings to be saved
    */
    protected function update_plugin_settings($settings){
        update_option("gravityformsaddon_" . $this->_slug . "_settings", $settings);
    }

    /***
    * Saves teh plugin settings if the submit button was pressed
    *
    */
    protected function maybe_save_plugin_settings(){
        if($this->is_save_postback()){
            $settings = $this->get_posted_settings();
            $this->update_plugin_settings($settings);
        }
    }

    /***
    * Override this function to specify the settings fields to be rendered on the plugin settings page
    */
    public function plugin_settings_fields(){
        // should return an array of sections, each section contains a title, description and an array of fields
        return array();
    }

    protected function settings_fields_only($settings_type = 'plugin') {

        $fields = array();

        if (!is_callable(array($this, "{$settings_type}_settings_fields")))
            return $fields;

        $sections = call_user_func(array($this, "{$settings_type}_settings_fields"));

        foreach ($sections as $section) {
            foreach ($section['fields'] as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    //--------------  Uninstall  ---------------

    public function render_uninstall(){

        //uninstalls the addon if the uninstall button was pressed
        $this->maybe_uninstall();

        ?>
        <form action="" method="post">
            <?php wp_nonce_field("uninstall", "gf_addon_uninstall") ?>
            <?php if ($this->current_user_can_any($this->_capabilities_uninstall)) { ?>
                <div class="hr-divider"></div>

                <h3><?php _e("Uninstall Add-On", "gravityforms") ?></h3>
                <div class="delete-alert"><?php _e("Warning! This operation deletes ALL settings.", "gravityforms") ?>
                    <?php
                    $uninstall_button = '<input type="submit" name="uninstall" value="' . __("Uninstall  Add-On", "gravityforms") . '" class="button" onclick="return confirm(\'' . __("Warning! ALL settings will be deleted. This cannot be undone. \'OK\' to delete, \'Cancel\' to stop", "gravityforms") . '\');"/>';
                    echo $uninstall_button;
                    ?>
                </div>
            <?php
            }
            ?>
        </form>
        <?php
    }

    public function maybe_uninstall(){
        if (rgpost("uninstall")) {
            check_admin_referer("uninstall", "gf_addon_uninstall");
            $this->uninstall_addon();

            ?>
            <div class="updated fade" style="padding:20px;">
                <?php _e(sprintf("%s has been successfully uninstalled. It can be re-activated from the %splugins page%s.", $this->_title, "<a href='plugins.php'>", "</a>"), "gravityforms")?>
            </div>
            <?php
            return;
        }
    }

    /**
     * Removes all settings and deactivates the Add-On.
     *
     * Not intended to be overridden or called directly by Add-Ons.
     *
     * @ignore
     */
    public function uninstall_addon() {

        if (!$this->current_user_can_any($this->_capabilities_uninstall))
            die(__("You don't have adequate permission to uninstall this addon: " . $this->_title, "gravityforms"));

        $continue = $this->uninstall();
        if (false === $continue)
            return;

        global $wpdb;
        $lead_meta_table = GFFormsModel::get_lead_meta_table_name();

        $forms        = GFFormsModel::get_forms();
        $all_form_ids = array();

        // remove entry meta
        foreach ($forms as $form) {
            $all_form_ids[] = $form->id;
            $entry_meta = $this->get_entry_meta(array(), $form->id);
            foreach(array_keys($entry_meta) as $meta_key){
                $sql = $wpdb->prepare("DELETE from $lead_meta_table WHERE meta_key=%s", $meta_key);
                $wpdb->query($sql);
            }
        }

        //remove form settings
        $form_metas = GFFormsModel::get_form_meta_by_id($all_form_ids);
        require_once(GFCommon::get_base_path() . '/form_detail.php');
        foreach ($form_metas as $form_meta) {
            if (isset($form_meta[$this->_slug])) {
                unset($form_meta[$this->_slug]);
                $form_json = json_encode($form_meta);
                GFFormDetail::save_form_info($form_meta["id"], addslashes($form_json));
            }
        }

        //removing options
        delete_option("gravityformsaddon_" . $this->_slug . "_settings");
        delete_option("gravityformsaddon_" . $this->_slug . "_version");


        //Deactivating plugin
        deactivate_plugins($this->_path);
        update_option('recently_activated', array($this->_path => time()) + (array)get_option('recently_activated'));
    }

    /**
     * Called when the user chooses to uninstall the Add-On  - after permissions have been checked and before removing
     * all Add-On settings and Form settings.
     *
     * Override this method to perform additional functions such as dropping database tables.
     *
     *
     * Return false to cancel the uninstall request.
     */
    public function uninstall() {
        return true;
    }

    //--------------  Enforce minimum GF version  ---------------------------------------------------

    /**
     * Target for the after_plugin_row action hook. Checks whether the current version of Gravity Forms
     * is supported and outputs a message just below the plugin info on the plugins page.
     *
     * Not intended to be overridden or called directly by Add-Ons.
     *
     * @ignore
     */
    public function plugin_row() {
        if (!self::is_gravityforms_supported($this->_min_gravityforms_version)) {
            $message = $this->plugin_message();
            self::display_plugin_message($message, true);
        }
    }

    /**
     * Returns the message that will be displayed if the current version of Gravity Forms is not supported.
     *
     * Override this method to display a custom message.
     */
    public function plugin_message() {
        $message = sprintf(__("Gravity Forms " . $this->_min_gravityforms_version . " is required. Activate it now or %spurchase it today!%s", "gravityformsaddon"), "<a href='http://www.gravityforms.com'>", "</a>");

        return $message;
    }

    /**
     * Formats and outs a message for the plugin row.
     *
     * Not intended to be overridden or called directly by Add-Ons.
     *
     * @ignore
     *
     * @param $message
     * @param bool $is_error
     */
    public static function display_plugin_message($message, $is_error = false) {
        $style = $is_error ? 'style="background-color: #ffebe8;"' : "";
        echo '</tr><tr class="plugin-update-tr"><td colspan="5" class="plugin-update"><div class="update-message" ' . $style . '>' . $message . '</div></td>';
    }

    //--------------- Logging -------------------------------------------------------------

    public function log_error($message){
        if (class_exists("GFLogging")) {
            GFLogging::include_logger();
            GFLogging::log_message($this->_slug, $message, KLogger::ERROR);
        }
    }

    public function log_debug($message){
        if (class_exists("GFLogging")) {
            GFLogging::include_logger();
            GFLogging::log_message($this->_slug, $message, KLogger::DEBUG);
        }
    }

    //--------------  Helper functions  ---------------------------------------------------

    /**
     * Returns the url of the root folder of the current Add-On.
     *
     * @param string $full_path Optional. The full path the the plugin file.
     * @return string
     */
    protected function get_base_url($full_path = "") {
        if (empty($full_path))
            $full_path = $this->_full_path;

        return plugins_url(null, $full_path);
    }

    /**
     * Returns the url of the Add-On Framework root folder.
     *
     * @return string
     */
    final public static function get_gfaddon_base_url() {
        return plugins_url(null, __FILE__);
    }

    /**
     * Returns the physical path of the Add-On Framework root folder.
     *
     * @return string
     */
    final public static function get_gfaddon_base_path() {
        return self::_get_base_path();
    }

    /**
     * Returns the physical path of the plugins root folder.
     *
     * @param string $full_path
     * @return string
     */
    protected function get_base_path($full_path = "") {
        if (empty($full_path))
            $full_path = $this->_full_path;
        $folder = basename(dirname($full_path));

        return WP_PLUGIN_DIR . "/" . $folder;
    }

    /**
     * Returns the physical path of the Add-On Framework root folder
     *
     * @return string
     */
    private static function _get_base_path() {
        $folder = basename(dirname(__FILE__));

        return WP_PLUGIN_DIR . "/" . $folder;
    }

    /**
     * Returns the URL of the Add-On Framework root folder
     *
     * @return string
     */
    private static function _get_base_url() {
        $folder = basename(dirname(__FILE__));

        return plugins_url($folder);
    }

    /**
     * Checks whether the Gravity Forms is installed.
     *
     * @return bool
     */
    public function is_gravityforms_installed() {
        return class_exists("GFForms");
    }


    /**
     * Checks whether the current version of Gravity Forms is supported
     *
     * @param $min_gravityforms_version
     * @return bool|mixed
     */
    public function is_gravityforms_supported($min_gravityforms_version = "") {
        if(isset($this->_min_gravityforms_version) && empty($min_gravityforms_version))
            $min_gravityforms_version = $this->_min_gravityforms_version;

        if(empty($min_gravityforms_version))
            return true;

        if (class_exists("GFCommon")) {
            $is_correct_version = version_compare(GFCommon::$version, $min_gravityforms_version, ">=");

            return $is_correct_version;
        } else {
            return false;
        }
    }

    /**
    * Returns this plugin's short title. Used to display the plugin title in small areas such as tabs
    */
    protected function get_short_title() {
        return isset( $this->_short_title ) ? $this->_short_title : $this->_title;
    }

    /**
    * Returns the URL for the plugin settings tab associated with this plugin
    *
    */
    protected function get_plugin_settings_url() {
        return add_query_arg( array( 'page' => 'gf_settings', 'subview' => $this->_short_title ), admin_url('admin.php') );
    }

    /**
    * When called from a page that supports it (i.e. entry detail, form editor and form settings),
    * returns the current form object. Otherwise returns false
    */
    protected function get_current_form(){
        if( $this->is_entry_edit() ||
            $this->is_entry_view() ||
            $this->is_form_editor() ||
            $this->is_form_settings() )
        {
            return rgempty("id", $_GET) ? false : GFFormsModel::get_form_meta(rgget('id'));
        }

        return false;
    }

    /**
    * Returns TRUE if the current request is a postback, otherwise returns FALSE
    */
    protected function is_postback(){
        return is_array($_POST) && count($_POST) > 0;
    }

    /**
    * Returns TRUE if the settings "Save" button was pressed
    */
    protected function is_save_postback(){
        return !rgempty("gform-settings-save");
    }

    /**
    * Returns TRUE if the current page is the form editor page. Otherwise, returns FALSE
    */
    protected function is_form_editor(){

        if(rgget("page") == "gf_edit_forms" && !rgempty("id", $_GET) && rgempty("view", $_GET))
            return true;

        return false;
    }

    /**
    * Returns TRUE if the current page is the form settings page, or a specific form settings tab (specified by the $tab parameter). Otherwise returns FALSE
    *
    * @param string $tab - Specifies a specific form setting page/tab
    */
    protected function is_form_settings($tab = null){

        $is_form_settings = rgget("page") == "gf_edit_forms" && rgget("view") == "settings";
        $is_tab = $this->_tab_matches($tab);

        if($is_form_settings && $is_tab){
            return true;
        }
        else{
            return false;
        }
    }

    private function _tab_matches($tabs){
        if($tabs == null)
            return true;

        if(!is_array($tabs))
            $tabs = array($tabs);

        $current_tab = rgempty("subview", $_GET) ? "settings" : rgget("subview");

        foreach($tabs as $tab){
            if(strtolower($tab) == strtolower($current_tab))
                return true;
        }
    }

    /**
    * Returns TRUE if the current page is the plugin settings main page, or a specific plugin settings tab (specified by the $tab parameter). Otherwise returns FALSE
    *
    * @param string $tab - Specifies a specific plugin setting page/tab.
    */
    protected function is_plugin_settings($tab = ""){

        $is_plugin_settings = rgget("page") == "gf_settings";
        $is_tab = $this->_tab_matches($tab);

        if($is_plugin_settings && $is_tab){
            return true;
        }
        else{
            return false;
        }
    }

    /**
    * Returns TRUE if the current page is the entry view page. Otherwise, returns FALSE
    */
    protected function is_entry_view(){
        if(rgget("page") == "gf_entries" && rgget("view") == "entry" && (!isset($_POST["screen_mode"]) || rgpost("screen_mode") == "view"))
            return true;

        return false;
    }

    /**
    * Returns TRUE if the current page is the entry edit page. Otherwise, returns FALSE
    */
    protected function is_entry_edit(){
        if(rgget("page") == "gf_entries" && rgget("view") == "entry" && rgpost("screen_mode") == "edit")
            return true;

        return false;
    }

}