<?php

namespace SM\Payment\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use SM\Payment\Model\RetailPayment;
use SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory as PaymentCollection;

/**
 * Class UpgradeSchema
 * @package SM\Payment\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var PaymentCollection
     */
    protected $paymentCollection;

    /**
     * UpgradeSchema constructor.
     * @param PaymentCollection $paymentCollection
     */
    public function __construct(
        PaymentCollection $paymentCollection
    ) {
        $this->paymentCollection = $paymentCollection;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->createPaymentTable($setup);
        }

        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->dummyPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            $this->addRpAndGcPayment($setup);
            $this->addPaypalPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            $this->updateCashPaymentData($setup);
            $this->addRoundingCashPayment($setup);
            $this->addIzettlePayment($setup);
        }

        if (version_compare($context->getVersion(), '0.1.5', '<')) {
            $this->addPaymentApp($setup);
        }

        if (version_compare($context->getVersion(), '0.1.5', '<')) {
            $this->addRefundToGiftCard($setup);
        }

        if (version_compare($context->getVersion(), '0.1.6', '<')) {
            $this->addCardKnoxPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.1.7', '<')) {
            $this->addStoreCreditPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.1.8', '<')) {
            $this->updateAuthorizeNetPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.1.9', '<')) {
            $this->addEwayPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.2.0', '<')) {
            $this->updatePaymentDataColumnType($setup);
        }

        if (version_compare($context->getVersion(), '0.2.1', '<')) {
            $this->updatePaymentExpressDataColumnType($setup);
        }

        if (version_compare($context->getVersion(), '0.2.2', '<')) {
            $this->addPaypalPWA($setup);
        }

        if (version_compare($context->getVersion(), '0.2.3', '<')) {
            $this->addAdyenPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.2.4', '<')) {
            $this->addStripePayment($setup);
        }

        if (version_compare($context->getVersion(), '0.2.5', '<')) {
            $this->addFlutterwavePayment($setup);
            $this->addPaystackPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.2.6', '<')) {
            $this->addBrainTreePayment($setup);
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function createPaymentTable(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('sm_payment'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_payment')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
            'Entity ID'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            25,
            ['nullable' => true, 'unsigned' => true],
            'Outlet Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'unsigned' => true],
            'Title'
        )->addColumn(
            'payment_data',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'unsigned' => true],
            'Data'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Creation Time'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Modification Time'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Active'
        )->addColumn(
            'is_dummy',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Dummy'
        )->addColumn(
            'allow_amount_tendered',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Allow Amount Tendered'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function dummyPayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->truncateTable($paymentTable);
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type' => 'cash',
                    'title' => 'Cash',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type' => 'tyro',
                    'title' => 'Tyro Gateway',
                    'is_dummy' => 0,
                    'payment_data' => json_encode(['mid' => 'provided by Tyro', 'tid' => 'provided by Tyro',
                        'api_key' => 'provided by Tyro']),
                ],
                [
                    'type' => 'credit_card',
                    'title' => 'Credit card',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type' => 'credit_card',
                    'title' => 'Debit card',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type' => 'credit_card',
                    'title' => 'Visa card',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ]
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addRpAndGcPayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::GIFT_CARD_PAYMENT_TYPE,
                    'title' => 'GiftCard',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type' => RetailPayment::REWARD_POINT_PAYMENT_TYPE,
                    'title' => 'RewardPoint',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([]),
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addPaypalPayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::PAYPAL_PAYMENT_TYPE,
                    'title' => 'Paypal',
                    'is_dummy' => 0,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function updateCashPaymentData(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $cash_payment_data = json_encode(['round_to' => '0.01_cash_denomination',
            'rounding_rule' => 'round_midpoint_down']);
        $setup->getConnection()->update(
            $paymentTable,
            ['payment_data' => $cash_payment_data],
            ['type = ?' => 'cash']
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addRoundingCashPayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::ROUNDING_CASH,
                    'title' => 'Cash Rounding',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addIzettlePayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::IZETTLE_PAYMENT_TYPE,
                    'title' => 'iZettle',

                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addRefundToGiftCard(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::REFUND_GC_PAYMENT_TYPE,
                    'title' => 'Refund To GC',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addPaypalPWA(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::PAYPAL_PWA,
                    'title' => 'Paypal PWA',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addPaymentApp(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::PAYMENT_EXPRESS,
                    'title' => 'Payment Express',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type' => RetailPayment::AUTHORIZE_NET,
                    'title' => 'Authorize NET',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type' => RetailPayment::USAEPAY,
                    'title' => 'Usaepay',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type' => RetailPayment::MONERIS,
                    'title' => 'Moneris',
                    'is_dummy' => 1,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addCardKnoxPayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::CARDKNOX,
                    'title' => 'CardKnox',
                    'is_dummy' => 0,
                    'payment_data' => json_encode(['xKey' => 'provided by CardKnox',
                        'xSoftwareName' => 'provided by CardKnox', 'xSoftwareVersion' => 'provided by CardKnox'])
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addStoreCreditPayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::STORE_CREDIT_PAYMENT_TYPE,
                    'title' => 'Store Credit',
                    'is_dummy' => 0,
                    'payment_data' => json_encode([])
                ],
                [
                    'type' => RetailPayment::REFUND_TO_STORE_CREDIT_PAYMENT_TYPE,
                    'title' => 'Refund To SC',
                    'is_dummy' => 0,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function updateAuthorizeNetPayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $authorize_net_payment_data = json_encode(['api_login_id' => 'provided by AuthorizeNET',
            'transaction_key' => 'provided by AuthorizeNET', 'sandbox_mode' => '0']);
        $setup->getConnection()->update(
            $paymentTable,
            ['payment_data' => $authorize_net_payment_data],
            ['type = ?' => 'authorize_net']
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addEwayPayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::EWAY,
                    'title' => 'Eway',
                    'is_dummy' => 0,
                    'payment_data' => json_encode(['api_key' => 'provided by Eway',
                        'api_password' => 'provided by Eway', 'encryption_key' => 'provided by Eway',
                        'sandbox_mode' => '0'])
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function updatePaymentDataColumnType(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $installer = $setup;

        $installer
            ->getConnection()
            ->modifyColumn(
                $paymentTable,
                'payment_data',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Payment Data'
                ]
            );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function updatePaymentExpressDataColumnType(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');

        $payment_express_payment_data = json_encode(
            [
                'hit_username' => 'provided by Payment Express',
                'hit_key' => 'provided by Payment Express',
                'device_id' => 'provided by Payment Express',
                'station_id' => 'provided by Payment Express',
                'endpoint' => 'uat'
            ]
        );
        $setup->getConnection()->update(
            $paymentTable,
            [
                'payment_data' => $payment_express_payment_data,
                'is_active' => 0
            ],
            ['type = ?' => 'payment_express']
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addAdyenPayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'allow_amount_tendered',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::ADYEN,
                    'title' => 'Adyen Payment',
                    'is_dummy' => 0,
                    'allow_amount_tendered' => true,
                    'payment_data' => json_encode(
                        [
                            'api_key' => 'provided by Adyen',
                            'client_key' => 'provided by Adyen',
                            'merchant_account' => 'provided by Adyen',
                            'environment' => 'test',
                            'POIID' => '',
                            'live_url_prefix' => 'Live Url Prefix'
                        ]
                    )
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addStripePayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'allow_amount_tendered',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::STRIPE,
                    'title' => 'Stripe',
                    'is_dummy' => 0,
                    'allow_amount_tendered' => true,
                    'payment_data' => json_encode(
                        [
                            'secret_api_key' => '',
                            'publishable_api_key' => '',
                        ]
                    )
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addFlutterwavePayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'allow_amount_tendered',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::FLUTTERWAVE,
                    'title' => 'Flutterwave',
                    'is_dummy' => 0,
                    'allow_amount_tendered' => true,
                    'payment_data' => json_encode(
                        [
                            'public_key' => '',
                            'secret_key' => '',
                            'encryption_key' => '',
                        ]
                    )
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addPaystackPayment(SchemaSetupInterface $setup)
    {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'allow_amount_tendered',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::PAYSTACK,
                    'title' => 'Paystack',
                    'is_dummy' => 0,
                    'allow_amount_tendered' => true,
                    'payment_data' => json_encode(
                        [
                            'public_key' => '',
                            'secret_key' => '',
                            'callback_url' => '',
                        ]
                    )
                ],
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addBrainTreePayment(SchemaSetupInterface $setup)
    {
        $paymentCollection = $this->paymentCollection->create();
        $exist = (bool) $paymentCollection->addFieldToFilter('type', RetailPayment::BRAIN_TREE)->getSize();
        if ($exist) {
            return;
        }

        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'allow_amount_tendered',
                'payment_data'
            ],
            [
                [
                    'type' => RetailPayment::BRAIN_TREE,
                    'title' => 'Brain Tree',
                    'is_dummy' => 0,
                    'allow_amount_tendered' => 1,
                    'payment_data' => json_encode(['name' => 'brain_tree'])
                ],
            ]
        );
    }
}
