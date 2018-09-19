<?php

namespace App\Orm;

use Nextras\Orm\Entity\Entity;


/**
 * @property string                 $id             {primary}
 * @property User                   $user           {m:1 User::$role}
 * @property string                 $role
 */
class UserRole extends Entity
{
}
