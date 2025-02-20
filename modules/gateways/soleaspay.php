<?php
/**
 * SoleasPay Payment Gateway Module
 *
 * SoleasPay Payment Gateway modules allow you to integrate payment solutions with the WHMCS platform.
 *
 * The SoleasPay Payment module help merchand to collect funds from theirs customer accross more than 100 currencies in many countries.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "soleaspay" and therefore all functions
 * begin "soleaspay_".
 *
 * If your module or third party API does not support a given function, you
 * should not define that function within your module. Only the _config
 * function is required.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://soleaspay.com/
 *
 * @copyright Copyright (c) MYSOLEAS 2021
 * @license MIT License
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * @return array
 */
function soleaspay_MetaData()
{
    return array(
        'DisplayName' => 'SoleasPay Payment Gateway',
        'APIVersion' => '1.1',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * @return array
 */
function soleaspay_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'SoleasPay Payment Gateway',
        ),
        'accountID' => array(
            'FriendlyName' => 'API Key',
            'Type' => 'text',
            'Size' => '40',
            'Default' => '',
            'Description' => 'Enter your SoleasPay API Key here to accept payments',
        ),
        'secretKey' => array(
            'FriendlyName' => 'Secret Key',
            'Type' => 'password',
            'Size' => '40',
            'Default' => '',
            'Description' => 'Enter your SoleasPay secret key here for refund purposes',
        ),
        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
        ),
    );
}

/**
 * Payment link.
 *
 * @param array $params Payment Gateway Module Parameters
 * @return string
 */
function soleaspay_link($params)
{
    // Gateway Configuration Parameters
    $apiKey = $params['accountID'];
    $shopName = $params['companyname'];
    $testMode = $params['testMode'];
    
    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $customer = $params['clientdetails']['firstname'].' '.$params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $phone = $params['clientdetails']['phonenumber'];

    // Set the payment URL based on test mode
    $paymentUrl = $testMode ? "https://test.soleaspay.com/api/pay" : "https://checkout.soleaspay.com";

    // Prepare the data for the payment request
    $postfields = array(
        'apiKey' => $apiKey,
        'shopName' => $shopName,
        'orderId' => $invoiceId,
        'description' => $description,
        'amount' => $amount,
        'currency' => $currencyCode,
        'successUrl' => $params['systemurl'] . '/modules/gateways/callback/soleaspay.php', // URL to receive updates
        'failureUrl' => $params['returnurl'], // URL to redirect user back after payment
        'customer[name]' => $customer,
        'customer[email]' => $email,
        'customer[phone]' => $phone
    );

    // Generate the payment form HTML
    $htmlOutput = '<form method="post" action="' .$paymentUrl. '">';
    foreach ($postfields as $k => $v) {
        $htmlOutput .= '<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . urlencode($v) . '" />';
    }
    $htmlOutput .= '<input type="submit" value="' . htmlspecialchars($params['langpaynow']) . '" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}

/**
 * Refund transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 * @return array Transaction response status
 */
function soleaspay_refund($params)
{
    // Retrieve relevant parameters for the refund
    $transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'];

    // Logic to perform refund goes here, typically using API request to SoleasPay's refund endpoint

    return array(
        'status' => 'success', // or 'declined', 'error'
        'rawdata' => '', // Add API response data for debugging/logging
        'transid' => $transactionIdToRefund, // Assuming the same transaction ID used for the refund
        'fees' => 0, // Fees if applicable
    );
}

/**
 * Cancel subscription.
 *
 * @param array $params Payment Gateway Module Parameters
 * @return array Transaction response status
 */
function soleaspay_cancelSubscription($params)
{
    $subscriptionIdToCancel = $params['subscriptionID'];

    // Logic to perform subscription cancellation if applicable

    return array(
        'status' => 'success', // or any other status for failure
        'rawdata' => '', // Log data as needed
    );
}
