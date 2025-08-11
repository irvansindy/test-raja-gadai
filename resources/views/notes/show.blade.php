<!-- resources/views/notes/show.blade.php -->
@extends('layouts.app')

@section('title', $note->title)

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>{{ $note->title }}</h4>

                        <div>
                            @if ($note->is_public)
                                <span class="badge bg-success">Public</span>
                            @else
                                <span class="badge bg-secondary">Private</span>
                            @endif

                            @if ($note->isOwnedBy(auth()->user()))
                                <span class="badge bg-primary">Owner</span>
                            @else
                                <span class="badge bg-warning">{{ ucfirst($note->getPermissionFor(auth()->user())) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">
                                Created by {{ $note->user->name }} on {{ $note->created_at->format('M d, Y') }}
                                @if ($note->updated_at != $note->created_at)
                                    · Updated {{ $note->updated_at->diffForHumans() }}
                                @endif
                            </small>
                        </div>

                        <div class="note-content">
                            {!! nl2br(e($note->content)) !!}
                        </div>
                    </div>

                    @if ($note->isOwnedBy(auth()->user()))
                        <div class="card-footer">
                            <div class="btn-group">
                                <a href="{{ route('notes.edit', $note) }}" class="btn btn-warning mr-2">Edit</a>

                                <form action="{{ route('notes.destroy', $note) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>

                            <a href="{{ route('dashboard') }}" class="btn btn-secondary float-end">Back to Notes</a>
                        </div>
                    @endif
                </div>

                <!-- Comments Section -->
                @if ($note->comments->count() > 0 || $canComment)
                    <div class="comment-section mt-4">
                        <h5>Comments ({{ $note->comments->count() }})</h5>

                        <!-- Add Comment Form -->
                        @if ($canComment)
                            <form action="{{ route('comments.store', $note) }}" method="POST" class="mb-4">
                                @csrf
                                <div class="mb-3">
                                    <textarea class="form-control @error('comment') is-invalid @enderror" name="comment" rows="3"
                                        placeholder="Add a comment..." required>{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">Add Comment</button>
                            </form>
                        @endif

                        <!-- Comments List -->
                        @foreach ($note->comments as $comment)
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

            <!-- Sidebar -->
            <div class="col-md-4">
                @if ($note->isOwnedBy(auth()->user()))
                    <!-- Share Note -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>Share Note</h5>
                        </div>
                        <div class="card-body">
                            <!-- Share to User -->
                            <form action="{{ route('notes.share', $note) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="mb-2">
                                    <label for="user_email" class="form-label">User Email</label>
                                    <input type="email" class="form-control @error('user_email') is-invalid @enderror"
                                        id="user_email" name="user_email" required>
                                    @error('user_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-2">
                                    <label for="permission" class="form-label">Permission</label>
                                    <select class="form-select" id="permission" name="permission" required>
                                        <option value="view">View Only</option>
                                        <option value="comment">View & Comment</option>
                                        <option value="edit">View, Comment & Edit</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm w-100">Share</button>
                            </form>

                            <!-- Toggle Public -->
                            <form action="{{ route('notes.toggle-public', $note) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="btn btn-{{ $note->is_public ? 'warning' : 'success' }} btn-sm w-100">
                                    {{ $note->is_public ? 'Make Private' : 'Make Public' }}
                                </button>
                            </form>
                            @if ($note->is_public)
                                <div class="mt-2">
                                    <label class="form-label">Public URL:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $note->publicUrl }}" readonly>
                                        <button class="btn btn-outline-secondary btn-sm" type="button"
                                            onclick="copyToClipboard('{{ $note->publicUrl }}')">Copy</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Shared With -->
                    @if ($note->sharedWith->count() > 0)
                        <div class="card">
                            <div class="card-header">
                                <h5>Shared With</h5>
                            </div>
                            <div class="card-body">
                                @foreach ($note->sharedWith as $user)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong>{{ $user->name }}</strong><br>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                        <span class="badge bg-info">{{ ucfirst($user->pivot->permission) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
@push('js')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('URL copied to clipboard!');
        });
    }
</script>
@endpush