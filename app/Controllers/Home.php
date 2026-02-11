<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home/index');
    }

    public function website_hub(): string
    {
        return view('home/website_hub');
    }
}
