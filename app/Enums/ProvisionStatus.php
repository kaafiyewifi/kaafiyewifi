<?php
// app/Enums/ProvisionStatus.php
namespace App\Enums;

enum ProvisionStatus: string
{
    case Generated = 'generated';
    case Ran = 'ran';
    case Success = 'success';
    case Failed = 'failed';
    case Expired = 'expired';
}
