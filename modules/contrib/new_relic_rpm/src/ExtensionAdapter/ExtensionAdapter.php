<?php

namespace Drupal\new_relic_rpm\ExtensionAdapter;

/**
 * Default new relic adapter.
 */
class ExtensionAdapter implements NewRelicAdapterInterface {

  /**
   * {@inheritdoc}
   */
  public function setTransactionState($state) {
    switch ($state) {
      case static::STATE_BACKGROUND:
        newrelic_background_job(TRUE);
        break;

      case static::STATE_IGNORE:
        newrelic_ignore_transaction(TRUE);
        break;

    }
  }

  /**
   * {@inheritdoc}
   */
  public function logException($exception) {
    newrelic_notice_error($exception);
  }

  /**
   * {@inheritdoc}
   */
  public function logError($message, $exception = NULL) {
    newrelic_notice_error($message, $exception);
  }

  /**
   * {@inheritdoc}
   */
  public function addCustomParameter($key, $value) {
    newrelic_add_custom_parameter($key, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function setTransactionName($name) {
    newrelic_name_transaction($name);
  }

  /**
   * {@inheritdoc}
   */
  public function recordCustomEvent($name, array $attributes) {
    if (function_exists('newrelic_record_custom_event')) {
      newrelic_record_custom_event($name, $attributes);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function disableAutorum() {
    return newrelic_disable_autorum();
  }

}
