<?php

namespace Modules\Users\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class UserResource extends CustomResource
{

    public function data(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'blood_group' => $this->resource->blood_group,
            'marital_status' => $this->resource->marital_status,
            'address' => $this->resource->address,
            'date_of_marriage' => $this->resource->date_of_marriage,
            'husband_name' => $this->resource->husband_name,
            'emergency_number' => $this->resource->emergency_number,
            'auth_token' => $this->resource->auth_token,
        ];
    }
}