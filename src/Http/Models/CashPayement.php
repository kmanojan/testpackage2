<?php

namespace Apptimus\Transaction\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class CashPayement extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'acc_cash_payments';
    protected $keyType = 'string';
    public $incrementing = false;

    public static function boot()
    {
        parent::boot();
        static::creating(function ($type) {
            $type->id = Str::uuid()->toString();

            if (Auth::check()) {
                $type->added_by_id = Auth::user()->id;
                $type->updated_by_id = Auth::user()->id;
                $type->department_id = Auth::user()->department_id;
            }
        });

        static::updating(function ($type) {
            if (Auth::check()) {
                $type->updated_by_id = Auth::user()->id;
            }
        });

        static::deleting(function ($type) {
            if (Auth::check()) {
                $type->deleted_by_id = Auth::user()->id;
                $type->save();
            }
        });
    }
}
