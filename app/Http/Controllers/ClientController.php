<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::all();
        return Inertia::render('clients/Index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('clients/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validates Data
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:clients,name',
            'code' => 'required|string|unique:clients,code|max:255',
        ]);

        // Attempt to create new client record in the database
        try {
            $client = Client::create($validated);

            // If successful, redirect to clients index page with success flash message
            // Also pass the newly created client data to the frontend
            return redirect()->route('clients.index')->with([
                'success' => 'Client created successfully!',
                'client' => $client,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create client: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        $client = Client::find($client->id);
        return Inertia::render('clients/Edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required',
        ]);

        Client::update($request->all());

        $clients = Client::all();

        return Inertia::render('clients/Index', [
            'clients' => $clients
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        $clients = Client::all();

        return Inertia::render('clients/Index', [
            'clients' => $clients
        ]);
    }
}
