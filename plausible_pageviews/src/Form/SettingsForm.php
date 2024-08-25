<?php

declare(strict_types=1);

namespace Drupal\plausible_pageviews\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Plausible pageviews settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'plausible_pageviews_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['plausible_pageviews.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['bearer_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bearer token'),
      '#default_value' => $this->config('plausible_pageviews.settings')->get('bearer_token'),
    ];

    $form['date_range'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date range'),
      '#default_value' => $this->config('plausible_pageviews.settings')->get('date_range'),
    ];

    $form['site_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site ID'),
      '#default_value' => $this->config('plausible_pageviews.settings')->get('site_id'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if ($form_state->getValue('example') === 'wrong') {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('The value is not correct.'),
    //     );
    //   }
    // @endcode
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('plausible_pageviews.settings')
      ->set('bearer_token', $form_state->getValue('bearer_token'))
      ->set('date_range', $form_state->getValue('date_range'))
      ->set('site_id', $form_state->getValue('site_id'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
