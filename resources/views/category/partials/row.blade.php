<tr class="category-row">
    <td>
        {{ $category->name }}
    </td>
    @if($category->status)
        <td>Active</td>
    @else
        <td>Inactive</td>
    @endif
    <td>
        <a class="btn btn-primary btn-sm" href="{{route('categories.edit', $category->id)}}"><i class="fa fa-edit"></i></a>
        <button class="btn btn-danger waves-effect btn-sm" type="submit" onclick="deleteTag({{ $category->id }})">
            <i class="fa fa-trash"></i>
        </button>
        <form id="delete-form-{{ $category->id }}" action="{{ route('categories.destroy',$category->id) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </td>
</tr>