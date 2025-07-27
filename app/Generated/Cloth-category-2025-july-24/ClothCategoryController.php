<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\ClothCategory;
use App\Models\FileManager;
use Illuminate\Http\Request;

class ClothCategoryController extends Controller
{
    public function index()
    {
        $info                     = new stdClass();
        $info->page_title         = __('admin.cloth_category');
        $info->title              = __('admin.ClothCategories');
        $info->first_button_title = __('admin.add_cloth_category');
        $info->first_button_route = 'admin.cloth-category.create';
        $info->route_index        = 'admin.cloth-category.index';
        $info->description        = __('admin.these_all_are_ClothCategories');

        $with_data = [];
        $per_page = request('per_page', 20);

        $data = ClothCategory::query();

        $data = $data->paginate($per_page);

        return view('admin.cloth-category.index', compact('data', 'info'));
    }

    public function create()
    {
        $info                          = new stdClass();
        $info->page_title              = __('admin.cloth_category');
        $info->title                   = __('admin.ClothCategories');
        $info->add_button_title        = __('admin.add_cloth_category');
        $info->add_button_route        = 'admin.cloth-category.create';
        $info->route_index             = 'admin.cloth-category.index';
        $info->form_submit_route_index = 'admin.cloth-category.store';
        $info->description             = __('create_ClothCategories');

        return view('admin.cloth-category.create', compact('info'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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

        $row = new ClothCategory();
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
                'storage/Uploads',
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
                'storage/Uploads',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            $row->thumbnail$ = $file_response['filename'];
        }

        $row->save();

        return redirect()->route('admin.cloth-category.index')->with('success', 'ClothCategory created successfully.');
    }

    public function show($id)
    {
        $info                    = new stdClass();
        $info->page_title        = __('admin.cloth_category');
        $info->title             = __('admin.ClothCategories');
        $info->add_button_title  = __('admin.add_cloth_category');
        $info->add_button_route  = 'admin.cloth-category.create';
        $info->edit_button_title = __('admin.edit_cloth_category');
        $info->edit_button_route = 'admin.cloth-category.edit';
        $info->route_index       = 'admin.cloth-category.index';
        $info->description       = __('cloth_category_showcase');

        $data = ClothCategory::find($id);

        return view('admin.cloth-category.show', compact('data', 'info'));
    }

    public function edit(Request $request, $id)
    {
        $info = new stdClass();
        $info->page_title = __('admin.cloth_category');
        $info->title = __('admin.ClothCategories');
        $info->add_button_title = __('admin.add_cloth_category');
        $info->add_button_route = 'admin.cloth-category.create';
        $info->route_index = 'admin.cloth-category.index';
        $info->form_submit_index = 'admin.cloth-category.update';
        $info->description = __('edit_cloth_category');

        $data = ClothCategory::findOrFail($id);

        return view('admin.cloth-category.edit', compact('data', 'info'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
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
            return back()->with('warning', 'Not Found!');
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
                'storage/Uploads',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            FileManager::deleteFile($row->banner);
            $row->banner = $file_response['filename'];
        }

        if ($request->hasFile('thumbnail$')) {
            $file_response = FileManager::saveFile(
                $request->file('thumbnail$'),
                'storage/Uploads',
                ['png', 'jpg', 'jpeg', 'gif']
            );

            if (isset($file_response['result']) && !$file_response['result']) {
                return back()->with('warning', $file_response['message']);
            };
            FileManager::deleteFile($row->thumbnail$);
            $row->thumbnail$ = $file_response['filename'];
        }

        $row->save();

        return redirect()->route('admin.cloth-category.index')->with('success', 'ClothCategory updated successfully.');
    }

    public function destroy($id)
    {
        $row = ClothCategory::find($id);

        if ($row) {
            $row->delete();
        } else {
            return redirect()->back()->with('error', 'ClothCategory Not Found!');
        }

        return redirect()->route('admin.cloth-category.index')->with('success', 'ClothCategory deleted successfully.');
    }
}
