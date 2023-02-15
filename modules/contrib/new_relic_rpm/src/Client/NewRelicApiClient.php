<?php

namespace Drupal\new_relic_rpm\Client;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 * Controls the interaction between us and newrelic rest API v2.
 */
class NewRelicApiClient {

  use StringTranslationTrait;

  const API_URL = 'https://api.newrelic.com/v2';

  /**
   * The API key to connect to newrelic.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * The serialisation JSON service.
   *
   * @var \Drupal\Component\Serialization\Json
   */
  protected $parser;

  /**
   * The application ID in newrelic.
   *
   * @var int
   */
  private $appId;

  /**
   * The application name being used for this instance.
   *
   * @var string
   */
  private $appName;

  /**
   * The http_client service.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * A loaded config object for new_relic_rpm.settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * A logger service instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new NewRelicApiClient.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory so we can load config that we need.
   * @param \GuzzleHttp\Client $http_client
   *   The http client to send requests to newrelic.
   * @param \Drupal\Component\Serialization\Json $serialization_json
   *   Decoding the returned result from newrelic.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   For logging notifications to Drupal.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Client $http_client, Json $serialization_json, LoggerChannelFactoryInterface $logger_factory, TranslationInterface $string_translation) {
    $this->config = $config_factory->get('new_relic_rpm.settings');
    $this->httpClient = $http_client;
    $this->parser = $serialization_json;
    $this->logger = $logger_factory->get('new_relic_rpm');
    $this->setStringTranslation($string_translation);

    $this->apiKey = $this->config->get('api_key');

    $app_name = ini_get('newrelic.appname');
    if (!empty($app_name)) {
      $this->setAppName($app_name);
    }
  }

  /**
   * Change the application name used for requests.
   *
   * Defaults to the newrelic.appname php configuration value.
   *
   * @param string $name
   *   The name to use.
   */
  public function setAppName($name) {
    $this->appName = $name;
    $this->appId = NULL;
  }

  /**
   * Lazy load the application ID from newrelic.
   *
   * @return int|null
   *   The application ID or null if it failed.
   */
  public function getAppId() {
    if (empty($this->appName)) {
      return NULL;
    }

    if (empty($this->appId)) {

      // Populate the appId from the name.
      try {
        $applications = $this->listApplications($this->appName);
      }
      catch (GuzzleException $e) {
        return NULL;
      }

      foreach ($applications as $application_details) {
        if ($application_details['name'] === $this->appName) {
          $this->appId = $application_details['id'];
          break;
        }
      }

      if (empty($this->appId)) {
        $this->logger->error('Unable to get appId for :name', [
          ':name' => $this->appName,
        ]);
      }
    }

    return $this->appId;
  }

  /**
   * Get a list of applications available for this API key.
   *
   * @param string $name
   *   Optional filter by application name.
   *
   * @return array
   *   An array of application details.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function listApplications($name = NULL) {
    $filters = [];
    if (!empty($name)) {
      $filters['name'] = $name;
    }
    $response = $this->request('/applications', ['filters' => $filters]);
    $result = $this->parser->decode($response->getBody()->getContents());
    return $result['applications'];
  }

  /**
   * Create a deployment marker in newrelic.
   *
   * @param string $revision
   *   The revision to deploy.
   * @param string $description
   *   A description for the deployment.
   * @param string $user
   *   The user to deploy as.
   * @param string $changelog
   *   The changelog information.
   *
   * @return bool
   *   Whether the deployment marker was successful or not.
   */
  public function createDeployment($revision, $description = NULL, $user = NULL, $changelog = NULL) {
    $app_id = $this->getAppId();
    if (empty($app_id)) {
      return FALSE;
    }

    $post_vars = [
      'deployment' => [
        'revision' => $revision,
      ],
    ];
    if (isset($description)) {
      $post_vars['deployment']['description'] = $description;
    }
    if (isset($user)) {
      $post_vars['deployment']['user'] = $user;
    }
    if (isset($changelog)) {
      $post_vars['deployment']['changelog'] = $changelog;
    }

    $uri = '/applications/' . $app_id . '/deployments';
    try {
      $this->request($uri, ['form_params' => $post_vars], 'POST');
      return TRUE;
    }
    catch (GuzzleException $e) {
      return FALSE;
    }
  }

  /**
   * Build the URL for the API.
   *
   * @param string $uri
   *   The base path.
   * @param array $filters
   *   Request filters.
   *
   * @return string
   *   The full URL.
   */
  public function buildUrl($uri, array $filters = []) {
    $url = static::API_URL . $uri . '.json';
    if (empty($filters)) {
      return $url;
    }

    $params = [];
    foreach ($filters as $name => $value) {
      $params['filter[' . $name . ']'] = $value;
    }
    return $url . '?' . http_build_query($params);
  }

  /**
   * Make the request to newrelic.
   *
   * @param string $uri
   *   The API path to query.
   * @param array $options
   *   The options to send for the request.
   * @param string $method
   *   The request method.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   A response from the API request.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function request($uri, array $options = [], $method = 'GET') {
    $options = array_merge([
      'headers' => [
        'X-Api-Key' => $this->apiKey,
      ],
      'filters' => [],
    ], $options);
    $url = $this->buildUrl($uri, $options['filters']);
    unset($options['filters']);
    try {
      $response = $this->httpClient->request($method, $url, $options);
      return $response;
    }
    catch (GuzzleException $e) {
      watchdog_exception('new_relic_rpm', $e);
      throw $e;
    }
  }

}
