<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function create()
    {
        return view('clients.create');
    }

    public function edit($id)
    {
        return view('clients.edit', compact('id'));
    }
}
