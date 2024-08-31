/**
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @copyright 2024 Marcus Green
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = () => {

    var selectAllCheckBox = document.getElementById('id_selectall');

    selectAllCheckBox.addEventListener('click', e => {
        // Hidden.
        document.querySelectorAll("[id^='id_activity']").forEach(checkbox => {
            checkbox.checked = e.target.checked ? true : false;
        });
        // Visible.
        document.querySelectorAll("[id^='id_cmid_']").forEach(checkbox => {
            checkbox.checked = e.target.checked ? true : false;
        });
    });

    var cmids = document.querySelectorAll('input[id^="id_cmid_"]');
    cmids.forEach(function (e) {
        e.addEventListener('click', cmidClick);
    });
    configureSelectAll();

    /**
     *
     * @param {*} e
     */
    function cmidClick(e) {
        var id = e.currentTarget.id.split('_')[2];
        var checkboxid = 'id_activitygroup_activity_' + id;
        var checkbox = document.getElementById(checkboxid);
        checkbox.checked = !checkbox.checked;
        configureSelectAll();
    }

    /**
     * Set up the selectAll checkbox
     */
    function configureSelectAll() {
        var selectAllCheckBox = document.getElementById('id_selectall');
        var allchecked = true;
        document.querySelectorAll("[id^='id_activity']").forEach(checkbox => {
            if (checkbox.checked == false) {
                allchecked = false;
            }
        });
        selectAllCheckBox.checked = allchecked;
    }
};

