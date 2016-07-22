<?php

namespace Drupal\manual_direct_debit_uk;

use \Drupal\payment_forms\FormInterface;


class AccountForm implements FormInterface {
  static protected $id = 0;

  public function getForm(array &$form, array &$form_state, \Payment $payment) {
    $context = $payment->contextObj;
    if ($context && $context->value('donation_interval') != 1) {
      $form['payment_date'] = array(
        '#type' => 'select',
        '#title' => t('Payment date'),
        '#description' => t('On which date would you like the donation to be made each month?'),
        '#options' => array(
          '1st' => '1st of the month',
          '15th' => '15th of the month',
          '28th' => '28th of the month',
        ),
      );
    }
    $form['holder'] = array(
      '#type' => 'textfield',
      '#title' => t('Account holder(s)'),
    );
    $form['account'] = array(
      '#type' => 'textfield',
      '#title' => t('Account Number'),
      '#maxlength' => 10,
    );
    $form['bank_code'] = array(
      '#type' => 'textfield',
      '#title' => t('Branch Sort Code'),
      '#maxlength' => 8,
    );
    return $form;
  }

  public function validateForm(array &$element, array &$form_state, \Payment $payment) {
    $values = &drupal_array_get_nested_value($form_state['values'], $element['#parents']);
    // In case we have a one-off donation.
    $values += array('payment_date' => NULL);

    if (empty($values['holder']) == TRUE) {
      form_error($element['holder'], t('Please enter the name of the account holder(s).'));
    }

    // Simple pre-validation
    $prevalidation_failed = FALSE;

    $values['account'] = trim($values['account']);
    if (!$values['account'] || !preg_match('/^[0-9]{6,10}$/', $values['account'])) {
      form_error($element['account'], t('Please enter valid Account Number.'));
      $prevalidation_failed = TRUE;
    }

    $values['bank_code'] = trim(str_replace('-', '', $values['bank_code']));
    if (!$values['bank_code'] || !preg_match('/^[0-9]{6}$/', $values['bank_code'])) {
      form_error($element['bank_code'], t('Please enter valid Branch Sort Code.'));
      $prevalidation_failed = TRUE;
    }

    if (!$prevalidation_failed && $key = variable_get('pca_bank_account_validation_key')) {
      $pa = new AccountValidation($key, $values['account'], $values['bank_code']);
      $pa->MakeRequest();
      if ($error = $pa->HasError()) {
        if ($error['id'] == 1003 || $error['id'] == 1004) {
          form_error($element['account'], t('Please enter valid Account Number.'));
        } elseif ($error['id'] == 1001 || $error['id'] == 1002) {
          form_error($element['bank_code'], t('Please enter valid Branch Sort Code.'));
        } elseif ($error['id'] == 3) {
          form_error($element['account'], t('Please check your account balance.'));
        } else {
          function_exists('watchdog') && watchdog('manual_direct_debit_uk', 'PCA Bank Account Validation Error: ' . $error['debug_info'], NULL, WATCHDOG_WARNING);
        }
      }
      if ($data = $pa->HasData()) {
        $item = $data[0];
        if (!$item["IsDirectDebitCapable"]) {
          form_error($element['account'], t('Please provide an account that can accept direct debits.'));
        }
        if (!$item["IsCorrect"]) {
          form_error($element['account'], t('Please provide valid account details.'));
        }
      }
    }

    $method_data = &$payment->method_data;
    $method_data['holder'] = $values['holder'];
    $method_data['country'] = 'GB';
    $method_data['account'] = $values['account'];
    $method_data['bank_code'] = $values['bank_code'];
    $method_data['payment_date'] = $values['payment_date'];
  }
}
