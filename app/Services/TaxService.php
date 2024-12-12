<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\DividendPayment;

class TaxService
{
    // Nigerian tax rates
    private const VAT_RATE = 0.075; // 7.5%
    private const CAPITAL_GAINS_RATE = 0.10; // 10%
    private const DIVIDEND_WITHHOLDING_RATE = 0.10; // 10%

    public function calculateTransactionTax(Transaction $transaction): float
    {
        if ($transaction->type === 'sell') {
            $originalInvestment = $transaction->assetShare->purchase_price ?? 0;
            $saleAmount = $transaction->amount;
            $profit = max(0, $saleAmount - $originalInvestment);
            
            return $this->calculateCapitalGainsTax($profit);
        }

        return $this->calculateVAT($transaction->fee);
    }

    public function calculateDividendTax(DividendPayment $payment): float
    {
        return $this->calculateWithholdingTax($payment->amount);
    }

    public function calculateAnnualTaxSummary(User $user, int $year): array
    {
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        // Calculate capital gains
        $capitalGains = $this->calculateYearlyCapitalGains($user, $startDate, $endDate);
        
        // Calculate dividend income
        $dividendIncome = $this->calculateYearlyDividends($user, $startDate, $endDate);

        // Calculate total VAT paid
        $vatPaid = $this->calculateYearlyVAT($user, $startDate, $endDate);

        return [
            'year' => $year,
            'capital_gains' => [
                'total_gains' => $capitalGains,
                'tax_amount' => $this->calculateCapitalGainsTax($capitalGains)
            ],
            'dividends' => [
                'total_dividends' => $dividendIncome,
                'tax_amount' => $this->calculateWithholdingTax($dividendIncome)
            ],
            'vat_paid' => $vatPaid,
            'total_tax_liability' => $this->calculateCapitalGainsTax($capitalGains) + 
                                   $this->calculateWithholdingTax($dividendIncome) +
                                   $vatPaid
        ];
    }

    private function calculateVAT(float $amount): float
    {
        return round($amount * self::VAT_RATE, 2);
    }

    private function calculateCapitalGainsTax(float $profit): float
    {
        return round($profit * self::CAPITAL_GAINS_RATE, 2);
    }

    private function calculateWithholdingTax(float $amount): float
    {
        return round($amount * self::DIVIDEND_WITHHOLDING_RATE, 2);
    }

    private function calculateYearlyCapitalGains(User $user, string $startDate, string $endDate): float
    {
        return $user->transactions()
            ->where('type', 'sell')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('amount');
    }

    private function calculateYearlyDividends(User $user, string $startDate, string $endDate): float
    {
        return $user->dividendPayments()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('amount');
    }

    private function calculateYearlyVAT(User $user, string $startDate, string $endDate): float
    {
        $totalFees = $user->transactions()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('fee');

        return $this->calculateVAT($totalFees);
    }
}