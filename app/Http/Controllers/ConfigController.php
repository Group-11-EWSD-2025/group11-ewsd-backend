<?php
namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\Idea;
use App\Models\MostViewPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
<<<<<<< Updated upstream
=======
use App\Models\Idea;
use App\Models\User;
use App\Models\Comment;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
>>>>>>> Stashed changes

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

        $total_ideas = Idea::where('academic_year_id',
        $request->academic_year_id)
            ->where('department_id', $request->deaprtment_id)
            ->count();
        $most_view_pages = MostViewPage::orderByDesc('view_count')
            ->limit(3)
            ->pluck('view_count', 'page_name')
            ->toArray();
        $result = [
            'total_ideas'       => $total_ideas,
            'total_comments'    => 100,
            'total_users'       => 50,
            'most_view_pages'   => $most_view_pages,
            'most_active_users' => [
                'User 1' => 10,
                'User 2' => 20,
                'User 3' => 30,
            ],
            'browser_usage'     => [
                'Chrome'  => 50,
                'Firefox' => 30,
                'Safari'  => 20,
            ],


        // get total comments of ideas that are in the academic year and department
        $total_comments = Comment::whereHas('idea', function ($query) use ($request) {
            $query->where('academic_year_id', $request->academic_year_id)
                ->where('department_id', $request->deaprtment_id);
        })->count();

        $total_user = User::count();

        // i want to get the group of user_id and count the login count
        $most_active_users = ActivityLog::where('activity_type', 'login')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as login_count'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('login_count', 'desc')
            ->limit(3)
            ->get();

        // get user agent from activity logs
        $browser_usage = ActivityLog::select('user_agent', DB::raw('count(*) as count'))
            ->groupBy('user_agent')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get();

        $result = [
            'total_ideas' => $total_ideas,
            'total_comments' => $total_comments,
            'total_users' => $total_user,
            'most_view_pages' => [
                'Idea Details Page' => 100,
                'User Management Page' => 200,
                'Account Setting Page' => 300
            ],
            'most_active_users' => $most_active_users->map(function ($user) {
                return [
                    'name' => $user->name,
                    'login_count' => $user->login_count
                ];
            }),
            'browser_usage' => $browser_usage->map(function ($browser) {
                return [
                    'browser' => $browser->user_agent,
                    'count' => $browser->count
                ];
            }),
        ];
        return apiResponse(true, 'Operation completed successfully', $result, 200);
    }
}
