<?php

namespace Drupal\manual_direct_debit_uk;

class AccountValidation {

  //Credit: Thanks to Stuart Sillitoe (http://stu.so/me) for the original PHP that these samples are based on.

  private $key; //The key to use to authenticate to the service.
  private $accountNumber; //The bank account number to validate.
  private $sortCode; //The branch sort code for the account number.

  function __construct($key, $accountNumber, $sortCode) {
    $this->key = $key;
    $this->accountNumber = $accountNumber;
    $this->sortCode = $sortCode;
  }

  function makeRequest() {
    $url = "https://services.postcodeanywhere.co.uk/BankAccountValidation/Interactive/Validate/v2.00/json.ws?";
    $url .= "&Key=" . urlencode($this->key);
    $url .= "&AccountNumber=" . urlencode($this->accountNumber);
    $url .= "&SortCode=" . urlencode($this->sortCode);

    // Make the request to Postcode Anywhere and parse the JSON returned
    $response = drupal_http_request($url, ["timeout" => 5]);
    if ($response->code != 200) {
      watchdog("manual_direct_debit_uk", "Could not connect to PCA server", NULL, WATCHDOG_WARNING);
      return false;
    }
    $json = json_decode($response->data);
    if (!$json) {
      watchdog("manual_direct_debit_uk", "Could not parse response data as JSON", NULL, WATCHDOG_WARNING);
      return false;
    }
    $data = $json[0];

    // Check for Gerneral Errors
    if (isset($data->Error) && $data->Error < 100) {
      watchdog("manual_direct_debit_uk", "PCA returns error: " . $data->Description . " – "  . $data->Cause . " – " . $data->Resolution, NULL, WATCHDOG_WARNING);
      return false;
    }

    return $data;
  }
}
