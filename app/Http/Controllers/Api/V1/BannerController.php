<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Admin\Banner;
use App\Models\Admin\BannerAds;
use App\Models\Admin\BannerText;
use App\Traits\HttpResponses;

class BannerController extends Controller
{
    use HttpResponses;

    public function __construct()
    {
        $this->middleware('checkBanned');
    }
    
    public function index()
    {
        $data = Banner::all();

        return $this->success($data);
    }

    public function bannerText()
    {
        $data = BannerText::latest()->first();

        return $this->success($data);
    }

    public function AdsBannerIndex()
    {
        $data = BannerAds::latest()->first();

        return $this->success($data);
    }
}
