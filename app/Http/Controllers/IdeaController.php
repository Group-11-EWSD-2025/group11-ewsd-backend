<?php
namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\IdeaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class IdeaController extends Controller
{

    public function index(Request $request)
    {
        $query = Idea::with('files', 'category', 'department', 'academicYear', 'user')
            ->withCount(['likes', 'unLikes', 'comments']);

        // Apply filters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('order_by')) {
            if($request->order_by == 'likes_count') {
                $query->orderBy('likes_count', 'desc');
            } else {
                $query->orderByDesc($request->order_by);
            }
           
        }

        $ideas = $query->paginate(5);

        $userId = auth()->id(); // get current user

        // Add flag for is_liked and is_unliked
        $ideas->getCollection()->transform(function ($idea) use ($userId) {
            $idea->is_liked   = $idea->likes()->where('user_id', $userId)->exists();
            $idea->is_unliked = $idea->unLikes()->where('user_id', $userId)->exists();
            return $idea;
        });

        return apiResponse(true, 'Operation completed successfully', $ideas, 200);
    }

    public function store(Request $request)
    {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'privacy'     => 'required',
            'content'     => 'required',
            'category_id' => 'required',
        ]);
        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }
        $user       = auth()->user();
        $department = $user->departments()->first();
        if (! $department) {
            return apiResponse(false, 'Department not found', null, 400);
        }
        $academic_year = getActiveAcademicYear();
        if (! $academic_year) {
            return apiResponse(false, 'Academic year not found', null, 400);
        }
        $academic_year_id = $academic_year->id;
        $idea             = Idea::create([
            'category_id'      => $request->category_id,
            'department_id'    => $department->id,
            'user_id'          => $user->id,
            'content'          => $request->content,
            'privacy'          => $request->privacy,
            'academic_year_id' => $academic_year_id,
        ]);
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = rand(10000, 99999) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('img'), $fileName);

                // Generate full URL including domain
                $fileUrl = asset('img/' . $fileName);

                IdeaFile::create([
                    'idea_id' => $idea->id,
                    'file'    => $fileUrl,
                ]);
            }
        }
        $idea->load('files');
        return apiResponse(true, 'Operation completed successfully', $idea, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Idea  $idea
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = auth()->user();
        $idea = Idea::with('files', 'category', 'department', 'user', 'comments', 'comments.replies','comments.user')->withCount(['likes', 'unLikes', 'comments'])->find($id);

        if (! $idea) {
            return apiResponse(false, 'Idea not found', null, 404);
        }

        // Use user_id and idea_id to create a unique cache key
        $cacheKey = "idea_viewed_{$user->id}_{$idea->id}";

        // Check if user has already viewed this idea
        if (! Cache::has($cacheKey)) {
            $idea->increment('views');

            // Store this view in cache for a long time (forever or for a certain period, e.g., 1 week)
            Cache::put($cacheKey, true, now()->addDays(7));
        }
        $idea->is_liked   = $idea->likes()->where('user_id', $user->id)->exists();
        $idea->is_unliked = $idea->unLikes()->where('user_id', $user->id)->exists();
        $idea->load('files');

        return apiResponse(true, 'Operation completed successfully', $idea, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Idea  $idea
     * @return \Illuminate\Http\Response
     */
    public function edit(Idea $idea)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Idea  $idea
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'privacy'     => 'required',
            'content'     => 'required',
            'category_id' => 'required',
            'id'          => 'required|exists:ideas,id',
        ]);
        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }
        $user             = auth()->user();
        $idea             = Idea::find($request->id);
        $idea->update([
            'category_id' => $request->category_id,
            'content'     => $request->content,
            'privacy'     => $request->privacy,
        ]);
        if ($request->hasFile('files')) {
            // Delete existing files
            foreach ($idea->files as $file) {
                $filePath = public_path($file->file);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $file->delete();
            }
            foreach ($request->file('files') as $file) {
                $fileName = rand(10000, 99999) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('img'), $fileName);

                // Generate full URL including domain
                $fileUrl = asset('img/' . $fileName);

                IdeaFile::create([
                    'idea_id' => $idea->id,
                    'file'    => $fileUrl,
                ]);
            }
        }
        $idea->load('files');
        return apiResponse(true, 'Operation completed successfully', $idea, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Idea  $idea
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:ideas,id',
        ]);
        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }
        $idea = Idea::find($request->id);
        if ($idea) {
            $idea->delete();
        }

        return apiResponse(true, 'Operation completed successfully', [], 200);
    }
}
