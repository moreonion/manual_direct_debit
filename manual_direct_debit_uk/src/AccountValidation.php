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
    $url = "https://services.postcodeanywhere.co.uk/BankAccountValidation/Interactive/Validate/v2.00/xmla.ws?";
    $url .= "&Key=" . urlencode($this->Key);
    $url .= "&AccountNumber=" . urlencode($this->AccountNumber);
    $url .= "&SortCode=" . urlencode($this->SortCode);

    //Make the request to Postcode Anywhere and parse the XML returned
    $file = simplexml_load_file($url);

    //Check for an error, if there is one then throw an exception
    if ($file->Columns->Column->attributes()->Name == "Error") {
      $this->Error = array(
        'id' => $file->Rows->Row->attributes()->Error,
        'description' => $file->Rows->Row->attributes()->Description,
        'resolution' => $file->Rows->Row->attributes()->Resolution,
        'debug_info' => "[KEY] " . $this->Key . " [ACCOUNT] " . $this->AccountNumber . " [SORTCODE] " . $this->SortCode .  " [ID] " . $file->Rows->Row->attributes()->Error . " [DESCRIPTION] " . $file->Rows->Row->attributes()->Description . " [CAUSE] " . $file->Rows->Row->attributes()->Cause . " [RESOLUTION] " . $file->Rows->Row->attributes()->Resolution,
      );
    }

    //Copy the data
    if ( !empty($file->Rows) ) {
      foreach ($file->Rows->Row as $item) {
        $this->Data[] = array(
          'IsCorrect' => $item->attributes()->IsCorrect,
          'IsDirectDebitCapable' => $item->attributes()->IsDirectDebitCapable,
          'StatusInformation' => $item->attributes()->StatusInformation,
          'CorrectedSortCode' => $item->attributes()->CorrectedSortCode,
          'CorrectedAccountNumber' => $item->attributes()->CorrectedAccountNumber,
          'IBAN' => $item->attributes()->IBAN,
          'Bank' => $item->attributes()->Bank,
          'BankBIC' => $item->attributes()->BankBIC,
          'Branch' => $item->attributes()->Branch,
          'BranchBIC' => $item->attributes()->BranchBIC,
          'ContactAddressLine1' => $item->attributes()->ContactAddressLine1,
          'ContactAddressLine2' => $item->attributes()->ContactAddressLine2,
          'ContactPostTown' => $item->attributes()->ContactPostTown,
          'ContactPostcode' => $item->attributes()->ContactPostcode,
          'ContactPhone' => $item->attributes()->ContactPhone,
          'ContactFax' => $item->attributes()->ContactFax,
          'FasterPaymentsSupported' => $item->attributes()->FasterPaymentsSupported,
          'CHAPSSupported' => $item->attributes()->CHAPSSupported
        );
      }
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
