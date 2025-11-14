Feature: Simple scenarios without background steps

  Scenario: Simple scenarios without background steps - success
    Given there is a thing with ID "sc-01" Result "ok"
    When I create a thing with ID "sc-02" Result "ok"
    Then I see two things with ID "sc-03" Result "ok"

  Scenario: Simple scenarios without background steps - first step fails
    Given there is a thing with ID "sc-01" Result "fail"
    When I create a thing with ID "sc-02" Result "ok"
    Then I see two things with ID "sc-03" Result "ok"

  Scenario: Simple scenarios without background steps - middle step fails
    Given there is a thing with ID "sc-01" Result "ok"
    When I create a thing with ID "sc-02" Result "fail"
    Then I see two things with ID "sc-03" Result "ok"

  Scenario: Simple scenarios without background steps - last step fails
    Given there is a thing with ID "sc-01" Result "ok"
    When I create a thing with ID "sc-02" Result "ok"
    Then I see two things with ID "sc-03" Result "fail"
