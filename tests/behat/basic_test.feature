@tool @tool_driprelease @tool_driprelease_basic
Feature: Drip release modifies activity availability
              In order to set a course activities for drip/sequential availabiliy
        Background:
            Given the following "users" exist:
                  | username | firstname | lastname | email                |
                  | teacher1 | Teacher   | 1        | teacher1@example.com |
                  | student1 | Student   | 1        | student1@example.com |
              And the following "courses" exist:
                  | fullname     | shortname | format | enablecompletion |
                  | Course 1     | C1        | topics | 1                |
                  | Empty course | C2        | topics | 1                |
              And the following "course enrolments" exist:
                  | user     | course | role           |
                  | teacher1 | C1     | editingteacher |
                  | teacher1 | C2     | editingteacher |
                  | student1 | C1     | student        |
              And the following "question categories" exist:
                  | contextlevel | reference | name           |
                  | Course       | C1        | Test questions |
              And the following "questions" exist:
                  | questioncategory | qtype       | name           | questiontext                            | answer 1 | grade |
                  | Test questions   | shortanswer | Short answer 1 | Where is the capital city of France?    | Paris    | 100%  |
                  | Test questions   | shortanswer | Short answer 2 | Where is the capital city of Australia? | Canberra | 100%  |
                  | Test questions   | shortanswer | Short answer 3 | Where is the capital city of Germany?   | Berlin   | 100%  |

              And the following "activities" exist:
                  | activity | name  | course | intro            |
                  | quiz     | Quiz1 | C1     | quiz1description |
                  | quiz     | Quiz2 | C1     | 1                |
                  | quiz     | Quiz3 | C1     | 1                |
                  | quiz     | Quiz4 | C1     | 1                |
                  | quiz     | Quiz5 | C1     | 1                |
                  | quiz     | Quiz6 | C1     | 1                |
                  | quiz     | Quiz7 | C1     | 1                |
                  | quiz     | Quiz8 | C1     | 1                |

              And quiz "Quiz1" contains the following questions:
                  | question       | page |
                  | Short answer 1 | 1    |
                  | Short answer 2 | 1    |
                  | Short answer 3 | 1    |
        @javascript
        Scenario: Select/deselect activities to set their availability
            Given I log in as "teacher1"

              And I am on "Empty course" course homepage with editing mode on
              And I navigate to "Drip release" in current page administration
             Then I should see "No modules in course"
              And I am on "Course 1" course homepage with editing mode on
              And I navigate to "Drip release" in current page administration
    # Can I follow the link to view the settings of the quiz
              And I click on "Quiz1" "link" in the "Quiz1" "table_row"
              And I should see "Preview quiz"
              And I am on "Course 1" course homepage with editing mode on

             When I open "Quiz1" actions menu
              And I click on "Edit settings" "link" in the "Quiz1" activity
              And I expand all fieldsets
              And I set the field "Add requirements" to "1"
              And I set the field "completionusegrade" to "1"
              And I press "Save and return to course"


             When I open "Quiz2" actions menu
              And I click on "Edit settings" "link" in the "Quiz2" activity
              And I expand all fieldsets

              And I click on "Add restriction..." "button"
              And I click on "Activity completion" "button" in the "Add restriction..." "dialogue"

              And I click on ".availability-item .availability-eye img" "css_element"
              And I set the field "Activity or resource" to "Previous activity with completion"
              And I press "Save and return to course"

              And I navigate to "Drip release" in current page administration
    # To confirm the question count column is showing the count of questions in a quiz
             Then I should see "3" in the "Quiz1" "table_row"
    # Confirm that module discriptions wrap and are not truncated
              And I should see "quiz1description"
    # Confirm that a common "off by 1 error has not crept in"
              And I should not see "Session 0"

    # Check the validation checks for empty fields works
              And I set the field "sessionlength" to ""
              And I should see "You must supply a value here"
              And I set the field "sessionlength" to "1"
              And I set the field "activitiespersession" to ""
              And I should see "You must supply a value here"
              And I set the field "activitiespersession" to "100"
              And I press "Save and return to course"
             Then I should see "Activities per session is 100 but the course only has 8 activities"

              And I press "Cancel"
    # Confirm I am back on the course page
              And I should see "Participants"
              And I navigate to "Drip release" in current page administration
              And I set the field "schedulestart[day]" to "1"
              And I set the field "schedulestart[month]" to "January"
              And I set the field "schedulestart[year]" to "2017"

              And I click on "select" "checkbox" in the "Quiz1" "table_row"

    # Typically you would press Save and display, but this is to confirm this button works
              And I press "Save and return to course"
              And I navigate to "Drip release" in current page administration
    # Check the table and modules availability has been updated
             Then I should see "1 Jan 2017" in the "Quiz1" "table_row"
    # Confirm only the row with a selected checkbox have been updated
             Then I should not see "1 Jan 2017" in the "Quiz2" "table_row"
             Then I should see "7 Jan 2017" in the "Quiz1" "table_row"

              And I am on "Course 1" course homepage with editing mode on
    # Confirming that Moodle is saving/displaying the updated availability
    # On the course page
             Then I should see "It is after 1 January 2017"
    # Confirm that non date availability restrictions have not been cleared
             Then I should see "Not available unless"

              And I navigate to "Drip release" in current page administration
              And I follow "Show more..."

    # Check the dates have been saved
             Then I should see "1 Jan 2017" in the "Quiz1" "table_row"
    # Confirm only the row with a selected checkbox have been updated
             Then I should not see "7 Jan 2017" in the "Quiz2" "table_row"
             Then I should see "7 Jan 2017" in the "Quiz1" "table_row"
    # Now select the second module/quiz
              And I click on "select" "checkbox" in the "Quiz2" "table_row"
              And I press "Save and display"
    # Check the table and modules availability has been updated
             Then I should see "1 Jan 2017" in the "Quiz1" "table_row"
             Then I should see "7 Jan 2017" in the "Quiz2" "table_row"
    # Deselect rows for Quiz1 and 2
              And I click on "select" "checkbox" in the "Quiz1" "table_row"
              And I click on "select" "checkbox" in the "Quiz2" "table_row"

    # Select quiz 3
              And I click on "select" "checkbox" in the "Quiz3" "table_row"
              And I set the field "schedulestart[day]" to "1"
              And I set the field "schedulestart[month]" to "February"
              And I set the field "activitiespersession" to "5"

              And I press "Save and display"
             Then I should see "1 Jan 2017" in the "Quiz1" "table_row"

    # This doesn't work as it should and so is commented out.
    # Then I should see "1 Feb 2017" in the "Quiz3" "table_row"
    # This will toggle all rows to checked
              And I click on "selectall" "checkbox"
              And I press "Save and display"
             Then I should see "1 Feb 2017" in the "Quiz5" "table_row"
    # The next session
             Then I should see "8 Feb 2017" in the "Quiz6" "table_row"
              And I click on "select" "checkbox" in the "Quiz1" "table_row"
              And I click on "select" "checkbox" in the "Quiz2" "table_row"
              And I click on "select" "checkbox" in the "Quiz3" "table_row"
              And I click on "select" "checkbox" in the "Quiz4" "table_row"
              And I click on "select" "checkbox" in the "Quiz5" "table_row"
    # Skip date count over the unselected items.
              And I press "Save and display"
             Then I should see "1 Feb 2017" in the "Quiz6" "table_row"
    #There may still be some inconsistancies in the start from checked code.
