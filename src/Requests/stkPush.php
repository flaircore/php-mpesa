<?php
/**
 * Defines mpesa requests
 * @todo :: change request urls between environments ie..live vs sandbox
 */

namespace Flaircore\Mpesa\Requests;

use Flaircore\Mpesa\MpesaConfigs;
use Flaircore\Mpesa\MpesaItem;

class stkPush
{
    private $mpesaConfigs;
    private $mpesaItem;

    public function __construct(MpesaConfigs $mpesaConfigs, MpesaItem $mpesaItem)
    {
        $this->mpesaConfigs = $mpesaConfigs;
        $this->mpesaItem = $mpesaItem;
    }

    public function mpesaSTKPush()
    {
        # define the variales
        $consumerKey = $this->mpesaConfigs->getConsumerKey(); //Fill with your app Consumer Key
        $consumerSecret = $this->mpesaConfigs->getConsumerSecret(); // Fill with your app Secret

        # provide the following details, this part is found on your test credentials on the developer account
        $BusinessShortCode = $this->mpesaItem->getBusinessShortCode();
        $Passkey = $this->mpesaConfigs->getPassKey();

        // generate the token
        $this->mpesaItem->setTransactionType('CustomerPayBillOnline');
        $headers = ['Content-Type:application/json; charset=utf8'];
        $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $curl = curl_init($access_token_url);
        curl_setopt($curl, CURLOPT_HEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_USERPWD, $this->mpesaConfigs->getConsumerKey().':'.$this->mpesaConfigs->getConsumerSecret());
        $result = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $result = json_decode($result);
        $result->access_token;

        $access_token = $result->access_token;
        curl_close($curl);
        // Initiating the transaction
        // define the valiables
        $initiate_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $curl = curl_init();
        $stkHeader = ['Content-Type:application/json','Authorization:Bearer '.$access_token];
        curl_setopt($curl, CURLOPT_URL, $initiate_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$stkHeader); //setting custom header
        $curl_post_data = array(
            //Fill in the request parameters with valid values
          'BusinessShortCode' => $this->mpesaItem->getBusinessShortCode(),
          'Password' => $this->mpesaItem->getPassword(),
          'Timestamp' => $this->mpesaConfigs->getTimestamp(),
          'TransactionType' => $this->mpesaItem->getTransactionType(),
          'Amount' => $this->mpesaItem->getAmount(),
          'PartyA' => $this->mpesaItem->getPhoneNumber(),
          'PartyB' => $this->mpesaItem->getBusinessShortCode(),
          'PhoneNumber' => $this->mpesaItem->getPhoneNumber(),
          'CallBackURL' => $this->mpesaItem->getCallbackUrl(),
          'AccountReference' => $this->mpesaItem->getAccountReference(),
          'TransactionDesc' => $this->mpesaItem->getTransactionDesc(),
        );
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);
        return $curl_response;
    }

}