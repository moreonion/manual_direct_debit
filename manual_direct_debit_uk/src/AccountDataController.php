<?php

namespace Drupal\manual_direct_debit_uk;

class AccountDataController extends \Drupal\manual_direct_debit\AccountDataController {
  /**
   * Define callbacks and classes.
   */
  public function __construct() {
    parent::__construct();
    $this->title = t('Collect account data (UK)');
    $this->form = new \Drupal\payment_forms\AccountForm();
  }
}
