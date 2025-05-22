<?php

namespace Drupal\eca_helper_workflow\Plugin\Action;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Action get workflow label.
 *
 * @Action(
 *   id = "eca_helper_workflow_for_entity",
 *   label = @Translation("ECA Helper Workflow: Get workflow for Entity"),
 *   description = @Translation("Get workflow instance of Entity."),
 *   type = "entity"
 * )
 */
class WorkflowForEntity extends ConfigurableActionBase {

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
      'token_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

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
      $this->tokenService->addTokenData($this->configuration['token_name'], $workflow);
    }
  }

}
