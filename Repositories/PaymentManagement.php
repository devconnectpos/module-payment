<?php

namespace SM\Payment\Repositories;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use SM\Core\Api\SearchResult;
use SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory;
use SM\Payment\Model\RetailPaymentFactory;
use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;
use SM\Core\Api\Data\XPayment;

/**
 * Class PaymentManagement
 *
 * @package SM\Payment\Repositories
 */
class PaymentManagement extends ServiceAbstract
{

    /**
     * @var \SM\Payment\Model\RetailPaymentFactory
     */
    protected $retailPaymentFactory;

    /**
     * @var \SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory
     */
    protected $paymentCollectionFactory;

    /**
     * PaymentManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                         $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                                   $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \SM\Payment\Model\RetailPaymentFactory                          $retailPaymentFactory
     * @param \SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory $paymentCollectionFactory
     */
    public function __construct(
        RequestInterface $requestInterface,
        DataConfig $dataConfig,
        StoreManagerInterface $storeManager,
        RetailPaymentFactory $retailPaymentFactory,
        CollectionFactory $paymentCollectionFactory
    ) {
        $this->retailPaymentFactory = $retailPaymentFactory;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPaymentData()
    {
        return $this->load($this->getSearchCriteria())->getOutput();
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return SearchResult
     * @throws \Exception
     */
    public function load(DataObject $searchCriteria)
    {
        if ($searchCriteria === null || !$searchCriteria) {
            $searchCriteria = $this->getSearchCriteria();
        }
        $registerId = $this->searchCriteria['registerId'];

        $this->getSearchResult()->setSearchCriteria($searchCriteria);
        $collection = $this->getPaymentCollection($searchCriteria);
        $collection->addFieldToFilter(['register_id', 'register_id', 'register_id'], [['eq' => $registerId], ['eq' => 0], ['null' => true]]);
        $items = [];
        if ($collection->getLastPageNumber() >= $searchCriteria->getData('currentPage')) {
            // Merge payments with and without register id
            $hasRegisterIdPayments = [];
            $hasNoRegisterIdPayments = [];

            foreach ($collection as $payment) {
                $idx = $payment->getData('type').'_'.$payment->getData('title').'_'.$payment->getData('register_id');
                if (!$payment->getData('register_id')) {
                    $hasNoRegisterIdPayments[$idx] = $payment;
                    continue;
                }
                $hasRegisterIdPayments[$idx] = $payment;
            }

            $countHasRegisterId = count($hasNoRegisterIdPayments);

            foreach ($hasNoRegisterIdPayments as $payment) {
                $idx = $payment->getData('type').'_'.$payment->getData('title').'_'.$payment->getData('register_id');
                if (isset($hasRegisterIdPayments[$idx])) {
                    continue;
                }

                // Set it like this to make sure that the no register id payment won't show at checkout
                if ($countHasRegisterId > 0) {
                    $payment->setData('is_active', false);
                }

                $hasRegisterIdPayments[$idx] = $payment;
            }

            // Use the final payment array for output
            foreach ($hasRegisterIdPayments as $payment) {
                $xPayment = new XPayment();
                if ($payment->getData('type') === 'adyen') {
                    $paymentData = json_decode($payment->getData('payment_data'), true);
                    if (isset($paymentData[$registerId])) {
                        $payment->setData('payment_data', json_encode($paymentData[$registerId]));
                    } elseif (isset($paymentData[0])) {
                        $payment->setData('payment_data', json_encode($paymentData[0]));
                    } else {
                        $payment->setData('payment_data', json_encode($paymentData));
                    }
                }
                $xPayment->addData($payment->getData());
                $items[] = $xPayment;
            }
        }

        return $this->getSearchResult()
            ->setItems($items)
            ->setTotalCount($collection->getSize())
            ->setLastPageNumber($collection->getLastPageNumber());
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return \SM\Payment\Model\ResourceModel\RetailPayment\Collection
     */
    public function getPaymentCollection(DataObject $searchCriteria)
    {
        /** @var \SM\Payment\Model\ResourceModel\RetailPayment\Collection $collection */
        $collection = $this->paymentCollectionFactory->create();

        if (is_nan($searchCriteria->getData('currentPage'))) {
            $collection->setCurPage(1);
        } else {
            $collection->setCurPage($searchCriteria->getData('currentPage'));
        }
        if (is_nan($searchCriteria->getData('pageSize'))) {
            $collection->setPageSize(
                DataConfig::PAGE_SIZE_LOAD_DATA
            );
        } else {
            $collection->setPageSize(
                $searchCriteria->getData('pageSize')
            );
        }

        $collection->setOrder("id");

        return $collection;
    }

    protected function dummyPayment()
    {
        $payments = [
            [
                'type'     => "cash",
                'title'    => "Cash",
                'is_dummy' => 1,
            ],
            [
                'type'     => "credit_card",
                'title'    => "Credit Card",
                'is_dummy' => 1,
            ],
            [
                'type'     => "credit_card",
                'title'    => "Debit Card",
                'is_dummy' => 1,
            ],
            [
                'type'     => "credit_card",
                'title'    => "Visa Card",
                'is_dummy' => 1,
            ],
        ];
        foreach ($payments as $pData) {
            $payment = $this->retailPaymentFactory->create();
            $payment->addData($pData)->save();
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function savePayment()
    {
        $data = $this->getRequestData();
        $listPayment = $data['payment_data'];
        $registerId = $data['register_id'] ?? null;
        $items = [];
        foreach ($listPayment as $pData) {
            $xPayment = new XPayment();
            if (isset($pData['payment_data'])) {
                $pData['payment_data'] = json_encode($pData['payment_data']);
            }
            if ($pData['type'] === 'adyen') {
                $items[] = $xPayment->addData($this->saveAdyenPayment($pData, $registerId));
            } elseif (isset($pData['id']) && $pData['id'] && $pData['id'] < 1481282470403) {
                $existingPayment = $this->retailPaymentFactory->create();
                $existingPayment = $existingPayment->load($pData['id']);

                // If the existing payment has register id, just update
                if ($existingPayment->getData('register_id')) {
                    $pData['id'] = $existingPayment->getId();
                    $existingPayment->setData($pData)->save();
                    $items[] = $xPayment->addData($existingPayment->getData());
                } else { // otherwise, create a new one as clone
                    $pData['id'] = null;
                    $pData['register_id'] = $registerId;
                    $payment = $this->retailPaymentFactory->create();
                    $payment->addData($pData)->save();
                    $items[] = $xPayment->addData($payment->getData());
                }
            } else {
                $pData['id'] = null;
                $pData['register_id'] = $registerId;
                $payment = $this->retailPaymentFactory->create();
                $payment->addData($pData)->save();
                $items[] = $xPayment->addData($payment->getData());
            }
        }

        return $this->getSearchResult()
            ->setItems($items)
            ->setTotalCount(count($items))
            ->setLastPageNumber(1)->getOutput();
    }

    /**
     * Delete payments by ids
     *
     * @return array|void
     * @throws \Exception
     */
    public function deletePayments()
    {
        $data = $this->getRequestData();

        if (!isset($data['ids'])) {
            return [];
        }

        $paymentIds = explode(",", $data['ids']);

        if (empty($paymentIds)) {
            return [];
        }

        /** @var \SM\Payment\Model\ResourceModel\RetailPayment\Collection $collection */
        $collection = $this->paymentCollectionFactory->create();
        $collection->addFieldToFilter('id', ['in' => $paymentIds]);

        /** @var \SM\Payment\Model\RetailPayment $payment */
        foreach ($collection as $payment) {
            $payment->delete();
        }

        return [];
    }

    public function deleteDuplicatePayments()
    {
        /** @var \SM\Payment\Model\ResourceModel\RetailPayment\Collection $collection */
        $collection = $this->paymentCollectionFactory->create();
        $collection->addFieldToFilter(['register_id', 'register_id'], [['gteq' => 0], ['null' => true]])
            ->setOrder("id", "DESC");
        $checked = [];
        $deletedCount = 0;

        /** @var \SM\Payment\Model\RetailPayment $payment */
        foreach ($collection as $payment) {
            $registerId = $payment->getData('register_id');
            $idx = $registerId . "_" . $payment->getData('title') . "_" . $payment->getData('type');

            if (empty($registerId)) {
                continue;
            }

            if (!isset($checked[$idx])) {
                $checked[$idx] = true;
                continue;
            }

            if ($payment->getData('payment_data') && $payment->getData('payment_data') !== '[]') {
                continue;
            }

            $payment->delete();
            $deletedCount++;
        }

        return ["count" => $deletedCount];
    }

    protected function saveAdyenPayment($pData, $registerId)
    {
        $payment = $this->retailPaymentFactory->create()->load($pData['id']);
        $paymentData = json_decode($pData['payment_data'], true);
        $oldPaymentData = json_decode($payment->getData('payment_data'), true);
        //unset old data, save to model
        unset($pData['payment_data']);
        $payment->addData($pData);

        if (isset($oldPaymentData['POIID'])) {
            $payment->setData('payment_data', json_encode([0 => $paymentData, $registerId => $paymentData]));
        } else {
            $oldPaymentData[$registerId] = $paymentData;
            $payment->setData('payment_data', json_encode($oldPaymentData));
        }

        $payment->save();

        $newPaymentData = json_decode($payment->getData('payment_data'), true);
        $payment->setData('payment_data', json_encode($newPaymentData[$registerId]));

        return $payment->getData();
    }
}
