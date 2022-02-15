<?php

namespace SM\Payment\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use SM\Payment\Model\RetailPayment;
use SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory as PaymentCollection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpgradeSchema
 *
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
     *
     * @param PaymentCollection $paymentCollection
     */
    public function __construct(PaymentCollection $paymentCollection)
    {
        $this->paymentCollection = $paymentCollection;
    }

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
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
            $this->addCashRoundingPayment($setup);
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

        if (version_compare($context->getVersion(), '0.1.9', '<')) {
            $this->addEwayPayment($setup);
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

        if (version_compare($context->getVersion(), '0.2.8', '<')) {
            $this->addPaypalExpressPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.2.9', '<')) {
            $this->addGravityPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.3.0', '<')) {
            $this->addRegisterIdToPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.3.1', '<')) {
            $this->addNetsPayment($setup);
        }

        if (version_compare($context->getVersion(), '0.3.2', '<')) {
            $this->addCCVPayment($setup);
            $this->addPayNlPayment($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param OutputInterface      $output
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup, OutputInterface $output)
    {
        $output->writeln('  |__ Create ConnectPOS payment table');
        $this->createPaymentTable($setup);

        $output->writeln('  |__ Initialize default payment data');
        $output->writeln('     >>> Add dummy payment methods (e.g. Cash, Tyro, Credit Card, Debit Card and Visa Card) default data');
        $this->dummyPayment($setup);
        $output->writeln('     >>> Add Reward Points and Gift Card payment methods default data');
        $this->addRpAndGcPayment($setup);
        $output->writeln('     >>> Add Paypal payment default data');
        $this->addPaypalPayment($setup);
        $output->writeln('     >>> Add Cash Rounding payment default data');
        $this->addCashRoundingPayment($setup);
        $output->writeln('     >>> Add iZettle payment default data');
        $this->addIzettlePayment($setup);
        $output->writeln('     >>> Add Refund to Gift Card payment default data');
        $this->addRefundToGiftCard($setup);
        $output->writeln('     >>> Add Paypal PWA payment default data');
        $this->addPaypalPWA($setup);
        $output->writeln('     >>> Add Payment Express, Authorized NET, Usaepay, Moneris payment default data');
        $this->addPaymentApp($setup);
        $output->writeln('     >>> Add Card Knox payment default data');
        $this->addCardKnoxPayment($setup);
        $output->writeln('     >>> Add Store Credit payment default data');
        $this->addStoreCreditPayment($setup);
        $output->writeln('     >>> Add Eway payment default data');
        $this->addEwayPayment($setup);
        $output->writeln('     >>> Add Adyen payment default data');
        $this->addAdyenPayment($setup);
        $output->writeln('     >>> Add Stripe payment default data');
        $this->addStripePayment($setup);
        $output->writeln('     >>> Add Flutterwave payment default data');
        $this->addFlutterwavePayment($setup);
        $output->writeln('     >>> Add Paystack payment default data');
        $this->addPaystackPayment($setup);
        $output->writeln('     >>> Add BrainTree payment default data');
        $this->addBrainTreePayment($setup);
        $output->writeln('     >>> Add Paypal Express payment default data');
        $this->addPaypalExpressPayment($setup);
        $output->writeln('     >>> Add Gravity payment default data');
        $this->addGravityPayment($setup);
        $output->writeln('     >>> Add CCV and Pay.nl payment default data');
        $this->addCCVPayment($setup);
        $this->addPayNlPayment($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    protected function createPaymentTable(SchemaSetupInterface $setup)
    {
        $setup->startSetup();

        if ($setup->getConnection()->isTableExists($setup->getTable('sm_payment'))) {
            $setup->endSetup();

            return;
        }

        $table = $setup->getConnection()->newTable(
            $setup->getTable('sm_payment')
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
            null,
            ['nullable' => true],
            'Payment Data'
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
        )->addColumn(
            'register_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true,],
            'Register Id'
        );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addRegisterIdToPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();

        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sm_payment'), 'register_id')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sm_payment'),
                'register_id',
                [
                    'type'    => Table::TYPE_INTEGER,
                    'comment' => 'Register id',
                ]
            );
        }
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function dummyPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->truncateTable($paymentTable);
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data',
            ],
            [
                [
                    'type'         => 'cash',
                    'title'        => 'Cash',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([
                        'round_to'      => '0.01_cash_denomination',
                        'rounding_rule' => 'round_midpoint_down',
                    ]),
                ],
                [
                    'type'         => 'tyro',
                    'title'        => 'Tyro Gateway',
                    'is_dummy'     => 0,
                    'payment_data' => json_encode([
                        'mid'     => 'provided by Tyro',
                        'tid'     => 'provided by Tyro',
                        'api_key' => 'provided by Tyro',
                    ]),
                ],
                [
                    'type'         => 'credit_card',
                    'title'        => 'Credit card',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
                [
                    'type'         => 'credit_card',
                    'title'        => 'Debit card',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
                [
                    'type'         => 'credit_card',
                    'title'        => 'Visa card',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addRpAndGcPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data',
            ],
            [
                [
                    'type'         => RetailPayment::GIFT_CARD_PAYMENT_TYPE,
                    'title'        => 'GiftCard',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
                [
                    'type'         => RetailPayment::REWARD_POINT_PAYMENT_TYPE,
                    'title'        => 'RewardPoint',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addPaypalPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data',
            ],
            [
                [
                    'type'         => RetailPayment::PAYPAL_PAYMENT_TYPE,
                    'title'        => 'Paypal',
                    'is_dummy'     => 0,
                    'payment_data' => json_encode([]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addCashRoundingPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data',
            ],
            [
                [
                    'type'         => RetailPayment::ROUNDING_CASH,
                    'title'        => 'Cash Rounding',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addIzettlePayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data',
            ],
            [
                [
                    'type'  => RetailPayment::IZETTLE_PAYMENT_TYPE,
                    'title' => 'iZettle',

                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addRefundToGiftCard(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data',
            ],
            [
                [
                    'type'         => RetailPayment::REFUND_GC_PAYMENT_TYPE,
                    'title'        => 'Refund To GC',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addPaypalPWA(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data',
            ],
            [
                [
                    'type'         => RetailPayment::PAYPAL_PWA,
                    'title'        => 'Paypal PWA',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addPaymentApp(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data',
            ],
            [
                [
                    'type'         => RetailPayment::PAYMENT_EXPRESS,
                    'title'        => 'Payment Express',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([
                        'hit_username' => 'provided by Payment Express',
                        'hit_key'      => 'provided by Payment Express',
                        'device_id'    => 'provided by Payment Express',
                        'station_id'   => 'provided by Payment Express',
                        'endpoint'     => 'uat',
                    ]),
                ],
                [
                    'type'         => RetailPayment::AUTHORIZE_NET,
                    'title'        => 'Authorize NET',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([
                        'api_login_id'    => 'provided by AuthorizeNET',
                        'transaction_key' => 'provided by AuthorizeNET',
                        'sandbox_mode'    => '0',
                    ]),
                ],
                [
                    'type'         => RetailPayment::USAEPAY,
                    'title'        => 'Usaepay',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
                [
                    'type'         => RetailPayment::MONERIS,
                    'title'        => 'Moneris',
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addCardKnoxPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data',
            ],
            [
                [
                    'type'         => RetailPayment::CARDKNOX,
                    'title'        => 'CardKnox',
                    'is_dummy'     => 0,
                    'payment_data' => json_encode([
                        'xKey'             => 'provided by CardKnox',
                        'xSoftwareName'    => 'provided by CardKnox',
                        'xSoftwareVersion' => 'provided by CardKnox',
                    ]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addStoreCreditPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data',
            ],
            [
                [
                    'type'         => RetailPayment::STORE_CREDIT_PAYMENT_TYPE,
                    'title'        => 'Store Credit',
                    'is_dummy'     => 0,
                    'payment_data' => json_encode([]),
                ],
                [
                    'type'         => RetailPayment::REFUND_TO_STORE_CREDIT_PAYMENT_TYPE,
                    'title'        => 'Refund To SC',
                    'is_dummy'     => 0,
                    'payment_data' => json_encode([]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addEwayPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data',
            ],
            [
                [
                    'type'         => RetailPayment::EWAY,
                    'title'        => 'Eway',
                    'is_dummy'     => 0,
                    'payment_data' => json_encode([
                        'api_key'        => 'provided by Eway',
                        'api_password'   => 'provided by Eway',
                        'encryption_key' => 'provided by Eway',
                        'sandbox_mode'   => '0',
                    ]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addAdyenPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'allow_amount_tendered',
                'payment_data',
            ],
            [
                [
                    'type'                  => RetailPayment::ADYEN,
                    'title'                 => 'Adyen Payment',
                    'is_dummy'              => 0,
                    'allow_amount_tendered' => true,
                    'payment_data'          => json_encode(
                        [
                            'api_key'          => 'provided by Adyen',
                            'client_key'       => 'provided by Adyen',
                            'merchant_account' => 'provided by Adyen',
                            'environment'      => 'test',
                            'POIID'            => '',
                            'live_url_prefix'  => 'Live Url Prefix',
                        ]
                    ),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addStripePayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'allow_amount_tendered',
                'payment_data',
            ],
            [
                [
                    'type'                  => RetailPayment::STRIPE,
                    'title'                 => 'Stripe',
                    'is_dummy'              => 0,
                    'allow_amount_tendered' => true,
                    'payment_data'          => json_encode(
                        [
                            'secret_api_key'      => '',
                            'publishable_api_key' => '',
                        ]
                    ),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addFlutterwavePayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'allow_amount_tendered',
                'payment_data',
            ],
            [
                [
                    'type'                  => RetailPayment::FLUTTERWAVE,
                    'title'                 => 'Flutterwave',
                    'is_dummy'              => 0,
                    'allow_amount_tendered' => true,
                    'payment_data'          => json_encode(
                        [
                            'public_key'     => '',
                            'secret_key'     => '',
                            'encryption_key' => '',
                        ]
                    ),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addPaystackPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'allow_amount_tendered',
                'payment_data',
            ],
            [
                [
                    'type'                  => RetailPayment::PAYSTACK,
                    'title'                 => 'Paystack',
                    'is_dummy'              => 0,
                    'allow_amount_tendered' => true,
                    'payment_data'          => json_encode(
                        [
                            'public_key'   => '',
                            'secret_key'   => '',
                            'callback_url' => '',
                        ]
                    ),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addBrainTreePayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentCollection = $this->paymentCollection->create();
        $exist = (bool)$paymentCollection->addFieldToFilter('type', RetailPayment::BRAIN_TREE)->getSize();
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
                'payment_data',
            ],
            [
                [
                    'type'                  => RetailPayment::BRAIN_TREE,
                    'title'                 => 'Brain Tree',
                    'is_dummy'              => 0,
                    'allow_amount_tendered' => 1,
                    'payment_data'          => json_encode(['name' => 'brain_tree']),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addPaypalExpressPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentCollection = $this->paymentCollection->create();
        $exist = (bool)$paymentCollection->addFieldToFilter('type', RetailPayment::PAYPAL_EXPRESS)->getSize();

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
                'payment_data',
            ],
            [
                [
                    'type'                  => RetailPayment::PAYPAL_EXPRESS,
                    'title'                 => 'Paypal Express',
                    'is_dummy'              => 0,
                    'allow_amount_tendered' => 1,
                    'payment_data'          => json_encode(['name' => RetailPayment::PAYPAL_EXPRESS]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addGravityPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentCollection = $this->paymentCollection->create();
        $exist = (bool)$paymentCollection->addFieldToFilter('type', RetailPayment::GRAVITY)->getSize();
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
                'payment_data',
            ],
            [
                [
                    'type'                  => RetailPayment::GRAVITY,
                    'title'                 => 'Gravity',
                    'is_dummy'              => 0,
                    'allow_amount_tendered' => 1,
                    'payment_data'          => json_encode(['name' => RetailPayment::GRAVITY]),
                ],
            ]
        );
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addNetsPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentCollection = $this->paymentCollection->create();
        $exist = (bool)$paymentCollection->addFieldToFilter('type', RetailPayment::NETS)->getSize();
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
                'payment_data',
            ],
            [
                [
                    'type'                  => RetailPayment::NETS,
                    'title'                 => 'NETS',
                    'is_dummy'              => 0,
                    'allow_amount_tendered' => 1,
                    'payment_data'          => json_encode(['name' => RetailPayment::NETS]),
                ],
            ]
        );
        $setup->endSetup();
    }

    protected function addCCVPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentCollection = $this->paymentCollection->create();
        $exist = (bool)$paymentCollection->addFieldToFilter('type', RetailPayment::CVV)->getSize();
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
                'payment_data',
            ],
            [
                [
                    'type'                  => RetailPayment::CCV,
                    'title'                 => 'CCV',
                    'is_dummy'              => 0,
                    'allow_amount_tendered' => 1,
                    'payment_data'          => json_encode(['name' => RetailPayment::CCV]),
                ],
            ]
        );
        $setup->endSetup();
    }

    protected function addPayNlPayment(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $paymentCollection = $this->paymentCollection->create();
        $exist = (bool)$paymentCollection->addFieldToFilter('type', RetailPayment::PAYNL)->getSize();
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
                'payment_data',
            ],
            [
                [
                    'type'                  => RetailPayment::PAYNL,
                    'title'                 => 'Pay.nl',
                    'is_dummy'              => 0,
                    'allow_amount_tendered' => 1,
                    'payment_data'          => json_encode(['name' => RetailPayment::PAYNL]),
                ],
            ]
        );
        $setup->endSetup();
    }
}
