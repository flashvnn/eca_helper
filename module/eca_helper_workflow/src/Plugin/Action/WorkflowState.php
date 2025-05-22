<?php

namespace Drupal\eca_helper_workflow\Plugin\Action;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Action get workflow state.
 *
 * @Action(
 *   id = "eca_helper_workflow_state",
 *   label = @Translation("ECA Helper Workflow: Get workflow state"),
 *   description = @Translation("Get workflow state instance of Entity."),
 *   type = "entity"
 * )
 */
class WorkflowState extends ConfigurableActionBase {

  /**
   * The Moderation Information service.
   */
  protected ModerationInformationInterface $moderationInfo;

  /**
   * Set the Moderation Information service.
   *
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderation_info
   *   The Moderation Information service.
   */
  protected function setModerationInfo(ModerationInformationInterface $moderation_info): void {
    $this->moderationInfo = $moderation_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setModerationInfo($container->get('content_moderation.moderation_information'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'type' => 'current',
      'property' => 'label',
      'token_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['type'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('State'),
      '#default_value' => $this->configuration['type'],
      '#options' => [
        'current' => $this->t('Current State'),
        'previous_state' => $this->t('Previous State'),
      ],
    ];

    $form['property'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('State Property'),
      '#default_value' => $this->configuration['property'],
      '#options' => [
        'label' => $this->t('State Label'),
        'id' => $this->t('State Id'),
      ],
    ];

    $form['token_name'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Name of token'),
      '#default_value' => $this->configuration['token_name'],
      '#description' => $this->t('The field value will be loaded into this specified token.'),
      '#weight' => -10,
      '#eca_token_reference' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['type'] = $form_state->getValue('type');
    $this->configuration['property'] = $form_state->getValue('property');
    $this->configuration['token_name'] = $form_state->getValue('token_name');

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!($entity instanceof EntityInterface)) {
      return;
    }

    if (!$this->moderationInfo->isModeratedEntity($entity)) {
      return;
    }

    if ($workflow = $this->moderationInfo->getWorkflowForEntity($entity)) {
      $type = $this->configuration['type'];
      $state = NULL;
      if ($type === 'current') {
        $state = $workflow->getTypePlugin()->getState($entity->moderation_state->value);
      }
      if ($type === 'previous_state') {
        $previous_state = FALSE;
        if (isset($entity->last_revision)) {
          $previous_state = $workflow->getTypePlugin()->getState($entity->last_revision->moderation_state->value);
        }
        if (!$previous_state) {
          $previous_state = $workflow->getTypePlugin()->getInitialState($entity);
        }
        $state = $previous_state;
      }
      $property = $this->configuration['property'];
      if ($state && $property === 'label') {
        $this->tokenService->addTokenData($this->configuration['token_name'], $state->label());
      }

      if ($state && $property === 'id') {
        $this->tokenService->addTokenData($this->configuration['token_name'], $state->id());
      }

    }
  }

}
