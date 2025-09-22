<?php

namespace App\Http\Controllers;

use App\Models\TGFile;
use Illuminate\Http\Request;

class TGFileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TGFile::orderByRaw('message_date IS NULL, message_date DESC')->orderBy('date', 'desc')->get(['file_unique_id', 'file_name', 'file_size', 'date', 'message_date']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TGFile $tGFile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TGFile $tGFile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TGFile $tGFile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TGFile $tGFile)
    {
        //
    }
}
