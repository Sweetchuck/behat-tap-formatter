Feature: Simple scenarios with background steps

  Background:
    Given there is a thing with ID "bg-01" Result "ok"
    And there is a thing with ID "bg-02" Result "ok"

  Scenario: Simple scenarios with background steps - success
    Given there is a thing with ID "sc-01" Result "ok"
    When I create a thing with ID "sc-02" Result "ok"
    Then I see two things with ID "sc-03" Result "ok"

  Scenario: Simple scenarios with background steps - first step fails
    Given there is a thing with ID "sc-01" Result "fail"
    When I create a thing with ID "sc-02" Result "ok"
    Then I see two things with ID "sc-04" Result "ok"

  Scenario: Simple scenarios with background steps - middle step fails
    Given there is a thing with ID "sc-01" Result "ok"
    When I create a thing with ID "sc-02" Result "fail"
    Then I see two things with ID "sc-04" Result "ok"

  Scenario: Simple scenarios with background steps - last step fails
    Given there is a thing with ID "sc-01" Result "ok"
    When I create a thing with ID "sc-02" Result "ok"
    Then I see two things with ID "sc-04" Result "fail"
