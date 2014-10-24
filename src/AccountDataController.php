<?php

/**
 * @file
 * Defines the payment method controller for manual direct debit payments.
 */

namespace Drupal\manual_direct_debit;

use \Drupal\webform_paymethod_select\PaymentRecurrentController;

/**
 * Payment controller for manual direct debit payments.
 */
class AccountDataController extends \PaymentMethodController implements PaymentRecurrentController {
  public $payment_method_configuration_form_elements_callback;
  public $payment_configuration_form_elements_callback;
  /**
   * Define callbacks and classes.
   */
  public function __construct() {
    $this->payment_method_configuration_form_elements_callback = '\Drupal\manual_direct_debit\configuration_form';
    $this->payment_configuration_form_elements_callback        = 'payment_forms_method_form';
    $this->title = t('Collect account data (SEPA)');
    $this->form = new \Drupal\payment_forms\AccountForm();
  }

  /**
   * Validate the payment data.
   *
   * We heavily rely on the form validations of the AccountForm.
   * @see \Drupal\payment_forms\AccountForm
   */
  public function validate(\Payment $payment, \PaymentMethod $payment_method, $strict) {
    if (!$strict) {
      return parent::validate($payment, $payment_method, $strict);
    }

    $data = &$payment->method_data;
    if (isset($data['iban'])) {
      $data['country'] = substr($data['iban'], 0, 2);
    }
  }

  /**
   * Finish the payment.
   *
   * Our payments are always successful (if the validation succceeds) because
   * we only need to save the data.
   */
  public function execute(\Payment $payment) {
    $payment->setStatus(new \PaymentStatusItem(PAYMENT_STATUS_SUCCESS));
  }
}
