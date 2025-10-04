Feature: Basic 01.

  Scenario: My scenario 01.
    Given a dummy thing with "01" and "ok"
    And a dummy thing with "02" and "ok"
    When I create a dummy thing with "03" and "ok"
    Then I should have a dummy thing with "04" and "ok"

  Scenario: My scenario 02.
    Given a dummy thing with "01" and "ok"
    When I create a dummy thing with "02" and "ok"
    And I create a dummy thing with "03" and "ok"
    Then I should have a dummy thing with "04" and "not ok"

  Scenario: My scenario 03.
    Given a dummy thing with "01" and "ok"
    And a dummy thing with "02" and "ok"
    When I create a dummy thing with "03" and "ok"
    Then I should have a dummy thing with "04" and "ok"
