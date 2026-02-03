<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasUuidPrimaryKey;

    protected $keyType = 'string';
    public $incrementing = false;
}
