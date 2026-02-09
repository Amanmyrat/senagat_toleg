<?php

namespace App\Services;

use App\Services\Belet\BeletBankService;

class BankResolverService
{
    protected BeletBankService $beletBankService;

    public function __construct(BeletBankService $beletBankService)
    {
        $this->beletBankService = $beletBankService;
    }

    public function resolveIdByName(string $bankName): ?int
    {
        $banks = $this->beletBankService->getBanks();

        $items = $banks['data']['items'] ?? [];

        foreach ($items as $bank) {
            if (($bank['bank_name'] ?? null) === $bankName) {
                return $bank['id'];
            }
        }

        return null;
    }
}
