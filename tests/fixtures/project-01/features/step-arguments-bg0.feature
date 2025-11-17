Feature: Scenario step arguments

  Scenario: Scenario step arguments - success
    Given there is a thing with ID "sc-01" Result "ok" and string:
      """
      Line 1
      Line 2
      """
    And there is a thing with ID "sc-02" Result "ok" and table:
      | h1 | h2 |
      | c1 | c2 |
    Then there is a thing with ID "sc-03" Result "ok"

  Scenario: Scenario step arguments - string fail
    Given there is a thing with ID "sc-01" Result "fail" and string:
      """
      Line 1
      Line 2
      """
    And there is a thing with ID "sc-02" Result "ok" and table:
      | h1 | h2 |
      | c1 | c2 |
    Then there is a thing with ID "sc-03" Result "ok"

  Scenario: Scenario step arguments - table fail
    Given there is a thing with ID "sc-01" Result "ok" and string:
      """
      Line 1
      Line 2
      """
    And there is a thing with ID "sc-02" Result "fail" and table:
      | h1 | h2 |
      | c1 | c2 |
    Then there is a thing with ID "sc-03" Result "ok"
