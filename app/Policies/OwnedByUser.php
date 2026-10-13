<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

/**
 * Every record belongs to exactly one advisor. Anyone else gets a 404, so record IDs can't be probed.
 */
abstract class OwnedByUser
{
    protected function owns(User $user, Model $model): Response
    {
        return (int) $model->getAttribute('user_id') === (int) $user->id ? Response::allow() : Response::denyAsNotFound();
    }

    public function view(User $user, Model $model): Response
    {
        return $this->owns($user, $model);
    }

    public function update(User $user, Model $model): Response
    {
        return $this->owns($user, $model);
    }

    public function delete(User $user, Model $model): Response
    {
        return $this->owns($user, $model);
    }
}
