<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
 * This file contains the version information for the code plugin.
 *
 * @package    certificateelement_certificatenumber
 * @copyright  2025 John Joel Alfabete <example@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certificateelement_certificatenumber;

class element extends \tool_certificate\element {

/**
 * @var int Option to display certificate number
 */
const DISPLAY_CERTIFICATENUMBER = 1;

    public function render_form_elements($mform) {
        $options = [];
        $options[self::DISPLAY_CERTIFICATENUMBER] = get_string('displaycertificatenumber', 'certificateelement_certificatenumber');

        $mform->addElement('select', 'display', get_string('display', 'certificateelement_certificatenumber'), $options);
        $mform->addHelpButton('display', 'display', 'certificateelement_certificatenumber');
        $mform->setDefault('display', self::DISPLAY_CERTIFICATENUMBER);

        parent::render_form_elements($mform);
        $mform->setDefault('width', 35);
    }

    public function save_form_data(\stdClass $data) {
        $data->data = json_encode(['display' => $data->display]);
        parent::save_form_data($data);
    }

    protected function format_code($code) {
        $data = json_decode($this->get_data());
        return ($data->display == self::DISPLAY_CERTIFICATENUMBER) ? $this->generate_certificate_number() : $code;
    }

    protected function generate_certificate_number() {
        global $DB;

        // Get the highest ID from the certificate issues table.
        $maxId = $DB->get_field_sql("SELECT MAX(id) FROM {tool_certificate_issues}");
        return ($maxId === null) ? 1 : ((int)$maxId + 1);
    }

    public function render($pdf, $preview, $user, $issue) {
        global $DB;

        $data = json_decode($this->get_data());

        if ($data->display == self::DISPLAY_CERTIFICATENUMBER) {
            if (!$issue->id) {
                return; // Ensure the issue exists.
            }
            $certificateNumber = $issue->id;
            \tool_certificate\element_helper::render_content($pdf, $this, $certificateNumber);
        } else {
            \tool_certificate\element_helper::render_content($pdf, $this, $this->format_code($issue->code));
        }
    }

    public function render_html() {
        $data = json_decode($this->get_data(), true);
        $code = \tool_certificate\certificate::generate_code();
        return \tool_certificate\element_helper::render_html_content($this, $this->format_code($code));
    }

    public function prepare_data_for_form() {
        $record = parent::prepare_data_for_form();
        if ($this->get_data()) {
            $info = json_decode($this->get_data());
            $record->display = $info->display;
        }
        return $record;
    }

    public function get_width(): int {
        $width = $this->persistent->get('width');
        return $width > 0 ? $width : 35;
    }

}
