<?php

namespace Drupal\manual_direct_debit_uk;

use \Drupal\payment_forms\MethodFormInterface;

/**
 * Additional settings form for manual_direct_debit_uk payment methods.
 */
class ControllerSettingsForm implements MethodFormInterface {

  /**
   * Returns a new configuration form.
   */
  public function form(array $form, array &$form_state, \PaymentMethod $method) {
    $cd = $method->controller_data;
    $cd += ['day_options' => ['1', '15', '28']];

    $form['day_options'] = [
      '#type' => 'select',
      '#title' => t('Day of the month options'),
      '#description' => t('The selected days will be presented to the user for choosing his/her preferred payment collection day.'),
      '#multiple' => TRUE,
      '#options' => AccountForm::allDayOptions(),
      '#default_value' => $cd['day_options'],
    ];

    return $form;
  }

  /**
   * Validates the configuration form input.
   */
  public function validate(array $element, array &$form_state, \PaymentMethod $method) {
    $cd = drupal_array_get_nested_value($form_state['values'], $element['#parents']);
    $method->controller_data = $cd;
  }

}
