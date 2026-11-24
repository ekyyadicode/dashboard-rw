<?php

namespace App\Traits;

use App\Models\Scopes\RtScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToRt
{
    /**
     * Boot the BelongsToRt trait for a model.
     */
    public static function bootBelongsToRt(): void
    {
        static::addGlobalScope(new RtScope);

        static::creating(function (Model $model): void {
            if (Auth::hasUser()) {
                $user = Auth::user();

                if (empty($model->created_by)) {
                    $model->created_by = Auth::id();
                }

                if ($user->role !== 'super_admin' && empty($model->rt_id)) {
                    $model->rt_id = $user->rt_id;
                }
            }
        });
    }
}

