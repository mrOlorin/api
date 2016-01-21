<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use Validator;

/**
 * Categories controller
 * 
 * @Resource("/categories")
 */
class CategoriesController extends Controller
{

    /**
     * Category validator
     * 
     * @param array $data
     * @return \Illuminate\Validation\Validator
     */
    protected function categoryValidator(array $data)
    {
        return Validator::make(
            $data, 
            ['name' => 'required|max:255|unique:categories',],
            [
                'required' => ':attribute is required',
                'max' => ':attribute too long',
                'unique' => ':attribute already exists',
            ]
        );
    }

    /**
     * Categories list
     * 
     * @Get("/categories/{sort_order}")
     * @Where({"sort_order": "(asc|desc)"})
     */
    public function index($sort_order = 'desc')
    {
        $products = DB::table('categories')->orderBy('name', $sort_order)->get();
        return response()->json(['success' => $products,], 200);
    }

    /**
     * Create a category
     * 
     * @Middleware("jwt.auth")
     * @Post("/categories")
     */
    public function store(Request $request)
    {
        $newCategory = $request->only('owner_id', 'name');
        $validator = $this->categoryValidator($newCategory);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages(),], 400);
        }
        $newCategory['owner_id'] = Auth::user()->id;
        DB::insert(
            'insert into categories (owner_id, name) values (?, ?)',
            [$newCategory['owner_id'], $newCategory['name']]
        );
        // TODO: Provide id
        return response()->json(['success' => $newCategory,], 200);
    }

    /**
     * Show category by id
     * 
     * @Get("/categories/{id}")
     * @Where({"id": "\d+"})
     */
    public function show($id)
    {
        $results = DB::select('select * from categories where id = ?', [$id]);
        if (empty($results)) {
            return response()->json(['error' => ['not_found']], 404);
        }
        return response()->json(['success' => get_object_vars($results[0]),], 200);
    }

    /**
     * Update category
     * 
     * @Put("/categories/{id}")
     * @Middleware("jwt.auth")
     * @Where({"id": "\d+"})
     */
    public function update(Request $request, $id)
    {
        // TODO: via validator
        $category = DB::table('categories')
                        ->where('id', $id)->first();
        if (empty($category)) {
            return response()->json(['error' => ['not_found']], 404);
        } elseif (Auth::user()->id !== $category->owner_id) {
            return response()->json(['error' => ['not_owner']], 401);
        }

        $newCategory = $request->only('name');
        $validator = $this->categoryValidator($newCategory);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages(),], 400);
        }

        DB::table('categories')->where('id', $id)->update($newCategory);

        return response()->json(['success' => $newCategory,], 200);
    }

    /**
     * Delete category
     * 
     * @Delete("/categories/{id}")
     * @Middleware("jwt.auth")
     * @Where({"id": "\d+"})
     */
    public function destroy($id)
    {
        // TODO: via validator
        $category = DB::table('categories')->where('id', $id)->first();
        if (empty($category)) {
            return response()->json(['error' => ['not_found']], 404);
        } elseif (Auth::user()->id !== $category->owner_id) {
            return response()->json(['error' => ['not_owner']], 401);
        }

        DB::table('categories')->where('id', $id)->delete();
        return response()->json(['success' => '',], 200);
    }

}
