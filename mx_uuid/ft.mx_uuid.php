<?php

/**
 * MX UUID field type.
 *
 * @author  Max Lazar <max@eecms.dev>
 *
 * @see    https://eecms.dev/add-ons/mx-uuid
 * @based
 *
 * @copyright Copyright (c) 2020, EEC.MS
 */

/**
 * Class Mx_uuid_ft.
 */
class Mx_uuid_ft extends EE_Fieldtype
{

    public $info = array(
        'name'     => MX_UUID_NAME,
        'version'  => MX_UUID_VERSION
    );

    public $field2ee =  array('boolean' => 'toggle', 'number' => 'text', 'select'=>'select', 'function'=>'textarea', 'string' => 'text', 'array' => 'textarea', 'object' => 'textarea');

    private static $js_added         = false;
    private static $cell_bind        = true;
    private static $grid_bind        = true;

    private $fallback_content        = '';
    public $cell_name;
    public $has_array_data           = false;
    public $entry_manager_compatible = true;

    /**
     * Package name.
     *
     * @var string
     */
    protected $package;

    /**
     * [$_themeUrl description].
     *
     * @var [type]
     */
    private static $themeUrl;


    /**
     * Field_limits_ft constructor.
     */
    public function __construct()
    {
        $this->package = basename(__DIR__);

        parent::__construct();

        if (!isset(static::$themeUrl)) {
            $themeFolderUrl = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : ee()->config->slash_item('theme_folder_url').'third_party/';
            static::$themeUrl = $themeFolderUrl.'mx_uuid/';
        }
    }

    /**
     * Specify compatibility.
     *
     * @param string $name
     *
     * @return bool
     */
    public function accepts_content_type($name)
    {
        $compatibility = array(
            'channel',
        );

        return in_array($name, $compatibility, false);
    }

    /**
     * Settings.
     *
     * @param array $data Existing setting data
     *
     * @return array
     */
    public function display_settings($data)
    {
        return $this->_build_settings($data);
    }

    /**
     * build_settings function.
     *
     * @param mixed $data
     */
    private function _build_settings($data, $type = false)
    {
        ee()->lang->loadfile($this->package);

        $settings = array();

        $config = self::getConfigFromFile('mx_uuid/Settings/Mx_uuid');

        foreach ($config as $field => $type) {

            $value = (isset($data[$field]) && '' != $data[$field]) ? $data[$field] : (false != ee()->config->item('mx_uuid_'.$field) ?
            ee()->config->item('mx_uuid_'.$field) : $config[$field]['defaults']);

            $settings[] = array(
                'title' => $field,
                'desc' => $field.'_description',
                'fields' => array(
                    'mx_uuid_'.$field => array(
                        'type' => $this->field2ee[$config[$field]['type']],
                        'choices' => isset($config[$field]['values']) ? $config[$field]['values'] : '',
                        'value' => $value,
                    )
                ),
            );

        }

        return array('field_options_mx_uuid' => array(
            'label' => 'field_options',
            'group' => 'mx_uuid',
            'settings' => $settings,
        ));
    }

    /**
     * Apply Config overrides to $this->settings.
     */
    private function _config_overrides()
    {
        // Check custom config values
        foreach ($this->_cfg as $key) {
            // Check the config for the value
            $val = ee()->config->item('mx_uuid_'.$key);

            // If not FALSE, override the settings
            if (false !== $val) {
                $this->_settings[$key] = $val;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Check if given setting is present in the config file.
     *
     * @return bool
     */
    public function is_config($item)
    {
        return in_array($item, $this->_cfg) && (false !== ee()->config->item('mx_rangeslider_'.$item));
    }

    /**
     * Display grid settings.
     *
     * @param array $data Existing setting data
     *
     * @return array
     */
    public function grid_display_settings($data)
    {
        return $this->_build_settings($data);
    }

    /**
     * Display Low Variables settings.
     *
     * @param array $data Existing setting data
     *
     * @return array
     */
    public function var_display_settings($data)
    {
        return $this->_build_settings($data, 'lv');
    }

    /**
     * Save settings.
     *
     * @param array $data
     *
     * @return array
     */
    public function save_settings($data)
    {
       //     var_dump($data);
     //   die();

        return $this->get($data, 'mx_uuid');
    }

    /**
     * Save Low Variables settings.
     *
     * @param array $data
     *
     * @return array
     */
    public function var_save_settings($data)
    {
        //    var_dump(ee('Request')->post());
      //  die();

        return $this->get(ee('Request')->post(), 'mx_uuid');
    }

    /**
     * Displays the field in the CP.
     * @param       string      $field_name             The field name.
     * @param       array       $field_data             The previously-saved field data.
     * @param       arrray      $field_settings         The field settings.
     * @return      string      The HTML to output.
     */
    public function display_field($data, $view_type = 'field', $settings = array(), $cp = true, $passed_init = array())
    {

        $js             = "";
        $css            = "";
        $class          = "";
        $subClass       = "";
        $r              = "";
        $js_block_start = '<script type="text/javascript">';
        $js_block_end   = '</script>';

        if (!empty($settings)) {
            $cp = false;
        }

        $cell = ($view_type != 'field') ? true : false;

        if (empty($settings)) {
            $settings = $this->settings;
        } else {
            $settings = array_merge($this->settings, $settings);
        }

        if ($data == "") {
            do {
                $data = self::uuid_generate_random();
            } while (self::validateUuid($data, $this->field_id));
        }

        if (!self::$js_added and $cp) {
            ee()->cp->add_to_foot('<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js"></script>');
            ee()->cp->add_to_foot($js_block_start . "$( document ).ready(function() { new ClipboardJS('.copyme'); });" . $js_block_end);

            self::$js_added = true;
        }


        return '<input type="hidden" name="'.$this->field_name.'" value="' . $data . '"  />
                <div><input type="text" name="' . $this->field_name . '" value="' . $data . '" disabled="disabled" id="' . $this->field_name  . '" style="width:90%; display:inline"/><span class="input-group-button" style="padding-left:15px;"><button class="btn copyme" type="button"  data-clipboard-text="' . $data . '"  style="margin-top: -3px;"><img src="data:image/svg+xml;base64,PHN2ZyBoZWlnaHQ9IjEwMjQiIHdpZHRoPSI4OTYiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0ibTEyOCA3NjhoMjU2djY0aC0yNTZ6bTMyMC0zODRoLTMyMHY2NGgzMjB6bTEyOCAxOTJ2LTEyOGwtMTkyIDE5MiAxOTIgMTkydi0xMjhoMzIwdi0xMjh6bS0yODgtNjRoLTE2MHY2NGgxNjB6bS0xNjAgMTkyaDE2MHYtNjRoLTE2MHptNTc2IDY0aDY0djEyOGMtMSAxOC03IDMzLTE5IDQ1cy0yNyAxOC00NSAxOWgtNjQwYy0zNSAwLTY0LTI5LTY0LTY0di03MDRjMC0zNSAyOS02NCA2NC02NGgxOTJjMC03MSA1Ny0xMjggMTI4LTEyOHMxMjggNTcgMTI4IDEyOGgxOTJjMzUgMCA2NCAyOSA2NCA2NHYzMjBoLTY0di0xOTJoLTY0MHY1NzZoNjQwem0tNTc2LTUxMmg1MTJjMC0zNS0yOS02NC02NC02NGgtNjRjLTM1IDAtNjQtMjktNjQtNjRzLTI5LTY0LTY0LTY0LTY0IDI5LTY0IDY0LTI5IDY0LTY0IDY0aC02NGMtMzUgMC02NCAyOS02NCA2NHoiLz48L3N2Zz4=" width="13" alt="Copy to clipboard" /></button></span></div>';
    }
    /**
     * [renderTableCell description]
     * @param  [type] $data     [description]
     * @param  [type] $field_id [description]
     * @param  [type] $entry    [description]
     * @return [type]           [description]
     */
    function renderTableCell($data, $field_id, $entry)
    {
        return $data;
    }

    //http://s3.amazonaws.com/scr.eecms.dev/1604691685.png


    /**
     * Display the field in a Grid cell.
     *
     * @param string $data field data
     *
     * @return string $field
     */
    public function grid_display_field($data)
    {
        return $this->display_field($data, 'grid');
    }

    /**
     * Display Low Variables field.
     *
     * @param mixed $data
     *
     * @return string
     */
    public function var_display_field($data)
    {
        return $this->display_field($data);
    }

    /**
     * Validate field data.
     *
     * @param mixed $data Submitted field data
     *
     * @return mixed
     */
    public function validate($data)
    {
        if (!$data) {
            return true;
        }

        $errors = '';

        if ($errors) {
            return $errors;
        }

        return true;
    }

    /**
     * Validate Low Variables field.
     *
     * @param string $data
     *
     * @return mixed
     */
    public function var_save($data)
    {
        ee()->lang->loadfile('mx_uuid');

        $validation = $this->validate($data);

        if (true !== $validation) {
            $this->error_msg = $validation;

            return false;
        }

        return $data;
    }

    /**
     * Replace tag.
     *
     * @param string $fieldData
     * @param array  $tagParams
     *
     * @return string
     */
    public function replace_tag($data, $params = array(), $tagdata = false)
    {

        return $data;
    }


  /**
     * replace_value function.
     *
     * @access public
     * @param mixed   $data
     * @param array   $params (default: array())
     * @return void
     */
    public function replace_value($data, $params = array())
    {
        return $data;
    }

    /**
     * Display Low Variables tag.
     *
     * @param string $fieldData
     * @param array  $tagParams
     *
     * @return string
     */
    public function var_replace_tag(
        $fieldData,
        $tagParams = array(),
        $tagData = false
    ) {
        return $this->replace_tag($fieldData, $tagParams);
    }

    /*

    HELPERS
    @needs to move to helpers file


     */

    /**
     * Insert JS in the page foot.
     *
     * @param string $js
     */
    public function insertGlobalResources($cell = false)
    {
        if (!isset(ee()->session->cache['mx_uuid']['header'])) {

            ee()->session->cache['mx_uuid']['header'] = true;
        }
    }

    /**
 * Insert JS in the page foot.
 *
 * @param string $js
 */
    public static function insertJsCode($js)
    {
        ee()->cp->add_to_foot('<script type="text/javascript">'.$js.'</script>');
    }

    /**
     * [includeJs description].
     *
     * @param [type] $file [description]
     *
     * @return [type] [description]
     */
    public static function includeJs($file)
    {
        ee()->cp->add_to_foot('<script type="text/javascript" src="'.static::$themeUrl.$file.'"></script>');
    }

    /**
     * [includeThemeCss description].
     *
     * @param [type] $file [description]
     *
     * @return [type] [description]
     */
    public static function includeCss($file)
    {
        ee()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.static::$themeUrl.$file.'" />');
    }

    /**
     * Settings helper.
     *
     * @param array  $data   Setting data
     * @param string $prefix
     *
     * @return array
     */
    public function get($data, $prefix)
    {
        $saveData = array();

        $prefix .= '_';

        $offset = strlen($prefix);

        foreach ($data as $saveKey => $save) {
            if (0 === strncmp($prefix, $saveKey, $offset)) {
                $saveData[substr($saveKey, $offset)] = $save;
            }
        }

        return $saveData;
    }

    private static function uuid_generate_random()
    {
        $uuid = bin2hex(random_bytes(16));

        return sprintf(
            '%08s-%04s-4%03s-%04x-%012s',
            // 32 bits for "time_low"
            substr($uuid, 0, 8),
            // 16 bits for "time_mid"
            substr($uuid, 8, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            substr($uuid, 13, 3),
            // 16 bits:
            // * 8 bits for "clk_seq_hi_res",
            // * 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            hexdec(substr($uuid, 16, 4)) & 0x3fff | 0x8000,
            // 48 bits for "node"
            substr($uuid, 20, 12)
        );
    }

    private function validateUuid(string $uuid, string $fieldId): bool
    {
        ee()->db->select('entry_id');

        $query =  ee()->db->get_where('exp_channel_data_field_' . $fieldId, array('field_id_' . $fieldId => $uuid));

        if ($query->num_rows() != 0) {
            return true;
        }

        return false;
    }

    /** @TODO move to helper:: */

    /**
     *
     */

    public static function getConfigFromFile(string $filePath): array
    {

        $path = PATH_THIRD  . $filePath . '.php';

        if (!file_exists($path)) {
                return [];
        }

        if (!\is_array($config = @include $path)) {
            return [];
        }

        return $config;

    }
}
