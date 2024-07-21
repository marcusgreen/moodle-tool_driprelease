@tool @tool_driprelease @tool_driprelease_multiple_modules
Feature: Testing multiple_modules in
  Background:
    Given the following "users" exist:
        | username | firstname | lastname | email                |
        | teacher1 | Teacher   | 1        | teacher1@example.com |
        | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
        | fullname | shortname | format | enablecompletion | category |
        | Course 1 | C1        | topics | 1                | 0        |
    And the following config values are set as admin:
      | enableasyncbackup | 0 |
    And I log in as "admin"
  @javascript @_file_upload
  Scenario: Restore course containing modules
    When I am on the "Course 1" "restore" page
    And I press "Manage course backups"
    And I upload "admin/tool/driprelease/tests/fixtures/multiple_mod_types.mbz" file to "Files" filemanager
    And I press "Save changes"
    And I restore "multiple_mod_types.mbz" backup into a new course using this options:
        | Schema | Course name       | Course 2 |
        | Schema | Course short name | C2       |
    And I am on "Course 2" course homepage with editing mode on
    And I navigate to "Drip release" in current page administration
    And I set the field "Activity type" to "Assignment"
    And I press "Refresh"

    And I follow "Show more..."
    And I set the field "Session length" to "1"

    # This will toggle all rows to checked
    And I click on "selectall" "checkbox"
    And I click on "Hide unselected" "checkbox"

    # Deselect rows for Assignment 1
    And I click on "select" "checkbox" in the "Assign1" "table_row"
    And I press "Save and return to course"
    And I should see "Hidden from students"
