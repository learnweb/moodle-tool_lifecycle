<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tool_lifecycle\settings;

/**
 * Static admin setting builder class, which is used, to create and to add admin settings for tool_lifecycle.
 *
 * @package    tool_lifecycle
 * @copyright  2024 Thomas Niedermaier, University of Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_settings_builder {
    /**
     * The admin category type.
     *
     * @var string
     */
    private const ADMINTYPE = 'tools';

    /**
     * The plugin type.
     *
     * @var string
     */
    private const PLUGINTYPE = 'tool';

    /**
     * The name of the plugin.
     *
     * @var string
     */
    private const PLUGINNAME = self::PLUGINTYPE.'_'.'lifecycle';

    /**
     * Make this class not instantiable.
     */
    private function __construct() {
    }

    /**
     * Creates the settings for the lifecycle tool and adds them to the admin settings page.
     *
     * @return void
     */
    public static function create_settings(): void {

        global $ADMIN;
        if (!$ADMIN->fulltree) {
            self::create_settings_no_fulltree();
            return;
        }

        self::create_settings_fulltree();
    }

    /**
     * Creates the settings for all subplugins for no fulltree and adds them to the admin settings page.
     *
     * @param array $subplugins
     * The installed lifecycle subplugins.
     *
     * @return void
     */
    private static function create_settings_no_fulltree(): void {
        self::add_admin_category();
        self::add_admin_settingpage(self::PLUGINNAME.'_subplugins', 'subplugins');

        if (count($subplugins) <= 1) {
            self::add_admin_settingpage(self::PLUGINNAME.'_configuration', 'configuration');
            return;
        }

        foreach ($subplugins as $subplugin) {
            self::add_admin_settingpage(self::PLUGINNAME.'_configuration_' . $subplugin->id,
                'configuration_instance', $subplugin->name);
        }
    }

    /**
     * Creates the settings for all lifecycle subplugins for fulltree and adds them to the admin settings page.
     *
     * @param array $subplugins
     * The installed lifecycle subplugins.
     *
     * @return void
     */
    private static function create_settings_fulltree(): void {
        self::add_admin_category();

        foreach ($subplugins as $subplugin) {
            if (file_exists($settingsfile = $subplugin->path . '/settings.php')) {
                include($settingsfile);
            }
            if (count($subplugins) <= 1) {
                $settings = self::create_admin_settingpage(self::PLUGINNAME.'_configuration',
                    'configuration');
            } else {
                $settings = self::create_admin_settingpage(self::PLUGINNAME.'_configuration_' . $subplugin->id,
                    'configuration_subplugin', $subplugin->name);
            }

            self::add_config_settings_fulltree($settings, $subplugin->id);

            self::include_admin_settingpage($settings);
        }
    }

    /**
     * Adds an admin category to the admin settings page.
     *
     * @return void
     */
    private static function add_admin_category(): void {
        $category = new \admin_category(self::PLUGINNAME,
            new \lang_string('pluginname', self::PLUGINNAME)
        );

        global $ADMIN;
        $ADMIN->add(self::ADMINTYPE, $category);
    }

    /**
     * Adds an admin settingpage to the admin settings page.
     *
     * @param string $name
     * The internal name for this settingpage.
     *
     * @param string $stringidentifier
     * The identifier for the string, that is used for the displayed name for this settingpage.
     *
     * @param stdClass|array $stringidentifierarguments
     * Optional arguments, which the string for the passed identifier requires,
     * that is used for the displayed name for this settingpage.
     *
     * @return void
     */
    private static function add_admin_settingpage(string $name, string $stringidentifier,
                                                  $stringidentifierarguments = null): void {
        $settingpage = self::create_admin_settingpage($name, $stringidentifier, $stringidentifierarguments);
        self::include_admin_settingpage($settingpage);
    }

    /**
     * Creates an admin settingpage.
     *
     * @param string $name
     * The internal name for this settingpage.
     *
     * @param string $stringidentifier
     * The identifier for the string, that is used for the displayed name for this settingpage.
     *
     * @param stdClass|array $stringidentifierarguments
     * Optional arguments, which the string for the passed identifier requires,
     * that is used for the displayed name for this settingpage.
     *
     * @return \admin_settingpage
     * The created admin settingpage.
     */
    private static function create_admin_settingpage(string $name, string $stringidentifier,
                                                     $stringidentifierarguments = null): \admin_settingpage {
        return new \admin_settingpage($name,
            new \lang_string($stringidentifier, self::PLUGINNAME, $stringidentifierarguments)
        );
    }

    /**
     * Includes an admin settingpage in the admin settings page.
     *
     * @param \admin_settingpage $settingpage
     * The admin settingpage to include.
     *
     * @return void
     */
    private static function include_admin_settingpage(\admin_settingpage $settingpage): void {
        global $ADMIN;
        $ADMIN->add(self::PLUGINNAME, $settingpage);
    }

    /**
     * Adds the config settings for fulltree to the passed admin settingpage for the
     * passed subplugin.
     *
     * @param \admin_settingpage $settings
     * The admin settingpage, the config settings are added to.
     *
     * @param int $instanceid
     * The subplugin id, to that the added settings are associated.
     *
     * @return void
     */
    private static function add_config_settings_fulltree(\admin_settingpage $settings,
                                                         int $instanceid): void {
        self::add_admin_setting_configtext($settings,
            'tool_lifecycle/apiurl_' . $instanceid,
            'apiurl', 'apiurldesc',
            'https://stable.opencast.org',
            PARAM_URL
        );

        self::add_admin_setting_configtext($settings,
            'tool_lifecycle/apiusername_' . $instanceid,
            'apiusername', 'apiusernamedesc',
            'admin'
        );

        self::add_admin_setting_configpasswordunmask($settings,
            'tool_lifecycle/apipassword_' . $instanceid,
            'apipassword', 'apipassworddesc',
            'opencast'
        );

        self::add_admin_setting_configtext($settings,
            'tool_lifecycle/lticonsumerkey_' . $instanceid,
            'lticonsumerkey', 'lticonsumerkey_desc',
            ''
        );

        self::add_admin_setting_configpasswordunmask($settings,
            'tool_lifecycle/lticonsumersecret_' . $instanceid,
            'lticonsumersecret', 'lticonsumersecret_desc',
            ''
        );

        self::add_admin_setting_configtext($settings,
            'tool_lifecycle/apitimeout_' . $instanceid,
            'timeout', 'timeoutdesc',
            '2000',
            PARAM_INT
        );

        self::add_admin_setting_configtext($settings,
            'tool_lifecycle/apiconnecttimeout_' . $instanceid,
            'connecttimeout', 'connecttimeoutdesc',
            '1000',
            PARAM_INT
        );
    }

}
