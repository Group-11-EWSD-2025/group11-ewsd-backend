<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Config;
use App\Models\Idea;
use App\Models\MostViewPage;
use App\Models\User;
use App\Models\UserDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    public function index()
    {
        $config = Config::first();

        if (! $config) {
            $defaultConfig = [
                'first_closure_date' => null,
                'final_closure_date' => null,
            ];
            return apiResponse(true, 'Config has not been created yet', $defaultConfig, 200);
        }

        $config = [
            'first_closure_date' => $config->first_closure_date,
            'final_closure_date' => $config->final_closure_date,
        ];
        return apiResponse(true, 'Operation completed successfully', $config, 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_closure_date' => 'required',
            'final_closure_date' => 'required',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $config = Config::updateOrCreate(
            [],
            [
                'first_closure_date' => $request->first_closure_date,
                'final_closure_date' => $request->final_closure_date,
            ]
        );

        return apiResponse(true, 'Operation completed successfully', [
            'first_closure_date' => $request->first_closure_date,
            'final_closure_date' => $request->final_closure_date,
        ], 200);
    }

    public function insight(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'required',
            'department_id'    => 'required',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $total_ideas = Idea::where('academic_year_id', $request->academic_year_id)
            ->where('department_id', $request->department_id)
            ->count();
        $most_view_pages = MostViewPage::orderByDesc('view_count')
            ->limit(3)
            ->pluck('view_count', 'page_name')
            ->toArray();
        $total_comments = Comment::whereHas('idea', function ($query) use ($request) {
            $query->where('academic_year_id', $request->academic_year_id)
                ->where('department_id', $request->department_id);
        })->count();
        $total_user        = UserDepartment::where('department_id', $request->department_id)->count();
        $most_active_users = ActivityLog::where('activity_type', 'login')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as login_count'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('login_count', 'desc')
            ->limit(3)
            ->get();
        $active = [];
        foreach ($most_active_users as $user) {
            $active[$user->name] = $user->login_count;
        }
        $browser_usage = ActivityLog::select('user_agent', DB::raw('count(*) as count'))
            ->groupBy('user_agent')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get();
        // dd($browser_usage);
        $browser_array = [];

        foreach ($browser_usage as $browser) {
            $agent = strtolower($browser->user_agent);

            if (str_contains($agent, 'chrome')) {
                $browser_name = 'Chrome';
            } elseif (str_contains($agent, 'firefox')) {
                $browser_name = 'Firefox';
            } elseif (str_contains($agent, 'safari') && ! str_contains($agent, 'chrome')) {
                $browser_name = 'Safari';
            } else {
                continue; // skip unknown browsers
            }

            if (isset($browser_array[$browser_name])) {
                $browser_array[$browser_name] += $browser->count;
            } else {
                $browser_array[$browser_name] = $browser->count;
            }
        }
        $result = [
            'total_ideas'       => $total_ideas,
            'total_comments'    => $total_comments,
            'total_users'       => $total_user,
            'most_view_pages'   => $most_view_pages,
            'most_active_users' => $active,
            'browser_usage'     => $browser_array,
        ];

        return apiResponse(true, 'Operation completed successfully', $result, 200);
    }
}
