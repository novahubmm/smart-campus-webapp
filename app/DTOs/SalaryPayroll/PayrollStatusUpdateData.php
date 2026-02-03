<?php

namespace App\DTOs\SalaryPayroll;

class PayrollStatusUpdateData
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $processedBy = null,
        public readonly ?string $paymentMethod = null,
        public readonly ?string $reference = null,
        public readonly ?string $receptionistId = null,
        public readonly ?string $receptionistName = null,
        public readonly ?string $remark = null,
        public readonly ?string $notes = null,
        public readonly ?\DateTimeInterface $paidAt = null,
    ) {}

    public static function fromPayData(PaySalaryPayrollData $data): self
    {
        return new self(
            status: 'paid',
            processedBy: $data->processedBy,
            paymentMethod: $data->paymentMethod,
            reference: $data->reference,
            receptionistId: $data->receptionistId,
            receptionistName: $data->receptionistName,
            remark: $data->remark,
            notes: $data->notes,
            paidAt: now(),
        );
    }
}
