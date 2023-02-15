<?php

namespace Drupal\new_relic_rpm\ExtensionAdapter;

/**
 * New relic API adapter interface.
 */
interface NewRelicAdapterInterface {

  const STATE_NORMAL = 'norm';

  const STATE_IGNORE = 'ignore';

  const STATE_BACKGROUND = 'bg';

  /**
   * Set the new relic transaction state.
   *
   * @param string $state
   *   One of the state constants.
   */
  public function setTransactionState($state);

  /**
   * Logs an exception.
   *
   * @param \Exception|\Throwable $exception
   *   The exception.
   */
  public function logException($exception);

  /**
   * Logs an error message.
   *
   * @param string $message
   *   The error message.
   * @param \Exception|\Throwable $exception
   *   The exception.
   */
  public function logError($message, $exception = NULL);

  /**
   * Adds a custom parameter.
   *
   * @param string $key
   *   Key that identifies the parameter.
   * @param string $value
   *   Value for the parameter.
   */
  public function addCustomParameter($key, $value);

  /**
   * Set the transaction name.
   *
   * @param string $name
   *   Name for this transaction.
   */
  public function setTransactionName($name);

  /**
   * Records a custom event for insights.
   *
   * @param string $name
   *   Name of the event.
   * @param array $attributes
   *   List of attributees for the event. Only scalar types are allowed.
   */
  public function recordCustomEvent($name, array $attributes);

  /**
   * Disable automatic injection of the New Relic Browser snippet.
   *
   * @return mixed
   *   TRUE if called within newrelic transaction. Otherwise NULL.
   */
  public function disableAutorum();

}
