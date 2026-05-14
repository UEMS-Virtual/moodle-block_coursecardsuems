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

/**
 * User perspective resolver.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

use context_course;

/**
 * Resolves the student, tutor, teacher and admin perspectives available to a user.
 */
class user_perspective_resolver {

    /** @var informative_period_reader Informative period reader. */
    private $periodreader;

    /** Student perspective key. */
    public const STUDENT = 'student';

    /** Tutor perspective key. */
    public const TUTOR = 'tutor';

    /** Teacher perspective key. */
    public const TEACHER = 'teacher';

    /** Admin perspective key. */
    public const ADMIN = 'admin';

    /** Capability that marks student-facing block content access. */
    private const VIEW_CONTENT_CAPABILITY = 'block/coursecardsuems:viewcontent';

    /** Tutor role shortnames. */
    private const TUTOR_ROLES = ['mod_tutor', 'mod_medpdg'];

    /** Teacher role shortnames. */
    private const TEACHER_ROLES = ['editingteacher', 'teacher', 'mod_prof'];

    /** Tie-break order for non-admin users. */
    private const DEFAULT_TIEBREAK = [self::TEACHER, self::TUTOR, self::STUDENT];

    /**
     * Constructor.
     *
     * @param informative_period_reader|null $periodreader Informative period reader.
     */
    public function __construct(?informative_period_reader $periodreader = null) {
        $this->periodreader = $periodreader ?? new informative_period_reader();
    }

    /**
     * Resolves perspectives for a user over an already scoped candidate course list.
     *
     * The caller is responsible for passing only EaD courses in the current academic window.
     * Courses are not deduplicated across perspectives: if Moodle contains conflicting role
     * assignments, the inconsistency remains visible to the user and support team.
     *
     * @param array $courses Candidate course records.
     * @param int|null $userid User id. Defaults to current user.
     * @param bool|null $issiteadmin Whether the user is site admin. Defaults to is_siteadmin().
     * @return array Perspective view models.
     */
    public function resolve(array $courses, ?int $userid = null, ?bool $issiteadmin = null): array {
        global $USER;

        $userid = $userid ?? (int) $USER->id;
        $issiteadmin = $issiteadmin ?? is_siteadmin($userid);

        $courseidsbyperspective = [
            self::STUDENT => [],
            self::TUTOR => [],
            self::TEACHER => [],
        ];

        foreach ($courses as $course) {
            if (empty($course->id)) {
                continue;
            }

            $courseid = (int) $course->id;
            $context = context_course::instance($courseid);

            if ($this->has_complete_schedule($courseid) &&
                    has_capability(self::VIEW_CONTENT_CAPABILITY, $context, $userid, false)) {
                $courseidsbyperspective[self::STUDENT][] = $courseid;
            }

            $roles = $this->get_course_role_shortnames($context, $userid);
            if (!empty(array_intersect($roles, self::TUTOR_ROLES))) {
                $courseidsbyperspective[self::TUTOR][] = $courseid;
            }
            if (!empty(array_intersect($roles, self::TEACHER_ROLES))) {
                $courseidsbyperspective[self::TEACHER][] = $courseid;
            }
        }

        $perspectives = [];
        foreach ([self::STUDENT, self::TUTOR, self::TEACHER] as $key) {
            if (empty($courseidsbyperspective[$key])) {
                continue;
            }
            $perspectives[] = $this->build_perspective($key, $courseidsbyperspective[$key]);
        }

        if ($issiteadmin && !empty($courses)) {
            $perspectives[] = $this->build_perspective(self::ADMIN, array_values(array_map(static function($course): int {
                return (int) $course->id;
            }, array_filter($courses, static function($course): bool {
                return !empty($course->id);
            }))));
        }

        $defaultkey = $this->get_default_perspective_key($perspectives, $issiteadmin);
        foreach ($perspectives as &$perspective) {
            $perspective['isdefault'] = $perspective['key'] === $defaultkey;
        }
        unset($perspective);

        return $perspectives;
    }

    /**
     * Returns whether a course has a complete Cronograma da disciplina.
     *
     * @param int $courseid Course id.
     * @return bool
     */
    private function has_complete_schedule(int $courseid): bool {
        return $this->periodreader->get_period($courseid)->has_complete_range();
    }

    /**
     * Returns role shortnames assigned directly in the course context.
     *
     * @param context_course $context Course context.
     * @param int $userid User id.
     * @return array Role shortnames.
     */
    private function get_course_role_shortnames(context_course $context, int $userid): array {
        $roles = get_user_roles($context, $userid, false);

        return array_values(array_map(static function($role): string {
            return (string) $role->shortname;
        }, $roles));
    }

    /**
     * Builds a perspective view model.
     *
     * @param string $key Perspective key.
     * @param array $courseids Course ids.
     * @return array
     */
    private function build_perspective(string $key, array $courseids): array {
        $courseids = array_values(array_unique(array_map('intval', $courseids)));

        return [
            'key' => $key,
            'label' => get_string('perspective_' . $key, 'block_coursecardsuems'),
            'courseids' => $courseids,
            'count' => count($courseids),
            'isdefault' => false,
        ];
    }

    /**
     * Returns the default perspective key.
     *
     * @param array $perspectives Perspective view models.
     * @param bool $issiteadmin Whether the user is site admin.
     * @return string|null
     */
    private function get_default_perspective_key(array $perspectives, bool $issiteadmin): ?string {
        if (empty($perspectives)) {
            return null;
        }

        if ($issiteadmin && $this->has_perspective($perspectives, self::ADMIN)) {
            return self::ADMIN;
        }

        $maxcount = max(array_map(static function(array $perspective): int {
            return (int) $perspective['count'];
        }, $perspectives));

        $candidates = array_values(array_filter($perspectives, static function(array $perspective) use ($maxcount): bool {
            return (int) $perspective['count'] === $maxcount;
        }));

        foreach (self::DEFAULT_TIEBREAK as $key) {
            if ($this->has_perspective($candidates, $key)) {
                return $key;
            }
        }

        return $candidates[0]['key'];
    }

    /**
     * Returns whether a perspective list contains a key.
     *
     * @param array $perspectives Perspective view models.
     * @param string $key Perspective key.
     * @return bool
     */
    private function has_perspective(array $perspectives, string $key): bool {
        foreach ($perspectives as $perspective) {
            if (($perspective['key'] ?? '') === $key) {
                return true;
            }
        }

        return false;
    }
}
