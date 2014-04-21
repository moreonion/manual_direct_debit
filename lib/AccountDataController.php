<?php

namespace Drupal\manual_direct_debit;

use \Drupal\webform_paymethod_select\PaymentRecurrentController;

class AccountDataController extends \PaymentMethodController implements PaymentRecurrentController {
  public $payment_method_configuration_form_elements_callback;
  public $payment_configuration_form_elements_callback;
  public function __construct() {
    $this->payment_method_configuration_form_elements_callback = '\Drupal\manual_direct_debit\configuration_form';
    $this->payment_configuration_form_elements_callback        = 'payment_forms_method_form';
    $this->title = t('Collect account data (direct debit)');
    $this->form = new \Drupal\payment_forms\AccountForm();
  }

  public function validate(\Payment $payment, \PaymentMethod $payment_method, $strict) {
    if (!$strict)
      return parent::validate($payment, $payment_method, $strict);

    $data = &$payment->method_data;
    if (isset($data['iban'])) {
      $data['country'] = substr($data['iban'], 0, 2);
    }
  }

  public function execute(\Payment $payment) {

    $payment->setStatus(new \PaymentStatusItem(PAYMENT_STATUS_SUCCESS));
  }
}

/**
 * Configuration form for the payment method.
 */
function configuration_form(array $form, array &$form_state) {
  return $form;
}
