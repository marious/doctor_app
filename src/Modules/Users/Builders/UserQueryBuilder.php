<?php

namespace Modules\Users\Builders;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Traits\ActiveTrait;

class UserQueryBuilder extends Builder
{
    use ActiveTrait;
}