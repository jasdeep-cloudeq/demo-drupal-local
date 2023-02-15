<?php

namespace Drupal\new_relic_rpm\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\new_relic_rpm\Client\NewRelicApiClient;
use Drupal\new_relic_rpm\ExtensionAdapter\NewRelicAdapterInterface;
use Drush\Commands\DrushCommands;

/**
 * Newrelic rpm drush commands.
 */
class NewRelicRpmCommands extends DrushCommands {

  /**
   * Newrelic API client.
   *
   * @var \Drupal\new_relic_rpm\Client\NewRelicApiClient
   */
  protected $apiClient;

  /**
   * New Relic adapter.
   *
   * @var \Drupal\new_relic_rpm\ExtensionAdapter\NewRelicAdapterInterface
   */
  protected $adapter;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * NewRelicRpmCommands constructor.
   *
   * @param \Drupal\new_relic_rpm\Client\NewRelicApiClient $api_client
   *   Newrelic API client.
   * @param \Drupal\new_relic_rpm\ExtensionAdapter\NewRelicAdapterInterface $adapter
   *   Newrelic adapter.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Drupal config factory.
   */
  public function __construct(NewRelicApiClient $api_client, NewRelicAdapterInterface $adapter, ConfigFactoryInterface $config_factory) {
    parent::__construct();
    $this->apiClient = $api_client;
    $this->adapter = $adapter;
    $this->configFactory = $config_factory;
  }

  /**
   * Setup how we want to track any drush command in newrelic.
   *
   * @hook pre-command *
   *
   * @validate-module-enabled new_relic_rpm
   */
  public function preCommandNewrelicTransactionType() {
    $track_drush = $this->configFactory->get('new_relic_rpm.settings')->get('track_drush');
    if (empty($track_drush)) {
      $track_drush = NewRelicAdapterInterface::STATE_NORMAL;
    }
    if ($track_drush !== NewRelicAdapterInterface::STATE_NORMAL) {
      $this->adapter->setTransactionState($track_drush);

      // Let the user know if they run verbose drush.
      $message = ($track_drush == NewRelicAdapterInterface::STATE_IGNORE) ? 'Newrelic is set to ignore this command' : 'Newrelic is set to track this command as a background task';
      $this->logger()->info($message);
    }
  }

  /**
   * Mark a deployment in newrelic.
   *
   * @param string $revision
   *   The revision label.
   * @param array $options
   *   The options to pass through to the deplopment.
   *
   * @command new-relic-rpm:deploy
   * @aliases nrd
   *
   * @option description
   *   A brief description of the deployment.
   * @option user
   *   User doing the deploy.
   * @option changelog
   *   A list of changes for this deployment.
   *
   * @usage new-relic-rpm:deploy 1.2.3
   *   Create a deployment with revision 1.2.3.
   * @usage new-relic-rpm:deploy 1.2.3 --description="New release".
   *   Create a deployment with revision 1.2.3 and a specific description.
   *
   * @validate-module-enabled new_relic_rpm
   */
  public function deploy($revision, array $options = [
    'description' => NULL,
    'user' => NULL,
    'changelog' => NULL,
  ]) {
    $deployment = $this->apiClient->createDeployment($revision, $options['description'], $options['user'], $options['changelog']);

    if ($deployment) {
      $this->output()->writeln('New Relic deployment created successfully.');
    }
    else {
      $this->logger()->error(dt('New Relic deployment failed.'));
    }
  }

}
