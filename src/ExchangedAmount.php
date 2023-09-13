<?php
declare(strict_types=1);

namespace ExchangeSberbank;

use Exception;

class ExchangedAmount
{
    const XML_LINK = "http://www.cbr.ru/scripts/XML_daily.asp";

    public function __construct
    (
        private readonly string $fromCurrency,
        private readonly string $toCurrency,
        private readonly int    $amount
    )
    {
    }

    /** @throws Exception */
    public function toDecimal(): float
    {
        $xml = simplexml_load_file(self::XML_LINK);

        if (!$xml) {
            throw new Exception("XML not found");
        }

        $currencyOriginalToRub = $this->getCurrencyRateToRub($xml, $this->fromCurrency);
        $currencyTargetToRub = $this->getCurrencyRateToRub($xml, $this->toCurrency);
        return round($this->amount * $currencyOriginalToRub / $currencyTargetToRub, 2);
    }

    /** @throws Exception */
    private function getCurrencyRateToRub($xml, string $currencyAbbr): float
    {
        foreach ($xml->Valute as $currency) {
            if ($currency->CharCode == $currencyAbbr) {
                return (float)str_replace(',', '.', $currency->Value);
            }
        }

        throw new Exception("Currency string $currencyAbbr not found in XML");
    }
}