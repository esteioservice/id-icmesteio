<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;

class DocumentsController extends Controller
{


    public function termos()
    {
      $hiddenNavBar = true;
      $hiddenButtons = true;
      return view('termos', compact('hiddenButtons', 'hiddenNavBar'));
    }

    public function politicas()
    {
      $hiddenNavBar = true;
      $hiddenButtons = true;
        return view('politicas', compact('hiddenButtons', 'hiddenNavBar'));
    }
}
