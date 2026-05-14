<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->fill($data);

        if ($user->isDirty()) {
            $user->save();
        }

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
