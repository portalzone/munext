<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentProfileController extends Controller
{
    /**
     * Get authenticated student's profile
     */
    public function show(Request $request)
    {
        $profile = $request->user()->studentProfile;

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
     * Create or update student profile
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_number' => 'nullable|string|max:20',
            'program' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
            'graduation_year' => 'required|integer|min:2000|max:2050',
            'gpa' => 'nullable|numeric|min:0|max:4.0',
            'bio' => 'nullable|string|max:1000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:50',
            'linkedin_url' => 'nullable|url',
            'github_url' => 'nullable|url',
            'portfolio_url' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'available_from' => 'nullable|date',
            'work_authorization' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Only students and alumni can create a student profile',
            ], 403);
        }

        $profile = $user->studentProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'student_number', 'program', 'faculty', 'graduation_year',
                'gpa', 'bio', 'skills', 'linkedin_url', 'github_url',
                'portfolio_url', 'phone', 'location', 'available_from',
                'work_authorization'
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile saved successfully',
            'data' => $profile,
        ]);
    }

    /**
     * Upload resume
     */
    public function uploadResume(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'resume' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $profile = $user->studentProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Please create your profile first',
            ], 404);
        }

        // Delete old resume if exists
        if ($profile->resume_path) {
            Storage::disk('public')->delete($profile->resume_path);
        }

        // Store new resume
        $path = $request->file('resume')->store('resumes', 'public');

        $profile->update(['resume_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Resume uploaded successfully',
            'data' => [
                'resume_path' => $path,
                'resume_url' => $profile->resume_url,
            ]
        ]);
    }

    /**
     * Delete resume
     */
    public function deleteResume(Request $request)
    {
        $user = $request->user();
        $profile = $user->studentProfile;

        if (!$profile || !$profile->resume_path) {
            return response()->json([
                'success' => false,
                'message' => 'No resume found',
            ], 404);
        }

        // Delete file from storage
        Storage::disk('public')->delete($profile->resume_path);

        // Update profile
        $profile->update(['resume_path' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Resume deleted successfully',
        ]);
    }

    /**
     * Get a specific student profile (for employers viewing applicants)
     */
    public function showPublic($id)
    {
        $profile = StudentProfile::with('user')->find($id);

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
