<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Service\YamlParser;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Simple http request action for ECA.
 *
 * @Action(
 *   id = "eca_helper_http_request",
 *   label = @Translation("ECA Helper: Http Request"),
 *   description = @Translation("Add Http request for ECA"),
 * )
 */
class HttpRequest extends ConfigurableActionBase {

  use Helper;

  /**
   * The YAML parser.
   */
  protected YamlParser $yamlParser;

  /**
   * The Http Client.
   */
  protected ClientInterface $httpClient;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setYamlParser($container->get('eca.service.yaml_parser'));
    $instance->setHttpClient($container->get('http_client'));
    return $instance;
  }

  /**
   * Set the YAML parser.
   *
   * @param \Drupal\eca\Service\YamlParser $yaml_parser
   *   The YAML parser.
   */
  public function setYamlParser(YamlParser $yaml_parser): void {
    $this->yamlParser = $yaml_parser;
  }

  /**
   * Set the http client.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The http client.
   */
  public function setHttpClient(ClientInterface $client): void {
    $this->httpClient = $client;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'method' => 'get',
      'url' => '',
      'data' => '',
      'data_yaml' => 0,
      'data_serialization' => 'raw',
      'query_parameters' => '',
      'headers' => '',
      'cookies' => '',
      'request_options' => '',
      'token_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['method'] = [
      '#type' => 'select',
      '#title' => $this->t('Method'),
      '#default_value' => $this->configuration['method'],
      '#options' => [
        'get' => $this->t('GET'),
        'post' => $this->t('POST'),
        'patch' => $this->t('PATCH'),
        'put' => $this->t('PUT'),
        'delete' => $this->t('DELETE'),
        'head' => $this->t('HEAD'),
      ],
    ];

    $form['url'] = [
      '#required' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $this->configuration['url'],
    ];

    $form['data'] = [
      '#type' => 'textarea',
      '#required' => FALSE,
      '#title' => $this->t('Data'),
      '#default_value' => $this->configuration['data'],
    ];

    $form['data_yaml'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Data YAML'),
      '#description' => $this->t('Use Data as yaml format.'),
      '#required' => FALSE,
      '#return_value' => 1,
      '#default_value' => $this->configuration['data_yaml'],
    ];

    $form['data_serialization'] = [
      '#type' => 'select',
      '#title' => $this->t('Data Serialization'),
      '#default_value' => $this->configuration['data_serialization'],
      '#options' => [
        'raw' => $this->t('Raw'),
        'json' => $this->t('JSON'),
        'url_encode' => $this->t('URL Encode'),
        'multipart' => $this->t('Multipart upload'),
      ],
    ];

    $form['query_parameters'] = [
      '#type' => 'textarea',
      '#required' => FALSE,
      '#title' => $this->t('Query Parameters'),
      '#description' => $this->t('YAML format, for example mykey: myvalue. When using tokens and YAML altogether, make sure that tokens are wrapped as a string. Example: title: "[node:title]"'),
      '#default_value' => $this->configuration['query_parameters'],
    ];

    $form['headers'] = [
      '#type' => 'textarea',
      '#required' => FALSE,
      '#title' => $this->t('Headers'),
      '#description' => $this->t('Headers with YAML format.'),
      '#default_value' => $this->configuration['headers'],
    ];

    $form['cookies'] = [
      '#type' => 'textarea',
      '#required' => FALSE,
      '#title' => $this->t('Cookies'),
      '#description' => $this->t('Cookies with YAML format.'),
      '#default_value' => $this->configuration['cookies'],
    ];

    $form['request_options'] = [
      '#type' => 'textarea',
      '#required' => FALSE,
      '#title' => $this->t('Options'),
      '#description' => $this->t('The Guzzle request options with YAML format. Reference: https://docs.guzzlephp.org/en/stable/request-options.html'),
      '#default_value' => $this->configuration['request_options'],
    ];

    $form['token_name'] = [
      '#required' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('Name of response token'),
      '#default_value' => $this->configuration['token_name'],
      '#description' => $this->t('The response value will be loaded into this specified token. You can access [token_name:body] raw response, [token_name:json] when return is valid json, [token_name:headers], [token_name:status], [token_name:client_error] > 0 when has problem.',),
      '#eca_token_reference' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['method'] = $form_state->getValue('method');
    $this->configuration['url'] = $form_state->getValue('url');
    $this->configuration['data'] = $form_state->getValue('data');
    $this->configuration['data_yaml'] = $form_state->getValue('data_yaml');
    $this->configuration['data_serialization'] = $form_state->getValue('data_serialization');
    $this->configuration['query_parameters'] = $form_state->getValue('query_parameters');
    $this->configuration['headers'] = $form_state->getValue('headers');
    $this->configuration['cookies'] = $form_state->getValue('cookies');
    $this->configuration['request_options'] = $form_state->getValue('request_options');
    $this->configuration['token_name'] = $form_state->getValue('token_name');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Check array is assoc.
   *
   * @param array $arr
   *   The array.
   *
   * @return bool
   *   Check result.
   */
  protected function isAssoc(array $arr): bool {
    if ([] === $arr) {
      return FALSE;
    }
    return array_keys($arr) !== range(0, count($arr) - 1);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $tokenService = $this->tokenService;
    $method = $this->configuration['method'];
    $url = $tokenService->getOrReplace($this->configuration['url']);
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
      $this->logger->error('The url is not a valid URL: ' . $url);
      return;
    }

    $data = $this->configuration['data'];
    if ($data && mb_strlen($data) > 0) {
      $data = $tokenService->getOrReplace($data);
    }

    $data_yaml = $this->configuration['data_yaml'];
    if ($data_yaml && is_string($data)) {
      try {
        $data = $this->yamlParser->parse($data);
      }
      catch (ParseException $e) {
        $this->logger->error('Tried parsing data as YAML format, but parsing failed.');
        return;
      }
    }

    $data_serialization = $this->configuration['data_serialization'];

    $query_parameters = $tokenService->getOrReplace($this->configuration['query_parameters']);
    try {
      $query_parameters = $this->yamlParser->parse($query_parameters);
    }
    catch (ParseException $e) {
      $this->logger->error('Tried parsing query parameters as YAML format, but parsing failed.');
      return;
    }

    $headers = $tokenService->getOrReplace($this->configuration['headers']);
    try {
      $headers = $this->yamlParser->parse($headers);
    }
    catch (ParseException $e) {
      $this->logger->error('Tried parsing headers as YAML format, but parsing failed.');
      return;
    }

    $cookies = $tokenService->getOrReplace($this->configuration['cookies']);
    try {
      $cookies = $this->yamlParser->parse($cookies);
    }
    catch (ParseException $e) {
      $this->logger->error('Tried parsing cookies as YAML format, but parsing failed.');
      return;
    }

    $request_options = $tokenService->getOrReplace($this->configuration['request_options']);
    try {
      $request_options = $this->yamlParser->parse($request_options);
    }
    catch (ParseException $e) {
      $this->logger->error('Tried parsing request options as YAML format, but parsing failed.');
      return;
    }
    if (empty($request_options)) {
      $request_options = [];
    }

    $options = [];
    if (!empty($headers)) {
      $options['headers'] = $headers;
    }
    if (!empty($cookies)) {
      $parsed = parse_url($url);
      $jar = CookieJar::fromArray(
        $cookies,
        $parsed['host']
      );
      $options['cookies'] = $jar;
    }

    if (!empty($data)) {
      if ($data_serialization === 'raw') {
        $options['body'] = $data;
      }
      if ($data_serialization === 'json') {
        $options['json'] = is_string($data) ? Json::decode($data) : $data;
      }
      if ($data_serialization === 'url_encode') {
        $options['form_params'] = is_string($data) ? urlencode($data) : $data;
      }
      if ($data_serialization === 'multipart') {
        $multipart = is_array($data) ? $data : [];
        if ($this->isAssoc($multipart)) {
          $multipart = [$multipart];
        }

        foreach ($multipart as &$item) {
          if (is_array($item) && isset($item['contents']) && file_exists($item['contents'])) {
            $item['contents'] = fopen($item['contents'], 'r');
          }
        }

        $options['multipart'] = $multipart;
      }
    }

    if (!empty($query_parameters) && is_array($query_parameters)) {
      $options['query'] = $query_parameters;
    }

    // Merge the request options.
    $options = NestedArray::mergeDeep($options, $request_options);

    try {
      $response = $this->httpClient->request($method, $url, $options);
      if (!empty($this->configuration['token_name'])) {
        $result['body'] = $response->getBody()->getContents();
        if ($this->isJson($result['body'])) {
          $result['json'] = Json::decode($result['body']);
        }
        $result['headers'] = $response->getHeaders();
        $result['status'] = $response->getStatusCode();
        $result['client_error'] = 0;
        $tokenService->addTokenData($this->configuration['token_name'], $result);
      }
    }
    catch (GuzzleException $e) {
      $this->logger->error($e->getMessage());
      if (!empty($this->configuration['token_name'])) {
        $result['body'] = $e->getMessage();
        $result['headers'] = [];
        $result['status'] = $e->getCode();
        $result['client_error'] = 1;
        $tokenService->addTokenData($this->configuration['token_name'], $result);
      }
    }
    catch (\Throwable $e) {
      $this->logger->error($e->getMessage());
      if (!empty($this->configuration['token_name'])) {
        $result['body'] = $e->getMessage();
        $result['headers'] = [];
        $result['status'] = $e->getCode();
        $result['client_error'] = 2;
        $tokenService->addTokenData($this->configuration['token_name'], $result);
      }
    }
  }

}
