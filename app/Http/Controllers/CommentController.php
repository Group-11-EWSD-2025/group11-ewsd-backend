<?php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Idea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;


class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idea_id' => 'required|exists:ideas,id',
            'content' => 'required',
            'privacy' => 'required',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $user    = auth()->user();
        $comment = Comment::create([
            'idea_id' => $request->idea_id,
            'user_id' => $user->id,
            'content' => $request->content,
            'privacy' => $request->privacy,
        ]);

        if ($comment) {
            $idea = Idea::find($request->idea_id);

            if ($idea && $idea->user) {
                $ideaOwner   = $idea->user;
                $email       = $ideaOwner->email;
                $subject     = 'New Comment on Your Idea';
                $bodyMessage = 'Hello ' . $ideaOwner->name . ",\n\n" .
                'Your idea titled "' . $idea->title . '" has received a new comment. Please check it out.';

                Mail::raw($bodyMessage, function ($message) use ($email, $subject) {
                    $message->to($email)
                        ->subject($subject);
                });
            }
        }
        return apiResponse(true, 'Operation completed successfully', $comment, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
