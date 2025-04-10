<?php
namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\IdeaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IdeaController extends Controller
{

    public function index(Request $request)
    {
        $query = Idea::with('files');

        // Apply filters only if parameters exist
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
            $query->orderByDesc($request->order_by);
        }
        $ideas = $query->paginate(5);

        return apiResponse(true, 'Operation completed successfully', $ideas, 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'privacy'     => 'required',
            'content'     => 'required',
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }
        $user             = auth()->user();
        $department       = $user->departments()->first();
        if(!$department) {
            return apiResponse(false, 'Department not found', null, 400);
        }
        $academic_year_id = 1;
        $idea             = Idea::create([
            'category_id'      => $request->category_id,
            'department_id'    => $department->id,
            'user_id'          => $user->id,
            'content'          => $request->content,
            'academic_year_id' => $academic_year_id,
        ]);
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = time() . '.' . $file->getClientOriginalExtension();
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
    public function show(Idea $idea)
    {
        //
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
    public function update(Request $request, Idea $idea)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Idea  $idea
     * @return \Illuminate\Http\Response
     */
    public function destroy(Idea $idea)
    {
        //
    }
}
