<?php
/**
 * Model class for the Mpesa object
 * sets and returns the relevant properties
 * needed in making the respective Daraja-Api calls
 * check https://developer.safaricom.co.ke/apis
 */

namespace Flaircore\Mpesa;

class Mpesa
{

    /**
     * Define all the properties needed when making
     * any of the m-pesa api calls
     */

    private $consumerKey;
    private $consumerSecret;
    private $businessShortCode;
    private $passKey;
    private $transactionType;
    private $amount;
    private $phoneNumber;
    private $callbackUrl;
    private $accountReference;


    public function getConsumerKey(): ?string
    {
        return $this->consumerKey;
    }

    public function setConsumerKey(?string $consumerKey): void
    {
        $this->consumerKey = $consumerKey;
    }

    public function getConsumerSecret(): ?string
    {
        return $this->consumerSecret;
    }

    public function setConsumerSecret(?string $consumerSecret): void
    {
        $this->consumerSecret = $consumerSecret;
    }

    public function getBusinessShortCode(): ?string
    {
        return $this->businessShortCode;
    }

    public function setBusinessShortCode(?string $businessShortCode): void
    {
        $this->businessShortCode = $businessShortCode;
    }

    public function getPassKey(): ?string
    {
        return $this->passKey;
    }

    public function setPassKey(?string $passKey): void
    {
        $this->passKey = $passKey;
    }


    public function getTransactionType(): ?string
    {
        return $this->transactionType;
    }

    public function setTransactionType(?string $transactionType): void
    {
        $this->transactionType = $transactionType;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): void
    {
        $this->amount = $amount;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }


    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }


    public function setCallbackUrl(?string $callbackUrl): void
    {
        $this->callbackUrl = $callbackUrl;
    }

    public function getAccountReference(): string
    {
        return $this->accountReference;
    }

    public function setAccountReference(?string $accountReference): void
    {
        $this->accountReference = $accountReference;
    }


    public function getTransactionDesc(): ?string
    {
        return $this->transactionDesc;
    }

    public function setTransactionDesc(?string $transactionDesc): void
    {
        $this->transactionDesc = $transactionDesc;
    }
    private $transactionDesc;

    public function mpesaSTKPush(){

        // generate the token
        $this->setTransactionType('CustomerPayBillOnline');

        $headers = ['Content-Type:application/json; charset=utf8'];

        $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init($access_token_url);
        curl_setopt($curl, CURLOPT_HEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($curl, CURLOPT_HEADER, FALSE);

        curl_setopt($curl, CURLOPT_USERPWD, $this->getConsumerKey().':'.$this->getConsumerSecret());

        $result = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $result = json_decode($result);

        $access_token = $result->access_token;
        curl_close($curl);

        // Initiating the transaction

        // define the valiables
        #$url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $initiate_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $Timestamp = date('YmdGis'); //20180920204512 y,M,D,Hour,MIN,SEC
        $Password = base64_encode($this->getBusinessShortCode().$this->getPassKey().$Timestamp);

        $curl = curl_init();
        $stkHeader = ['Content-Type:application/json','Authorization:Bearer '.$access_token];
        curl_setopt($curl, CURLOPT_URL, $initiate_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$stkHeader); //setting custom header


        $curl_post_data = array(
            //Fill in the request parameters with valid values
          'BusinessShortCode' => $this->getBusinessShortCode(),
          'Password' => $Password,
          'Timestamp' => $Timestamp,
          'TransactionType' => $this->getTransactionType(),
          'Amount' => $this->getAmount(),
          #'PartyA' => $PartyA,
          'PartyA' => $this->getPhoneNumber(),
          'PartyB' => $this->getBusinessShortCode(),
          #'PhoneNumber' => $PartyA,
          'PhoneNumber' => $this->getPhoneNumber(),
          'CallBackURL' => $this->getCallbackUrl(),
          'AccountReference' => $this->getAccountReference(),
          'TransactionDesc' => $this->getTransactionDesc(),
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $curl_response = curl_exec($curl);

        return $curl_response;
    }

}