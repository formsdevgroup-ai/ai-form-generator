<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedForm extends Model
{
    // Add this line to allow these fields to be saved
    protected $fillable = ['form_name', 'version', 'shortcode', 'generated_code', 'user_id', 'session_id'];
}
