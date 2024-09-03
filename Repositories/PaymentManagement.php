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
use SM\Core\Api\Data\XPaymentFactory;
use SM\Payment\Helper\Data as PaymentDataHelper;

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
     * @var XPaymentFactory
     */
    protected $xPaymentFactory;

    /**
     * @var PaymentDataHelper
     */
    protected $paymentDataHelper;

    /**
     * PaymentManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param \SM\XRetail\Helper\DataConfig $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \SM\Payment\Model\RetailPaymentFactory $retailPaymentFactory
     * @param \SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory $paymentCollectionFactory
     * @param XPaymentFactory $xPaymentFactory
     * @param PaymentDataHelper $paymentDataHelper
     */
    public function __construct(
        RequestInterface      $requestInterface,
        DataConfig            $dataConfig,
        StoreManagerInterface $storeManager,
        RetailPaymentFactory  $retailPaymentFactory,
        CollectionFactory     $paymentCollectionFactory,
        XPaymentFactory       $xPaymentFactory,
        PaymentDataHelper     $paymentDataHelper
    )
    {
        $this->retailPaymentFactory = $retailPaymentFactory;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->xPaymentFactory = $xPaymentFactory;
        $this->paymentDataHelper = $paymentDataHelper;

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

        $registerId = $searchCriteria->getData('registerId');
        $this->getSearchResult()->setSearchCriteria($searchCriteria);
        $collection = $this->getPaymentCollection($searchCriteria);
        $collection->addFieldToFilter('register_id', $registerId);
        $items = [];

        if ($collection->getLastPageNumber() >= $searchCriteria->getData('currentPage') && $collection->count() > 0) {
            foreach ($collection as $payment) {
                $xPayment = $this->xPaymentFactory->create();
                $xPayment->addData($payment->getData());
                $items[] = $xPayment;
            }
        } else {
            $defaults = $this->paymentDataHelper->getDefaultPaymentData($registerId);

            if ($searchCriteria->getData('currentPage') > 1) {
                return $this->getSearchResult()
                    ->setItems([])
                    ->setTotalCount(count($defaults))
                    ->setLastPageNumber(1);
            }

            return $this->getSearchResult()
                ->setItems($defaults)
                ->setTotalCount(count($defaults))
                ->setLastPageNumber(1);
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

        if (is_nan((float)$searchCriteria->getData('currentPage'))) {
            $collection->setCurPage(1);
        } else {
            $collection->setCurPage($searchCriteria->getData('currentPage'));
        }
        if (is_nan((float)$searchCriteria->getData('pageSize'))) {
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
                'type' => "cash",
                'title' => "Cash",
                'is_dummy' => 1,
            ],
            [
                'type' => "credit_card",
                'title' => "Credit Card",
                'is_dummy' => 1,
            ],
            [
                'type' => "credit_card",
                'title' => "Debit Card",
                'is_dummy' => 1,
            ],
            [
                'type' => "credit_card",
                'title' => "Visa Card",
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
//            if (isset($pData['id']) && $pData['id'] >= 100000) {
//                $pData['id'] = null;
//            }
            $xPayment = $this->xPaymentFactory->create();

            if (isset($pData['payment_data'])) {
                $pData['payment_data'] = json_encode($pData['payment_data']);
            }

            if ($pData['type'] === 'adyen') {
                $items[] = $xPayment->addData($this->saveAdyenPayment($pData, $registerId));
            } else {
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
        /** @var \SM\Payment\Model\ResourceModel\RetailPayment\Collection $collection */
        $collection = $this->paymentCollectionFactory->create();
        /** @var \SM\Payment\Model\RetailPayment $payment */
        $type = [];
        $count = 0;
        foreach ($collection as $payment) {
            if (in_array($payment['type'].'_'.$payment['register_id'], $type)) {
                $payment->delete();
                $count++;
                continue;
            }
            $type[] = $payment['type'].'_'.$payment['register_id'];
        }

        return [$count];
    }

    public function deleteDuplicatePayments()
    {
        $data = $this->getRequestData();

        if (!isset($data['register_id'])) {
            throw new \Exception("register_id is required");
        }

        $regId = $data['register_id'];

        /** @var \SM\Payment\Model\ResourceModel\RetailPayment\Collection $collection */
        $collection = $this->paymentCollectionFactory->create();
        $collection->addFieldToFilter(['register_id', 'register_id', 'register_id'], [['eq' => $regId], ['null' => true], ['eq' => '']])
            ->setOrder("id", "DESC");
        $checked = [];
        $deletedCount = 0;

        /** @var \SM\Payment\Model\RetailPayment $payment */
        foreach ($collection as $payment) {
            $registerId = $payment->getData('register_id');
            $idx = $registerId . "_" . $payment->getData('title') . "_" . $payment->getData('type');

            if (!in_array($idx, $checked)) {
                $checked[] = $idx;
                continue;
            }

            $payment->delete();
            $deletedCount++;
        }

        return ["count" => $deletedCount];
    }

    protected function saveAdyenPayment($pData, $registerId)
    {
        // Version
        $payment = $this->retailPaymentFactory->create()->load($pData['id']);
        $paymentData = json_decode((string)$pData['payment_data'], true);
        $oldPaymentData = json_decode((string)$payment->getData('payment_data'), true);
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

        $newPaymentData = json_decode((string)$payment->getData('payment_data'), true);
        $payment->setData('payment_data', json_encode($newPaymentData[$registerId]));

        return $payment->getData();
    }
}
