<?php

namespace Drupal\manual_direct_debit_uk;

class AccountValidation {

  //Credit: Thanks to Stuart Sillitoe (http://stu.so/me) for the original PHP that these samples are based on.

  private $Key; //The key to use to authenticate to the service.
  private $AccountNumber; //The bank account number to validate.
  private $SortCode; //The branch sort code for the account number.
  private $Data; //Holds the results of the query
  private $Error; //Error information

  function __construct($Key, $AccountNumber, $SortCode) {
    $this->Key = $Key;
    $this->AccountNumber = $AccountNumber;
    $this->SortCode = $SortCode;
  }

  function MakeRequest() {
    $url = "https://services.postcodeanywhere.co.uk/BankAccountValidation/Interactive/Validate/v2.00/json.ws?";
    $url .= "&Key=" . urlencode($this->Key);
    $url .= "&AccountNumber=" . urlencode($this->AccountNumber);
    $url .= "&SortCode=" . urlencode($this->SortCode);

    // Make the request to Postcode Anywhere and parse the JSON returned
    $response = drupal_http_request($url, ["timeout" => 5]);
    if ($response->code != 200) {
      $exception = new \Exception("Could not connect to PCA server");
      function_exists("watchdog_exception") && watchdog_exception("manual_direct_debit_uk", $exception);
      return;
    }
    $json = json_decode($response->data);
    if (!$json) {
      $exception = new \Exception("Could not parse response data as JSON");
      function_exists("watchdog_exception") && watchdog_exception("manual_direct_debit_uk", $exception);
      return;
    }
    $data = $json[0];

    // Check for Errors
    if (isset($data->Error)) {
      if ($data->Error < 100) {
        // General errors throw an exception
        $exception = new \Exception($data->Error);
        $message = "Error: " . $data->Description . " – "  . $data->Cause . "\n" . $data->Resolution;
        function_exists("watchdog_exception") && watchdog_exception("manual_direct_debit_uk", $exception, $message);
      }
      $this->Error = array(
        "id" => $data->Error,
        "description" => $data->Description,
        "cause" => $data->Cause,
        "resolution" => $data->Resolution
      );
    } else {
      // Save the Data
      $this->Data = $data;
    }
  }

  function HasData() {
    if ( !empty($this->Data) ) {
      return $this->Data;
    }
    return false;
  }

  function HasError() {
    if ( !empty($this->Error) ) {
      return $this->Error;
    }
    return false;
  }
}
