# ECA Helper

## Add helpers functions for ECA

**Events**
- Preprocess event

**Actions**:

- Http request client
- Workflow label
- Workflow state
- Server variable Get `$_SERVER, $_COOKIE, $_SESSION, $_ENV, $_GET, $_POST` value
- Cookie set value for response.
- Form Set any element field value by key
- Form Get any element field value by key
- Form dump data
- ThirdPartySetting Get/Set value
- Preprocess Get/Set value for variables
- Preprocess attach library
- Preprocess add css class
- Preprocess attach library
- Preprocess remove item
- Preprocess dump variable
- Dumper data
- Route get current value
- Status message alter
- Header Set value
- Header Footer tag Set/Get value

**ECA Quick Action**

Create quick action for ECA without need define plugin and module.

Create file `docroot/sites/eca/EcaActions.php`

```php
<?php

/**
 * Define the ECA quick actions.
 *
 * Define action id, label, and callback, service.
 */
function ECAQuickActions(): array {
  return [
    "hello" => [
      "label" => "ECA Action Hello",
      "callback" => "eca_quick_actions_hello"
    ],
    "hello_inline" => [
      "label" => "ECA Action Inline",
      "callback" => function ($hello) {
        \Drupal::messenger()->addMessage('Inline callback' . $hello);
      }
    ],
    "hello_service" => [
      "label" => "ECA Action Call Drupal service",
      "service" => "messenger",
      "callback" => "addMessage"
    ]
  ];
}

function eca_quick_actions_hello($hello) {
  \Drupal::messenger()->addMessage('Hello world: ' . $hello);
  return "Hello world: " . $hello;
}

```

Use `ECA Helper: Quick Action` and choose the `ECA Action Hello`
