<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmployerProfileController extends Controller
{
    /**
     * Get authenticated employer's profile
     */
    public function show(Request $request)
    {
        $profile = $request->user()->employerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }

    /**
     * Create or update employer profile
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'company_description' => 'required|string|max:2000',
            'industry' => 'required|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'contact_person' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'location' => 'required|string|max:255',
            'founded_year' => 'nullable|integer|min:1800|max:' . date('Y'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!$user->isEmployer()) {
            return response()->json([
                'success' => false,
                'message' => 'Only employers can create an employer profile',
            ], 403);
        }

        $profile = $user->employerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'company_name', 'company_description', 'industry',
                'company_size', 'website', 'contact_person',
                'contact_email', 'contact_phone', 'location', 'founded_year'
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile saved successfully',
            'data' => $profile,
        ]);
    }

    /**
     * Upload company logo
     */
    public function uploadLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $profile = $user->employerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Please create your profile first',
            ], 404);
        }

        // Delete old logo if exists
        if ($profile->logo_path) {
            Storage::disk('public')->delete($profile->logo_path);
        }

        // Store new logo
        $path = $request->file('logo')->store('logos', 'public');

        $profile->update(['logo_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'data' => [
                'logo_path' => $path,
                'logo_url' => $profile->logo_url,
            ]
        ]);
    }

    /**
     * Delete logo
     */
    public function deleteLogo(Request $request)
    {
        $user = $request->user();
        $profile = $user->employerProfile;

        if (!$profile || !$profile->logo_path) {
            return response()->json([
                'success' => false,
                'message' => 'No logo found',
            ], 404);
        }

        // Delete file from storage
        Storage::disk('public')->delete($profile->logo_path);

        // Update profile
        $profile->update(['logo_path' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Logo deleted successfully',
        ]);
    }

    /**
     * Get a specific employer profile (public view)
     */
    public function showPublic($id)
    {
        $profile = EmployerProfile::with('user')->find($id);

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }
}
