Feature: Simple scenarios with background steps

  Background:
    Given there is a thing with ID "bg-01" Result "ok"
    And there is a thing with ID "bg-02" Result "fail"

  Scenario: Simple scenarios with background steps - 01
    Given there is a thing with ID "sc-01" Result "ok"
    When I create a thing with ID "sc-02" Result "ok"
    Then I see two things with ID "sc-03" Result "ok"

  Scenario: Simple scenarios with background steps - 02
    Given there is a thing with ID "sc-01" Result "ok"
    When I create a thing with ID "sc-02" Result "ok"
    Then I see two things with ID "sc-03" Result "ok"

  Scenario: Simple scenarios with background steps - 03
    Given there is a thing with ID "sc-01" Result "ok"
    When I create a thing with ID "sc-02" Result "ok"
    Then I see two things with ID "sc-03" Result "ok"
