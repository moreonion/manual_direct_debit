<?php

/**
 * @file
 * Definition of the AccountData model class.
 */

namespace Drupal\manual_direct_debit;

/**
 * Model class for manual_direct_debit_account_data.
 *
 * Objects of this class store the account data needed for the
 * manual direct debit payment controller.
 */
class AccountData extends \Drupal\little_helpers\DB\Model {
  protected static $table = 'manual_direct_debit_account_data';
  protected static $key = array('pid');
  protected static $values = array('holder', 'account', 'country');
  protected static $serial = FALSE;
  protected static $serialize = array('account' => TRUE);

  /**
   * Builds a basic select query for account data records.
   */
  public static function queryBase() {
    return db_select(static::$table, 'a')->fields('a');
  }

  /**
   * Loads an AccountData object by it's payment id.
   */
  public static function load($pid) {
    $row = static::queryBase()->condition('pid', $pid)->execute()->fetch();
    if ($row) {
      return new static($row, FALSE);
    }
  }

  /**
   * Loads a list AccountData objects that belong to webform submissions.
   *
   * @param int $nid
   *   The node's nid of the webform node.
   * @param int $cid
   *   The webform_components.cid of the component having a payment.pid as value.
   * @param int[] $sids
   *   A list of submission ids to load the account data for.
   * @return
   *   An associative array containing a AccountData objects keyed by
   *   submission ids.
   */
  public static function bySubmissions($nid, $cid, $sids) {
    $query = static::queryBase();
    $query->innerJoin(
      'webform_submitted_data', 's', 's.nid=:nid AND s.cid=:cid AND s.data=a.pid',
      array(':nid' => $nid, ':cid' => $cid)
    );
    $query->fields('s', array('sid'));
    $query->condition('s.sid', $sids, 'IN');

    $data = array();
    foreach ($query->execute() as $row) {
      $sid = $row->sid;
      unset($row->sid);
      $obj = new static($row, FALSE);
      $data[$sid] = $obj;
    }
    return $data;
  }

  /**
   * Constructs an AccountData object based on a payment object.
   */
  public static function fromPayment(\Payment $payment, $new = FALSE) {
    $md = &$payment->method_data;
    $data = array();
    $data['pid'] = $payment->pid;
    $data['holder'] = $md['holder'];
    $data['country'] = $md['country'];
    $data['account'] = array();
    foreach (array('iban', 'bic', 'account', 'bank_code') as $key) {
      $data['account'][$key] = isset($md[$key]) ? $md[$key] : NULL;
    }
    return new static($data, $new);
  }
}
