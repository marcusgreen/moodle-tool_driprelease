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
 * @copyright 2022 Marcus Green
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
    cmids.forEach(function(e) {
        e.addEventListener('click', cmidClick);
    });
    configureSelectAll();
    configureSessions();

    /**
     * When an item checkbox is clicked, toggle the matching
     * checkbox in the activitygroup_activity group of checkboxes
     * This is to ensure the options are passed to form processing.
     *
     * @param {PointerEvent} e
     */
    function cmidClick(e) {
            var id = e.currentTarget.id.split('_')[2];
            var checkboxid = 'id_activitygroup_activity_' +id;
            var checkbox = document.getElementById(checkboxid);
            checkbox.checked = !checkbox.checked;
            configureSelectAll();
            configureSessions();
    }

    /**
     * Add event listener to all the session buttons that
     * toggle the items within a session.
     */
    function configureSessions(){
        var selectButtons = document.querySelectorAll('button[id^="sessionid_"]');
        selectButtons.forEach(function(e) {
            e.addEventListener('click', sessionClick);
        });
    }
    /**
     * Toggle all the checkboxes for the session and
     * the matching checkboxes in the hidden group
     * (for passing through to form processing)
     *
     * @param {PointerEvent} e
     */
    function sessionClick(e){
        var sessionid = e.currentTarget.id.split('_')[1];
        var sesscbx = document.querySelectorAll('input[id^="id_cmid_"]');
        sesscbx.forEach((cbx) => {
            var cboxid = cbx.id.split('_')[4];
            var itemid = cbx.id.split('_')[2];
                if ((cboxid == sessionid) && (cboxid > 0)) {
                    hiddencbx = 'id_activitygroup_activity_'+itemid;
                    var hiddencbx = document.getElementById(hiddencbx);
                    hiddencbx.checked = !cbx.checked;
                    cbx.checked = !cbx.checked;
                }
        });
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

