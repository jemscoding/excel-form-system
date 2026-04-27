<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        
        // This will load the page at URL: /clients
        return Inertia::render('clients/Index', [
            'clients' => $clients
        ]);
    }

    public function create()
    {
        // This will load the page at URL: /clients/create
        return Inertia::render('clients/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
        ]);
    
        Client::create($request->all());

        // After create, redirect back to /clients
        return redirect()->route('clients.index');
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);
        
        // This will load the page at URL: /clients/{id}/edit
        return Inertia::render('clients/Edit', [
            'client' => $client
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
        ]);

        $client = Client::findOrFail($id);
        $client->update($request->all());

        // After update, redirect back to /clients
        return redirect()->route('clients.index');
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        // After delete, redirect back to /clients
        return redirect()->route('clients.index');
    }
}