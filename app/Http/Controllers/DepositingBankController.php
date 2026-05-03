<?php

namespace App\Http\Controllers;

use App\Models\DepositingBank;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepositingBankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $depositingBanks = DepositingBank::all();
        return Inertia::render('depositing-banks/Index', compact('depositingBanks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('depositing-banks/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DepositingBank::create($request->all());
        return redirect()->route('depositing-banks.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(DepositingBank $depositingBank)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DepositingBank $depositingBank)
    {
        $depositingBank = DepositingBank::find($depositingBank->id);
        return Inertia::render('depositing-banks/Edit', compact('depositingBank'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DepositingBank $depositingBank)
    {
         $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DepositingBank::update($request->all());
        return redirect()->route('depositing-banks.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DepositingBank $depositingBank)
    {
         $depositingBank->delete();

        $depositingBanks = DepositingBank::all();

        return Inertia::render('depositing-banks/Index', [
            'depositing-banks' => $depositingBanks
        ]);
    }
}
