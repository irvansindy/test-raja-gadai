<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
class PublicController extends Controller
{
    public function show(Note $note)
    {
        if (!$note->is_public) {
            abort(404, 'Note not found.');
        }
        
        $note->load(['user', 'comments.user']);
        $canComment = auth()->check(); // Only logged in users can comment
        
        return view('notes.public', compact('note', 'canComment'));
    }
}
