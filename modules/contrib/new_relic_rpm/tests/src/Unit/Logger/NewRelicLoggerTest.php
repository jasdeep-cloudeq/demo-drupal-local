<?php

namespace Drupal\Tests\new_relic_rpm\Unit\Logger;

use Drupal\Core\Logger\LogMessageParser;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\new_relic_rpm\ExtensionAdapter\NewRelicAdapterInterface;
use Drupal\new_relic_rpm\Logger\NewRelicLogger;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\new_relic_rpm\Logger\NewRelicLogger
 * @group new_relic_rpm
 */
class NewRelicLoggerTest extends UnitTestCase {

  /**
   * The default request context for tests.
   *
   * @var array
   */
  private static $defaultContext = [
    'channel' => 'mytype',
    'ip' => '127.0.0.1',
    'request_uri' => '/foo',
    'referer' => '/bar',
    'uid' => 1,
  ];

  /**
   * Get a preconfigured logger.
   *
   * @param \Drupal\new_relic_rpm\ExtensionAdapter\NewRelicAdapterInterface $adapter
   *   The adapter to use.
   * @param array $levels
   *   The log levels to report.
   *
   * @return \Drupal\new_relic_rpm\Logger\NewRelicLogger
   *   A new logger instance.
   */
  private function getLogger(NewRelicAdapterInterface $adapter, array $levels = []) {
    $parser = new LogMessageParser();
    $config = $this->getConfigFactoryStub([
      'new_relic_rpm.settings' => [
        'watchdog_severities' => $levels,
      ],
    ]);
    return new NewRelicLogger($parser, $adapter, $config);
  }

  /**
   * Test that log levels requested are logged.
   */
  public function testLogsSelectedLevelMessages() {
    $adapter = $this->prophesize(NewRelicAdapterInterface::class);
    $adapter
      ->logError(Argument::type('string'), NULL)
      ->shouldBeCalled();
    $logger = $this->getLogger($adapter->reveal(), [RfcLogLevel::CRITICAL]);
    $logger->log(RfcLogLevel::CRITICAL, 'Test', self::$defaultContext);
  }

  /**
   * Test that log levels not requested are ignored.
   */
  public function testIgnoresUnselectedLevelMessages() {
    $adapter = $this->prophesize(NewRelicAdapterInterface::class);
    $adapter
      ->logError()
      ->shouldNotBeCalled();
    $logger = $this->getLogger($adapter->reveal());
    $logger->log(RfcLogLevel::CRITICAL, 'Test', self::$defaultContext);
  }

  /**
   * Data source for tests.
   */
  public function getMessageTests() {
    return [
      ['My Log Message |'],
      ['Severity: (2) Critical |'],
      ['Type: mytype |'],
      ['Request URI: /foo |'],
      ['Referrer URI: /bar |'],
      ['User: 1'],
      ['IP Address: 127.0.0.1'],
    ];
  }

  /**
   * Test that we log a message.
   *
   * @dataProvider getMessageTests
   */
  public function testCreatesMessage($expectedPart) {
    $adapter = $this->prophesize(NewRelicAdapterInterface::class);
    $adapter
      ->logError(Argument::containingString($expectedPart), NULL)
      ->shouldBeCalled();

    $logger = $this->getLogger($adapter->reveal(), [RfcLogLevel::CRITICAL]);
    $logger->log(RfcLogLevel::CRITICAL, 'My Log Message', self::$defaultContext);
  }

  /**
   * Test that an unknown log level is handled.
   */
  public function testHandlesUnknownLevel() {
    $adapter = $this->prophesize(NewRelicAdapterInterface::class);
    $adapter
      ->logError(Argument::containingString('Severity: (8) Unknown'), NULL)
      ->shouldBeCalled();

    $logger = $this->getLogger($adapter->reveal(), [8]);
    $logger->log(8, 'My Log Message', self::$defaultContext);
  }

  /**
   * Test that exceptions are being passed to the adapter.
   */
  public function testExceptionPassing() {
    $exception = new \Exception('Some exception to be logged.');
    $adapter = $this->prophesize(NewRelicAdapterInterface::class);
    $adapter
      ->logError(Argument::containingString('My Log Message'), $exception)
      ->shouldBeCalled();

    $logger = $this->getLogger($adapter->reveal(), [RfcLogLevel::ERROR]);
    $logger->log(RfcLogLevel::ERROR, 'My Log Message', self::$defaultContext + [
      'exception' => $exception,
    ]);
  }

}
