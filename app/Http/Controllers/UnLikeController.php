<?php
namespace App\Http\Controllers;

use App\Models\UnLike;
use App\Models\Like;
use Illuminate\Http\Request;

class UnLikeController extends Controller
{
    public function unlike(Request $request)
    {
        $request->validate([
            'idea_id' => 'required|exists:ideas,id',
        ]);
        $checkLike = Like::where('idea_id', $request->idea_id)
            ->where('user_id', auth()->user()->id)
            ->first();
        if ($checkLike) {
            $checkLike->delete();
        }
        $user = auth()->user();
        $like = UnLike::where('idea_id', $request->idea_id)
            ->where('user_id', $user->id)
            ->first();
        if ($like) {
            return apiResponse(true, 'Operation completed successfully', $like, 200);
        }
        $like = UnLike::create([
            'idea_id' => $request->idea_id,
            'user_id' => $user->id,
        ]);
        return apiResponse(true, 'Operation completed successfully', $like, 200);
    }

    public function removeUnLike(Request $request)
    {
        $request->validate([
            'idea_id' => 'required|exists:ideas,id',
        ]);
        $user = auth()->user();
        $like = UnLike::where('idea_id', $request->idea_id)
            ->where('user_id', $user->id)
            ->first();
        if ($like) {
            $like->delete();
            return apiResponse(true, 'Operation completed successfully', null, 200);
        }
        return apiResponse(false, 'Like not found', null, 404);
    }
}
