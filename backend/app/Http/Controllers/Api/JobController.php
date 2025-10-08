<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    /**
     * Get all jobs (public listing)
     */
    public function index(Request $request)
    {
        $query = Job::with(['employer', 'employerProfile'])
                    ->open()
                    ->recent();

        // Filters
        if ($request->has('category') && $request->category) {
            $query->byCategory($request->category);
        }

        if ($request->has('job_type') && $request->job_type) {
            $query->byJobType($request->job_type);
        }

        if ($request->has('is_remote') && $request->is_remote == 'true') {
            $query->remote();
        }

        if ($request->has('experience_level') && $request->experience_level) {
            $query->where('experience_level', $request->experience_level);
        }

        if ($request->has('location') && $request->location) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $perPage = $request->get('per_page', 15);
        $jobs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    /**
     * Get a single job
     */
    public function show($id)
    {
        $job = Job::with([
            'employer',
            'employerProfile',
            'screeningQuestions' => function($q) {
                $q->ordered();
            }
        ])->find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found',
            ], 404);
        }

        // Increment view count
        $job->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }

    /**
     * Create a new job (employer only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'job_type' => 'required|in:full-time,part-time,contract,internship,co-op',
            'location' => 'required|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'salary_period' => 'nullable|in:per hour,per month,per year',
            'experience_level' => 'required|in:entry,intermediate,senior,executive',
            'category' => 'required|string|max:100',
            'skills_required' => 'nullable|array',
            'skills_required.*' => 'string|max:50',
            'benefits' => 'nullable|array',
            'benefits.*' => 'string|max:100',
            'application_deadline' => 'nullable|date|after:today',
            'start_date' => 'nullable|date',
            'is_remote' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!$user->employerProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your employer profile first',
            ], 403);
        }

        $job = Job::create([
            'employer_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'responsibilities' => $request->responsibilities,
            'job_type' => $request->job_type,
            'location' => $request->location,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'salary_period' => $request->salary_period ?? 'per year',
            'experience_level' => $request->experience_level,
            'category' => $request->category,
            'skills_required' => $request->skills_required ?? [],
            'benefits' => $request->benefits ?? [],
            'application_deadline' => $request->application_deadline,
            'start_date' => $request->start_date,
            'is_remote' => $request->is_remote ?? false,
            'status' => 'open',
            'views_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job created successfully',
            'data' => $job,
        ], 201);
    }

    /**
     * Update a job
     */
    public function update(Request $request, $id)
    {
        $job = Job::find($id);

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

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'job_type' => 'sometimes|required|in:full-time,part-time,contract,internship,co-op',
            'location' => 'sometimes|required|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'salary_period' => 'nullable|in:per hour,per month,per year',
            'experience_level' => 'sometimes|required|in:entry,intermediate,senior,executive',
            'category' => 'sometimes|required|string|max:100',
            'skills_required' => 'nullable|array',
            'benefits' => 'nullable|array',
            'application_deadline' => 'nullable|date',
            'start_date' => 'nullable|date',
            'is_remote' => 'boolean',
            'status' => 'sometimes|in:open,closed,filled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $job->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully',
            'data' => $job,
        ]);
    }

    /**
     * Delete a job
     */
    public function destroy(Request $request, $id)
    {
        $job = Job::find($id);

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

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully',
        ]);
    }

    /**
     * Get jobs posted by the authenticated employer
     */
    public function myJobs(Request $request)
    {
        $jobs = Job::where('employer_id', $request->user()->id)
                   ->withCount('applications')
                   ->recent()
                   ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    /**
     * Save/unsave a job (for students)
     */
    public function toggleSave(Request $request, $id)
    {
        $job = Job::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found',
            ], 404);
        }

        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Only students can save jobs',
            ], 403);
        }

        $isSaved = $user->savedJobs()->where('job_id', $id)->exists();

        if ($isSaved) {
            $user->savedJobs()->detach($id);
            $message = 'Job removed from saved list';
        } else {
            $user->savedJobs()->attach($id);
            $message = 'Job saved successfully';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => ['is_saved' => !$isSaved]
        ]);
    }

    /**
     * Get saved jobs for authenticated student
     */
    public function savedJobs(Request $request)
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Only students can view saved jobs',
            ], 403);
        }

        $savedJobs = $user->savedJobs()
                          ->with(['employer', 'employerProfile'])
                          ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $savedJobs,
        ]);
    }
}
