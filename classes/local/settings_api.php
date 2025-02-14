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

namespace tool_lifecycle\local;

defined('MOODLE_INTERNAL') || die;

/**
 * Settings API for lifecycle.
 *
 * This static class is used by the lifecycle plugins, to fetch information about the settings of
 * the installed lifecycle plugins as well as of the plugin tool_lifecycle itself.
 * A lifecycle subplugin is also defined and configured with the admin settings of tool_lifecycle.
 *
 * @package    tool_lifecycle
 * @copyright  2024 Thomas Niedermaier, University of Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_api {

    /**
     * Make this class not instantiable.
     */
    private function __construct() {
    }

    /**
     * Returns the version of the plugin tool_opencast as string
     * or false, if the corresponding config was not found.
     *
     * @return string|bool
     * The requested config as string or false, if the corresponding config was not found.
     *
     * @throws \dml_exception
     */
    public static function get_plugin_version() {
        return get_config('tool_opencast', 'version');
    }

    /**
     * Returns the api url of an Opencast instance as string
     * or false, if the corresponding config was not found.
     *
     * @param int $ocinstanceid
     * The id of the Opencast instance, for that the config is retrieved.
     *
     * @return string|bool
     * The requested config as string or false, if the corresponding config was not found.
     *
     * @throws \dml_exception
     */
    public static function get_apiurl(int $ocinstanceid) {
        return get_config('tool_opencast', 'apiurl_' . $ocinstanceid);
    }

    /**
     * Returns the api username of an Opencast instance as string
     * or false, if the corresponding config was not found.
     *
     * @param int $ocinstanceid
     * The id of the Opencast instance, for that the config is retrieved.
     *
     * @return string|bool
     * The requested config as string or false, if the corresponding config was not found.
     *
     * @throws \dml_exception
     */
    public static function get_apiusername(int $ocinstanceid) {
        return get_config('tool_opencast', 'apiusername_' . $ocinstanceid);
    }

    /**
     * Returns the api password of an Opencast instance as string
     * or false, if the corresponding config was not found.
     *
     * @param int $ocinstanceid
     * The id of the Opencast instance, for that the config is retrieved.
     *
     * @return string|bool
     * The requested config as string or false, if the corresponding config was not found.
     *
     * @throws \dml_exception
     */
    public static function get_apipassword(int $ocinstanceid) {
        return get_config('tool_opencast', 'apipassword_' . $ocinstanceid);
    }

    /**
     * Returns the api timeout of an Opencast instance as string
     * or false, if the corresponding config was not found.
     *
     * @param int $ocinstanceid
     * The id of the Opencast instance, for that the config is retrieved.
     *
     * @return string|bool
     * The requested config as string or false, if the corresponding config was not found.
     *
     * @throws \dml_exception
     */
    public static function get_apitimeout(int $ocinstanceid) {
        return get_config('tool_opencast', 'apitimeout_' . $ocinstanceid);
    }

    /**
     * Returns the api connecttimeout of an Opencast instance as string
     * or false, if the corresponding config was not found.
     *
     * @param int $ocinstanceid
     * The id of the Opencast instance, for that the config is retrieved.
     *
     * @return string|bool
     * The requested config as string or false, if the corresponding config was not found.
     *
     * @throws \dml_exception
     */
    public static function get_apiconnecttimeout(int $ocinstanceid) {
        return get_config('tool_opencast', 'apiconnecttimeout_' . $ocinstanceid);
    }

    /**
     * Returns the lticonsumerkey of an Opencast instance as string
     * or false, if the corresponding config was not found.
     *
     * @param int $ocinstanceid
     * The id of the Opencast instance, for that the config is retrieved.
     *
     * @return string|bool
     * The requested config as string or false, if the corresponding config was not found.
     *
     * @throws \dml_exception
     */
    public static function get_lticonsumerkey(int $ocinstanceid) {
        return get_config('tool_opencast', 'lticonsumerkey_' . $ocinstanceid);
    }

    /**
     * Returns the lticonsumersecret of an Opencast instance as string
     * or false, if the corresponding config was not found.
     *
     * @param int $ocinstanceid
     * The id of the Opencast instance, for that the config is retrieved.
     *
     * @return string|bool
     * The requested config as string or false, if the corresponding config was not found.
     *
     * @throws \dml_exception
     */
    public static function get_lticonsumersecret(int $ocinstanceid) {
        return get_config('tool_opencast', 'lticonsumersecret_' . $ocinstanceid);
    }

    /**
     * Return the Opencast instance for the passed Opencast instance id, if any.
     * If no Opencast instance with this id is configured, null is returned.
     *
     * @param int $ocinstanceid
     * The id of the requested Opencast instance.
     *
     * @return opencast_instance|null
     * The corresponding Opencast instance or null.
     */
    public static function get_ocinstance(int $ocinstanceid): ?opencast_instance {
        $ocinstances = self::get_ocinstances();

        foreach ($ocinstances as $ocinstance) {
            if (intval($ocinstance->id) === intval($ocinstanceid)) {
                return $ocinstance;
            }
        }

        return null;
    }

    /**
     * Returns the default Opencast instance, if any.
     * If no default Opencast instance is configured, null is returned.
     *
     * @return opencast_instance|null
     * The corresponding Opencast instance or null.
     */
    public static function get_default_ocinstance(): ?opencast_instance {
        $ocinstances = self::get_ocinstances();

        foreach ($ocinstances as $ocinstance) {
            if (boolval($ocinstance->isdefault) === true) {
                return $ocinstance;
            }
        }

        return null;
    }

    /**
     * Returns all configured Opencast instances as array.
     *
     * This array contains instances of the class opencast_instance only.
     *
     * @return array
     * All configured Opencast instances as array.
     */
    public static function get_ocinstances(): array {
        try {
            $ocinstancesconfig = get_config('tool_opencast', 'ocinstances');
        } catch (\dml_exception $exception) {
            return [];
        }

        $dynamicocinstances = json_decode($ocinstancesconfig);

        $ocinstances = [];
        foreach ($dynamicocinstances as $dynamicocinstance) {
            $ocinstances[] = new opencast_instance($dynamicocinstance);
        }

        return $ocinstances;
    }

    /**
     * Sets all configured Opencast instances to the passed Opencast instance,
     * namely, the passed Opencast instance will be the only configured Opencast instance
     * afterwards.
     *
     * @param \stdClass $dynamicocinstance
     * The Opencast instance, to that all configured Opencast instances are set to.
     */
    public static function set_ocinstances_to_ocinstance($dynamicocinstance): void {
        set_config('ocinstances', json_encode([$dynamicocinstance]), 'tool_opencast');
    }

    /**
     * Returns the number of configured Opencast instances.
     *
     * @return int
     * The number of configured Opencast instances.
     */
    public static function num_ocinstances(): int {
        return count(self::get_ocinstances());
    }
}
