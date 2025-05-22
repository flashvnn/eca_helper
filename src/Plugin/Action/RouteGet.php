<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatchInterface; // Added
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Service\Token; // Added
use Symfony\Component\DependencyInjection\ContainerInterface; // Added

/**
 * Get current route name.
 *
 * @Action(
 *   id = "eca_helper_route_get",
 *   label = @Translation("ECA Helper: Get route name"),
 *   description = @Translation("Get current route name."),
 * )
 */
class RouteGet extends ConfigurableActionBase {

  /**
   * The current route match service.
   */
  protected CurrentRouteMatchInterface $routeMatch; // Added

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Token $token_service, CurrentRouteMatchInterface $route_match) { // Modified
    parent::__construct($configuration, $plugin_id, $plugin_definition, $token_service);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static { // Added
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('eca.service.token'),
      $container->get('current_route_match')
    );
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
      '#required' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('Name of token'),
      '#default_value' => $this->configuration['token_name'],
      '#description' => $this->t('The route name value will be loaded into this specified token.'),
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
  public function execute() {
    if (!empty($this->configuration['token_name'])) {
      // \Drupal::routeMatch() -> $this->routeMatch // MODIFIED
      $this->tokenService->addTokenData($this->configuration['token_name'], $this->routeMatch
        ->getRouteName());
    }
  }

}
