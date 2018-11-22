<?php

namespace Drupal\manual_direct_debit_uk;

use \Drupal\manual_direct_debit\AccountDataController as _Controller;

class AccountDataController extends _Controller {

  public $controller_data_defaults = [
    'day_options' => ['1', '15', '28'],
    'long_account_numbers' => TRUE,
  ];

  public $payment_method_configuration_form_elements_callback = 'payment_forms_method_configuration_form';

  /**
   * Define callbacks and classes.
   */
  public function __construct() {
    parent::__construct();
    $this->title = t('Collect account data (UK)');
  }

  public function paymentForm() {
    return new AccountForm();
  }

  /**
   * Get a configuration form instance.
   */
  public function configurationForm() {
    return new ControllerSettingsForm();
  }

  function validate(\Payment $payment, \PaymentMethod $payment_method, $strict) {
    parent::validate($payment, $payment_method, $strict);
    if ($strict) {
      $interval = $payment->contextObj->value('donation_interval');
      if (!$interval || $interval == '1') {
        throw new \PaymentValidationException('This payment method does not support one-off payments.');
      }
    }
  }
}
