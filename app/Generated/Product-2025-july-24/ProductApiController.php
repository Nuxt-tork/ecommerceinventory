<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\FileManager;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function index()
    {
        $items = Product::all();
        return apiResponse(true, 'Fetched successfully', $items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'color' => 'nullable|string',
            'sell_date' => 'nullable|date',
            'sell_time' => 'nullable|string',
            'sell_year' => 'nullable|string',
            'is_active' => 'required|boolean',
            'is_popular' => 'nullable|boolean',
            'invoice' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'about' => 'nullable|string',
            'tags' => 'nullable|string',
            'use_status' => 'nullable',
            'status' => 'nullable',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'product_id' => 'nullable|exists:product_images,id'
        ]);

        $row = new Product;
        $row->title = $request->title;
        $row->description = $request->description;
        $row->price = $request->price;
        $row->stock = $request->stock;
        $row->discount = $request->discount;
        $row->color = $request->color;
        $row->sell_date = $request->sell_date;
        $row->sell_time = $request->sell_time;
        $row->sell_year = $request->sell_year;
        $row->is_active = $request->is_active;
        $row->is_popular = $request->is_popular;
        $row->use_status = $request->use_status;
        $row->status = $request->status;
        $row->about = $request->about;
        $row->tags = $request->tags;
        if ($request->hasFile('banner')) {
            $file_response = FileManager::saveFile(
                $request->file('banner'),
                'storage/Product',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            $row->banner = $file_response['filename'];
        }

        if ($request->hasFile('cover')) {
            $file_response = FileManager::saveFile(
                $request->file('cover'),
                'storage/Product',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            $row->cover = $file_response['filename'];
        }
        $row->save();

        return apiResponse(true, 'Created successfully', $row);
    }

    public function show($id)
    {
        $item = Product::find($id);
        if (!$item) {
            return apiResponse(false, 'Not found', null, 404);
        }

        return apiResponse(true, 'Fetched successfully', $item);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'color' => 'nullable|string',
            'sell_date' => 'nullable|date',
            'sell_time' => 'nullable|string',
            'sell_year' => 'nullable|string',
            'is_active' => 'required|boolean',
            'is_popular' => 'nullable|boolean',
            'invoice' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'about' => 'nullable|string',
            'tags' => 'nullable|string',
            'use_status' => 'nullable',
            'status' => 'nullable',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'product_id' => 'nullable|exists:product_images,id'
        ]);

        $row = Product::find($id);
        if (!$row) {
            return apiResponse(false, 'Not found', null, 404);
        }

        $row->title = $request->title;
        $row->description = $request->description;
        $row->price = $request->price;
        $row->stock = $request->stock;
        $row->discount = $request->discount;
        $row->color = $request->color;
        $row->sell_date = $request->sell_date;
        $row->sell_time = $request->sell_time;
        $row->sell_year = $request->sell_year;
        $row->is_active = $request->is_active;
        $row->is_popular = $request->is_popular;
        $row->use_status = $request->use_status;
        $row->status = $request->status;
        $row->about = $request->about;
        $row->tags = $request->tags;
        if ($request->hasFile('banner')) {
            $file_response = FileManager::saveFile(
                $request->file('banner'),
                'storage/Product',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            $row->banner = $file_response['filename'];
        }

        if ($request->hasFile('cover')) {
            $file_response = FileManager::saveFile(
                $request->file('cover'),
                'storage/Product',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            $row->cover = $file_response['filename'];
        }
        $row->save();

        return apiResponse(true, 'Updated successfully', $row);
    }

    public function destroy($id)
    {
        $item = Product::find($id);
        if (!$item) {
            return apiResponse(false, 'Not found', null, 404);
        }

        $item->delete();
        return apiResponse(true, 'Deleted successfully');
    }
}
