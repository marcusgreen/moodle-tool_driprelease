@tool @tool_driprelease @tool_driprelease_coursegroups
Feature: Drip release modifies activity availability
    In order to set a course activities for drip/sequential availabiliy
  Background:
    Given the following "users" exist:
        | username | firstname | lastname | email                |
        | teacher1 | Teacher   | 1        | teacher1@example.com |
        | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
        | fullname     | shortname | format | groupmode |
        | Course 1     | C1        | topics | 2         |
        | Empty course | C2        | topics | 2         |
    And the following "groups" exist:
        | name    | course | idnumber |
        | Group 1 | C1     | G1       |
        | Group 2 | C1     | G2       |
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
        | activity | name  | course | intro                                                                                                                                  |
        | quiz     | Quiz1 | C1     | Loren sum dolor sit mate, sol um cu quo, est ea accustom investiture. Nahum slum vociferous e viz, ad discern inimical descriptionend. |
        | quiz     | Quiz2 | C1     |                                                                                                                                        |
        | quiz     | Quiz3 | C1     |                                                                                                                                        |
        | quiz     | Quiz4 | C1     |                                                                                                                                        |
        | quiz     | Quiz5 | C1     |                                                                                                                                        |
        | quiz     | Quiz6 | C1     |                                                                                                                                        |
        | quiz     | Quiz7 | C1     |                                                                                                                                        |

    And quiz "Quiz1" contains the following questions:
        | question       | page |
        | Short answer 1 | 1    |
        | Short answer 2 | 1    |
        | Short answer 3 | 1    |

  @javascript
  Scenario: Select/deselect activities to set their availability
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I navigate to "Drip release" in current page administration

    # Check the validation checks for empty fields works
    And I set the field "sessiongroup[sessionlength]" to ""
    And I should see "You must supply a value here"
    And I set the field "sessiongroup[sessionlength]" to "1"
    And I set the field "activitiespersession" to "1"
    And I set the field "coursegroup" to "Group 1"
    And I click on "select" "checkbox" in the "Quiz1" "table_row"
    And I press "Save and return to course"
    And I should see "You belong to Group 1 (hidden otherwise)"
