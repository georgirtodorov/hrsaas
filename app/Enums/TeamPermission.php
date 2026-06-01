<?php

namespace App\Enums;

enum TeamPermission: string
{
    case UpdateTeam = 'team:update';
    case DeleteTeam = 'team:delete';

    case AddMember = 'member:add';
    case UpdateMember = 'member:update';
    case RemoveMember = 'member:remove';

    case CreateInvitation = 'invitation:create';
    case CancelInvitation = 'invitation:cancel';

    case CreateEmployee = 'employee:create';
    case UpdateEmployee = 'employee:update';
    case DeleteEmployee = 'employee:delete';

    case CreateDepartment = 'department:create';
    case UpdateDepartment = 'department:update';
    case DeleteDepartment = 'department:delete';
}
