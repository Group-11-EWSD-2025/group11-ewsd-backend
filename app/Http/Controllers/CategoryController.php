<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $paginate = $request->per_page ?? 10;
        $query = Category::query()->withCount('ideas');

        // Add any filters if needed
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->orderbydesc('id')->paginate($paginate);

        return apiResponse(true, 'Operation completed successfully', $categories, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $category = Category::create([
            'name' => $request->name,
        ]);

        return apiResponse(true, 'Operation completed successfully', $category, 200);
    }

    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);
            return apiResponse(true, 'Operation completed successfully', $category, 200);
        } catch (\Exception $e) {
            return apiResponse(false, 'Category not found', null, 404);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:categories,id',
            'name' => 'required|unique:categories,name,' . $request->id,
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        try {
            $category = Category::findOrFail($request->id);

            $category->update([
                'name' => $request->name,
            ]);

            return apiResponse(true, 'Operation completed successfully', $category, 200);
        } catch (\Exception $e) {
            return apiResponse(false, 'Failed to update category', null, 500);
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        try {
            $category = Category::findOrFail($request->id);
            if($category->ideas()->count() > 0) {
                return apiResponse(false, 'Category cannot be deleted because it has associated ideas', null, 400);
            }
            $category->delete();

            return apiResponse(true, 'Operation completed successfully', null, 200);
        } catch (\Exception $e) {
            return apiResponse(false, 'Failed to delete category', null, 500);
        }
    }
}