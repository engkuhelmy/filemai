<?php

namespace App\Controllers;

use App\Router;

class PageController
{
    public function terms(): void
    {
        Router::render('pages/terms', [
            'title' => 'Terma Penggunaan - Filemai.my'
        ]);
    }

    public function privacy(): void
    {
        Router::render('pages/privacy', [
            'title' => 'Polisi Privasi - Filemai.my'
        ]);
    }

    public function contact(): void
    {
        $submitted = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submitted = true;
        }

        Router::render('pages/contact', [
            'title' => 'Hubungi Kami - Filemai.my',
            'submitted' => $submitted
        ]);
    }

    public function faq(): void
    {
        Router::render('pages/faq', [
            'title' => 'Soalan Lazim (FAQ) - Filemai.my'
        ]);
    }
}
