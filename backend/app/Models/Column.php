<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Column extends Model {
    protected $fillable = ['board_id','name','position'];
    public function board() { return $this->belongsTo(Board::class); }
    public function tasks() { return $this->hasMany(Task::class)->orderBy('position'); }
}

