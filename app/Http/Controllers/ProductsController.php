<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use Validator;

/**
 * Products controller
 * 
 * @Resource("/products")
 */
class ProductsController extends Controller
{

    /**
     * Product validator
     * 
     * @param array $data
     * @return \Illuminate\Validation\Validator
     */
    protected function productValidator(array $data, $rules = [])
    {
        $id = isset($data['id'])?$data['id']:'NULL';
        return Validator::make(
            $data, 
            [
                'name' => 'required|max:255|unique:products,name,'.$id.',id,owner_id,' . $data['owner_id'],
                'category_id' => 'required|exists:categories,id',
                'owner_id' => 'required',
            ],
            [
                'required' => ':attribute is required',
                'max' => ':attribute too long',
                'unique' => ':attribute already exists',
                'exists' => ':attribute not exists'
            ]
        );
    }

    /**
     * Products list
     * 
     * @Get("/products/{sort_order}")
     * @Where({"sort_order": "(asc|desc)"})
     */
    public function index(Request $request = null, $sort_order = 'desc')
    {
        $category_id = $request->get('category_id');
        if(empty($category_id)) {
            return response()->json(['error' => 'category_id is required',], 400);
        }
        $products = DB::table('products')
                ->where('category_id', $category_id)
                ->orderBy('name', $sort_order)->get();
        return response()->json(['success' => $products,], 200);
    }

    /**
     * Create a product
     * 
     * @Middleware("jwt.auth")
     * @Post("/products")
     */
    public function store(Request $request)
    {
        $newProduct = $request->only('name', 'category_id');
        $newProduct['owner_id'] = Auth::user()->id;

        $validator = $this->productValidator($newProduct);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages(),], 400);
        }

        DB::insert(
            'insert into products (name, category_id, owner_id) values (?, ?, ?)', 
            [$newProduct['name'], $newProduct['category_id'], $newProduct['owner_id'],]
        );
        // TODO: Provide id
        return response()->json(['success' => $newProduct,], 200);
    }

    /**
     * Get product by id
     * 
     * @Get("/products/{id}")
     * @Where({"id": "\d+"})
     */
    public function show($id)
    {
        $results = DB::select('select * from products where id = ?', [$id]);
        if (empty($results)) {
            return response()->json(['error' => ['not found']], 404);
        }
        return response()->json(['success' => get_object_vars($results[0]),], 200);
    }

    /**
     * Update product
     * 
     * @Put("/products/{id}")
     * @Middleware("jwt.auth")
     * @Where({"id": "\d+"})
     */
    public function update(Request $request, $id)
    {
        // TODO: Refactor via validator
        $product = DB::table('products')
                        ->where('id', $id)->first();
        if (empty($product)) {
            return response()->json(['error' => ['not_found']], 404);
        } elseif (Auth::user()->id !== $product->owner_id) {
            return response()->json(['error' => ['not_owner']], 401);
        }

        $newData = array_filter($request->only('name', 'category_id', 'owner_id', 'product_id'));
        $newProduct = array_merge(get_object_vars($product), $newData, ['id' => $id,]);
        
        $validator = $this->productValidator($newProduct);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages(),], 400);
        }
        DB::table('products')->where('id', $id)->update($newData);

        return response()->json(['success' => $newProduct,], 200);
    }

    /**
     * Delete product
     * 
     * @Delete("/products/{id}")
     * @Middleware("jwt.auth")
     * @Where({"id": "\d+"})
     */
    public function destroy($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        if (empty($product)) {
            return response()->json(['error' => ['not_found']], 404);
        } elseif (Auth::user()->id !== $product->owner_id) {
            return response()->json(['error' => ['not_owner']], 401);
        }

        DB::table('products')->where('id', $id)->delete();
        return response()->json(['success' => '',], 200);
    }

}
