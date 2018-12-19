<?php

namespace Drupal\manual_direct_debit_uk;

use Upal\DrupalUnitTestCase;

/**
 * Test the form for adding/editing payment methods.
 */
class ControllerSettingsFormTest extends DrupalUnitTestCase {

  /**
   * Test whether we get a form for adding a payment method.
   */
  public function testAddForm() {
    $GLOBALS['user'] = user_load(1);
    $controller_name = manual_direct_debit_uk_payment_method_controller_info()[0];
    $router_item = menu_get_item("admin/config/services/payment/method/add/$controller_name");
    $this->assertNotEmpty($router_item);
    $result = call_user_func_array($router_item['page_callback'], $router_item['page_arguments']);
    $this->assertNotEmpty($result['controller_form']['day_options']);
    $this->assertNotEmpty($result['controller_form']['long_account_numbers']);
  }

}
