<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Tailor;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function activeCustomers()
    {
        $q = Customer::query();
        $q->where('active', true);
        $q->orderBy('name', 'asc');
        $items = $q->get([
            'id', 'name', 'phone', 'address', 'active'
        ]);
        return response()->json($items);
    }

}
