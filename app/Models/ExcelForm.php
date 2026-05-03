<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelForm extends Model
{
    protected $fillable = [
        'client_id',
        'product_id',
        'agent_id',
        'payment_method_id',
        'depositing_bank_id',
        'container_code',
        'china_recieved_date',
        'order_reference',
        'pkgs',
        'total_cbm',
        'soa_number',
        'actual_payment',
        'initial_billing',
        'withholding_tax',
        'inbound_cost',
        'service_fee',
        'overweight',
        'discount',
        'others',
        'amount_to_be_paid',
        'balance',
        'payment_reference_number',
        'status',
        'total',
        'purpose',
    ];

     public function getAmountToBePaidAttribute()
    {
        return ($this->inbound_cost + $this->service_fee + $this->overweight_charge + $this->others) 
            - ($this->discount + $this->withholding_tax);
    }

    public function getInitialBilling() 
    {
        return ($this->inbound_cost + $this->service_fee);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function product() 
    {
        return $this->belongsTo(Product::class);
    }

    public function agent() 
    {
        return $this->belongsTo(Agent::class);
    }

    public function paymentMethod() 
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function depositingBank() 
    {
        return $this->belongsTo(DepositingBank::class);
    }
}
