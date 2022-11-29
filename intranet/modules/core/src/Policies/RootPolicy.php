<?php

namespace Rikkei\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class RootPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
}
