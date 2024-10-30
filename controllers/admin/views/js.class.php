<?php
namespace O10n;

/**
 * Javascript Optimization Admin View Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminViewJs extends AdminViewBase
{
    protected static $view_key = 'js'; // reference key for view
    protected $module_key = 'js';

    // default tab view
    private $default_tab_view = 'intro';

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @param  string     $View View key.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'json',
            'AdminClient',
            'options',
            'AdminOptions'
        ));
    }
    
    /**
     * Setup controller
     */
    protected function setup()
    {
        // set view etc
        parent::setup();
    }

    /**
     * Setup view
     */
    public function setup_view()
    {
        // process form submissions
        add_action('o10n_save_settings_verify_input', array( $this, 'verify_input' ), 10, 1);

        // enqueue scripts
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), $this->first_priority);
    }

    /**
     * Return help tab data
     */
    final public function help_tab()
    {
        $data = array(
            'name' => __('Javascript Optimization', 'o10n'),
            'github' => 'https://github.com/o10n-x/wordpress-javascript-optimization',
            'wordpress' => 'https://wordpress.org/support/plugin/javascript-optimization',
            'docs' => 'https://github.com/o10n-x/wordpress-javascript-optimization/tree/master/docs'
        );

        return $data;
    }

    /**
     * Enqueue scripts and styles
     */
    final public function enqueue_scripts()
    {
        // skip if user is not logged in
        if (!is_admin() || !is_user_logged_in()) {
            return;
        }

        // set module path
        $this->AdminClient->set_config('module_url', $this->module->dir_url());

        // global admin script
        wp_enqueue_script('o10n_view_js', $this->module->dir_url() . 'admin/js/view-js.js', array( 'jquery', 'o10n_cp' ), $this->module->version());
    }

    /**
     * Return view template
     */
    public function template($view_key = false)
    {

        // template view key
        $view_key = false;

        $tab = (isset($_REQUEST['tab'])) ? trim($_REQUEST['tab']) : $this->default_tab_view;
        if ($tab) {
            switch ($tab) {
                case "optimization":
                    $view_key = 'js';
                break;
                case "delivery":
                case "editor":
                case "pwa":
                case "settings":
                case "intro":
                    $view_key = 'js-' . $tab;
                break;
                default:
                    throw new Exception('Invalid JS view ' . esc_html($view_key), 'core');
                break;
            }
        }

        return parent::template($view_key);
    }
    
    /**
     * Verify settings input
     *
     * @param  object   Form input controller object
     */
    final public function verify_input($forminput)
    {
        $tab = (isset($_REQUEST['tab'])) ? trim($_REQUEST['tab']) : 'o10n';

        switch ($tab) {
            case "optimization":

                // Javascript code optimization

                $forminput->type_verify(array(
                    'js.minify.enabled' => 'bool',
                    'js.url_filter.enabled' => 'bool'
                ));
 
                // minify
                if ($forminput->bool('js.minify.enabled')) {
                    $forminput->type_verify(array(
                        'js.minify.minifier' => 'string',

                        'js.minify.comments.remove_important.enabled' => 'bool',
                        'js.minify.filter.enabled' => 'bool',
                        'js.replace' => 'json-array',
                        'js.minify.concat.enabled' => 'bool'

                    ));

                    // minifier
                    $minifier = $forminput->get('js.minify.minifier');
                    if ($minifier === 'closure-compiler-service') {
                        $forminput->type_verify(array(
                            'js.minify.minifier' => 'string',
                            'js.minify.fallback.enabled' => 'bool',

                            'js.minify.closure-compiler-service.options.compilation_level.enabled' => 'bool',
                            'js.minify.closure-compiler-service.options.externs_files.enabled' => 'bool',
                            'js.minify.closure-compiler-service.options.exclude_default_externs' => 'bool',
                            'js.minify.closure-compiler-service.options.formatting.enabled' => 'bool',
                            'js.minify.closure-compiler-service.options.use_closure_library' => 'bool'
                        ));

                        if ($forminput->bool('js.minify.closure-compiler-service.options.compilation_level.enabled')) {
                            $forminput->type_verify(array(
                                'js.minify.closure-compiler-service.options.compilation_level.level' => 'string'
                            ));
                        }

                        if ($forminput->bool('js.minify.closure-compiler-service.options.externs_files.enabled')) {
                            $forminput->type_verify(array(
                                'js.minify.closure-compiler-service.options.externs_files.files' => 'json-array'
                            ));
                        }

                        if ($forminput->bool('js.minify.closure-compiler-service.options.formatting.enabled')) {
                            $forminput->type_verify(array(
                                'js.minify.closure-compiler-service.options.formatting.format' => 'string'
                            ));
                        }

                        // fallback minifier
                        if ($forminput->bool('js.minify.fallback.enabled')) {
                            $forminput->type_verify(array(
                                'js.minify.fallback.minifier' => 'string',
                                'js.minify.fallback.timeout' => 'int-empty'
                            ));
                        }
                    }

                    // minify filter
                    if ($forminput->bool('js.minify.filter.enabled')) {
                        $forminput->type_verify(array(
                            'js.minify.filter.type' => 'string',
                            'js.minify.filter.include' => 'newline_array',
                            'js.minify.filter.exclude' => 'newline_array'
                        ));
                    }

                    // concat
                    if ($forminput->bool('js.minify.concat.enabled')) {
                        $forminput->type_verify(array(
                            
                            'js.minify.concat.minify' => 'bool',
                            'js.minify.concat.trycatch' => 'bool',

                            'js.minify.concat.filter.enabled' => 'bool',
                            'js.minify.concat.filter.type' => 'string',
                            'js.minify.concat.filter.config' => 'json-array',

                            'js.minify.concat.inline.enabled' => 'bool',
                            'js.minify.concat.inline.filter.enabled' => 'bool'
                        ));
                        
                        // concat filter
                        if ($forminput->bool('js.minify.concat.filter.enabled')) {
                            $forminput->type_verify(array(
                                'js.minify.concat.filter.type' => 'string',
                                'js.minify.concat.filter.config' => 'json-array'
                            ));
                        }

                        // concat inline filter
                        if ($forminput->bool('js.minify.concat.inline.enabled') && $forminput->bool('js.minify.concat.inline.filter.enabled')) {
                            $forminput->type_verify(array(
                                'js.minify.concat.inline.filter.type' => 'string',
                                'js.minify.concat.inline.filter.include' => 'newline_array',
                                'js.minify.concat.inline.filter.exclude' => 'newline_array'
                            ));
                        }
                    }

                    // verify search & replace
                    $jsreplace = $forminput->get('js.replace', 'json-array', array());
                    if (!empty($jsreplace)) {
                        $searchreplace = array();
                        $position = 0;
                        foreach ($jsreplace as $cnf) {
                            if (!is_array($cnf) || !isset($cnf['search']) || !isset($cnf['replace'])) {
                                continue;
                            }
                            $position++;
                            if (isset($cnf['regex'])) {
                                // exec preg_match on null
                                $valid = @preg_match($cnf['search'], null);
                                $error = $this->is_preg_error();
                                if ($valid === false || $error) {
                                    throw new Exception('<code>'.esc_html($cnf['search']).'</code> is an invalid regular expression and has been removed.' . (($error) ? '<br /><p>Error: '.$error.'</p>' : ''), 'settings');
                                }
                            }
                            $searchreplace[] = $cnf;
                        }
                        $jsreplace = $searchreplace;
                    }
  
                    // set search & replace
                    $forminput->set('js.replace', $jsreplace);
                }

                // url filter
                if ($forminput->bool('js.url_filter.enabled')) {
                    $forminput->type_verify(array(
                        'js.url_filter.config' => 'json-array'
                    ));
                }
            break;
            case "delivery":

                // Javascript delivery optimization

                $forminput->type_verify(array(
                    'js.async.enabled' => 'bool',

                    'js.http2_push.enabled' => 'bool',

                    'js.proxy.enabled' => 'bool',

                    'js.cdn.enabled' => 'bool'
                ));
                
                // async
                if ($forminput->bool('js.async.enabled')) {
                    $forminput->type_verify(array(
                        'js.async.rel_preload' => 'bool',

                        'js.async.filter.enabled' => 'bool',
                        'js.async.filter.type' => 'string',

                        'js.async.load_position' => 'string',

                        'js.async.exec_timing.enabled' => 'bool',

                        'js.async.responsive' => 'bool',
                        'js.async.jQuery_stub' => 'bool',
                        'js.async.localStorage.enabled' => 'bool'
                    ));

                    // async filter
                    if ($forminput->bool('js.async.filter.enabled')) {
                        $forminput->type_verify(array(
                            'js.async.filter.config' => 'json-array'
                        ));
                    }

                    // load timing
                    if ($forminput->get('js.async.load_position') === 'timing') {
                        $forminput->type_verify(array(
                            'js.async.load_timing.type' => 'string'
                        ));

                        if ($forminput->get('js.async.load_timing.type') === 'requestAnimationFrame') {
                            $forminput->type_verify(array(
                                'js.async.load_timing.frame' => 'int-empty'
                            ));
                        }

                        if ($forminput->get('js.async.load_timing.type') === 'requestIdleCallback') {
                            $forminput->type_verify(array(
                                'js.async.load_timing.timeout' => 'int-empty',
                                'js.async.load_timing.setTimeout' => 'int-empty'
                            ));
                        }
                
                        if ($forminput->get('js.async.load_timing.type') === 'inview') {
                            $forminput->type_verify(array(
                                'js.async.load_timing.selector' => 'string',
                                'js.async.load_timing.offset' => 'int-empty'
                            ));
                        }

                        if ($forminput->get('js.async.load_timing.type') === 'media') {
                            $forminput->type_verify(array(
                                'js.async.load_timing.media' => 'string'
                            ));
                        }
                    }

                    // exec timing
                    if ($forminput->bool('js.async.exec_timing.enabled')) {
                        $forminput->type_verify(array(
                            'js.async.exec_timing.type' => 'string'
                        ));

                        if ($forminput->get('js.async.exec_timing.type') === 'requestAnimationFrame') {
                            $forminput->type_verify(array(
                                'js.async.exec_timing.frame' => 'int-empty'
                            ));
                        }

                        if ($forminput->get('js.async.exec_timing.type') === 'requestIdleCallback') {
                            $forminput->type_verify(array(
                                'js.async.exec_timing.timeout' => 'int-empty',
                                'js.async.exec_timing.setTimeout' => 'int-empty'
                            ));
                        }
            
                        if ($forminput->get('js.async.exec_timing.type') === 'inview') {
                            $forminput->type_verify(array(
                                'js.async.exec_timing.selector' => 'string',
                                'js.async.exec_timing.offset' => 'int-empty'
                            ));
                        }

                        if ($forminput->get('js.async.exec_timing.type') === 'media') {
                            $forminput->type_verify(array(
                                'js.async.exec_timing.media' => 'string'
                            ));
                        }
                    }

                    // localStorage
                    if ($forminput->bool('js.async.localStorage.enabled')) {
                        $forminput->type_verify(array(
                            'js.async.localStorage.max_size' => 'int',
                            'js.async.localStorage.expire' => 'int',
                            'js.async.localStorage.update_interval' => 'int',
                            'js.async.localStorage.head_update' => 'bool'
                        ));
                    }
                }


                // HTTP/2
                if ($forminput->bool('js.http2_push.filter.enabled')) {
                    $forminput->type_verify(array(
                        'js.http2_push.filter.type' => 'string',
                        'js.http2_push.filter.include' => 'newline_array',
                        'js.http2_push.filter.exclude' => 'newline_array'
                    ));
                }

                // proxy
                if ($forminput->bool('js.proxy.enabled')) {
                    $forminput->type_verify(array(
                        'js.proxy.include' => 'newline_array',
                        'js.proxy.capture.enabled' => 'bool'
                    ));

                    // proxy capture
                    if ($forminput->bool('js.proxy.capture.enabled')) {
                        $forminput->type_verify(array(
                            'js.proxy.capture.list' => 'json-array'
                        ));
                    }
                }

                if ($forminput->bool('js.cdn.enabled')) {
                    $forminput->type_verify(array(
                        'js.cdn.http2_push' => 'bool',
                        'js.cdn.url' => 'string',
                        'js.cdn.mask' => 'string',
                    ));
                }
            break;
            case "editor":
                
            break;

            case "settings":

                // Javascript profile
                $js = $forminput->get('js', 'json-array');
                if ($js) {

                    // @todo improve
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveArrayIterator($js),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );
                    $path = [];
                    $flatArray = [];

                    $arrayVal = false;
                    foreach ($iterator as $key => $value) {
                        $path[$iterator->getDepth()] = $key;

                        $dotpath = 'js.'.implode('.', array_slice($path, 0, $iterator->getDepth() + 1));
                        if ($arrayVal && strpos($dotpath, $arrayVal) === 0) {
                            continue 1;
                        }

                        if (!is_array($value) || empty($value) || array_keys($value)[0] === 0) {
                            if (is_array($value) && (empty($value) || array_keys($value)[0] === 0)) {
                                $arrayVal = $dotpath;
                            } else {
                                $arrayVal = false;
                            }

                            $flatArray[$dotpath] = $value;
                        }
                    }

                    // delete existing options
                    $this->options->delete('js.*');

                    // replace all options
                    $this->AdminOptions->save($flatArray);
                }
            break;
            default:
                throw new Exception('Invalid Javascript view ' . esc_html($tab), 'core');
            break;
        }
    }
}
