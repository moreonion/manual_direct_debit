<?php

namespace Drupal\manual_direct_debit_uk;

class FormBuilderFormBaseTest extends \Drupal\Tests\DrupalUnitTestCase {
  protected function paymentMethod() {
    $name = 'Manual Direct Debit UK';
    $controller = payment_method_controller_load('\Drupal\manual_direct_debit_uk\AccountDataController');
    $payment_method = new \PaymentMethod(array(
      'controller' => $controller,
      'controller_data' => $controller->controller_data_defaults,
      'name' => strtolower($name),
      'title_generic' => strtoupper($name) . '-general',
      'title_specific' => strtoupper($name) . '-specific',
      'uid' => 0,
    ));
    return $payment_method;
  }

  /**
   * This is the way payment methods are tested for availability in
   * webform_paymethod_select.
   *
   * @see _webform_paymethod_select_payment_method_options()
   */
  public function test_validation_passesWithWPSDummyPayment() {
    $payment = entity_create('payment', array(
      'currency_code'   => 'EUR',
      'description'     => t('Default Payment'),
      'finish_callback' => 'webform_paymethod_select_payment_finish',
    ));
    $method = $this->paymentMethod();
    $method->validate($payment, FALSE);
  }

  /**
   * During payment non-recurring payments should be invalid.
   *
   * @expectedException PaymentValidationException
   */
  public function test_validation_filtersImplicitOneTimePayments() {
    $payment = entity_create('payment', array(
      'currency_code'   => 'EUR',
      'description'     => t('Default Payment'),
      'finish_callback' => 'webform_paymethod_select_payment_finish',
    ));
    $method = $this->paymentMethod();
    $method->validate($payment, TRUE);
  }
}
