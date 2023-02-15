<?php

namespace Drupal\new_relic_rpm\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\new_relic_rpm\Client\NewRelicApiClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to allow marking deployments on the New Relic interface.
 */
class NewRelicRpmDeploy extends FormBase {

  /**
   * The new relic HTTP cilent.
   *
   * @var \Drupal\new_relic_rpm\Client\NewRelicApiClient
   */
  protected $newRelicClient;

  /**
   * NewRelicRpmDeploy constructor.
   *
   * @param \Drupal\new_relic_rpm\Client\NewRelicApiClient $new_relic_client
   *   The new relic HTTP client.
   */
  public function __construct(NewRelicApiClient $new_relic_client) {
    $this->newRelicClient = $new_relic_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('new_relic_rpm.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'new_relic_rpm_deploy';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $form['revision'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Revision'),
      '#required' => TRUE,
      '#description' => $this->t('Add a revision number to this deployment.'),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('Provide some notes and description regarding this deployment.'),
    ];

    $form['user'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User'),
      '#default_value' => $this->currentUser()->getAccountName(),
      '#description' => $this->t('Enter the name for this deployment of your application. This will be the name shown in your list of deployments on the New Relic website.'),
    ];

    $form['changelog'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Changelog'),
      '#description' => $this->t('Provide a specific changelog for this deployment.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Deployment'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $deployment = $this->newRelicClient->createDeployment(
      $form_state->getValue(['revision']),
      $form_state->getValue(['description']),
      $form_state->getValue(['user']),
      $form_state->getValue(['changelog'])
    );

    if ($deployment) {
      $this->messenger()->addStatus($this->t('New Relic deployment created successfully.'));
    }
    else {
      $this->messenger()->addError($this->t('New Relic deployment failed.'));
    }
  }

}
