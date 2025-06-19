<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyProfileController extends Controller
{
    public function __construct()
    {
        allowed_roles(User::Role_Admin);
    }

    /**
     * Display the company's profile form.
     */
    public function edit()
    {
        $data = [
            'name' => Setting::value('company_name', 'My Company'),
            'phone' => Setting::value('company_phone', '-'),
            'address' => Setting::value('company_address', '-'),
        ];
        return inertia('admin/company-profile/Edit', compact('data'));
    }

    /**
     * Update the company's profile information.
     */
    public function update(Request $request)
    {
        Auth::user()->setLastActivity('Memperbarui profil perusahaan');

        $rules = [
            'name' => 'required|min:2|max:100',
            'phone' => 'nullable|regex:/^(\+?\d{1,4})?[\s.-]?\(?\d{1,4}\)?[\s.-]?\d{1,4}[\s.-]?\d{1,9}$/|max:40',
            'address' => 'max:1000',
        ];

        $fields = $request->only(['name', 'phone', 'address']);
        $fields['phone'] = $fields['phone'] ?? '';
        $fields['address'] = $fields['address'] ?? '';
        $request->validate($rules);

        Setting::setValue('company_name', $fields['name']);
        Setting::setValue('company_phone', $fields['phone']);
        Setting::setValue('company_address', $fields['address']);

        return back()->with('success', 'Profil perusahaan berhasil diperbarui.');
    }
}
