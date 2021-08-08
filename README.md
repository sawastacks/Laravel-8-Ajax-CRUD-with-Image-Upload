# Laravel 8 Ajax CRUD with Image Upload
![Multi Step Form](img1.png)
<p>This repository will help you to learn how to make  Ajax CRUD with image file upload and preview.These  tutorials guide you step by step on how to implement Ajax CRUD app with image upload and preview using jquery in Laravel 8</p>
<p>You will need to run this command:</p>

  ```bash
      php artisan storage:link
  ```


>#### Routes
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::view('/products','products');
Route::post('/save',[ProductController::class,'save'])->name('save.product');
Route::get('/fetchProducts',[ProductController::class,'fetchProducts'])->name('fetch.products');
```
>#### Product Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['product_name','product_image'];
}

```
>#### ProductController.php
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
     public function save(Request $request){
           $validator = \Validator::make($request->all(),[
              'product_name'=>'required|string|unique:products',
              'product_image'=>'required|image'
           ],[
               'product_name.required'=>'Product name is required',
               'product_name.string'=>'Product name must be a string',
               'product_name.unique'=>'This product name is already taken',
               'product_image.required'=>'Product image is required',
               'product_image.image'=>'Product file must be an image',
           ]);

           if(!$validator->passes()){
               return response()->json(['code'=>0,'error'=>$validator->errors()->toArray()]);
           }else{
               $path = 'files/';
               $file = $request->file('product_image');
               $file_name = time().'_'.$file->getClientOriginalName();

            //    $upload = $file->storeAs($path, $file_name);
            $upload = $file->storeAs($path, $file_name, 'public');

               if($upload){
                   Product::insert([
                       'product_name'=>$request->product_name,
                       'product_image'=>$file_name,
                   ]);
                   return response()->json(['code'=>1,'msg'=>'New product has been saved successfully']);
               }
           }
     } 


     public function fetchProducts(){
         $products = Product::all();
         $data = \View::make('all_products')->with('products', $products)->render();
         return response()->json(['code'=>1,'result'=>$data]);
     }
}

```

>#### products.blade.php
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <title>Products</title>
    <link rel="stylesheet" href="{{asset('bootstrap.min.css')}}">
</head>
<body>
    
    <div class="container">
        <div class="row" style="margin-top: 50px">
            <div class="col-md-6">
                  <div class="card">
                      <div class="card-header bg-primary text-white">Add new product</div>
                      <div class="card-body">
                          <form action="{{route('save.product')}}" method="post" enctype="multipart/form-data" id="form">
                            @csrf
                              <div class="form-group">
                                  <label for="">Product name</label>
                                  <input type="text" name="product_name" class="form-control" placeholder="Enter product name">
                                  <span class="text-danger error-text product_name_error"></span>
                              </div>
                              <div class="form-group">
                                  <label for="">Product image</label>
                                  <input type="file" name="product_image" class="form-control">
                                  <span class="text-danger error-text product_image_error"></span>
                              </div>
                              <div class="img-holder"></div>
                              <button type="submit" class="btn btn-primary">Save Product</button>
                          </form>
                      </div>
                  </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">All Products</div>
                    <div class="card-body" id="AllProducts">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{asset('jquery.min.js')}}"></script>
    <script>
        $(function(){

            $('#form').on('submit', function(e){
                e.preventDefault();

                var form = this;
                $.ajax({
                    url:$(form).attr('action'),
                    method:$(form).attr('method'),
                    data:new FormData(form),
                    processData:false,
                    dataType:'json',
                    contentType:false,
                    beforeSend:function(){
                        $(form).find('span.error-text').text('');
                    },
                    success:function(data){
                        if(data.code == 0){
                            $.each(data.error, function(prefix,val){
                                $(form).find('span.'+prefix+'_error').text(val[0]);
                            });
                        }else{
                            $(form)[0].reset();
                            // alert(data.msg);
                            fetchAllProducts();
                        }
                    }
                });
            });

            //Reset input file
            $('input[type="file"][name="product_image"]').val('');
            //Image preview
            $('input[type="file"][name="product_image"]').on('change', function(){
                var img_path = $(this)[0].value;
                var img_holder = $('.img-holder');
                var extension = img_path.substring(img_path.lastIndexOf('.')+1).toLowerCase();

                if(extension == 'jpeg' || extension == 'jpg' || extension == 'png'){
                     if(typeof(FileReader) != 'undefined'){
                          img_holder.empty();
                          var reader = new FileReader();
                          reader.onload = function(e){
                              $('<img/>',{'src':e.target.result,'class':'img-fluid','style':'max-width:100px;margin-bottom:10px;'}).appendTo(img_holder);
                          }
                          img_holder.show();
                          reader.readAsDataURL($(this)[0].files[0]);
                     }else{
                         $(img_holder).html('This browser does not support FileReader');
                     }
                }else{
                    $(img_holder).empty();
                }
            });

            //Fetch all products
            fetchAllProducts();
            function fetchAllProducts(){
                $.get('{{route("fetch.products")}}',{}, function(data){
                     $('#AllProducts').html(data.result);
                },'json');
            }
    
        })
    </script>
</body>
</html>
```

>#### all_products.blade.php
```html
@forelse ($products as $item)
    <div class="media mb-4">
        <img src="/storage/files/{{$item->product_image}}" alt="" class="d-flex align-self-start rounded mr-3" height="64">
        <div class="media-body">
            <h5 class="mt-0 font-16">{{$item->product_name}}</h5>
            <div class="btn-group">
                <button class="btn btn-sm btn-primary">Edit</button>
                <button class="btn btn-sm btn-danger">Delete</button>
            </div>
        </div>
    </div>
@empty
    <code>No product found</code>
@endforelse
```