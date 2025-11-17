Feature: Outline scenarios without background steps

  Scenario: Simple scenarios without background steps - success 01
    Given there is a thing with ID "sc-01" Result "ok"
    When I create a thing with ID "sc-02" Result "ok"
    Then I see two things with ID "sc-03" Result "ok"

  Scenario Outline: Outline scenarios without background steps - main
    Given there is a thing with ID "sc-01" Result "<g-result>"
    When I create a thing with ID "sc-02" Result "<w-result>"
    Then I see two things with ID "sc-03" Result "<t-result>"
    Examples:
      | g-result | w-result | t-result |
      | ok       | ok       | ok       |
      | fail     | ok       | ok       |
      | ok       | fail     | ok       |
      | ok       | ok       | fail     |
      | success  | success  | success  |

  Scenario: Simple scenarios without background steps - success 02
    Given there is a thing with ID "sc-01" Result "ok"
    When I create a thing with ID "sc-02" Result "ok"
    Then I see two things with ID "sc-03" Result "ok"
