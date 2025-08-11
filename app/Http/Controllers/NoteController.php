<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class NoteController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get user's own notes and shared notes
        $ownNotes = $user->notes()->latest()->get();
        $sharedNotes = $user->sharedNotes()->with('user')->latest()->get();
        // dd($sharedNotes);
        return view('notes.index', compact('ownNotes', 'sharedNotes'));
    }
    public function create()
    {
        return view('notes.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required'
        ]);
        
        Auth::user()->notes()->create($request->only(['title', 'content']));
        
        return redirect()->route('notes.index')->with('success', 'Note created successfully!');
    }
    public function show(Note $note)
    {
        // Check if user can view this note
        if (!$this->canViewNote($note)) {
            abort(403, 'Unauthorized access to this note.');
        }
        
        $note->load(['user', 'comments.user']);
        $canComment = $this->canComment($note);
        
        return view('notes.show', compact('note', 'canComment'));
    }
    public function edit(Note $note)
    {
        if (!$this->canEditNote($note)) {
            abort(403, 'Unauthorized to edit this note.');
        }
        
        return view('notes.edit', compact('note'));
    }
    
    public function update(Request $request, Note $note)
    {
        if (!$this->canEditNote($note)) {
            abort(403, 'Unauthorized to edit this note.');
        }
        
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required'
        ]);
        
        $note->update($request->only(['title', 'content']));
        
        return redirect()->route('notes.show', $note)->with('success', 'Note updated successfully!');
    }
    
    public function destroy(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to delete this note.');
        }
        
        $note->delete();
        
        return redirect()->route('notes.index')->with('success', 'Note deleted successfully!');
    }
    
    public function shareToUser(Request $request, Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to share this note.');
        }
        
        $request->validate([
            'user_email' => 'required|email|exists:users,email',
            'permission' => 'required|in:view,comment,edit'
        ]);
        
        $user = User::where('email', $request->user_email)->first();
        
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user_email' => 'Cannot share note with yourself.']);
        }
        
        $note->sharedWith()->syncWithoutDetaching([
            $user->id => ['permission' => $request->permission]
        ]);
        
        return back()->with('success', 'Note shared successfully!');
    }
    
    public function togglePublic(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to modify this note.');
        }
        
        $note->update(['is_public' => !$note->is_public]);
        
        $message = $note->is_public ? 'Note is now public!' : 'Note is now private!';
        
        return back()->with('success', $message);
    }
    
    private function canViewNote(Note $note)
    {
        $user = Auth::user();
        
        return $note->user_id === $user->id || $note->is_public || $note->sharedWith()->where('shared_with_user_id', $user->id)->exists();
    }
    
    private function canEditNote(Note $note)
    {
        $user = Auth::user();
        
        if ($note->user_id === $user->id) {
            return true;
        }
        
        return $note->sharedWith()
            ->where('shared_with_user_id', $user->id)
            ->where('permission', 'edit')
            ->exists();
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
