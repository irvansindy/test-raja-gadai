<!-- resources/views/notes/public.blade.php -->
@extends('layouts.app')

@section('title', $note->title . ' (Public)')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-info">
                <i class="fas fa-globe"></i> This is a public note shared by {{ $note->user->name }}
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $note->title }}</h4>
                    <span class="badge bg-success">Public</span>
                </div>
                
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">
                            Created by {{ $note->user->name }} on {{ $note->created_at->format('M d, Y') }}
                        </small>
                    </div>
                    
                    <div class="note-content">
                        {!! nl2br(e($note->content)) !!}
                    </div>
                </div>
            </div>
            
            <!-- Comments Section -->
            @if($note->comments->count() > 0 || $canComment)
                <div class="comment-section mt-4">
                    <h5>Comments ({{ $note->comments->count() }})</h5>
                    
                    <!-- Add Comment Form -->
                    @if($canComment)
                        <form action="{{ route('comments.store', $note) }}" method="POST" class="mb-4">
                            @csrf
                            <div class="mb-3">
                                <textarea class="form-control @error('comment') is-invalid @enderror" name="comment" rows="3" placeholder="Add a comment..." required>{{ old('comment') }}</textarea>
                                @error('comment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Add Comment</button>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <a href="{{ route('login') }}">Login</a> to add comments.
                        </div>
                    @endif
                    
                    <!-- Comments List -->
                    @foreach($note->comments as $comment)
                        <div class="card mb-2">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $comment->user->name }}</strong>
                                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <p class="mb-0 mt-1">{{ $comment->comment }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

<!-- resources/views/notes/index.blade.php (Dashboard/My Notes) -->
@extends('layouts.app')

@section('title', 'My Notes')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Notes</h2>
                <a href="{{ route('notes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Note
                </a>
            </div>

            <!-- My Notes -->
            <div class="mb-5">
                <h3>My Notes ({{ $myNotes->count() }})</h3>
                @if($myNotes->count() > 0)
                    <div class="row">
                        @foreach($myNotes as $note)
                            <div class="col-md-4 mb-3">
                                <div class="card note-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $note->title }}</h5>
                                        <p class="card-text">{{ Str::limit($note->content, 100) }}</p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                @if($note->is_public)
                                                    <span class="badge bg-success">Public</span>
                                                @else
                                                    <span class="badge bg-secondary">Private</span>
                                                @endif
                                                
                                                @if($note->sharedWith->count() > 0)
                                                    <span class="badge bg-info">Shared ({{ $note->sharedWith->count() }})</span>
                                                @endif
                                            </div>
                                            
                                            <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <div class="btn-group w-100">
                                            <a href="{{ route('notes.show', $note) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="{{ route('notes.edit', $note) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                            
                                            <form action="{{ route('notes.destroy', $note) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        You haven't created any notes yet. <a href="{{ route('notes.create') }}">Create your first note</a>
                    </div>
                @endif
            </div>

            <!-- Shared Notes -->
            <div>
                <h3>Shared with Me ({{ $sharedNotes->count() }})</h3>
                @if($sharedNotes->count() > 0)
                    <div class="row">
                        @foreach($sharedNotes as $note)
                            <div class="col-md-4 mb-3">
                                <div class="card note-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $note->title }}</h5>
                                        <p class="card-text">{{ Str::limit($note->content, 100) }}</p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-warning">Shared by {{ $note->user->name }}</span>
                                                
                                                @if($note->is_public)
                                                    <span class="badge bg-success">Public</span>
                                                @endif
                                            </div>
                                            
                                            <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <a href="{{ route('notes.share', $note) }}" class="btn btn-sm btn-outline-primary w-100">View</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        No notes have been shared with you yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.note-card {
    transition: transform 0.2s;
}

.note-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card-text {
    color: #6c757d;
    font-size: 0.9rem;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endsection