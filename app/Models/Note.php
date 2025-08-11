<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
    protected $table = 'notes';
    protected $fillable = ['title', 'content', 'user_id', 'is_public'];
    protected $casts = [
        'is_public' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function sharedWith()
    {
        return $this->belongsToMany(User::class, 'note_shares', 'note_id', 'shared_with_user_id')->withPivot('permission')->withTimestamps();
    }
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }
    public function getPublicUrlAttribute()
    {
        return route('public.notes.show', $this);
    }
    
    public function isOwnedBy($user)
    {
        return $this->user_id === $user->id;
    }
    
    public function isSharedWith($user)
    {
        return $this->sharedWith()->where('shared_with_user_id', $user->id)->exists();
    }
    
    public function getPermissionFor($user)
    {
        if ($this->isOwnedBy($user)) {
            return 'owner';
        }
        
        $share = $this->sharedWith()->where('shared_with_user_id', $user->id)->first();
        
        return $share ? $share->pivot->permission : null;
    }
}
