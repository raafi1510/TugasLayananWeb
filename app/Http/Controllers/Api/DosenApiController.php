<?php

namespace App\Http\Controllers\Api;
use App\Models\Dosen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DosenApiController extends Controller
{
    public function index()
    {
        $dosen = Dosen::with(['user', 'prodi'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List data dosen',
            'data' => $dosen
        ]);
    }

}