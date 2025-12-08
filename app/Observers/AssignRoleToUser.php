<?php

namespace App\Observers;
use App\Model\User;
use Spatie\Permission\Models\Role;
class AssignRoleToUser
{
    public function saved(User $user)
    {  
          $user->assignRole(User::ROLE_ADMIN);
      
    }
}
