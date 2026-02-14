<?php

namespace Modules\Core\Traits;

trait ActiveTrait
{
    public function active()
    {
        return $this->where('active', true);
    }
}