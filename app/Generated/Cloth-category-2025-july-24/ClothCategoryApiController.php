<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClothCategory;
use App\Models\FileManager;
use Illuminate\Http\Request;

class ClothCategoryApiController extends Controller
{
    public function index()
    {
        $items = ClothCategory::all();
        return apiResponse(true, 'Fetched successfully', $items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'thumbnail$' => 'required|image|mimes:jpeg,png,jpg,gif',
            'title$' => 'required|string',
            'slug' => 'required|string',
            'description' => 'nullable|string',
            'stock$' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'color$' => 'nullable|string',
            'create_date$' => 'required|date',
            'sell_time' => 'nullable|string',
            'sell_year' => 'nullable|string',
            'is_active$' => 'required|boolean',
            'is_popular' => 'nullable|boolean',
            'invoice' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'about' => 'nullable|string',
            'tags' => 'nullable|string',
            'Gender$#*' => 'required',
            'status$' => 'nullable'
        ]);

        $row = new ClothCategory;
        $row->title$ = $request->title$;
        $row->slug = $request->slug;
        $row->description = $request->description;
        $row->stock$ = $request->stock$;
        $row->discount = $request->discount;
        $row->color$ = $request->color$;
        $row->create_date$ = $request->create_date$;
        $row->sell_time = $request->sell_time;
        $row->sell_year = $request->sell_year;
        $row->is_active$ = $request->is_active$;
        $row->is_popular = $request->is_popular;
        $row->Gender$#* = $request->Gender$#*;
        $row->status$ = $request->status$;
        $row->about = $request->about;
        $row->tags = $request->tags;
        if ($request->hasFile('banner')) {
            $file_response = FileManager::saveFile(
                $request->file('banner'),
                'storage/ClothCategory',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            $row->banner = $file_response['filename'];
        }

        if ($request->hasFile('thumbnail$')) {
            $file_response = FileManager::saveFile(
                $request->file('thumbnail$'),
                'storage/ClothCategory',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            $row->thumbnail$ = $file_response['filename'];
        }
        $row->save();

        return apiResponse(true, 'Created successfully', $row);
    }

    public function show($id)
    {
        $item = ClothCategory::find($id);
        if (!$item) {
            return apiResponse(false, 'Not found', null, 404);
        }

        return apiResponse(true, 'Fetched successfully', $item);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'thumbnail$' => 'required|image|mimes:jpeg,png,jpg,gif',
            'title$' => 'required|string',
            'slug' => 'required|string',
            'description' => 'nullable|string',
            'stock$' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'color$' => 'nullable|string',
            'create_date$' => 'required|date',
            'sell_time' => 'nullable|string',
            'sell_year' => 'nullable|string',
            'is_active$' => 'required|boolean',
            'is_popular' => 'nullable|boolean',
            'invoice' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'about' => 'nullable|string',
            'tags' => 'nullable|string',
            'Gender$#*' => 'required',
            'status$' => 'nullable'
        ]);

        $row = ClothCategory::find($id);
        if (!$row) {
            return apiResponse(false, 'Not found', null, 404);
        }

        $row->title$ = $request->title$;
        $row->slug = $request->slug;
        $row->description = $request->description;
        $row->stock$ = $request->stock$;
        $row->discount = $request->discount;
        $row->color$ = $request->color$;
        $row->create_date$ = $request->create_date$;
        $row->sell_time = $request->sell_time;
        $row->sell_year = $request->sell_year;
        $row->is_active$ = $request->is_active$;
        $row->is_popular = $request->is_popular;
        $row->Gender$#* = $request->Gender$#*;
        $row->status$ = $request->status$;
        $row->about = $request->about;
        $row->tags = $request->tags;
        if ($request->hasFile('banner')) {
            $file_response = FileManager::saveFile(
                $request->file('banner'),
                'storage/ClothCategory',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            $row->banner = $file_response['filename'];
        }

        if ($request->hasFile('thumbnail$')) {
            $file_response = FileManager::saveFile(
                $request->file('thumbnail$'),
                'storage/ClothCategory',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            $row->thumbnail$ = $file_response['filename'];
        }
        $row->save();

        return apiResponse(true, 'Updated successfully', $row);
    }

    public function destroy($id)
    {
        $item = ClothCategory::find($id);
        if (!$item) {
            return apiResponse(false, 'Not found', null, 404);
        }

        $item->delete();
        return apiResponse(true, 'Deleted successfully');
    }
}
