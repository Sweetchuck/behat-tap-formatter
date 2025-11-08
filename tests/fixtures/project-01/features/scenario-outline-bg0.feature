Feature: Outline scenarios without background steps

  Scenario Outline: Outline scenarios without background steps
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
