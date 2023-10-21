<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Hobby;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $disk_name;
    function __construct()
    {
        $this->disk_name = 'profile_pics';
    }

    public function index()
    {
        try {
            $searchTerm = '%' .  request('searchText') . '%';
            $categories = Category::pluck('title', 'id');
            $hobbies = Hobby::pluck('name', 'id');
            if (request()->ajax()) {
                $lists = User::with(['category', 'hobbies'])->latest()->get();
                $data = [
                    'html' => view('ajax', compact('lists'))->render(),
                ];
                return response()->json($data);
            }
            return view('index', compact('categories', 'hobbies'));
        } catch (\Exception $e) {
            info($e);
            abort(500);
        }
    }

    public function store(Request $request)
    {
        $id =  $request->id ?? NULL;
        $rules = [
            'name' => 'required|max:255',
            'contact_no' => 'required|unique:users,contact_no,' . $id . ',id|digits:10',
            // 'contact_no' => 'required|unique:users,contact_no,' . $id . ',id|regex:/^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]\d{3}[\s.-]\d{4}$/',
            'category_id' => 'required|exists:categories,id',
            'hobby' => 'required|array',
            'profile_pic' => 'required_without:id|image|mimes:jpeg,png,jpg|max:2048',
        ];
        $this->validate($request, $rules);
        try {
            DB::beginTransaction();
            $obj = User::firstOrNew(['id' => $id]);
            $obj->name = $request->name;
            $obj->contact_no = $request->contact_no;
            $obj->category_id = $request->category_id;
            $obj->profile_pic = $request->profile_pic ?  $this->file_upload($this->disk_name, $request->profile_pic, $obj->id ?? NULL, $obj->profile_pic ?? NULL) : $obj->profile_pic ?? NULL;

            if ($obj->save()) {
                $obj->hobbies()->sync($request->hobby);
                DB::commit();
                $msg = !$id ? 'user created successfully' : 'user updated successfully';
                return response()->json(['success' => true, 'message' => $msg], 200);
            }
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            info($e);
            abort(500);
        }
    }

    public function edit($id)
    {
        try {
            $result = User::with('hobbies')->findOrFail($id);
            return response()->json(['success' => true, 'data' => $result], 200);
        } catch (\Exception $e) {
            return $this->debugLog($e);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $result = User::findOrFail($id);
            if ($result) {
                Storage::disk('profile_pics')->delete($result->profile_pic);
                $result->delete();
                DB::commit();
            }
            return response()->json(['success' => true, 'message' => 'user deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            abort(500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids');
            $result = User::whereIn('id', $ids)->get();
            foreach ($result as $record) {
                Storage::disk('profile_pics')->delete($record->profile_pic);
                $record->delete();
            }
            return response()->json(['success' => true, 'message' => 'user deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            abort(500);
        }
    }

    function file_upload($disk_name, $newImage, $data_id = null, $oldImage = null)
    {
        if (Storage::disk($disk_name)->exists($newImage) || $newImage == null) {
            return $oldImage;
        } else {
            if ($data_id && $newImage && $oldImage) {
                Storage::disk($disk_name)->delete($oldImage);
            }
            if (!is_string($newImage)) {
                $path = Storage::disk($disk_name)->getConfig()['root'];
                if (!\File::isDirectory($path)) {
                    \File::makeDirectory($path, 0777, true);
                }
                $fileUpload = Storage::disk($disk_name)->put('/', $newImage);
                return $fileUpload;
            }
        }
    }
}
