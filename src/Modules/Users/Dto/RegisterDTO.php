<?php

namespace Modules\Users\Dto;

readonly class RegisterDTO
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly string $bloodGroup,
        public readonly string $maritalStatus,
        public readonly string|null $dateOfMarriage,
        public readonly string|null $husbandName,
        public readonly string $phone,
        public readonly string|null $emergencyNumber,
        public readonly string|null $address,
        public readonly string $email,
        public readonly string $password,
    )
    {
    }

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->input('name'),
            age: $request->input('age'),
            bloodGroup: $request->input('blood_group'),
            maritalStatus: $request->input('marital_status'),
            dateOfMarriage: $request->input('date_of_marriage'),
            husbandName: $request->input('husband_name'),
            phone: $request->input('phone'),
            emergencyNumber: $request->input('emergency_number'),
            address: $request->input('address'),
            email: $request->input('email'),
            password: $request->input('password'),
        );
    }
}