<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;


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
            'logo_path' => Setting::value('company_logo_path', null),
        ];

        return inertia('admin/company-profile/Edit', compact('data'));
    }

    /**
     * Update the company's profile information.
     */
    public function update(Request $request)
    {
        Auth::user()->setLastActivity('Memperbarui profil perusahaan');

        $validated = $request->validate([
            'name' => 'required|min:2|max:100',
            'phone' => 'nullable|regex:/^(\+?\d{1,4})?[\s.-]?\(?\d{1,4}\)?[\s.-]?\d{1,4}[\s.-]?\d{1,9}$/|max:40',
            'address' => 'nullable|max:1000',
            'logo_path' => 'nullable|max:500',
            'logo_image' => 'nullable|image|max:5120',
        ]);

        $existingLogoPath = Setting::value('company_logo_path');

        if (!$validated['logo_path'] || $request->hasFile('logo_image')) {
            if ($existingLogoPath && file_exists(public_path($existingLogoPath))) {
                @unlink(public_path($existingLogoPath));
            }
        }

        if ($request->hasFile('logo_image')) {
            $file = $request->file('logo_image');

            // Persiapkan direktori penyimpanan
            $relativeDir = 'uploads/logos';
            $absoluteDir = public_path($relativeDir);
            if (!file_exists($absoluteDir)) {
                mkdir($absoluteDir, 0755, true);
            }

            // Tentukan nama file baru
            $filename = 'logo.' . $file->getClientOriginalExtension();
            $relativePath = $relativeDir . '/' . $filename;
            $absolutePath = $absoluteDir . '/' . $filename;

            // Resize dan simpan dengan Intervention Image v3
            $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

            $image = $manager->read($file)->resize(240, 240, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $image->save($absolutePath);

            // Update path logo baru
            $logo_path = $relativePath;
        }

        Setting::setValue('company_name', $validated['name']);
        Setting::setValue('company_phone', $validated['phone'] ?? '');
        Setting::setValue('company_address', $validated['address'] ?? '');
        Setting::setValue('company_logo_path', $logo_path ?? '');

        return back()->with('success', 'Profil perusahaan berhasil diperbarui.');
    }
}
