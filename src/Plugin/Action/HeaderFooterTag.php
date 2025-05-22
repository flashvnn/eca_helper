<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;

/**
 * Add header footer tag data.
 *
 * @Action(
 *   id = "eca_helper_header_footer_tag",
 *   label = @Translation("ECA Helper: Header footer tag and script"),
 *   description = @Translation("Add header footer tag, script and style."),
 * )
 */
class HeaderFooterTag extends ConfigurableActionBase {
  /**
   * Storage the header array.
   */
  public static array $data = [];

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'position' => 'header',
      'type' => 'markup',
      'content' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['position'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Position'),
      '#description' => $this->t('Choose position insert tag.'),
      '#default_value' => $this->configuration['position'],
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#options' => [
        'header' => $this->t('Header'),
        'top' => $this->t('Page Top'),
        'bottom' => $this->t('Page Bottom'),
      ],
    ];

    $form['type'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Tag type'),
      '#description' => $this->t('Select tag type.'),
      '#default_value' => $this->configuration['type'],
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#options' => [
        'markup' => $this->t('Markup'),
        'script' => $this->t('Script'),
        'style' => $this->t('Css Style'),
      ],
    ];
    $form['content'] = [
      '#type' => 'textarea',
      '#required' => FALSE,
      '#title' => $this->t('Tag content'),
      '#description' => $this->t('Input tag content.'),
      '#cols' => 60,
      '#rows' => 5,
      '#default_value' => $this->configuration['content'],
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['position'] = $form_state->getValue('position');
    $this->configuration['type'] = $form_state->getValue('type');
    $this->configuration['content'] = $form_state->getValue('content');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $position = $this->configuration['position'];
    $type = $this->configuration['type'];
    $content = $this->tokenService->getOrReplace($this->configuration['content']);
    if ($content instanceof TypedDataInterface) {
      $content = $content->getValue();
    }
    $content = (string) $content;
    self::$data[$position][$type][] = $content;
  }

}
