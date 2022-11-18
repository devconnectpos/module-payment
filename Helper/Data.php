<?php

namespace SM\Payment\Helper;

use Magento\Framework\Exception\CouldNotSaveException;
use SM\Payment\Model\RetailPayment;
use SM\Core\Api\Data\XPaymentFactory;
use SM\Payment\Model\RetailPaymentInterfaceFactory;
use SM\Payment\Model\RetailPaymentRepository;

class Data
{
    /**
     * @var XPaymentFactory
     */
    private $xPaymentFactory;

    /**
     * @var RetailPaymentRepository
     */
    private $paymentRepository;

    /**
     * @var RetailPaymentInterfaceFactory
     */
    private $retailPayment;

    /**
     * @param XPaymentFactory $xPaymentFactory
     * @param RetailPaymentRepository $paymentRepository
     * @param RetailPaymentInterfaceFactory $retailPayment
     */
    public function __construct(
        XPaymentFactory                                 $xPaymentFactory,
        RetailPaymentRepository                         $paymentRepository,
        RetailPaymentInterfaceFactory $retailPayment
    )
    {
        $this->xPaymentFactory = $xPaymentFactory;
        $this->paymentRepository = $paymentRepository;
        $this->retailPayment = $retailPayment;
    }

    /**
     * @param $registerId
     * @return array
     * @throws CouldNotSaveException
     */
    public function getDefaultPaymentData($registerId = null): array
    {
        $results = [];

        foreach ($this->getPaymentDataArray($registerId) as $paymentData) {
            $payment = $this->retailPayment->create();
            $payment->addData($paymentData);
            $payment = $this->paymentRepository->save($payment);

            $xPayment = $this->xPaymentFactory->create();
            $xPayment->addData($payment->getData());
            $results[] = $xPayment;
        }

        return $results;
    }

    /**
     * @param $registerId
     * @return array[]
     */
    public function getPaymentDataArray($registerId): array
    {
        return [
            100000 => [
                'type' => 'cash',
                'title' => 'Cash',
                'is_active' => 1,
                'is_dummy' => 1,
                'payment_data' => json_encode([
                    'round_to' => '0.01_cash_denomination',
                    'rounding_rule' => 'round_midpoint_down',
                ]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100001 => [
                'type' => 'tyro',
                'title' => 'Tyro Gateway',
                'is_dummy' => 0,
                'is_active' => 0,
                'payment_data' => json_encode([
                    'mid' => 'provided by Tyro',
                    'tid' => 'provided by Tyro',
                    'api_key' => 'provided by Tyro',
                ]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100002 => [
                'type' => 'credit_card',
                'title' => 'Credit card',
                'is_dummy' => 1,
                'is_active' => 1,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100003 => [
                'type' => 'credit_card',
                'title' => 'Debit card',
                'is_dummy' => 1,
                'is_active' => 1,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100004 => [
                'type' => 'credit_card',
                'title' => 'Visa card',
                'is_dummy' => 1,
                'is_active' => 1,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100005 => [
                'type' => RetailPayment::GIFT_CARD_PAYMENT_TYPE,
                'title' => 'GiftCard',
                'is_dummy' => 1,
                'is_active' => 0,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100006 => [
                'type' => RetailPayment::REWARD_POINT_PAYMENT_TYPE,
                'title' => 'RewardPoint',
                'is_dummy' => 1,
                'is_active' => 0,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100007 => [
                'type' => RetailPayment::PAYPAL_PAYMENT_TYPE,
                'title' => 'Paypal',
                'is_dummy' => 0,
                'is_active' => 0,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100008 => [
                'type' => RetailPayment::ROUNDING_CASH,
                'title' => 'Cash Rounding',
                'is_dummy' => 1,
                'is_active' => 0,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100009 => [
                'type' => RetailPayment::IZETTLE_PAYMENT_TYPE,
                'title' => 'iZettle',
                'is_dummy' => 1,
                'is_active' => 0,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100010 => [
                'type' => RetailPayment::REFUND_GC_PAYMENT_TYPE,
                'title' => 'Refund To GC',
                'is_dummy' => 1,
                'is_active' => 0,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100011 => [
                'type' => RetailPayment::PAYPAL_PWA,
                'title' => 'Paypal PWA',
                'is_dummy' => 1,
                'is_active' => 0,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100012 => [
                'type' => RetailPayment::PAYMENT_EXPRESS,
                'title' => 'Payment Express',
                'is_dummy' => 1,
                'is_active' => 0,
                'payment_data' => json_encode([
                    'hit_username' => 'provided by Payment Express',
                    'hit_key' => 'provided by Payment Express',
                    'device_id' => 'provided by Payment Express',
                    'station_id' => 'provided by Payment Express',
                    'endpoint' => 'uat',
                ]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100013 => [
                'type' => RetailPayment::AUTHORIZE_NET,
                'title' => 'Authorize NET',
                'is_dummy' => 1,
                'is_active' => 0,
                'payment_data' => json_encode([
                    'api_login_id' => 'provided by AuthorizeNET',
                    'transaction_key' => 'provided by AuthorizeNET',
                    'sandbox_mode' => '0',
                ]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100014 => [
                'type' => RetailPayment::USAEPAY,
                'title' => 'Usaepay',
                'is_dummy' => 1,
                'is_active' => 0,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100015 => [
                'type' => RetailPayment::MONERIS,
                'title' => 'Moneris',
                'is_dummy' => 1,
                'is_active' => 0,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100016 => [
                'type' => RetailPayment::CARDKNOX,
                'title' => 'CardKnox',
                'is_dummy' => 0,
                'is_active' => 0,
                'payment_data' => json_encode([
                    'xKey' => 'provided by CardKnox',
                    'xSoftwareName' => 'provided by CardKnox',
                    'xSoftwareVersion' => 'provided by CardKnox',
                ]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100017 => [
                'type' => RetailPayment::STORE_CREDIT_PAYMENT_TYPE,
                'title' => 'Store Credit',
                'is_dummy' => 0,
                'is_active' => 0,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100018 => [
                'type' => RetailPayment::REFUND_TO_STORE_CREDIT_PAYMENT_TYPE,
                'title' => 'Refund To SC',
                'is_dummy' => 0,
                'is_active' => 0,
                'payment_data' => json_encode([]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100019 => [
                'type' => RetailPayment::EWAY,
                'title' => 'Eway',
                'is_dummy' => 0,
                'is_active' => 0,
                'payment_data' => json_encode([
                    'api_key' => 'provided by Eway',
                    'api_password' => 'provided by Eway',
                    'encryption_key' => 'provided by Eway',
                    'sandbox_mode' => '0',
                ]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100020 => [
                'type' => RetailPayment::ADYEN,
                'title' => 'Adyen Payment',
                'is_dummy' => 0,
                'is_active' => 0,
                'allow_amount_tendered' => true,
                'payment_data' => json_encode(
                    [
                        'api_key' => 'provided by Adyen',
                        'client_key' => 'provided by Adyen',
                        'merchant_account' => 'provided by Adyen',
                        'environment' => 'test',
                        'POIID' => '',
                        'live_url_prefix' => 'Live Url Prefix',
                    ]
                ),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100021 => [
                'type' => RetailPayment::STRIPE,
                'title' => 'Stripe',
                'is_dummy' => 0,
                'is_active' => 0,
                'allow_amount_tendered' => true,
                'payment_data' => json_encode(
                    [
                        'secret_api_key' => '',
                        'publishable_api_key' => '',
                    ]
                ),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100022 => [
                'type' => RetailPayment::FLUTTERWAVE,
                'title' => 'Flutterwave',
                'is_dummy' => 0,
                'is_active' => 0,
                'allow_amount_tendered' => true,
                'payment_data' => json_encode(
                    [
                        'public_key' => '',
                        'secret_key' => '',
                        'encryption_key' => '',
                    ]
                ),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100023 => [
                'type' => RetailPayment::PAYSTACK,
                'title' => 'Paystack',
                'is_dummy' => 0,
                'is_active' => 0,
                'allow_amount_tendered' => true,
                'payment_data' => json_encode(
                    [
                        'public_key' => '',
                        'secret_key' => '',
                        'callback_url' => '',
                    ]
                ),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100024 => [
                'type' => RetailPayment::BRAIN_TREE,
                'title' => 'Brain Tree',
                'is_dummy' => 0,
                'is_active' => 0,
                'allow_amount_tendered' => 1,
                'payment_data' => json_encode(['name' => 'brain_tree']),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100025 => [
                'type' => RetailPayment::PAYPAL_EXPRESS,
                'title' => 'Paypal Express',
                'is_dummy' => 0,
                'is_active' => 0,
                'allow_amount_tendered' => 1,
                'payment_data' => json_encode(['name' => RetailPayment::PAYPAL_EXPRESS]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100026 => [
                'type' => RetailPayment::GRAVITY,
                'title' => 'Gravity',
                'is_dummy' => 0,
                'is_active' => 0,
                'allow_amount_tendered' => 1,
                'payment_data' => json_encode(['name' => RetailPayment::GRAVITY]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100027 => [
                'type' => RetailPayment::NETS,
                'title' => 'NETS',
                'is_dummy' => 0,
                'is_active' => 0,
                'allow_amount_tendered' => 1,
                'payment_data' => json_encode(['name' => RetailPayment::NETS]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100028 => [
                'type' => RetailPayment::CCV,
                'title' => 'CCV',
                'is_dummy' => 0,
                'is_active' => 0,
                'allow_amount_tendered' => 1,
                'payment_data' => json_encode(['name' => RetailPayment::CCV]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
            100029 => [
                'type' => RetailPayment::PAYNL,
                'title' => 'Pay.nl',
                'is_dummy' => 0,
                'is_active' => 0,
                'allow_amount_tendered' => 1,
                'payment_data' => json_encode(['name' => RetailPayment::PAYNL]),
                'register_id' => $registerId,
                'created_at' => date('d-m-Y H:i:s'),
                'updated_at' => date('d-m-Y H:i:s'),
            ],
        ];
    }
}
