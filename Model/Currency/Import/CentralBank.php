<?php

namespace Aur\CentralBankIntegration\Model\Currency\Import;

use Aur\CentralBankIntegration\Service\Currency;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;

class CentralBank extends \Magento\Directory\Model\Currency\Import\AbstractImport
{
    private $scopeConfig;
    public $currency;
    public $curl;


    public function __construct(
        \Magento\Directory\Model\CurrencyFactory           $currencyFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Currency                                           $currency,
        Curl                                               $curl
    )
    {
        parent::__construct($currencyFactory);
        $this->scopeConfig = $scopeConfig;
        $this->currency = $currency;
        $this->curl = $curl;
    }

    /**
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param int $retry
     * @return float|null
     */
    protected function _convert($currencyFrom, $currencyTo, $retry = 0)
    {
        $result = null;
        $timeout = (int)$this->scopeConfig->getValue(
            'currency/currencyLayer/timeout',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $centralBankCurrencyCode = $this->currency->getCentralBankCurrencyCde($currencyTo);

        $url = $this->currency->getUrl($centralBankCurrencyCode);

        $centralBankCurrencyCode = str_replace('.', "_", $centralBankCurrencyCode);

        try {
            $this->curl->setTimeout($timeout);
            $this->curl->get($url);
            $response = json_decode($this->curl->getBody(), true);

            if (isset($response['items'][0][$centralBankCurrencyCode])) {
                $result = (float)$response['items'][0][$centralBankCurrencyCode];
            } else {
                $this->_messages[] = __('We can\'t retrieve a rate from %1.', $url);
                $this->_convert($currencyFrom, $currencyTo, 1);
            }
        } catch (\Exception $e) {
            if ($retry == 0) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = __('We can\'t retrieve a rate from %1.', $url);
            }
        }
        return $result;
    }
}


