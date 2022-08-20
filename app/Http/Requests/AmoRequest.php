<?php

namespace App\Http\Requests;

use AmoCRM\Client\AmoCRMApiClient;
use Illuminate\Http\Request;

class AmoRequest extends Request
{
    public ?AmoCRMApiClient $amoApiClient;
}
