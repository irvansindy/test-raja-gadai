<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
class CommentController extends Controller
{
    public function store(Request $request, Note $note)
    {
        $request->validate([
            'comment' => 'required|max:1000'
        ]);
        
        // Check if user can comment on this note
        if (!$this->canComment($note)) {
            abort(403, 'Unauthorized to comment on this note.');
        }
        
        $note->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment
        ]);
        
        return back()->with('success', 'Comment added successfully!');
    }
    private function canComment(Note $note)
    {
        $user = Auth::user();
        
        if ($note->user_id === $user->id) {
            return true;
        }
        
        if ($note->is_public) {
            return true;
        }
        
        return $note->sharedWith()
            ->where('shared_with_user_id', $user->id)
            ->whereIn('permission', ['comment', 'edit'])
            ->exists();
    }
}
