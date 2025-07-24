<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Product;
use App\Models\FileManager;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $info                     = new stdClass();
        $info->page_title         = __('admin.product');
        $info->title              = __('admin.Products');
        $info->first_button_title = __('admin.add_product');
        $info->first_button_route = 'admin.product.create';
        $info->route_index        = 'admin.product.index';
        $info->description        = __('admin.these_all_are_Products');

        $with_data = [];
        $per_page = request('per_page', 20);

        $data = Product::query();

        $data = $data->paginate($per_page);

        return view('admin.product.index', compact('data', 'info'));
    }

    public function create()
    {
        $info                          = new stdClass();
        $info->page_title              = __('admin.product');
        $info->title                   = __('admin.Products');
        $info->add_button_title        = __('admin.add_product');
        $info->add_button_route        = 'admin.product.create';
        $info->route_index             = 'admin.product.index';
        $info->form_submit_route_index = 'admin.product.store';
        $info->description             = __('create_Products');

        return view('admin.product.create', compact('info'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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

        $row = new Product();
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
                'storage/Uploads',
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
                'storage/Uploads',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            $row->cover = $file_response['filename'];
        }

        $row->save();

        return redirect()->route('admin.product.index')->with('success', 'Product created successfully.');
    }

    public function show($id)
    {
        $info                    = new stdClass();
        $info->page_title        = __('admin.product');
        $info->title             = __('admin.Products');
        $info->add_button_title  = __('admin.add_product');
        $info->add_button_route  = 'admin.product.create';
        $info->edit_button_title = __('admin.edit_product');
        $info->edit_button_route = 'admin.product.edit';
        $info->route_index       = 'admin.product.index';
        $info->description       = __('product_showcase');

        $data = Product::find($id);

        return view('admin.product.show', compact('data', 'info'));
    }

    public function edit(Request $request, $id)
    {
        $info = new stdClass();
        $info->page_title = __('admin.product');
        $info->title = __('admin.Products');
        $info->add_button_title = __('admin.add_product');
        $info->add_button_route = 'admin.product.create';
        $info->route_index = 'admin.product.index';
        $info->form_submit_index = 'admin.product.update';
        $info->description = __('edit_product');

        $data = Product::findOrFail($id);

        return view('admin.product.edit', compact('data', 'info'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
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
            return back()->with('warning', 'Not Found!');
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
                'storage/Uploads',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            FileManager::deleteFile($row->banner);
            $row->banner = $file_response['filename'];
        }

        if ($request->hasFile('cover')) {
            $file_response = FileManager::saveFile(
                $request->file('cover'),
                'storage/Uploads',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            FileManager::deleteFile($row->cover);
            $row->cover = $file_response['filename'];
        }

        $row->save();

        return redirect()->route('admin.product.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $row = Product::find($id);

        if ($row) {
            $row->delete();
        } else {
            return redirect()->back()->with('error', 'Product Not Found!');
        }

        return redirect()->route('admin.product.index')->with('success', 'Product deleted successfully.');
    }
}
