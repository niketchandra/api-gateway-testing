<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Sanctum\PersonalAccessToken;

class PatToken extends PersonalAccessToken
{
	protected static function booted(): void
	{
		static::creating(function (PatToken $token): void {
			if ($token->tokenable_type === User::class) {
				$token->user_id = $token->tokenable_id;
			}
		});
	}

	public function tokenable(): MorphTo
	{
		return $this->morphTo();
	}
}
