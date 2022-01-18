<?php

namespace Aur\CentralBankIntegration\Service;

class Currency
{
    public $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getCentralBankCurrencyCde($currency)
    {
        return "TP.DK." . $currency . ".S";
    }

    public function getUrl($code)
    {
//        $date = date_create("2013-03-15");
//        echo date_format($date,"l");
        $key = $this->scopeConfig->getValue(
            'currency/centralbank/key',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );

        $url = $this->scopeConfig->getValue(
            'currency/centralbank/url',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );

        $date = date('d-m-Y');
        return $url . "series=" . $code . "&key=" . $key . "&startDate=" . $date . "&endDate=" . $date . "&type=json";
    }

}
