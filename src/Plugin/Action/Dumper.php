<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Plugin\DataType\DataTransferObject;
use Drupal\user\Entity\Role;

/**
 * Set access to a form field.
 *
 * @Action(
 *   id = "eca_helper_dumper",
 *   label = @Translation("ECA Helper: Dumper"),
 *   description = @Translation("Dump the data."),
 *   type = "form"
 * )
 */
class Dumper extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'data' => '',
      'role' => 'administrator',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['data'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Value'),
      '#reqruired' => TRUE,
      '#description' => $this->t('The data used for dumping.'),
      '#default_value' => $this->configuration['data'],
      '#eca_token_replacement' => TRUE,
    ];

    // Role element.
    $roles = Role::loadMultiple();
    $role_options = [];

    foreach ($roles as $role_id => $role) {
      $role_options[$role_id] = $role->label();
    }
    $form['role'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Role'),
      '#description' => $this->t('The role used for dumping.'),
      '#default_value' => $this->configuration['role'],
      '#options' => $role_options,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['data'] = $form_state->getValue('data');
    $this->configuration['role'] = $form_state->getValue('role');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $role = $this->tokenService->getOrReplace($this->configuration['role']) ?? 'administrator';
    if (!$this->currentUser->hasRole($role)) {
      return;
    }

    $value = $this->tokenService->getOrReplace($this->configuration['data']);
    if ($value instanceof DataTransferObject) {
      $value = $value->toArray();
      if (isset($value[0]) && count($value) === 1 && (is_scalar($value[0]) || ($value[0] instanceof TranslatableMarkup))) {
        $value = reset($value);
      }
    }
    elseif (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
      $value = (string) $value;
    }

    if ($value instanceof StringData) {
      $value = $value->getString();
    }

    if (function_exists('dump')) {
      dump($value);
    }
    else {
      $this->messenger()
        ->addWarning($this->t('The symfony/var-dumper not found. Please install with command "composer require symfony/var-dumper".'));
    }
  }

}
