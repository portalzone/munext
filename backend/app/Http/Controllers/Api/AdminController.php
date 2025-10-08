<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Job;
use App\Models\Application;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::whereIn('role', ['student', 'alumni'])->count(),
            'total_employers' => User::where('role', 'employer')->count(),
            'total_jobs' => Job::count(),
            'active_jobs' => Job::where('status', 'open')->count(),
            'total_applications' => Application::count(),
            'pending_applications' => Application::where('status', 'pending')->count(),
            'verified_users' => User::where('is_verified', true)->count(),
            'unverified_users' => User::where('is_verified', false)->count(),
        ];

        // Recent activity
        $recent_users = User::latest()->take(5)->get();
        $recent_jobs = Job::with(['employer', 'employerProfile'])->latest()->take(5)->get();
        $recent_applications = Application::with(['student', 'job'])->latest()->take(5)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_users' => $recent_users,
                'recent_jobs' => $recent_jobs,
                'recent_applications' => $recent_applications,
            ]
        ]);
    }

    /**
     * Get analytics data
     */
    public function analytics()
    {
        // User growth over last 12 months
        $userGrowth = User::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count'),
            'role'
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month', 'role')
        ->orderBy('month')
        ->get();

        // Job postings by category
        $jobsByCategory = Job::select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get();

        // Application status distribution
        $applicationsByStatus = Application::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Top employers by job postings
        $topEmployers = User::where('role', 'employer')
            ->withCount('jobs')
            ->orderBy('jobs_count', 'desc')
            ->take(10)
            ->get();

        // Job type distribution
        $jobsByType = Job::select('job_type', DB::raw('COUNT(*) as count'))
            ->groupBy('job_type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user_growth' => $userGrowth,
                'jobs_by_category' => $jobsByCategory,
                'applications_by_status' => $applicationsByStatus,
                'top_employers' => $topEmployers,
                'jobs_by_type' => $jobsByType,
            ]
        ]);
    }

    /**
     * Get all users with filters
     */
    public function users(Request $request)
{
    $query = User::query();

    // Filter by role
    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }

    // Filter by verification status
    if ($request->filled('is_verified')) {
        $query->where('is_verified', $request->is_verified === 'true');
    }

    // Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%');
        });
    }

    // Load relationships based on role
    $query->with(['studentProfile', 'employerProfile']);

    $users = $query->latest()->paginate(20);

    return response()->json([
        'success' => true,
        'data' => $users,
    ]);
}
    /**
     * Get single user details
     */
    public function showUser($id)
    {
        $user = User::with(['studentProfile', 'employerProfile', 'jobs', 'applications'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Verify a user
     */
    public function verifyUser($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Send notification to user
        Notification::create([
            'user_id' => $user->id,
            'type' => 'account_verified',
            'title' => 'Account Verified',
            'message' => 'Your account has been verified by an administrator.',
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User verified successfully',
            'data' => $user,
        ]);
    }

    /**
     * Unverify a user
     */
    public function unverifyUser($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'is_verified' => false,
            'email_verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User unverified successfully',
            'data' => $user,
        ]);
    }

    /**
     * Delete a user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account',
            ], 403);
        }

        // Prevent deleting other admins
        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete admin accounts',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Get all jobs (admin view)
     */
    public function jobs(Request $request)
    {
        $query = Job::with(['employer', 'employerProfile']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $jobs = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    /**
     * Delete a job
     */
    public function deleteJob($id)
    {
        $job = Job::findOrFail($id);
        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully',
        ]);
    }

    /**
     * Update job status
     */
    public function updateJobStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,closed,filled',
        ]);

        $job = Job::findOrFail($id);
        $job->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Job status updated successfully',
            'data' => $job,
        ]);
    }

    /**
     * Get all applications
     */
    public function applications(Request $request)
    {
        $query = Application::with(['student', 'job', 'job.employer']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $applications = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    /**
     * Generate reports
     */
    public function reports(Request $request)
    {
        $type = $request->get('type', 'overview');

        switch ($type) {
            case 'users':
                return $this->userReport();
            case 'jobs':
                return $this->jobReport();
            case 'applications':
                return $this->applicationReport();
            default:
                return $this->overviewReport();
        }
    }

    private function overviewReport()
    {
        $data = [
            'total_users' => User::count(),
            'users_by_role' => User::select('role', DB::raw('COUNT(*) as count'))
                ->groupBy('role')
                ->get(),
            'total_jobs' => Job::count(),
            'jobs_by_status' => Job::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),
            'total_applications' => Application::count(),
            'average_applications_per_job' => round(Application::count() / max(Job::count(), 1), 2),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function userReport()
    {
        $data = [
            'total_users' => User::count(),
            'verified_users' => User::where('is_verified', true)->count(),
            'users_by_role' => User::select('role', DB::raw('COUNT(*) as count'))
                ->groupBy('role')
                ->get(),
            'recent_signups' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function jobReport()
    {
        $data = [
            'total_jobs' => Job::count(),
            'active_jobs' => Job::where('status', 'open')->count(),
            'jobs_by_category' => Job::select('category', DB::raw('COUNT(*) as count'))
                ->groupBy('category')
                ->get(),
            'jobs_by_type' => Job::select('job_type', DB::raw('COUNT(*) as count'))
                ->groupBy('job_type')
                ->get(),
            'average_views_per_job' => round(Job::avg('views_count'), 2),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function applicationReport()
    {
        $data = [
            'total_applications' => Application::count(),
            'applications_by_status' => Application::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),
            'recent_applications' => Application::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
