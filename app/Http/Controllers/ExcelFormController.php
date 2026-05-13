<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Client;
use App\Models\DepositingBank;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Services\ExcelWriter\ExcelWriterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ExcelFormController extends Controller
{
    protected ExcelWriterService $excelWriter;

    public function __construct(ExcelWriterService $excelWriter)
    {
        $this->excelWriter = $excelWriter;
    }

    public function index()
    {
        return Inertia::render('excel-forms/Index');
    }

    public function create()
    {
        $agents = Agent::all();
        $paymentMethods = PaymentMethod::all();
        $depositingBanks = DepositingBank::all();
        $products = Product::all();
        $clients = Client::all();

        $purposes = [
            ['id' => '1', 'name' => 'FREIGHT PAYMENT'],
            ['id' => '2', 'name' => 'OTHERS']
        ];

        $status = [
            ['id' => '1', 'name' => 'Pending'],
            ['id' => '2', 'name' => 'Paid'],
            ['id' => '3', 'name' => 'Unpaid'],
        ];

        return Inertia::render('excel-forms/Create', compact('agents', 'paymentMethods', 'depositingBanks', 'products', 'clients', 'purposes', 'status'));
    }

    public function store(Request $request)
    {
        try {
            Log::info('=== STARTING EXCEL ENTRY ===');
            Log::info('Request data:', $request->all());

            $validated = $request->validate([
                'container_code' => 'required|string|max:255',
                'china_received_date' => 'required|date',
                'order_reference' => 'required|string|max:255',
                'client_id' => 'required|exists:clients,id',
                'client_name' => 'required|string|max:255',
                'client_code' => 'required|string|max:255',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'nullable|integer|min:1', // ADD THIS LINE
                'pkgs' => 'nullable|integer',
                'total_cbm' => 'nullable|numeric',
                'weight' => 'nullable|numeric',
                'applied_rate' => 'nullable|string',
                'soa_number' => 'nullable|string',
                'soa_code' => 'nullable|string',
                'initial_billing' => 'nullable|numeric',
                'withholding_tax' => 'nullable|numeric',
                'inbound_cost' => 'required|numeric|min:0',
                'service_fee' => 'required|numeric|min:0',
                'overweight' => 'nullable|numeric',
                'discount' => 'nullable|numeric',
                'others' => 'nullable|numeric',
                'depositing_bank_id' => 'required|exists:depositing_banks,id',
                'payment_reference_number' => 'required|string',
                'deposit_date' => 'nullable|date',
                'receiving_bank' => 'nullable|string',
                'purpose' => 'nullable|string',
                'agent_id' => 'nullable|exists:agents,id',
                'payment_method_id' => 'nullable|exists:payment_methods,id',
                'handler' => 'nullable|string|max:255',
                'status' => 'required|string',
            ]);

            Log::info('Validation passed', $validated);

            // Get related data for display names
            $depositingBank = DepositingBank::find($validated['depositing_bank_id']);
            $paymentMethod = PaymentMethod::find($validated['payment_method_id'] ?? 0);
            $agent = Agent::find($validated['agent_id'] ?? 0);
            $product = Product::find($validated['product_id']);
            $client = Client::find($validated['client_id']);

            // Add display names to data
            $validated['depositing_bank_name'] = $depositingBank->name ?? '';
            $validated['payment_method_name'] = $paymentMethod->name ?? '';
            $validated['payment_method_code'] = $paymentMethod->code ?? '';
            $validated['agent_name'] = $agent->name ?? '';
            $validated['product_name'] = $product->name ?? '';
            $validated['client_name'] = $client->name ?? $validated['client_name'] ?? '';
            $validated['handler'] = $validated['handler'] ?? '';
            $validated['created_at'] = now()->format('Y-m-d H:i:s');

            // Ensure quantity has a default value
            $validated['quantity'] = isset($validated['quantity']) ? (int)$validated['quantity'] : 1;

            Log::info('Data prepared for Excel:', $validated);

            $result = $this->excelWriter->addEntry($validated);

            Log::info('Excel entry successful', $result);

            return redirect()->route('excel-forms.create')
                ->with('success', "Entry #{$result['index']} added successfully! ({$result['quantity']} row(s) created)");
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to add entry: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Failed to add entry: ' . $e->getMessage());
        }
    }

    public function download()
    {
        return $this->excelWriter->download();
    }
}
