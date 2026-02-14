<?php

namespace Modules\Core\Enums;

enum OtpEnum: string
{
    case REGISTER = 'register';
    case FORGET = 'forget';
    case CHANGE_PHONE = 'change_phone';
}
