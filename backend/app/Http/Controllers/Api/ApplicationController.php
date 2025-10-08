<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Job;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    /**
     * Submit a job application
     */
    public function store(Request $request, $jobId)
    {
        $job = Job::find($jobId);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found',
            ], 404);
        }

        if (!$job->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'This job is no longer accepting applications',
            ], 400);
        }

        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Only students and alumni can apply for jobs',
            ], 403);
        }

        // Check if already applied
        $existingApplication = Application::where('job_id', $jobId)
                                          ->where('student_id', $user->id)
                                          ->first();

        if ($existingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied for this job',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'cover_letter' => 'required|string|max:5000',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'screening_answers' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle resume upload
        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('application_resumes', 'public');
        } else {
            // Use profile resume if available
            $profile = $user->studentProfile;
            if ($profile && $profile->resume_path) {
                $resumePath = $profile->resume_path;
            }
        }

        if (!$resumePath) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload a resume or add one to your profile',
            ], 400);
        }

        $application = Application::create([
            'job_id' => $jobId,
            'student_id' => $user->id,
            'cover_letter' => $request->cover_letter,
            'resume_path' => $resumePath,
            'screening_answers' => $request->screening_answers ?? [],
            'status' => 'pending',
            'applied_at' => now(),
        ]);

        // Create notification for employer
        Notification::create([
            'user_id' => $job->employer_id,
            'type' => 'new_application',
            'title' => 'New Application Received',
            'message' => $user->name . ' has applied for ' . $job->title,
            'data' => [
                'job_id' => $jobId,
                'application_id' => $application->id,
            ],
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'data' => $application,
        ], 201);
    }

    /**
     * Get student's applications
     */
    public function myApplications(Request $request)
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Only students can view applications',
            ], 403);
        }

        $applications = Application::where('student_id', $user->id)
                                   ->with(['job', 'job.employer', 'job.employerProfile'])
                                   ->recent()
                                   ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    /**
     * Get applications for a job (employer only)
     */
    public function jobApplications(Request $request, $jobId)
    {
        $job = Job::find($jobId);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found',
            ], 404);
        }

        // Check ownership
        if ($job->employer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $query = Application::where('job_id', $jobId)
                            ->with(['student', 'studentProfile']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $applications = $query->recent()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    /**
     * Get a single application (for employer or student)
     */
    public function show(Request $request, $id)
    {
        $application = Application::with([
            'job',
            'student',
            'studentProfile',
            'job.screeningQuestions'
        ])->find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found',
            ], 404);
        }

        $user = $request->user();

        // Check authorization
        if ($application->student_id !== $user->id &&
            $application->job->employer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $application,
        ]);
    }

    /**
     * Update application status (employer only)
     */
    public function updateStatus(Request $request, $id)
    {
        $application = Application::find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found',
            ], 404);
        }

        // Check if user is the employer
        if ($application->job->employer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,reviewed,shortlisted,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $application->update([
            'status' => $request->status,
            'notes' => $request->notes,
            'reviewed_at' => now(),
        ]);

        // Create notification for student
        $statusMessages = [
            'reviewed' => 'Your application has been reviewed',
            'shortlisted' => 'Congratulations! You have been shortlisted',
            'rejected' => 'Your application status has been updated',
        ];

        if (isset($statusMessages[$request->status])) {
            Notification::create([
                'user_id' => $application->student_id,
                'type' => 'application_status',
                'title' => 'Application Status Update',
                'message' => $statusMessages[$request->status] . ' for ' . $application->job->title,
                'data' => [
                    'job_id' => $application->job_id,
                    'application_id' => $application->id,
                    'status' => $request->status,
                ],
                'is_read' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Application status updated successfully',
            'data' => $application,
        ]);
    }

    /**
     * Withdraw application (student only)
     */
    public function withdraw(Request $request, $id)
    {
        $application = Application::find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found',
            ], 404);
        }

        // Check ownership
        if ($application->student_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot withdraw application that has been reviewed',
            ], 400);
        }

        $application->delete();

        return response()->json([
            'success' => true,
            'message' => 'Application withdrawn successfully',
        ]);
    }
}
