@tool @tool_driprelease @tool_driprelease_hideunselected
Feature: Unselected course items are set to hidden on save
        Background:
    Given the following "users" exist:
        | username | firstname | lastname | email                |
        | teacher1 | Teacher   | 1        | teacher1@example.com |
        | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
        | fullname | shortname | format | enablecompletion | category |
        | Course 1 | C1        | topics | 1                | 0        |
    And I log in as "admin"
  @javascript @_file_upload
  Scenario: Restore course containing quizzes
    When I am on the "Course 1" "restore" page
    And I press "Manage backup files"
    And I upload "admin/tool/driprelease/tests/fixtures/test_backup.mbz" file to "Files" filemanager
    And I press "Save changes"
    And I restore "test_backup.mbz" backup into a new course using this options:
        | Schema | Course name       | Course 2 |
        | Schema | Course short name | C2       |
    And I am on "Course 2" course homepage with editing mode on
    And I navigate to "Drip release" in current page administration
    # This will toggle all rows to checked
    And I click on "selectall" "checkbox"
    And I click on "Hide unselected" "checkbox"
    # Deselect rows for Quiz1
    And I click on "select" "checkbox" in the "Quiz1" "table_row"
    And I press "Save and return to course"
    And I should see "Hidden from students"
