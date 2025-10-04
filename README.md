# Behat TAP Formatter

[![QA](https://github.com/Sweetchuck/behat-tap-formatter/actions/workflows/qa.yml/badge.svg?branch=1.x)](https://github.com/Sweetchuck/behat-tap-formatter/actions/workflows/qa.yml)
[![codecov](https://codecov.io/gh/Sweetchuck/behat-tap-formatter/branch/1.x/graph/badge.svg?token=PXHVAU895N)](https://app.codecov.io/gh/Sweetchuck/behat-tap-formatter-1.x/branch/1.x)

A TAP (Test Anything Protocol) formatter for Behat. \
[TAP 14 specification]


## Usage

```shell
composer require --dev 'sweetchuck/behat-tap-formatter'
```

Add the extension to your `behat.yml` like this:

```yaml
default:
    extensions:
        Sweetchuck\BehatTapFormatter\TapFormatterExtension: ~
    formatters:
        tap:
            show_trace: true
            trace_depth: 3
            show_steps: 'on_failure'
            ##
            # Available placeholders:
            #  - {{ suite.title }}
            #  - {{ feature.title }}
            #  - {{ feature.filePath }}
            #  - {{ scenario.lineNumber }}
            #  - {{ scenario.title }}
            #  - {{ outline.title }}
            ##
            description_patterns:
                scenario: '{{ suite.title }}: {{ feature.title }} | {{ scenario.title}}'
                example: '{{ suite.title }}: {{ feature.title }} | {{ outline.title }} {{ example.title }}'
```

Run your tests with the following command:
```shell
behat --format='tap'
```

[TAP 14 specification]: https://testanything.org/tap-version-14-specification.html
