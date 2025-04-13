<?php
namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\UnLike;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function like(Request $request)
    {
        $request->validate([
            'idea_id' => 'required|exists:ideas,id',
        ]);
        $user = auth()->user();
        $like = Like::where('idea_id', $request->idea_id)
            ->where('user_id', $user->id)
            ->first();
        $checkunLike = UnLike::where('idea_id', $request->idea_id)
            ->where('user_id', auth()->user()->id)
            ->first();
        if ($checkunLike) {
            $checkunLike->delete();
        }
        if ($like) {
            return apiResponse(true, 'Operation completed successfully', $like, 200);
        }
        $like = Like::create([
            'idea_id' => $request->idea_id,
            'user_id' => $user->id,
        ]);
        return apiResponse(true, 'Operation completed successfully', $like, 200);
    }

    public function removeLike(Request $request)
    {
        $request->validate([
            'idea_id' => 'required|exists:ideas,id',
        ]);
        $user = auth()->user();
        $like = Like::where('idea_id', $request->idea_id)
            ->where('user_id', $user->id)
            ->first();
        if ($like) {
            $like->delete();
            return apiResponse(true, 'Operation completed successfully', null, 200);
        }
        return apiResponse(false, 'Like not found', null, 404);
    }
}
