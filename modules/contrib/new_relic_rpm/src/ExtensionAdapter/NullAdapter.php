<?php

namespace Drupal\new_relic_rpm\ExtensionAdapter;

/**
 * Default new relic adapter.
 */
class NullAdapter implements NewRelicAdapterInterface {

  /**
   * {@inheritdoc}
   */
  public function setTransactionState($state) {}

  /**
   * {@inheritdoc}
   */
  public function logException($exception) {}

  /**
   * {@inheritdoc}
   */
  public function logError($message, $exception = NULL) {}

  /**
   * {@inheritdoc}
   */
  public function addCustomParameter($key, $value) {}

  /**
   * {@inheritdoc}
   */
  public function setTransactionName($name) {}

  /**
   * {@inheritdoc}
   */
  public function recordCustomEvent($name, array $attributes) {}

  /**
   * {@inheritdoc}
   */
  public function disableAutorum() {
    return NULL;
  }

}
