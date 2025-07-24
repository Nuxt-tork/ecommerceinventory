<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Product;
use App\Models\FileManager;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /products
    public function index()
    {
        $info                     = new stdClass();
        $info->page_title         = __('admin.product');
        $info->title              = __('admin.products');
        $info->first_button_title = __('admin.add_product');
        $info->first_button_route = 'admin.products.create';
        $info->route_index        = 'admin.products.index';
        $info->description        = __('admin.these_all_are_products');


        $with_data = [];
        $per_page = request('per_page', 20);

        $data = Product::query();

        $data->paginate($per_page); // If JSON contains "pagination_type": "backend", else $data->get();

        return view('admin.products.index', compact('data', 'info'));
    }

    // GET /products/create
    public function create()
    {
        $info                          = new stdClass();
        $info->page_title              = __('admin.product');
        $info->title                   = __('admin.products');
        $info->add_button_title        = __('admin.add_product');
        $info->add_button_route        = 'admin.products.create';
        $info->route_index             = 'admin.products.index';
        $info->form_submit_route_index = 'admin.products.store';
        $info->description             = __('create_products');
        
        return view('admin.products.create', compact('info'));
    }

   public function store(Request $request)
    {
        // make * marked to required
        $validated = $request->validate([
            'banner'              => 'required|image|mimes:jpeg,png,jpg,gif',
            'cover'               => 'nullable|image|mimes:jpeg,png,jpg,gif',
            
            'title'               => 'required|string',
            'description'         => 'nullable|string',
            'price'               => 'required|numeric',
            'stock'               => 'nullable|integer',
            'sell_date'           => 'nullable|date',
            'is_active'           => 'required|boolean',
            'is_popular'          => 'nullable|boolean',
            'use_status'          => 'required',
            'status'              => 'required',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'invoice'             => 'nullable|string',
            'about'               => 'nullable|string',
        ]);

        $row                      = new Product();
        $row->cover               = $request->cover;
        $row->title               = $request->title;
        $row->description         = $request->description;
        $row->price               = $request->price;
        $row->stock               = $request->stock;
        $row->sell_date           = $request->sell_date;
        $row->is_active           = $request->is_active;
        $row->is_popular          = $request->is_popular;
        $row->use_status          = $request->use_status;
        $row->status              = $request->status;
        $row->product_category_id = $request->product_category_id;
        $row->invoice             = $request->invoice;
        $row->about               = $request->about;
        $row->save();

        if ($request->hasfile('banner')) {
            $file_response = FileManager::saveFile(
                $request->file('banner'),
                'storage/Product',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {

                return back()->with('warning', $file_response['message']);
            }

            $row->banner = $file_response['filename'];
        }

        $row->save();

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }


    // GET /products/{product}
    public function show($id)
    {
        $info                    = new stdClass();
        $info->page_title        = __('admin.product');
        $info->title             = __('admin.products');
        $info->add_button_title  = __('admin.add_product');
        $info->add_button_route  = 'admin.products.create';
        $info->edit_button_title = __('admin.edit_product');
        $info->edit_button_route = 'admin.products.edit';
        $info->route_index       = 'admin.products.index';
        $info->description       = __('product_showcase');

        $data = Product::find($info);

        return view('admin.products.show', compact('data', 'info'));
    }

    // GET /products/{product}/edit
    public function edit(Request $request, $id)
    {
        $info = new stdClass();
        $info->page_title = __('admin.product');
        $info->title = __('admin.products');
        $info->add_button_title = __('admin.add_product');
        $info->add_button_route = 'admin.products.create';
        $info->route_index = 'admin.products.index';
        $info->form_submit_index = 'admin.products.update';
        $info->description = __('edit_product');


        $data = Product::findOrFail($id);
       
        return view('admin.products.edit', compact('data', 'info'));
    }

    // PUT /products/{product}
    public function update(Request $request, $id)
    {
     
        $validated = $request->validate([
         
            'banner'              => 'required|image|mimes:jpeg,png,jpg,gif',
            'cover'               => 'nullable|image|mimes:jpeg,png,jpg,gif',
            
            'title'               => 'required|string',
            'description'         => 'nullable|string',
            'price'               => 'required|numeric',
            'stock'               => 'nullable|integer',
            'sell_date'           => 'nullable|date',
            'is_active'           => 'required|boolean',
            'is_popular'          => 'nullable|boolean',
            'use_status'          => 'required',
            'status'              => 'required',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'invoice'             => 'nullable|string',
            'about'               => 'nullable|string',
        ]);
        
        $row                      = Product::find($id);

        if (!$row) {
            return back()->with('warning', 'Not Found!');
        }

        $row->title               = $request->title;
        $row->description         = $request->description;
        $row->price               = $request->price;
        $row->stock               = $request->stock;
        $row->sell_date           = $request->sell_date;
        $row->is_active           = $request->is_active;
        $row->is_popular          = $request->is_popular;
        $row->use_status          = $request->use_status;
        $row->status              = $request->status;
        $row->product_category_id = $request->product_category_id;
        $row->invoice             = $request->invoice;
        $row->about               = $request->about;
        $row->save();

         if ($request->hasfile('banner')) {
            $file_response = FileManager::saveFile(
                $request->file('banner'),
                'storage/Product',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {

                return back()->with('warning', $file_response['message']);
            }

            $old_file = $row->banner;
            FileManager::deleteFile($old_file);
            $row->banner = $file_response['filename'];
        }

        $row->save();

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }


    // DELETE /products/{product}
    public function destroy($id)
    {
        $row = Product::find($id);

        if ($row){
            $row->delete();
        }else{
            return redirect()->back()->with('error', 'Product Not Found!');
        }
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}

