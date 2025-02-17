<?php

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
        'APIVersion' => '1.0',
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
        'apiKey' => array(
            'FriendlyName' => 'API Key',
            'Type' => 'text',
            'Size' => '40',
            'Default' => '',
            'Description' => 'Enter your SoleasPay API Key here',
        ),
        'shopName' => array(
            'FriendlyName' => 'Shop Name',
            'Type' => 'text',
            'Size' => '40',
            'Default' => '',
            'Description' => 'Enter the name of your shop',
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
    $apiKey = $params['apiKey'];
    $shopName = $params['shopName'];
    $testMode = $params['testMode'];
    
    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Set the payment URL based on test mode
    $paymentUrl = $testMode ? "https://test.soleaspay.com/api/pay" : "https://checkout.soleaspay.com/api/pay";

    // Prepare the data for the payment request
    $postfields = array(
        'api_key' => $apiKey,
        'shop_name' => $shopName,
        'invoice_id' => $invoiceId,
        'amount' => $amount,
        'currency' => $currencyCode,
        'callback_url' => $params['systemurl'] . '/modules/gateways/callback/soleaspay.php', // URL to receive updates
        'return_url' => $params['returnurl'], // URL to redirect user back after payment
    );

    // Generate the payment form HTML
    $htmlOutput = '<form method="post" action="' . esc_url($paymentUrl) . '">';
    foreach ($postfields as $k => $v) {
        $htmlOutput .= '<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . htmlspecialchars($v) . '" />';
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
