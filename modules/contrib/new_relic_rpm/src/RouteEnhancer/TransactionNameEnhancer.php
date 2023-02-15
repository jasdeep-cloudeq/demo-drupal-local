<?php

namespace Drupal\new_relic_rpm\RouteEnhancer;

use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Routing\EnhancerInterface;
use Drupal\Core\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

/**
 * Enhances routes with a dynamic transaction name.
 */
class TransactionNameEnhancer implements EnhancerInterface {

  /**
   * Controller resolver service.
   *
   * @var \Drupal\Core\Controller\ControllerResolverInterface
   */
  private $controllerResolver;

  /**
   * Argument resolver service.
   *
   * @var \Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface
   */
  private $argumentResolver;

  /**
   * Constructor.
   */
  public function __construct(ControllerResolverInterface $controller_resolver, ArgumentResolverInterface $argument_resolver) {
    $this->controllerResolver = $controller_resolver;
    $this->argumentResolver = $argument_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {

    /** @var \Symfony\Component\Routing\Route $route */
    $route = $defaults[RouteObjectInterface::ROUTE_OBJECT];
    if (!$route->hasDefault('_transaction_name_callback')) {
      return $defaults;
    }

    $cb = $route->getDefault('_transaction_name_callback');
    $callable = $this->controllerResolver->getControllerFromDefinition($cb);

    // Clone the request so we can set the attributes now.  Otherwise,
    // attributes aren't populated until after the route is enhanced.
    $cloned = clone $request;
    $cloned->attributes->replace($defaults);
    $arguments = $this->argumentResolver->getArguments($cloned, $callable);
    $defaults['_transaction_name'] = call_user_func_array($callable, $arguments);

    return $defaults;
  }

}
