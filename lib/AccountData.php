<?php

namespace Drupal\manual_direct_debit;

class AccountData extends \Drupal\little_helpers\DB\Model {
  protected static $table = 'manual_direct_debit_account_data';
  protected static $key = array('pid');
  protected static $values = array('holder', 'account', 'country');
  protected static $serial = FALSE;
  protected static $serialize = array('account' => TRUE);

  public static function load($pid) {
    $row = db_select(static::$table, $a)
      ->fields('a')
      ->condition('pid', $pid)
      ->execute()
      ->fetch();
    if ($row) {
      return new static($row, FALSE);
    }
  }

  public static function fromPayment(\Payment $payment, $new = FALSE) {
    $md = &$payment->method_data;
    $data = array();
    $data['pid'] = $payment->pid;
    $data['holder'] = $md['holder'];
    $data['country'] = $md['country'];
    $data['account'] = array();
    foreach (array('iban', 'bic', 'account', 'bank_code') as $key) {
      $data['account'][$key] = isset($md[$key])? $md[$key] : NULL;
    }
    return new static($data, $new);
  }
}