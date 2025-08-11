<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteShare extends Model
{
    use HasFactory;
    protected $table = 'note_shares';
    protected $guarded = [];
}
