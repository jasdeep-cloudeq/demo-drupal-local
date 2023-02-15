<?php

namespace Drupal\new_relic_rpm\EventSubscriber;

use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\new_relic_rpm\ExtensionAdapter\NewRelicAdapterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Config event listener to mark deployments when a user imports configuration.
 */
class NewRelicConfigSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

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
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a NewRelicConfigSubscriber.
   *
   * @param \Drupal\new_relic_rpm\ExtensionAdapter\NewRelicAdapterInterface $adapter
   *   The Adapter that we use to talk to the New Relic extension.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The object we use to get our settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The currently logged in user.
   */
  public function __construct(NewRelicAdapterInterface $adapter, ConfigFactoryInterface $config_factory, AccountInterface $current_user) {
    $this->adapter = $adapter;
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::IMPORT][] = ['onImport'];
    return $events;
  }

  /**
   * Attempts to create a deployment on New Relic when a config import happens.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The current config event that we are responding to.
   */
  public function onImport(ConfigImporterEvent $event) {
    $config = $this->configFactory->get('new_relic_rpm.settings');

    if (!$config->get('config_import')) {
      return;
    }

    $changes = $event->getChangelist();

    $name = $this->currentUser->getAccountName();
    $description = $this->t('A configuration import was run on the site.');
    $changelog = '';

    if (!empty($changes['create'])) {
      $changelog .= 'Configurations created:
';
      foreach ($changes['create'] as $config_key) {
        $changelog .= $config_key . '
';
      }
    }
    if (!empty($changes['update'])) {
      $changelog .= 'Configurations updated:
';
      foreach ($changes['update'] as $config_key) {
        $changelog .= $config_key . '
';
      }
    }
    if (!empty($changes['delete'])) {
      $changelog .= 'Configurations deleted:
';
      foreach ($changes['delete'] as $config_key) {
        $changelog .= $config_key . '
';
      }
    }
    if (!empty($changes['rename'])) {
      $changelog .= 'Configurations renamed:
';
      foreach ($changes['rename'] as $config_key) {
        $changelog .= $config_key . '
';
      }
    }

    /** @var \Drupal\new_relic_rpm\Client\NewRelicApiClient $client */
    $client = \Drupal::service('new_relic_rpm.client');
    $client->createDeployment('config_import', $description, $name, $changelog);
  }

}
