<?php
namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\IdeaFile;
use App\Models\IdeaReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use ZipArchive;

class IdeaController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();
        // Check if the user is a QA coordinator
        if ($user->role != 'staff') {
            $academic_year = AcademicYear::orderBy('created_at', 'desc')->first();
        } else {
            $academic_year = getActiveAcademicYear();
        }
        $query = Idea::with(['files', 'category', 'department', 'academicYear', 'user'])
            ->withCount(['likes', 'unLikes', 'comments', 'report'])
            ->whereHas('user', function ($q) {
                $q->where('is_disable', 0);
            })
            ->where('academic_year_id', optional($academic_year)->id);

        // Apply date filters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Apply sorting
        if ($request->filled('order_by')) {
            if ($request->order_by == 'likes_count') {
                $query->orderBy('likes_count', 'desc');
            } else {
                $query->orderByDesc($request->order_by);
            }
        }

        // Visibility filter
        if ($request->filled('is_hidden') && $request->is_hidden == 0) {
            $query->where('status', 'active');
        }

        // Paginate results
        $ideas  = $query->paginate(5);
        $userId = auth()->id();

        // Add flags for each idea
        $ideas->getCollection()->transform(function ($idea) use ($userId) {
            $idea->is_liked   = $idea->likes()->where('user_id', $userId)->exists();
            $idea->is_unliked = $idea->unLikes()->where('user_id', $userId)->exists();
            $idea->is_report  = $idea->report()->where('user_id', $userId)->exists();
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
        $departmentId = $department->id;
        if ($idea) {
            $qcs = User::where('role', 'qa-coordinator')
                ->whereHas('departments', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })
                ->get();
            foreach ($qcs as $qc) {
                if ($qc) {
                    $email       = $qc->email;
                    $subject     = 'New Idea Submitted';
                    $bodyMessage = 'A new idea has been submitted by ' . $user->name . '. Please review it.';

                    Mail::raw($bodyMessage, function ($message) use ($email, $subject) {
                        $message->to($email)
                            ->subject($subject);
                    });
                }
            }
        }
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
        $idea = Idea::with([
            'files',
            'category',
            'department',
            'user',
            'comments.replies',
            'comments.user',
        ])->withCount(['likes', 'unLikes', 'comments', 'report'])->find($id);

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
        $idea->is_report  = $idea->report()->where('user_id', $user->id)->exists();
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
        $user = auth()->user();
        $idea = Idea::find($request->id);
        $idea->update([
            'category_id' => $request->category_id,
            'content'     => $request->content,
            'privacy'     => $request->privacy,
        ]);
        IdeaFile::where('idea_id', $idea->id)->delete();
        if ($request->hasFile('files')) {
            // Delete existing files
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
        $existingFiles = json_decode($request->existing_files, true);
        foreach ($existingFiles as $file) {
            IdeaFile::create([
                'idea_id' => $idea->id,
                'file'    => $file,
            ]);
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

    public function hide(Request $request)
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
            $idea->update([
                'status' => 'hide',
            ]);
        }

        return apiResponse(true, 'Operation completed successfully', $idea, 200);
    }

    public function unhide(Request $request)
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
            $idea->update([
                'status' => 'active',
            ]);
        }

        return apiResponse(true, 'Operation completed successfully', $idea, 200);
    }

    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idea_id' => 'required|exists:ideas,id',
        ]);
        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }
        $user = Auth::user();
        IdeaReport::create([
            'user_id' => $user->id,
            'idea_id' => $request->idea_id,
        ]);
        return apiResponse(true, 'Operation completed successfully', [], 200);
    }

    public function export(Request $request)
    {
        // Validate incoming parameters
        $request->validate([
            'department_id'    => 'nullable',
            'academic_year_id' => 'nullable',
            'csv'              => 'nullable|boolean',
            'zip'              => 'nullable|boolean',
        ]);

        $isCsv = $request->boolean('csv');
        $isZip = $request->boolean('zip');

        if (! $isCsv && ! $isZip) {
            return response()->json(['message' => 'At least one of csv or zip must be true.'], 422);
        }

        // Build query
        $query = Idea::query();

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $ideas = $query->with(['department', 'academicYear', 'files'])->get();

        $downloads = [];

        if ($isCsv) {
            // Create Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Content');
            $sheet->setCellValue('C1', 'Department');
            $sheet->setCellValue('D1', 'Academic Year');
            $sheet->setCellValue('E1', 'Created At');

            $row = 2;
            foreach ($ideas as $idea) {
                $sheet->setCellValue('A' . $row, $idea->id);
                $sheet->setCellValue('B' . $row, $idea->content);
                $sheet->setCellValue('C' . $row, $idea->department->name ?? '-');
                $sheet->setCellValue('D' . $row, $idea->academicYear->start_date . ' - ' . $idea->academicYear->end_date);
                $sheet->setCellValue('E' . $row, $idea->created_at->format('Y-m-d'));
                $row++;
            }

            $csvTempFilePath = tempnam(sys_get_temp_dir(), 'ideas') . '.csv';
            $writer          = new Csv($spreadsheet);
            $writer->save($csvTempFilePath);

            $downloads['csv'] = basename($csvTempFilePath);
        }

        if ($isZip) {
            $zipTempFilePath = tempnam(sys_get_temp_dir(), 'ideas_zip') . '.zip';
            $zip             = new ZipArchive();

            if ($zip->open($zipTempFilePath, ZipArchive::CREATE) === true) {
                foreach ($ideas as $idea) {
                    foreach ($idea->files as $file) {
                        if (! empty($file->file)) {
                            $parsedUrl = parse_url($file->file);
                            $localPath = public_path($parsedUrl['path']);
                            if (file_exists($localPath)) {
                                $filePath = $localPath;
                                $fileName = basename($file->file);
                                $zip->addFile($filePath, $idea->id . '_' . $fileName);
                            }
                        }
                    }
                }
                $zip->close();

                $downloads['zip'] = basename($zipTempFilePath);
            } else {
                return response()->json(['message' => 'Could not create ZIP file.'], 500);
            }
        }

        // If only one type (csv or zip)
        if (count($downloads) === 1) {
            $type     = array_key_first($downloads);
            $filename = $type === 'csv' ? 'idea_list_' . now()->format('Ymd_His') . '.csv' : 'idea_images_' . now()->format('Ymd_His') . '.zip';
            $fullPath = sys_get_temp_dir() . '/' . $downloads[$type];

            return response()->download($fullPath, $filename)->deleteFileAfterSend(true);
        }

        // If both csv and zip, return auto download page
        if (count($downloads) === 2) {
            $csvUrl = route('idea.download_temp', ['file' => $downloads['csv']]);
            $zipUrl = route('idea.download_temp', ['file' => $downloads['zip']]);

            return response()->view('idea.auto_download', [
                'csvUrl' => $csvUrl,
                'zipUrl' => $zipUrl,
            ]);
        }
    }

}
