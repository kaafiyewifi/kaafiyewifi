<?php
// app/Enums/RouterStatus.php
namespace App\Enums;

enum RouterStatus: string
{
    case Pending = 'pending';
    case Provisioning = 'provisioning';
    case Connected = 'connected';
    case Offline = 'offline';
    case Error = 'error';
    case Disabled = 'disabled';
}
