<?php

namespace Drupal\manual_direct_debit_uk;

use \Drupal\payment_forms\PaymentFormInterface;

class AccountForm implements PaymentFormInterface {
  static protected $id = 0;

  /**
   * Gives all day choices.
   */
  public static function allDayOptions() {
    return [
      '1' => t('1st'),
      '2' => t('2nd'),
      '3' => t('3rd'),
      '4' => t('4th'),
      '5' => t('5th'),
      '6' => t('6th'),
      '7' => t('7th'),
      '8' => t('8th'),
      '9' => t('9th'),
      '10' => t('10th'),
      '11' => t('11th'),
      '12' => t('12th'),
      '13' => t('13th'),
      '14' => t('14th'),
      '15' => t('15th'),
      '16' => t('16th'),
      '17' => t('17th'),
      '18' => t('18th'),
      '19' => t('19th'),
      '20' => t('20th'),
      '21' => t('21st'),
      '22' => t('22nd'),
      '23' => t('23rd'),
      '24' => t('24th'),
      '25' => t('25th'),
      '26' => t('26th'),
      '27' => t('27th'),
      '28' => t('28th'),
    ];
  }

  public function form(array $form, array &$form_state, \Payment $payment) {
    $cd = $payment->method->controller_data;
    $cd += ['day_options' => ['1', '15', '28']];
    $context = $payment->contextObj;
    if ($context && $context->value('donation_interval') != 1) {
      $options = [];
      $all_options = static::allDayOptions();
      foreach ($cd['day_options'] as $day) {
        $options[$day] = $all_options[$day];
      }
      $form['payment_date'] = array(
        '#type' => 'select',
        '#title' => t('Payment date'),
        '#description' => t('On which date would you like the donation to be made each month?'),
        '#options' => $options,
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

  public function validate(array $element, array &$form_state, \Payment $payment) {
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

    if (!$prevalidation_failed) {
      $pa = new BankAccountValidation_Interactive_Validate_v2_00 (variable_get('pca_bank_account_validation_key'), $values['account'], $values['bank_code']);
      $pa->MakeRequest();
      if ($error = $pa->HasError()) {
        # Which field caused the error?
        if ($error['id'] == 1003 || $error['id'] == 1004) {
          form_error($element['account'], t('Please enter valid Account Number.'));
        } elseif ($error['id'] == 1001 || $error['id'] == 1002) {
          form_error($element['bank_code'], t('Please enter valid Branch Sort Code.'));
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
