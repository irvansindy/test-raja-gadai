@extends('layouts.app')

@section('title', 'My Notes')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>My Notes</h1>
                <a href="{{ route('notes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Note
                </a>
            </div>

            <!-- My Notes -->
            <div class="mb-5">
                <h3>My Notes ({{ $ownNotes->count() }})</h3>
                {{-- @dd($ownNotes) --}}
                @if($ownNotes->count() > 0)
                    <div class="row">
                        @foreach($ownNotes as $note)
                            <div class="col-md-4 mb-3">
                                <div class="card note-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $note->title }}</h5>
                                        <p class="card-text">{{ Str::limit($note->content, 100) }}</p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-warning">{{ ucfirst($note->pivot != null ? $note->pivot->permission : '-') }}</span>
                                                <small class="text-muted">by {{ $note->user->name }}</small>
                                            </div>
                                            
                                            <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <a href="{{ route('notes.show', $note) }}" class="btn btn-sm btn-primary w-100">View</a>
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
@endsection