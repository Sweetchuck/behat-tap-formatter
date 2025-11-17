# Behat TAP Formatter

[![QA](https://github.com/Sweetchuck/behat-tap-formatter/actions/workflows/qa.yml/badge.svg?branch=1.x)](https://github.com/Sweetchuck/behat-tap-formatter/actions/workflows/qa.yml)
[![codecov](https://codecov.io/gh/Sweetchuck/behat-tap-formatter/branch/1.x/graph/badge.svg?token=PXHVAU895N)](https://app.codecov.io/gh/Sweetchuck/behat-tap-formatter-1.x/branch/1.x)


## What is This?

A **TAP (Test Anything Protocol) formatter for Behat** that converts Behat test results into standardized TAP format.
This formatter outputs test results in a machine-readable format that conforms to the [TAP 14 specification],
enabling integration with CI/CD systems and tools that consume TAP output.


## When to Use

- **CI/CD Integration**: Parse Behat test results programmatically in your CI/CD pipeline
- **Machine-Readable Reports**: Generate structured test output for automated analysis and reporting
- **Multi-Format Testing**: Combine TAP output with other test formatters to serve different needs
- **Test Aggregation**: Feed results into TAP harnesses that aggregate and report on multiple test suites
- **Platform Integration**: Use with TAP-compatible tools for reporting, parsing, and analysis


## Configuration

**Command line:**
`behat --config-reference`:

**Output:**
```yaml
tap:
    # If TRUE then the exception trace is added into the YamlBlock when a test fails.
    show_trace:           true

    # Number of entries from the call stack to show. 0 to show all.
    # Used only when "show_trace" is TRUE.
    trace_depth:          3

    # Show steps as subtests.
    # Allowed values:
    # - never: Do not show the steps as subtest test points.
    # - on_failure: Shows the steps as subtest test points only when there was a failure.
    # - always: Always shows the steps as test points.
    show_executed_steps:  on_failure

    # When there was a failure, show the remaining steps as skipped test points.
    # This only makes sense when "show_executed_steps" is set to "on_failure" or "always".
    show_remaining_steps: false
```


**Quick starter**
```yaml
default:
    extensions:
        Sweetchuck\BehatTapFormatter\TapFormatterExtension: ~
    formatters:
        tap:
            show_trace: true
            trace_depth: 3
            show_executed_steps: 'on_failure'
            show_remaining_steps: false
```

Run your tests:

```shell
behat --format='tap'
```


## Configuration Options


### `show_trace` (boolean, default: `true`)

Include exception stack traces in the TAP YAML block when tests fail. Useful for debugging.


### `trace_depth` (integer, default: `3`)

Number of stack trace entries to display. Set to `0` to show all entries. Only used when `show_trace` is `true`.


### `show_executed_steps` (string, default: `'on_failure'`)

Control how individual steps are reported as subtests.

**Allowed values:**
- `'never'`: Don't show steps as subtest points
- `'on_failure'`: Show steps as subtest points only when a scenario fails
- `'always'`: Always show steps as subtest test points


### `show_remaining_steps` (boolean, default: `false`)

When a failure occurs, show remaining steps as skipped test points. Only meaningful with `show_executed_steps` set to `'on_failure'` or `'always'`.


## Output Examples

```gherkin
Feature: My feature 01

  Scenario: My scenario 01
    Given I have a thing
    When I create a thing
    Then I see two things

  Scenario: My scenario 02
    Given I have a thing
    When something broke
    Then I see two things
```


**Configuration:**
```yaml
show_trace: false
show_executed_steps: 'never'
show_remaining_steps: false
```

**Output:**
```
TAP version 14
ok 1 - default: My feature 01 | My scenario 01
not ok 2 - default: My feature 01 | My scenario 02
1..2
```


**Configuration:**
```yaml
show_trace: false
show_executed_steps: 'on_failure'
show_remaining_steps: false
```

**Output:**
```
TAP version 14
ok 1 - default: My feature 01 | My scenario 01
# Subtest: Steps
    ok 1 - I have a thing
    not ok 2 - When something broke
    1..3
not ok 2 - default: My feature 01 | My scenario 02
  ---
  feature:
    title: 'Scenario step arguments'
    file: my-feature-01.feature
  scenario:
    title: 'My scenario 02'
    line: 8
  step:
    text: 'When something broke'
    line: 10
  message: 'Depends on the exception.'
  ...
1..2
```


**Configuration:**
```yaml
show_trace: false
show_executed_steps: 'on_failure'
show_remaining_steps: true
```

**Output:**
```
TAP version 14
ok 1 - default: My feature 01 | My scenario 01
# Subtest: Steps
    ok 1 - I have a thing
    not ok 2 - When something broke
    not ok 3 - Then I see two things # SKIP previous step failed
    1..3
not ok 2 - default: My feature 01 | My scenario 02
  ---
  feature:
    title: 'Scenario step arguments'
    file: my-feature-01.feature
  scenario:
    title: 'My scenario 02'
    line: 8
  step:
    text: 'When something broke'
    line: 10
  message: 'Depends on the exception.'
  ...
1..2
```


[TAP 14 specification]: https://testanything.org/tap-version-14-specification.html
