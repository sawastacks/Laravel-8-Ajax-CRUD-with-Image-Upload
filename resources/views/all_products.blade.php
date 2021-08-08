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