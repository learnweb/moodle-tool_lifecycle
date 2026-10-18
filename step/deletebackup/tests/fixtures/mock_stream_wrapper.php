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

namespace lifecyclestep_deletebackup;

/**
 * Minimal in-memory stream wrapper that records chmod() calls on a mocked backup directory.
 *
 * @package    lifecyclestep_deletebackup
 * @category   test
 * @copyright  2026 ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mock_stream_wrapper {
    /** @var resource|null Stream context, set by PHP. */
    public $context;

    /** @var string[] Names of the files that exist in the mocked backup directory. */
    public static $files = [];

    /** @var string[] Paths for which chmod() was called. */
    public static $chmodcalls = [];

    /**
     * Returns the file status of the mocked directory and its files.
     *
     * @param string $path
     * @param int $flags
     * @return array|false
     */
    public function url_stat(string $path, int $flags) {
        $name = substr($path, strlen('lifecyclemock://'));
        if ($name === 'backups') {
            return ['mode' => 0040755, 'size' => 0];
        }
        if (str_starts_with($name, 'backups/') && in_array(substr($name, strlen('backups/')), self::$files, true)) {
            return ['mode' => 0100600, 'size' => 1];
        }
        return false;
    }

    /**
     * Records chmod() calls.
     *
     * @param string $path
     * @param int $option
     * @param mixed $value
     * @return bool
     */
    public function stream_metadata(string $path, int $option, $value): bool {
        self::$chmodcalls[] = $path;
        return true;
    }

    /**
     * Deletes a file from the mocked directory.
     *
     * @param string $path
     * @return bool
     */
    public function unlink(string $path): bool {
        self::$files = array_values(array_diff(self::$files, [substr($path, strlen('lifecyclemock://backups/'))]));
        return true;
    }
}
